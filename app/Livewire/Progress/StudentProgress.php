<?php

namespace App\Livewire\Progress;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\LessonProgress;
use App\Models\StudentLessonProgress;
use App\Models\UserProgress;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout as LivewireLayout;
use Livewire\Component;

#[LivewireLayout('components.layouts.app')]
class StudentProgress extends Component
{
    public $courseId = null;
    public $selectedCourse = null;
    public $userId;
    public $filterStatus = 'all';
    public $viewMode = 'overview';

    public function mount($courseId = null, $userId = null)
    {
        $this->userId = $userId ?? Auth::id();
        $this->courseId = $courseId;
        
        if ($courseId) {
            $this->selectedCourse = Course::with(['modules.lessons', 'enrollments' => fn($q) => $q->where('user_id', $this->userId)])
                ->findOrFail($courseId);
        }
    }

    public function selectCourse($courseId)
    {
        $this->courseId = $courseId;
        $this->selectedCourse = Course::with(['modules.lessons', 'enrollments' => fn($q) => $q->where('user_id', $this->userId)])
            ->findOrFail($courseId);
    }

    public function render()
    {
        $user = \App\Models\User::find($this->userId);
        
        $enrollments = CourseEnrollment::where('user_id', $this->userId)
            ->with(['course' => function ($q) {
                $q->with(['modules.lessons', 'instructor']);
            }])
            ->when($this->courseId, fn($q) => $q->where('course_id', $this->courseId))
            ->orderBy('enrolled_at', 'desc')
            ->get();

        $detailedProgress = null;
        if ($this->selectedCourse) {
            $lessonProgress = LessonProgress::where('user_id', $this->userId)
                ->whereHas('lesson', fn($q) => $q->where('course_id', $this->selectedCourse->id))
                ->with('lesson')
                ->get()
                ->keyBy('lesson_id');

            $studentLessonProgress = StudentLessonProgress::where('user_id', $this->userId)
                ->whereHas('lesson', fn($q) => $q->where('course_id', $this->selectedCourse->id))
                ->with('lesson')
                ->get()
                ->keyBy('lesson_id');

            $detailedProgress = [
                'lessons' => $lessonProgress,
                'studentProgress' => $studentLessonProgress,
            ];
        }

        $stats = [
            'totalCourses' => $enrollments->count(),
            'completedCourses' => $enrollments->whereNotNull('completed_at')->count(),
            'inProgressCourses' => $enrollments->whereNull('completed_at')->where('progress_percentage', '>', 0)->count(),
            'totalLessons' => $enrollments->sum('lessons_completed'),
            'averageProgress' => $enrollments->avg('progress_percentage') ?? 0,
            'averageScore' => $enrollments->avg('average_quiz_score') ?? 0,
        ];

        $recentActivity = UserProgress::where('user_id', $this->userId)
            ->with(['course', 'lesson'])
            ->latest()
            ->take(10)
            ->get();

        $learningStreak = $this->calculateLearningStreak($this->userId);

        return view('livewire.progress.student-progress', [
            'user' => $user,
            'enrollments' => $enrollments,
            'selectedCourse' => $this->selectedCourse,
            'detailedProgress' => $detailedProgress,
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'learningStreak' => $learningStreak,
        ]);
    }

    private function calculateLearningStreak($userId): array
    {
        $activities = UserProgress::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date')
            ->distinct()
            ->pluck('date')
            ->map(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        $streak = 0;
        $checkDate = now()->toDateString();

        while (in_array($checkDate, $activities)) {
            $streak++;
            $checkDate = \Carbon\Carbon::parse($checkDate)->subDay()->toDateString();
        }

        return [
            'current' => $streak,
            'longest' => $streak,
            'lastActivity' => !empty($activities) ? $activities[0] : null,
        ];
    }
}
