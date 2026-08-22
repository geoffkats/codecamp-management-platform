<?php

namespace App\Livewire\Lessons;

use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterCourse = 'all';
    public $filterModule = 'all';
    public $filterType = 'all';
    public $filterStatus = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCourse' => ['except' => 'all'],
        'filterModule' => ['except' => 'all'],
        'filterType' => ['except' => 'all'],
        'filterStatus' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Lesson::query()->with(['course', 'module']);

        // Role-based filtering
        if (Auth::user()->hasRole('teacher')) {
            $query->whereHas('course', fn($q) => $q->where('instructor_id', Auth::id()));
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('summary', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by course
        if ($this->filterCourse !== 'all') {
            $query->where('course_id', $this->filterCourse);
        }

        // Filter by module
        if ($this->filterModule !== 'all') {
            $query->where('module_id', $this->filterModule);
        }

        // Filter by type
        if ($this->filterType !== 'all') {
            $query->where('lesson_type', $this->filterType);
        }

        // Filter by status
        if ($this->filterStatus !== 'all') {
            if ($this->filterStatus === 'published') {
                $query->where('is_published', true);
            } elseif ($this->filterStatus === 'draft') {
                $query->where('is_published', false);
            }
        }

        $lessons = $query->orderBy('created_at', 'desc')->paginate(20);

        $courses = \App\Models\Course::orderBy('title')->get();
        $modules = $this->filterCourse !== 'all' 
            ? \App\Models\CourseModule::where('course_id', $this->filterCourse)->orderBy('title')->get()
            : collect();

        return view('livewire.lessons.index', [
            'lessons' => $lessons,
            'courses' => $courses,
            'modules' => $modules,
        ]);
    }
}
