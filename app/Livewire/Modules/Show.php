<?php

namespace App\Livewire\Modules;

use App\Models\CourseModule;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public CourseModule $module;

    public function mount(CourseModule $module): void
    {
        $this->module = $module->load(['course.schools', 'lessons']);

        $user = Auth::user();
        if ($user?->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();

            $hasAccess = $schoolId
                && $this->module->course
                && $this->module->course->schools
                    ->where('id', (int) $schoolId)
                    ->where('pivot.is_active', true)
                    ->isNotEmpty();

            if (!$hasAccess) {
                abort(403, 'Unauthorized module access.');
            }
        }
    }

    public function render()
    {
        $view = Auth::user()?->isIctTeacher()
            ? 'livewire.modules.show-ict'
            : 'livewire.modules.show';

        return view($view, [
            'module' => $this->module,
        ]);
    }
}
