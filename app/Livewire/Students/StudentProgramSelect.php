<?php

namespace App\Livewire\Students;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentProgramSelect extends Component
{
    public function mount(): void
    {
        $user = auth()->user();

        if ($user->isIctTeacher()) {
            redirect()->route('students.create-ict');
        }

        if (
            config('features.code_club', false)
            && $user->isClubFacilitator()
            && ! $user->isCodecampTrainer()
            && ! $user->isAdmin()
            && ! $user->isSupervisor()
            && ! $user->isOperationsManager()
        ) {
            redirect()->route('students.create-codeclub');
        }
    }

    public function canAccessCodeClub(): bool
    {
        if (! config('features.code_club', false)) {
            return false;
        }

        $user = auth()->user();

        return $user->hasCodeClubAccess()
            || $user->isOperationsManager();
    }

    public function render()
    {
        return view('livewire.students.student-program-select');
    }
}
