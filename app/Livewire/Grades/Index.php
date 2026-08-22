<?php

namespace App\Livewire\Grades;

use App\Models\Grade;
use App\Support\ProgramScope;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filter = 'all'; // 'all', 'assignment', 'quiz', 'assessment'
    public $courseId = null;

    public function mount(): void
    {
        if (Auth::user()->isIctTeacher()) {
            redirect()->route('test-marks.index');
        }
    }

    private function applyGradeScope($query)
    {
        $user = Auth::user();

        if ($user->hasRole('student')) {
            return $query->where('user_id', $user->id);
        }

        if ($user->hasRole('teacher')) {
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('instructor_id', $user->id)
                    ->orWhereHas('collaborators', fn ($c) => $c->where('user_id', $user->id))
                    ->orWhereHas('enrollments', fn ($e) => $e->where('user_id', $user->id));
            });

            $scopedStudentIds = \App\Models\User::query()
                ->whereHas('studentProfile', fn ($q) => ProgramScope::applyStudentProfileScope($q, $user))
                ->pluck('id')
                ->all();

            if ($scopedStudentIds !== []) {
                return $query->whereIn('user_id', $scopedStudentIds);
            }

            return $query;
        }

        return $query;
    }

    public function render()
    {
        $query = Grade::query();
        $this->applyGradeScope($query);

        if ($this->search) {
            $query->whereHasMorph('gradeable', [
                \App\Models\AssignmentSubmission::class,
                \App\Models\AssessmentAttempt::class,
                \App\Models\Assignment::class,
                \App\Models\Quiz::class,
                \App\Models\Assessment::class,
            ], function ($q) {
                if (method_exists($q->getModel(), 'assignment')) {
                    $q->whereHas('assignment', fn ($aq) => $aq->where('title', 'like', '%' . $this->search . '%'));
                } elseif (method_exists($q->getModel(), 'assessment')) {
                    $q->whereHas('assessment', fn ($aq) => $aq->where('title', 'like', '%' . $this->search . '%'));
                } else {
                    $q->where('title', 'like', '%' . $this->search . '%');
                }
            });
        }

        if ($this->filter === 'assignment') {
            $query->whereIn('gradeable_type', [
                \App\Models\Assignment::class,
                \App\Models\AssignmentSubmission::class,
            ]);
        } elseif ($this->filter === 'quiz') {
            $query->where('gradeable_type', \App\Models\Quiz::class);
        } elseif ($this->filter === 'assessment') {
            $query->whereIn('gradeable_type', [
                \App\Models\Assessment::class,
                \App\Models\AssessmentAttempt::class,
            ]);
        }

        $grades = $query->with([
            'gradeable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Assignment::class             => ['course'],
                    \App\Models\Assessment::class             => ['course'],
                    \App\Models\Quiz::class                   => ['lesson.course'],
                    \App\Models\AssessmentAttempt::class      => ['assessment.course'],
                    \App\Models\AssignmentSubmission::class   => ['assignment.course'],
                ]);
            },
            'user',
            'course'
        ])
            ->orderByDesc('created_at')
            ->paginate(15);

        // Calculate stats on full dataset (not paginated)
        $statsQuery = Grade::query();
        $this->applyGradeScope($statsQuery);

        $stats = [
            'total' => $statsQuery->count(),
            'average' => $statsQuery->avg('percentage') ?? 0,
            'highest' => $statsQuery->max('percentage') ?? 0,
            'lowest' => $statsQuery->min('percentage') ?? 0,
        ];

        return view('livewire.grades.index', [
            'grades' => $grades,
            'stats' => $stats,
        ]);
    }
}
