<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;

class CourseList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterDifficulty = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Course::with(['instructor', 'modules'])
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('approval_status', $this->filterStatus);
            })
            ->when($this->filterDifficulty, function ($q) {
                $q->where('difficulty_level', $this->filterDifficulty);
            });

        return view('livewire.courses.course-list', [
            'courses' => $query->paginate(12)
        ]);
    }
}

