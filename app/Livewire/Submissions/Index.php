<?php

namespace App\Livewire\Submissions;

use App\Models\AssignmentSubmission;
use App\Models\AssessmentAttempt;
use App\Models\Assessment;
use App\Models\CodeCamp;
use App\Models\CourseEnrollment;
use App\Support\ProgramScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    /** Assessment types shown on the submissions page (includes quizzes, not surveys). */
    private const SUBMISSION_ASSESSMENT_TYPES = [
        'assignment',
        'quiz',
        'pre_project_test',
        'post_project_test',
        'rubric_assessment',
        'peer_review',
        'self_assessment',
    ];

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filter = 'all'; // 'all', 'pending', 'graded', 'overdue'
    public $submissionType = 'all'; // 'all', 'assignment', 'assessment'
    public $courseId = null;
    public $campId = null;
    public $sortBy = 'submitted_at'; // 'submitted_at', 'due_date', 'title'
    public $sortOrder = 'desc';
    public $currentPage = 1;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => 'all'],
        'submissionType' => ['except' => 'all'],
        'courseId' => ['except' => null],
        'campId' => ['except' => null],
    ];

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function nextPage()
    {
        $this->currentPage++;
    }

    public function gotoPage($page)
    {
        $this->currentPage = $page;
    }

    public function updatingSearch()
    {
        $this->currentPage = 1;
    }

    public function updatingFilter()
    {
        $this->currentPage = 1;
    }

    public function updatingSubmissionType()
    {
        $this->currentPage = 1;
    }

    public function updatingCourseId()
    {
        $this->currentPage = 1;
    }

    public function updatingCampId()
    {
        $this->currentPage = 1;
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortOrder = 'asc';
        }
    }

    private function applyStaffCourseConstraint($query, string $relation): void
    {
        $user = Auth::user();

        $query->whereHas($relation, fn ($q) => $q->accessibleBy($user));
    }

    private function scopedStudentUserIds(): ?array
    {
        $user = Auth::user();

        // Prefer staff identity over leftover student role on converted accounts.
        if ($user->isAdmin() || $user->isSupervisor()) {
            return null;
        }

        if (! $user->isTeacher()) {
            return null;
        }

        return \App\Models\User::query()
            ->whereHas('studentProfile', fn ($q) => ProgramScope::applyStudentProfileScope($q, $user))
            ->pluck('id')
            ->all();
    }

    private function getAssignmentSubmissions()
    {
        $user = Auth::user();
        $query = AssignmentSubmission::query();
        $scopedStudentIds = $this->scopedStudentUserIds();
        $clubContext = ProgramScope::isClubFacilitatorContext($user);

        // Staff checks first — converted accounts may still have the student role.
        if ($user->isAdmin() || $user->isSupervisor()) {
            // no course ownership filter
        } elseif ($clubContext) {
            $clubStudentIds = ProgramScope::clubStudentUserIds($user);
            $query->whereIn('user_id', $clubStudentIds ?: [-1]);
        } elseif ($user->isTeacher()) {
            $this->applyStaffCourseConstraint($query, 'assignment.course');
        } elseif ($user->isStudent()) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }

        if ($scopedStudentIds !== null && ! $clubContext && $user->isTeacher()) {
            // Soft filter: if scope resolves students, apply it; never wipe course results to empty by mistake.
            if ($scopedStudentIds !== []) {
                $query->whereIn('user_id', $scopedStudentIds);
            }
        }

        if ($this->search) {
            $query->where(function ($outer) {
                $outer->whereHas('assignment', function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%');
                })->orWhereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Only show submitted assignments (not drafts)
        $query->whereIn('status', ['submitted', 'graded', 'returned']);

        if ($this->courseId) {
            $query->whereHas('assignment', fn ($q) => $q->where('course_id', $this->courseId));
        }

        if ($this->campId) {
            $query->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('course_enrollments')
                    ->join('assignments', 'assignments.course_id', '=', 'course_enrollments.course_id')
                    ->whereColumn('assignments.id', 'assignment_submissions.assignment_id')
                    ->whereColumn('course_enrollments.user_id', 'assignment_submissions.user_id')
                    ->where('course_enrollments.camp_id', $this->campId);
            });
        }

        return $query->with(['assignment.course.instructor', 'user', 'grader'])->get();
    }

    private function getAssessmentSubmissions()
    {
        $user = Auth::user();
        $scopedStudentIds = $this->scopedStudentUserIds();
        $clubContext = ProgramScope::isClubFacilitatorContext($user);

        $query = AssessmentAttempt::query()
            ->whereHas('assessment', fn ($q) => $q->whereIn('assessment_type', self::SUBMISSION_ASSESSMENT_TYPES))
            ->where(function ($q) {
                $q->where('status', 'completed')
                    ->orWhereNotNull('completed_at');
            });

        if ($user->isAdmin() || $user->isSupervisor()) {
            // unscoped
        } elseif ($clubContext) {
            $clubStudentIds = ProgramScope::clubStudentUserIds($user);
            $query->whereIn('user_id', $clubStudentIds ?: [-1]);
        } elseif ($user->isIctTeacher()) {
            $query->visibleTo($user);
        } elseif ($user->isTeacher()) {
            $query->where(function ($q) use ($user) {
                $q->where(function ($st) {
                    $st->whereNull('student_type')
                        ->orWhere('student_type', '!=', 'ict');
                })->whereHas('assessment.course', fn ($courseQuery) => $courseQuery->accessibleBy($user));
            });

            if ($scopedStudentIds !== null && $scopedStudentIds !== []) {
                $query->whereIn('user_id', $scopedStudentIds);
            }
        } elseif ($user->isStudent()) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }

        if ($this->search) {
            $query->where(function ($outer) {
                $outer->whereHas('assessment', function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%');
                })->orWhereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }

        if ($this->courseId) {
            $query->whereHas('assessment', fn ($q) => $q->where('course_id', $this->courseId));
        }

        if ($this->campId) {
            $query->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('course_enrollments')
                    ->join('assessments', 'assessments.course_id', '=', 'course_enrollments.course_id')
                    ->whereColumn('assessments.id', 'assessment_attempts.assessment_id')
                    ->whereColumn('course_enrollments.user_id', 'assessment_attempts.user_id')
                    ->where('course_enrollments.camp_id', $this->campId);
            });
        }

        return $query->with(['assessment.course.instructor', 'user'])->get();
    }

    public function render()
    {
        $allSubmissions = collect();

        // Get assignment submissions
        if ($this->submissionType === 'all' || $this->submissionType === 'assignment') {
            $assignmentSubmissions = $this->getAssignmentSubmissions();
            foreach ($assignmentSubmissions as $sub) {
                $allSubmissions->push([
                    'type' => 'assignment',
                    'id' => $sub->id,
                    'title' => $sub->assignment->title,
                    'course' => $sub->assignment->course,
                    'user' => $sub->user,
                    'submitted_at' => $sub->submitted_at,
                    'due_date' => $sub->assignment->due_date,
                    'status' => $sub->status,
                    'graded_at' => $sub->graded_at,
                    'score' => $sub->points_earned,
                    'max_score' => $sub->assignment->max_points ?? 100,
                    'percentage' => ($sub->graded_at && ($sub->assignment->max_points ?? 0) > 0)
                        ? round(((float) $sub->points_earned / $sub->assignment->max_points) * 100, 1)
                        : null,
                    'feedback' => $sub->feedback,
                    'attachments' => $this->normalizeAttachmentList($sub->attachments),
                    'content' => $sub->content,
                    'grader' => $sub->grader,
                    'submission' => $sub,
                ]);
            }
        }

        // Get assessment assignment submissions
        if ($this->submissionType === 'all' || $this->submissionType === 'assessment') {
            $assessmentSubmissions = $this->getAssessmentSubmissions();
            foreach ($assessmentSubmissions as $attempt) {
                $assessment = $attempt->assessment;
                if (! $assessment) {
                    continue;
                }

                $answers = $attempt->answers ?? [];
                $submittedAt = isset($answers['submitted_at'])
                    ? \Carbon\Carbon::parse($answers['submitted_at'])
                    : ($attempt->completed_at ?? $attempt->updated_at);

                $allSubmissions->push([
                    'type' => 'assessment',
                    'id' => $attempt->id,
                    'title' => $assessment->title,
                    'course' => $assessment->course,
                    'user' => $attempt->user,
                    'submitted_at' => $submittedAt,
                    'due_date' => $assessment->due_date,
                    'status' => $attempt->score === null ? 'pending' : 'graded',
                    'graded_at' => $attempt->score !== null ? $attempt->updated_at : null,
                    'score' => $attempt->scoreAsPoints(),
                    'max_score' => $attempt->maxScore(),
                    'percentage' => $attempt->scorePercentage(),
                    'feedback' => $attempt->graderFeedback(),
                    'attachments' => $this->normalizeAttachmentList($attempt->submissionFiles()),
                    'content' => $attempt->submissionText() ?: null,
                    'grader' => null,
                    'submission' => $attempt,
                ]);
            }
        }

        $stats = [
            'total' => $allSubmissions->count(),
            'pending' => $allSubmissions->where('graded_at', null)->where('status', '!=', 'draft')->count(),
            'graded' => $allSubmissions->where('graded_at', '!=', null)->count(),
            'overdue' => $allSubmissions->filter(function ($sub) {
                return $sub['due_date'] && $sub['due_date']->isPast() && $sub['graded_at'] === null && $sub['status'] !== 'draft';
            })->count(),
        ];

        $filteredSubmissions = match ($this->filter) {
            'pending' => $allSubmissions->filter(fn ($sub) => $sub['graded_at'] === null && $sub['status'] !== 'draft'),
            'graded' => $allSubmissions->filter(fn ($sub) => $sub['graded_at'] !== null),
            'overdue' => $allSubmissions->filter(function ($sub) {
                return $sub['due_date'] && $sub['due_date']->isPast() && $sub['graded_at'] === null && $sub['status'] !== 'draft';
            }),
            default => $allSubmissions,
        };

        $sortedSubmissions = $filteredSubmissions->sortBy(function ($sub) {
            return match ($this->sortBy) {
                'title' => strtolower($sub['title']),
                'due_date' => $sub['due_date']?->timestamp ?? 0,
                default => $sub['submitted_at']?->timestamp ?? 0,
            };
        });

        if ($this->sortOrder === 'desc') {
            $sortedSubmissions = $sortedSubmissions->reverse();
        }

        // Paginate manually
        $perPage = 15;
        $currentPage = $this->currentPage;
        $items = $sortedSubmissions->forPage($currentPage, $perPage);
        $total = $sortedSubmissions->count();

        // Get available courses for filter
        $user = Auth::user();
        $clubContext = ProgramScope::isClubFacilitatorContext($user);
        $clubStudentIds = $clubContext ? ProgramScope::clubStudentUserIds($user) : [];

        $courses = match (true) {
            $user->isAdmin() || $user->isSupervisor() => \App\Models\Course::query()->orderBy('title')->get(),
            $clubContext => \App\Models\Course::whereHas('enrollments', fn ($q) => $q->whereIn('user_id', $clubStudentIds ?: [-1]))->orderBy('title')->get(),
            $user->isTeacher() => \App\Models\Course::accessibleBy($user)->orderBy('title')->get(),
            default => \App\Models\Course::whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id))->orderBy('title')->get(),
        };

        $showCampFilter = ProgramScope::context($user) !== 'codeclub';

        $camps = $showCampFilter
            ? CodeCamp::orderByDesc('start_date')->get(['id', 'name'])
            : collect();

        return view('livewire.submissions.index', [
            'submissions' => $items,
            'stats' => $stats,
            'totalPages' => ceil($total / $perPage),
            'currentPage' => $currentPage,
            'total' => $total,
            'courses' => $courses,
            'camps' => $camps,
            'showCampFilter' => $showCampFilter,
        ]);
    }

    /**
     * Normalize mixed attachment payloads (string paths or {path, name} arrays)
     * into a consistent list for Storage::url() / display.
     *
     * @param  mixed  $attachments
     * @return array<int, array{path: string, name: string}>
     */
    private function normalizeAttachmentList($attachments): array
    {
        if (! is_array($attachments) || $attachments === []) {
            return [];
        }

        $normalized = [];

        foreach ($attachments as $file) {
            if (is_string($file) && $file !== '') {
                $normalized[] = [
                    'path' => $file,
                    'name' => basename($file),
                ];
                continue;
            }

            if (! is_array($file)) {
                continue;
            }

            $path = $file['path'] ?? $file['url'] ?? null;
            if (! is_string($path) || $path === '') {
                continue;
            }

            $name = $file['name'] ?? $file['original_name'] ?? basename($path);
            $normalized[] = [
                'path' => $path,
                'name' => is_string($name) && $name !== '' ? $name : basename($path),
            ];
        }

        return $normalized;
    }
}
