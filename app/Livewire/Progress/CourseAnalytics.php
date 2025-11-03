<?php

namespace App\Livewire\Progress;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Livewire\Component;

class CourseAnalytics extends Component
{
    public $courseId;

    public function mount($courseId)
    {
        $this->courseId = $courseId;
    }

    public function render()
    {
        $course = Course::findOrFail($this->courseId);
        
        $enrollments = CourseEnrollment::where('course_id', $this->courseId)->get();
        
        $stats = [
            'total_enrollments' => $enrollments->count(),
            'completed' => $enrollments->whereNotNull('completed_at')->count(),
            'in_progress' => $enrollments->whereNull('completed_at')->count(),
            'average_progress' => $enrollments->avg('progress_percentage'),
            'average_quiz_score' => $enrollments->avg('average_quiz_score'),
        ];

        return view('livewire.progress.course-analytics', [
            'course' => $course,
            'stats' => $stats,
            'enrollments' => $enrollments,
        ]);
    }
}

