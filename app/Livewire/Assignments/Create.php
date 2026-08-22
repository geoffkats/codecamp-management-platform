<?php

namespace App\Livewire\Assignments;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public function mount($courseId = null, $lessonId = null)
    {
        $params = ['type' => 'assignment'];

        $course = $courseId ?? request()->query('course_id');
        $lesson = $lessonId ?? request()->query('lesson_id');

        if ($course) {
            $params['course_id'] = $course;
        }

        if ($lesson) {
            $params['lesson_id'] = $lesson;
        }

        return $this->redirect(route('assessments.create', $params), navigate: true);
    }

    public function render()
    {
        return view('livewire.assignments.create-redirect');
    }
}
