<?php

namespace App\Livewire\Progress;

use App\Models\CourseEnrollment;
use App\Models\StudentLessonProgress;
use App\Models\UserProgress;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Tracking extends Component
{
    public $selectedCourse = null;
    public $stats = [];
    public $courseProgress = [];
    public $recentActivity = [];

    public function mount($courseId = null)
    {
        $this->selectedCourse = $courseId;
        $this->loadStats();
    }

    public function updatedSelectedCourse()
    {
        $this->loadStats();
    }

    private function loadStats()
    {
        $user = Auth::user();

        // Overall Statistics
        $enrollments = CourseEnrollment::where('user_id', $user->id);
        if ($this->selectedCourse) {
            $enrollments->where('course_id', $this->selectedCourse);
        }
        $enrollments = $enrollments->get();

        $totalCourses = $enrollments->count();
        $completedCourses = $enrollments->whereNotNull('completed_at')->count();
        $inProgressCourses = $enrollments->whereNull('completed_at')->where('progress_percentage', '>', 0)->count();
        $avgProgress = $enrollments->avg('progress_percentage') ?? 0;

        // Lesson Progress
        $lessonProgress = StudentLessonProgress::where('user_id', $user->id);
        if ($this->selectedCourse) {
            $lessonProgress->whereHas('lesson.course', fn($q) => $q->where('id', $this->selectedCourse));
        }
        $totalLessons = $lessonProgress->count();
        $completedLessons = $lessonProgress->where('status', 'completed')->count();

        $this->stats = [
            'total_courses' => $totalCourses,
            'completed_courses' => $completedCourses,
            'in_progress_courses' => $inProgressCourses,
            'avg_progress' => round($avgProgress, 1),
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'completion_rate' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 1) : 0,
        ];

        // Course Progress Details
        $this->courseProgress = $enrollments->map(function ($enrollment) {
            return [
                'course' => $enrollment->course,
                'progress' => $enrollment->progress_percentage,
                'completed' => $enrollment->completed_at !== null,
                'lessons_completed' => $enrollment->lessons_completed ?? 0,
            ];
        })->take(10);

        // Recent Activity
        $this->recentActivity = UserProgress::where('user_id', $user->id)
            ->with(['course', 'lesson'])
            ->latest()
            ->take(10)
            ->get();
    }

    public function render()
    {
        $courses = \App\Models\Course::whereHas('enrollments', fn($q) => $q->where('user_id', Auth::id()))
            ->orderBy('title')
            ->get();

        return view('livewire.progress.tracking', [
            'courses' => $courses,
        ]);
    }
}
