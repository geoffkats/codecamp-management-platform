<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CourseShow extends Component
{
    public Course $course;
    public $enrolled = false;
    public $progress = 0;

    public function mount($id)
    {
        $this->course = Course::with(['instructor', 'modules.lessons', 'enrollments'])
            ->findOrFail($id);

        $enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->where('course_id', $this->course->id)
            ->first();

        if ($enrollment) {
            $this->enrolled = true;
            $this->progress = $enrollment->progress_percentage;
        }
    }

    public function enroll()
    {
        if (!$this->enrolled && $this->course->is_published) {
            CourseEnrollment::create([
                'user_id' => Auth::id(),
                'course_id' => $this->course->id,
                'enrolled_at' => now(),
            ]);

            $this->enrolled = true;
            session()->flash('message', 'Successfully enrolled in course!');
        }
    }

    public function render()
    {
        return view('livewire.courses.course-show');
    }
}

