<?php

namespace App\Livewire\Enrollments;

use App\Models\CourseEnrollment;
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
    public $filterStatus = 'all'; // 'all', 'active', 'completed', 'in_progress'
    public $sortBy = 'recent';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => 'all'],
        'sortBy' => ['except' => 'recent'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = CourseEnrollment::where('user_id', Auth::id())
            ->with(['course' => function ($q) {
                $q->with(['instructor', 'modules', 'lessons']);
            }]);

        // Search
        if ($this->search) {
            $query->whereHas('course', function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->filterStatus === 'completed') {
            $query->whereNotNull('completed_at');
        } elseif ($this->filterStatus === 'active') {
            $query->whereNull('completed_at')
                  ->where('progress_percentage', '>', 0);
        } elseif ($this->filterStatus === 'in_progress') {
            $query->whereNull('completed_at')
                  ->where('progress_percentage', '>', 0)
                  ->where('progress_percentage', '<', 100);
        }

        // Sorting
        match($this->sortBy) {
            'recent' => $query->latest('enrolled_at'),
            'oldest' => $query->oldest('enrolled_at'),
            'progress' => $query->orderByDesc('progress_percentage'),
            'title' => $query->join('courses', 'course_enrollments.course_id', '=', 'courses.id')
                            ->orderBy('courses.title')
                            ->select('course_enrollments.*'),
            default => $query->latest('enrolled_at'),
        };

        $enrollments = $query->paginate(12);

        // Calculate stats
        $stats = [
            'total' => CourseEnrollment::where('user_id', Auth::id())->count(),
            'active' => CourseEnrollment::where('user_id', Auth::id())
                ->whereNull('completed_at')
                ->where('progress_percentage', '>', 0)
                ->count(),
            'completed' => CourseEnrollment::where('user_id', Auth::id())
                ->whereNotNull('completed_at')
                ->count(),
            'average_progress' => CourseEnrollment::where('user_id', Auth::id())
                ->whereNull('completed_at')
                ->avg('progress_percentage') ?? 0,
        ];

        return view('livewire.enrollments.index', [
            'enrollments' => $enrollments,
            'stats' => $stats,
        ]);
    }
}
