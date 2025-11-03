<?php

namespace App\Livewire\Modules;

use App\Models\CourseModule;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public CourseModule $module;

    public function mount(CourseModule $module)
    {
        // Authorization check
        $user = Auth::user();
        
        // Check if user can edit this module
        if ($user->isTeacher()) {
            // Teachers can only edit modules from their own courses
            if (!$module->course || $module->course->instructor_id !== $user->id) {
                abort(403, 'You can only edit modules from your own courses.');
            }
        } elseif (!$user->isAdmin()) {
            abort(403, 'You do not have permission to edit modules.');
        }
        
        $this->module = $module->load('course');
    }

    public function render()
    {
        return view('livewire.modules.edit');
    }
}
