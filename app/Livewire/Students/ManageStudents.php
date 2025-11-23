<?php

namespace App\Livewire\Students;

use App\Models\StudentProfile;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ManageStudents extends Component
{
    use WithPagination;

    public $search = '';
    public $filterClass = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $students = StudentProfile::with(['user', 'gadgets'])
            ->when($this->search, function($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('student_id', 'like', '%' . $this->search . '%')
                  ->orWhere('parent_guardian_contact', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterClass, function($q) {
                $q->where('class_grade', $this->filterClass);
            })
            ->latest()
            ->paginate(15);

        $classes = StudentProfile::distinct()->pluck('class_grade')->filter();

        return view('livewire.students.manage-students', [
            'students' => $students,
            'classes' => $classes,
        ]);
    }
}
