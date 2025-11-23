<?php

namespace App\Livewire\Students;

use App\Models\StudentProfile as StudentProfileModel;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentProfile extends Component
{
    public $student;

    public function mount($student)
    {
        $this->student = StudentProfileModel::with(['user.enrollments.course', 'gadgets', 'attendance'])
            ->findOrFail($student);
    }

    public function exportPDF()
    {
        session()->flash('message', 'PDF export feature coming soon!');
    }

    public function downloadData()
    {
        session()->flash('message', 'Data download feature coming soon!');
    }

    public function render()
    {
        return view('livewire.students.student-profile');
    }
}
