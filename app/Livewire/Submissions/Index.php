<?php

namespace App\Livewire\Submissions;

use App\Models\AssignmentSubmission;
use App\Models\AssessmentAttempt;
use App\Models\Assessment;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filter = 'all'; // 'all', 'pending', 'graded', 'overdue'
    public $submissionType = 'all'; // 'all', 'assignment', 'assessment'
    public $courseId = null;
    public $sortBy = 'submitted_at'; // 'submitted_at', 'due_date', 'title'
    public $sortOrder = 'desc';
    public $currentPage = 1;

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

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortOrder = 'asc';
        }
    }

    private function getAssignmentSubmissions()
    {
        $query = AssignmentSubmission::query();

        // Role-based filtering
        if (Auth::user()->hasRole('teacher')) {
            $query->whereHas('assignment.course', fn($q) => $q->where('instructor_id', Auth::id()));
        } else {
            $query->where('user_id', Auth::id());
        }

        if ($this->search) {
            $query->whereHas('assignment', function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            })->orWhereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        // Only show submitted assignments (not drafts)
        $query->whereIn('status', ['submitted', 'graded', 'returned']);

        if ($this->filter === 'pending') {
            $query->whereNull('graded_at')->where('status', 'submitted');
        } elseif ($this->filter === 'graded') {
            $query->whereNotNull('graded_at');
        } elseif ($this->filter === 'overdue') {
            $query->whereHas('assignment', function ($q) {
                $q->where('due_date', '<', now());
            })->whereNull('graded_at')->where('status', 'submitted');
        }

        if ($this->courseId) {
            $query->whereHas('assignment', fn($q) => $q->where('course_id', $this->courseId));
        }

        return $query->with(['assignment.course.instructor', 'user', 'grader'])->get();
    }

    private function getAssessmentSubmissions()
    {
        $query = AssessmentAttempt::query()
            ->whereHas('assessment', fn($q) => $q->where('assessment_type', 'assignment'))
            ->where('status', 'completed');

        // Role-based filtering
        if (Auth::user()->hasRole('teacher')) {
            $query->whereHas('assessment.course', fn($q) => $q->where('instructor_id', Auth::id()));
        } else {
            $query->where('user_id', Auth::id());
        }

        if ($this->search) {
            $query->whereHas('assessment', function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            })->orWhereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filter === 'pending') {
            $query->whereNull('score'); // Pending grading
        } elseif ($this->filter === 'graded') {
            $query->whereNotNull('score');
        }

        if ($this->courseId) {
            $query->whereHas('assessment', fn($q) => $q->where('course_id', $this->courseId));
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
                    'feedback' => $sub->feedback,
                    'attachments' => $sub->attachments,
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
                $answers = $attempt->answers ?? [];
                $submittedAt = isset($answers['submitted_at']) 
                    ? \Carbon\Carbon::parse($answers['submitted_at'])
                    : $attempt->completed_at;
                
                $allSubmissions->push([
                    'type' => 'assessment',
                    'id' => $attempt->id,
                    'title' => $assessment->title,
                    'course' => $assessment->course,
                    'user' => $attempt->user,
                    'submitted_at' => $submittedAt,
                    'due_date' => null, // Assessments don't have due dates in the same way
                    'status' => $attempt->score === null ? 'pending' : 'graded',
                    'graded_at' => $attempt->score !== null ? $attempt->updated_at : null,
                    'score' => $attempt->score,
                    'max_score' => $assessment->max_points ?? 100,
                    'feedback' => null,
                    'attachments' => $answers['files'] ?? [],
                    'content' => $answers['text'] ?? null,
                    'grader' => null,
                    'submission' => $attempt,
                ]);
            }
        }

        // Sort submissions
        $sortedSubmissions = $allSubmissions->sortBy(function ($sub) {
            return match($this->sortBy) {
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

        // Calculate stats
        $stats = [
            'total' => $allSubmissions->count(),
            'pending' => $allSubmissions->where('graded_at', null)->where('status', '!=', 'draft')->count(),
            'graded' => $allSubmissions->where('graded_at', '!=', null)->count(),
            'overdue' => $allSubmissions->filter(function($sub) {
                return $sub['due_date'] && $sub['due_date']->isPast() && $sub['graded_at'] === null && $sub['status'] === 'submitted';
            })->count(),
        ];

        // Get available courses for filter
        $courses = Auth::user()->hasRole('teacher')
            ? \App\Models\Course::where('instructor_id', Auth::id())->get()
            : \App\Models\Course::whereHas('enrollments', fn($q) => $q->where('user_id', Auth::id()))->get();

        return view('livewire.submissions.index', [
            'submissions' => $items,
            'stats' => $stats,
            'totalPages' => ceil($total / $perPage),
            'currentPage' => $currentPage,
            'total' => $total,
            'courses' => $courses,
        ]);
    }
}
