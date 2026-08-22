<?php

namespace App\Livewire\Certificates;

use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filterCourse = 'all';
    public $search = '';

    protected $queryString = [
        'filterCourse' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Certificate::where('user_id', Auth::id())
            ->with(['course', 'user']);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('certificate_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('course', function ($courseQuery) {
                      $courseQuery->where('title', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filter by course
        if ($this->filterCourse !== 'all') {
            $query->where('course_id', $this->filterCourse);
        }

        $certificates = $query->orderByDesc('issued_at')->paginate(12);

        // Calculate stats
        $stats = [
            'total' => Certificate::where('user_id', Auth::id())->count(),
            'verified' => Certificate::where('user_id', Auth::id())
                ->where('is_verified', true)
                ->count(),
            'expired' => Certificate::where('user_id', Auth::id())
                ->where('expires_at', '<', now())
                ->count(),
            'active' => Certificate::where('user_id', Auth::id())
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
                })
                ->where('is_verified', true)
                ->count(),
        ];

        // Get available courses for filter (issued certificates only)
        $availableCourses = Certificate::where('user_id', Auth::id())
            ->with('course')
            ->get()
            ->pluck('course')
            ->unique('id')
            ->filter()
            ->sortBy('title')
            ->values();

        // Prepare course options for select
        $courseOptions = ['all' => 'All Courses'];
        foreach ($availableCourses as $course) {
            if ($course) {
                $courseOptions[$course->id] = $course->title;
            }
        }

        return view('livewire.certificates.index', [
            'certificates' => $certificates,
            'stats' => $stats,
            'availableCourses' => $availableCourses,
            'courseOptions' => $courseOptions,
        ]);
    }
}
