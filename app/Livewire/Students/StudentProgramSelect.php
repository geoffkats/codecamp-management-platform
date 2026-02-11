<?php

namespace App\Livewire\Students;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentProgramSelect extends Component
{
    public function mount(): void
    {
        if (auth()->user()->isIctTeacher()) {
            redirect()->route('students.create-ict');
        }
    }

    public function render()
    {
        return view('livewire.students.student-program-select');
    }
}
