<?php

namespace App\Livewire\Courses;

use App\Models\Course;
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
    public $filterStatus = 'all';
    public $filterCategory = 'all';
    public $filterDifficulty = 'all';
    public $sortBy = 'latest';
    public $viewMode = 'grid'; // 'grid' or 'list'

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => 'all'],
        'filterCategory' => ['except' => 'all'],
        'filterDifficulty' => ['except' => 'all'],
        'sortBy' => ['except' => 'latest'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    public function updatingFilterDifficulty()
    {
        $this->resetPage();
    }

    public function filterByCategory($category)
    {
        $this->filterCategory = $category;
        $this->resetPage();
    }

    public function filterByDifficulty($difficulty)
    {
        $this->filterDifficulty = $difficulty;
        $this->resetPage();
    }

    public function sort($sort)
    {
        $this->sortBy = $sort;
        $this->resetPage();
    }

    public function toggleView()
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'list' : 'grid';
    }

    public function mount(): void
    {
        if (!Auth::user()?->canAccessCourseCatalog()) {
            $this->redirect(route('enrollments.index'), navigate: true);
        }
    }

    public function render()
    {
        $query = Course::query()
            ->select('courses.*')
            ->with(['instructor:id,name'])
            ->withCount(['enrollments', 'lessons']);

        // Role-based filtering
        if (Auth::user()?->hasRole('teacher')) {
            $user = Auth::user();

            if ($user->isIctTeacher()) {
                $schoolId = $user->ictSchoolId();

                if ($schoolId) {
                    $query->whereHas('schools', function ($q) use ($schoolId) {
                        $q->where('school_id', $schoolId)->where('is_active', true);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->accessibleBy($user);
            }
        } else {
            // For other users, only show published and approved courses
            $query->where('is_published', true)
                  ->where('approval_status', 'approved');
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->filterStatus && $this->filterStatus !== 'all') {
            if ($this->filterStatus === 'published') {
                $query->where('is_published', true);
            } elseif ($this->filterStatus === 'draft') {
                $query->where('is_published', false);
            } elseif (in_array($this->filterStatus, ['pending', 'approved', 'rejected'])) {
                $query->where('approval_status', $this->filterStatus);
            }
        }

        // Category filter
        if ($this->filterCategory && $this->filterCategory !== 'all') {
            $query->where('category', $this->filterCategory);
        }

        // Difficulty filter
        if ($this->filterDifficulty && $this->filterDifficulty !== 'all') {
            $query->where('difficulty_level', ucfirst($this->filterDifficulty));
        }

        // Sorting
        match($this->sortBy) {
            'latest' => $query->latest(),
            'popular' => $query->orderBy('enrollments_count', 'desc'),
            'title' => $query->orderBy('title'),
            'duration' => $query->orderBy('estimated_duration'),
            default => $query->latest(),
        };

        // Use simplePaginate for better performance if total count isn't needed
        $courses = $query->paginate(12);

        // Cache categories list for 1 hour to reduce query load
        $categories = cache()->remember('course_categories_list', 3600, function () {
            return Course::where('is_published', true)
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->filter(fn($cat) => is_string($cat) && !empty($cat))
                ->sort()
                ->values();
        });

        // Prepare category options array - format for Flux select (key => label)
        $categoryOptions = ['all' => 'All Categories'];
        foreach ($categories as $category) {
            if (is_string($category) && !empty($category)) {
                $categoryOptions[$category] = $category;
            }
        }

        // Prepare status options - format for Flux select (key => label)
        $statusOptions = [
            'all' => 'All Status',
            'published' => 'Published',
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];

        // Prepare difficulty options - format for Flux select (key => label)
        $difficultyOptions = [
            'all' => 'All Levels',
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
        ];

        $difficulties = ['Beginner', 'Intermediate', 'Advanced'];

        $isIctTeacher = Auth::user()?->isIctTeacher() ?? false;

        return view('livewire.courses.index', [
            'courses' => $courses,
            'categories' => $categories,
            'categoryOptions' => $categoryOptions,
            'statusOptions' => $statusOptions,
            'difficultyOptions' => $difficultyOptions,
            'difficulties' => $difficulties,
            'isIctTeacher' => $isIctTeacher,
            'pageTitle' => $isIctTeacher ? 'ICT Modules' : 'Courses',
            'pageSubtitle' => $isIctTeacher
                ? 'Modules available for your school'
                : 'Manage the course catalog',
        ]);
    }
}
