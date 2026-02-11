<?php

namespace App\Livewire\Modules;

use App\Models\CourseModule;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterStatus = 'all';
    public $sortBy = 'latest';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => 'all'],
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

    public function render()
    {
        $query = CourseModule::query()
            ->with([
                'course' => function ($courseQuery) {
                    $courseQuery
                        ->select('id', 'title')
                        ->withCount('enrollments')
                        ->withAvg('enrollments', 'progress_percentage');
                },
            ])
            ->withCount('lessons');

        $user = Auth::user();

        if ($user?->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();

            if ($schoolId) {
                $query->whereHas('course.schools', function ($schoolQuery) use ($schoolId) {
                    $schoolQuery->where('school_id', $schoolId)->where('is_active', true);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('overview', 'like', '%' . $this->search . '%')
                    ->orWhereHas('course', function ($courseQuery) {
                        $courseQuery->where('title', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterStatus && $this->filterStatus !== 'all') {
            if ($this->filterStatus === 'active') {
                $query->where('is_active', true);
            } elseif ($this->filterStatus === 'inactive') {
                $query->where('is_active', false);
            }
        }

        match ($this->sortBy) {
            'title' => $query->orderBy('title'),
            'active' => $query->orderBy('is_active', 'desc'),
            default => $query->latest(),
        };

        $modules = $query->paginate(12);

        $view = $user?->isIctTeacher()
            ? 'livewire.modules.index-ict'
            : 'livewire.modules.index';

        return view($view, [
            'modules' => $modules,
        ]);
    }
}
