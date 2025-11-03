<?php

namespace App\Livewire\Lessons;

use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Lesson $lesson;

    public function mount(Lesson $lesson)
    {
        // Authorization check
        $user = Auth::user();
        
        // Check if user can edit this lesson
        if ($user->isTeacher()) {
            // Teachers can only edit lessons from their own courses
            $course = $lesson->module->course ?? null;
            if (!$course || $course->instructor_id !== $user->id) {
                abort(403, 'You can only edit lessons from your own courses.');
            }
        } elseif (!$user->isAdmin()) {
            abort(403, 'You do not have permission to edit lessons.');
        }
        
        $this->lesson = $lesson->load('module.course');
    }

    public function render()
    {
        return view('livewire.lessons.edit');
    }
}
