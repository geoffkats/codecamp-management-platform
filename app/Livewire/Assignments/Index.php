<?php

namespace App\Livewire\Assignments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Support\ProgramScope;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $filter = 'all';
    public string $search = '';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $isTeacher = $user->hasAnyRole(['teacher', 'admin', 'supervisor', 'operations_manager']);
        $clubContext = ProgramScope::isClubFacilitatorContext($user);
        $clubStudentIds = $clubContext ? ProgramScope::clubStudentUserIds($user) : [];

        // All assignment-type assessments visible to this user
        $query = Assessment::where('assessment_type', 'assignment');

        if ($user->hasRole('student')) {
            // Students only see unlocked assignments from courses they're enrolled in
            $query->where('is_locked', false)
                ->where(function ($lessonLock) {
                    $lessonLock->whereNull('lesson_id')
                        ->orWhereHas('lesson', fn ($lessonQuery) => $lessonQuery->where('is_locked', false));
                })
                ->whereHas('course.enrollments', fn ($q) => $q->where('user_id', $user->id));
        } elseif ($clubContext) {
            $query->whereHas('course.enrollments', fn ($q) => $q->whereIn('user_id', $clubStudentIds ?: [-1]));
        } elseif (!$isTeacher) {
            $query->whereRaw('0=1'); // nothing for unknown roles
        } elseif ($user->hasRole('teacher')) {
            // Regular teachers see their own courses only
            if (!$user->hasAnyRole(['admin', 'supervisor'])) {
                $query->whereHas('course', fn ($q) => $q->where('instructor_id', $user->id));
            }
        }

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")->orWhereHas('course', fn ($q2) => $q2->where('title', 'like', "%{$s}%")));
        }

        $assignments = $query->with(['course', 'lesson', 'questions'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // For students: attach their own latest attempt to each assignment
        $myAttempts = collect();
        if ($user->hasRole('student')) {
            $myAttempts = AssessmentAttempt::where('user_id', $user->id)
                ->whereIn('assessment_id', $assignments->pluck('id'))
                ->latest('completed_at')
                ->get()
                ->keyBy('assessment_id');
        }

        // For teachers: attach submission counts
        $submissionCounts = collect();
        $pendingCounts = collect();
        if ($isTeacher) {
            $ids = $assignments->pluck('id');
            $submissionCounts = AssessmentAttempt::whereIn('assessment_id', $ids)
                ->selectRaw('assessment_id, count(*) as total')
                ->groupBy('assessment_id')
                ->pluck('total', 'assessment_id');

            $pendingCounts = AssessmentAttempt::whereIn('assessment_id', $ids)
                ->whereNull('score')
                ->whereNotNull('completed_at')
                ->selectRaw('assessment_id, count(*) as total')
                ->groupBy('assessment_id')
                ->pluck('total', 'assessment_id');
        }

        // Apply student-side filters after we have attempt data
        if ($user->hasRole('student') && $this->filter !== 'all') {
            $assignments->getCollection()->transform(function ($a) use ($myAttempts) {
                $a->myAttempt = $myAttempts->get($a->id);
                return $a;
            });

            $filtered = $assignments->getCollection()->filter(function ($a) {
                return match ($this->filter) {
                    'submitted' => $a->myAttempt !== null,
                    'pending'   => $a->myAttempt === null,
                    default     => true,
                };
            });
            $assignments->setCollection($filtered);
        } else {
            $assignments->getCollection()->transform(function ($a) use ($myAttempts, $submissionCounts, $pendingCounts) {
                $a->myAttempt        = $myAttempts->get($a->id);
                $a->submissionCount  = $submissionCounts->get($a->id, 0);
                $a->pendingCount     = $pendingCounts->get($a->id, 0);
                return $a;
            });
        }

        return view('livewire.assignments.index', [
            'assignments' => $assignments,
            'isTeacher'   => $isTeacher || $clubContext,
        ]);
    }
}
