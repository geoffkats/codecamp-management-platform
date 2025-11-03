<?php

namespace App\Livewire\Assignments;

use App\Models\Assignment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Assignment $assignment;

    public function mount(Assignment $assignment)
    {
        // Authorization check
        $user = Auth::user();
        
        // Check if user can edit this assignment
        if ($user->isTeacher()) {
            // Teachers can only edit assignments from their own courses
            if (!$assignment->course || $assignment->course->instructor_id !== $user->id) {
                abort(403, 'You can only edit assignments from your own courses.');
            }
        } elseif (!$user->isAdmin()) {
            abort(403, 'You do not have permission to edit assignments.');
        }
        
        $this->assignment = $assignment->load('course');
    }

    public function render()
    {
        return view('livewire.assignments.edit');
    }
}
