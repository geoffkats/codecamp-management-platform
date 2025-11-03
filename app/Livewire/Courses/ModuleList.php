<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\CourseModule;
use Livewire\Component;
use Livewire\WithPagination;

class ModuleList extends Component
{
    use WithPagination;

    public $courseId;
    public $search = '';

    public function mount($courseId)
    {
        $this->courseId = $courseId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($moduleId)
    {
        CourseModule::findOrFail($moduleId)->delete();
        session()->flash('message', 'Module deleted successfully!');
    }

    public function render()
    {
        $course = Course::findOrFail($this->courseId);
        
        $query = CourseModule::where('course_id', $this->courseId)
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            })
            ->orderBy('order_index');

        return view('livewire.courses.module-list', [
            'course' => $course,
            'modules' => $query->paginate(12)
        ]);
    }
}

