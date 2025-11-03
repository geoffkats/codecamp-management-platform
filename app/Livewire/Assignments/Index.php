<?php

namespace App\Livewire\Assignments;

use App\Models\Assignment;
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
    public $filter = 'all'; // 'all', 'pending', 'submitted', 'graded'
    public $courseId = null;

    public function render()
    {
        $query = Assignment::query();

        // Role-based filtering
        if (Auth::user()->hasRole('student')) {
            // Students see assignments from enrolled courses
            $query->whereHas('course.enrollments', fn($q) => $q->where('user_id', Auth::id()));
        } elseif (Auth::user()->hasRole('teacher')) {
            // Teachers see assignments from their courses
            $query->whereHas('course', fn($q) => $q->where('instructor_id', Auth::id()));
        }

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        if ($this->courseId) {
            $query->where('course_id', $this->courseId);
        }

        $assignments = $query->with(['course', 'lesson'])
            ->orderByDesc('due_date')
            ->paginate(12);

        // Get user submissions for students
        $submissions = collect(); // Initialize as empty Collection
        if (Auth::user()->hasRole('student')) {
            $assignmentIds = $assignments->pluck('id')->toArray();
            if (!empty($assignmentIds)) {
                $submissions = \App\Models\AssignmentSubmission::where('user_id', Auth::id())
                    ->whereIn('assignment_id', $assignmentIds)
                    ->get()
                    ->keyBy('assignment_id');
            }
        }

        $assignments->getCollection()->transform(function ($assignment) use ($submissions) {
            $assignment->submission = $submissions->get($assignment->id);
            return $assignment;
        });

        return view('livewire.assignments.index', [
            'assignments' => $assignments,
        ]);
    }
}
