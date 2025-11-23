<?php

namespace App\Livewire\Analytics;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public $timeRange = '30'; // days
    public $courseId = null;

    public function render()
    {
        $user = Auth::user();

        if ($user->hasRole('teacher')) {
            return $this->renderTeacherAnalytics($user);
        } elseif ($user->hasRole('admin')) {
            return $this->renderAdminAnalytics();
        } else {
            return $this->renderStudentAnalytics($user);
        }
    }

    private function renderTeacherAnalytics($user)
    {
        $courses = Course::where('instructor_id', $user->id)->get();
        $courseIds = $courses->pluck('id')->toArray();

        $stats = [
            'total_courses' => $courses->count(),
            'total_enrollments' => CourseEnrollment::whereIn('course_id', $courseIds)->count(),
            'active_students' => CourseEnrollment::whereIn('course_id', $courseIds)
                ->whereNull('completed_at')
                ->distinct('user_id')
                ->count('user_id'),
            'completion_rate' => $this->calculateCompletionRate($courseIds),
            'average_score' => $this->calculateAverageScore($courseIds),
        ];

        $enrollmentTrends = $this->getEnrollmentTrends($courseIds);
        $coursePerformance = $this->getCoursePerformance($courses);

        return view('livewire.analytics.dashboard', [
            'role' => 'teacher',
            'stats' => $stats,
            'enrollmentTrends' => $enrollmentTrends,
            'coursePerformance' => $coursePerformance,
            'courses' => $courses,
        ]);
    }

    private function renderAdminAnalytics()
    {
        $stats = [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_enrollments' => CourseEnrollment::count(),
            'completion_rate' => $this->calculateCompletionRate(),
            'active_students' => User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count(),
        ];

        $userGrowth = $this->getUserGrowth();
        $coursePerformance = $this->getCoursePerformance();

        return view('livewire.analytics.dashboard', [
            'role' => 'admin',
            'stats' => $stats,
            'userGrowth' => $userGrowth,
            'coursePerformance' => $coursePerformance,
        ]);
    }

    private function renderStudentAnalytics($user)
    {
        $enrollments = CourseEnrollment::where('user_id', $user->id)->get();

        $stats = [
            'total_courses' => $enrollments->count(),
            'completed_courses' => $enrollments->whereNotNull('completed_at')->count(),
            'average_progress' => $enrollments->avg('progress_percentage') ?? 0,
            'average_score' => $enrollments->avg('average_quiz_score') ?? 0,
        ];

        $progressTrend = $this->getProgressTrend($user->id);

        return view('livewire.analytics.dashboard', [
            'role' => 'student',
            'stats' => $stats,
            'progressTrend' => $progressTrend,
        ]);
    }

    private function calculateCompletionRate($courseIds = null)
    {
        $query = CourseEnrollment::query();
        if ($courseIds) {
            $query->whereIn('course_id', $courseIds);
        }
        $total = $query->count();
        $completed = $query->whereNotNull('completed_at')->count();
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    private function calculateAverageScore($courseIds = null)
    {
        $query = CourseEnrollment::query();
        if ($courseIds) {
            $query->whereIn('course_id', $courseIds);
        }
        return round($query->avg('average_quiz_score') ?? 0, 2);
    }

    private function getEnrollmentTrends($courseIds)
    {
        return CourseEnrollment::whereIn('course_id', $courseIds)
            ->where('created_at', '>=', now()->subDays((int)$this->timeRange))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => ['date' => $item->date, 'count' => $item->count]);
    }

    private function getUserGrowth()
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return User::where('created_at', '>=', now()->subDays((int)$this->timeRange))
                ->selectRaw("strftime('%Y-%m-%d', created_at) as date, COUNT(*) as count")
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => ['date' => $item->date, 'count' => $item->count]);
        } else {
            return User::where('created_at', '>=', now()->subDays((int)$this->timeRange))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => ['date' => $item->date, 'count' => $item->count]);
        }
    }

    private function getCoursePerformance($courses = null)
    {
        $query = Course::query();
        if ($courses) {
            $query->whereIn('id', $courses->pluck('id'));
        }
        return $query->withCount('enrollments')
            ->withAvg('enrollments', 'progress_percentage')
            ->orderByDesc('enrollments_count')
            ->limit(10)
            ->get()
            ->map(fn($course) => [
                'title' => $course->title,
                'enrollments' => $course->enrollments_count,
                'average_progress' => round($course->enrollments_avg_progress_percentage ?? 0, 2),
            ]);
    }

    private function getProgressTrend($userId)
    {
        return CourseEnrollment::where('user_id', $userId)
            ->where('updated_at', '>=', now()->subDays((int)$this->timeRange))
            ->selectRaw('DATE(updated_at) as date, AVG(progress_percentage) as avg_progress')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => ['date' => $item->date, 'progress' => round($item->avg_progress, 2)]);
    }
}
