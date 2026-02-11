<?php

namespace App\Livewire\Students;

use App\Models\StudentProfile as StudentProfileModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentProfile extends Component
{
    use AuthorizesRequests;

    public $student;

    public function mount($student)
    {
        $this->student = StudentProfileModel::with(['user.enrollments.course', 'gadgets', 'attendance', 'school'])
            ->findOrFail($student);

        $this->authorize('view', $this->student);
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
        $view = auth()->user()?->isIctTeacher()
            ? 'livewire.students.student-profile-ict'
            : 'livewire.students.student-profile';

        return view($view);
    }
}
