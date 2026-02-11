<?php

namespace App\Livewire\Grades;

use App\Models\Grade;
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

    public function render()
    {
        $query = Grade::query();

        // Role-based filtering
        if (Auth::user()->hasRole('student')) {
            $query->where('user_id', Auth::id());
        } elseif (Auth::user()->hasRole('teacher')) {
            $query->whereHas('course', fn($q) => $q->where('instructor_id', Auth::id()));
        }

        if ($this->search) {
            $query->whereHasMorph('gradeable', [\App\Models\Assignment::class, \App\Models\Quiz::class, \App\Models\Assessment::class], function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filter === 'assignment') {
            $query->where('gradeable_type', \App\Models\Assignment::class);
        } elseif ($this->filter === 'quiz') {
            $query->where('gradeable_type', \App\Models\Quiz::class);
        } elseif ($this->filter === 'assessment') {
            $query->where('gradeable_type', \App\Models\Assessment::class);
        }

        $grades = $query->with([
            'gradeable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Assignment::class => ['course'],
                    \App\Models\Assessment::class => ['course'],
                    \App\Models\Quiz::class => ['lesson.course'],
                ]);
            },
            'user',
            'course'
        ])
            ->orderByDesc('created_at')
            ->paginate(15);

        // Calculate stats on full dataset (not paginated)
        $statsQuery = Grade::query();
        if (Auth::user()->hasRole('student')) {
            $statsQuery->where('user_id', Auth::id());
        } elseif (Auth::user()->hasRole('teacher')) {
            $statsQuery->whereHas('course', fn($q) => $q->where('instructor_id', Auth::id()));
        }

        $stats = [
            'total' => $statsQuery->count(),
            'average' => $statsQuery->avg('score') ?? 0,
            'highest' => $statsQuery->max('score') ?? 0,
            'lowest' => $statsQuery->min('score') ?? 0,
        ];

        return view('livewire.grades.index', [
            'grades' => $grades,
            'stats' => $stats,
        ]);
    }
}
