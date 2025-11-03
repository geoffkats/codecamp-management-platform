<?php

namespace App\Livewire\Analytics;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Grade;
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
            'total_assessments' => Assessment::whereIn('course_id', $courseIds)->count(),
            'total_submissions' => $this->getTotalSubmissions($courseIds),
            'pending_grading' => $this->getPendingGrading($courseIds),
            'avg_assessment_score' => $this->getAverageAssessmentScore($courseIds),
        ];

        $enrollmentTrends = $this->getEnrollmentTrends($courseIds);
        $coursePerformance = $this->getCoursePerformance($courses);
        $submissionTrends = $this->getSubmissionTrends($courseIds);
        $assessmentStats = $this->getAssessmentStats($courseIds);
        $topPerformers = $this->getTopPerformers($courseIds);
        $recentActivity = $this->getRecentActivity($courseIds);

        return view('livewire.analytics.dashboard', [
            'role' => 'teacher',
            'stats' => $stats,
            'enrollmentTrends' => $enrollmentTrends,
            'coursePerformance' => $coursePerformance,
            'submissionTrends' => $submissionTrends,
            'assessmentStats' => $assessmentStats,
            'topPerformers' => $topPerformers,
            'recentActivity' => $recentActivity,
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
            'total_teachers' => User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->count(),
            'total_assessments' => Assessment::count(),
            'total_submissions' => AssignmentSubmission::where('status', 'submitted')->count() + AssessmentAttempt::where('status', 'completed')->count(),
            'pending_grading' => AssignmentSubmission::where('status', 'submitted')->whereNull('graded_at')->count() + AssessmentAttempt::where('status', 'completed')->whereNull('score')->whereHas('assessment', fn($q) => $q->where('assessment_type', 'assignment'))->count(),
        ];

        $userGrowth = $this->getUserGrowth();
        $coursePerformance = $this->getCoursePerformance();
        $enrollmentTrends = $this->getEnrollmentTrends();
        $userRoleDistribution = $this->getUserRoleDistribution();
        $systemHealth = $this->getSystemHealth();

        return view('livewire.analytics.dashboard', [
            'role' => 'admin',
            'stats' => $stats,
            'userGrowth' => $userGrowth,
            'coursePerformance' => $coursePerformance,
            'enrollmentTrends' => $enrollmentTrends,
            'userRoleDistribution' => $userRoleDistribution,
            'systemHealth' => $systemHealth,
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
            'total_submissions' => AssignmentSubmission::where('user_id', $user->id)->where('status', '!=', 'draft')->count() + AssessmentAttempt::where('user_id', $user->id)->where('status', 'completed')->count(),
            'total_grades' => Grade::where('user_id', $user->id)->count(),
            'avg_grade' => Grade::where('user_id', $user->id)->avg('percentage') ?? 0,
            'streak_days' => $this->getStreakDays($user->id),
        ];

        $progressTrend = $this->getProgressTrend($user->id);
        $scoreTrend = $this->getScoreTrend($user->id);
        $courseBreakdown = $this->getCourseBreakdown($user->id);
        $recentGrades = $this->getRecentGrades($user->id);

        return view('livewire.analytics.dashboard', [
            'role' => 'student',
            'stats' => $stats,
            'progressTrend' => $progressTrend,
            'scoreTrend' => $scoreTrend,
            'courseBreakdown' => $courseBreakdown,
            'recentGrades' => $recentGrades,
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
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return CourseEnrollment::where('user_id', $userId)
                ->where('updated_at', '>=', now()->subDays((int)$this->timeRange))
                ->selectRaw("strftime('%Y-%m-%d', updated_at) as date, AVG(progress_percentage) as avg_progress")
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => ['date' => $item->date, 'progress' => round($item->avg_progress, 2)]);
        } else {
            return CourseEnrollment::where('user_id', $userId)
                ->where('updated_at', '>=', now()->subDays((int)$this->timeRange))
                ->selectRaw('DATE(updated_at) as date, AVG(progress_percentage) as avg_progress')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => ['date' => $item->date, 'progress' => round($item->avg_progress, 2)]);
        }
    }

    private function getTotalSubmissions($courseIds)
    {
        $assignments = AssignmentSubmission::whereHas('assignment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->where('status', '!=', 'draft')
            ->count();
        $assessments = AssessmentAttempt::whereHas('assessment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->where('status', 'completed')
            ->count();
        return $assignments + $assessments;
    }

    private function getPendingGrading($courseIds)
    {
        $assignments = AssignmentSubmission::whereHas('assignment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->where('status', 'submitted')
            ->whereNull('graded_at')
            ->count();
        $assessments = AssessmentAttempt::whereHas('assessment', fn($q) => $q->whereIn('course_id', $courseIds)->where('assessment_type', 'assignment'))
            ->where('status', 'completed')
            ->whereNull('score')
            ->count();
        return $assignments + $assessments;
    }

    private function getAverageAssessmentScore($courseIds)
    {
        $avg = AssessmentAttempt::whereHas('assessment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->whereNotNull('score')
            ->avg('score');
        return round($avg ?? 0, 2);
    }

    private function getSubmissionTrends($courseIds)
    {
        $driver = DB::connection()->getDriverName();
        $assignments = AssignmentSubmission::whereHas('assignment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->where('status', 'submitted')
            ->where('submitted_at', '>=', now()->subDays((int)$this->timeRange));
        
        $assessments = AssessmentAttempt::whereHas('assessment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays((int)$this->timeRange));

        if ($driver === 'sqlite') {
            $assignmentData = $assignments->selectRaw("strftime('%Y-%m-%d', submitted_at) as date, COUNT(*) as count")
                ->groupBy('date')
                ->get()
                ->pluck('count', 'date');
            
            $assessmentData = $assessments->selectRaw("strftime('%Y-%m-%d', completed_at) as date, COUNT(*) as count")
                ->groupBy('date')
                ->get()
                ->pluck('count', 'date');
        } else {
            $assignmentData = $assignments->selectRaw('DATE(submitted_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->get()
                ->pluck('count', 'date');
            
            $assessmentData = $assessments->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->get()
                ->pluck('count', 'date');
        }

        // Merge data
        $allDates = $assignmentData->keys()->merge($assessmentData->keys())->unique()->sort();
        return $allDates->map(fn($date) => [
            'date' => $date,
            'count' => ($assignmentData[$date] ?? 0) + ($assessmentData[$date] ?? 0),
        ]);
    }

    private function getAssessmentStats($courseIds)
    {
        return Assessment::whereIn('course_id', $courseIds)
            ->select('assessment_type', DB::raw('COUNT(*) as count'))
            ->groupBy('assessment_type')
            ->get()
            ->map(fn($item) => [
                'type' => ucfirst(str_replace('_', ' ', $item->assessment_type)),
                'count' => $item->count,
            ]);
    }

    private function getTopPerformers($courseIds)
    {
        return CourseEnrollment::whereIn('course_id', $courseIds)
            ->whereNotNull('average_quiz_score')
            ->with('user')
            ->orderByDesc('average_quiz_score')
            ->limit(10)
            ->get()
            ->map(fn($enrollment) => [
                'user' => $enrollment->user->name,
                'course' => $enrollment->course->title ?? 'N/A',
                'score' => round($enrollment->average_quiz_score, 2),
                'progress' => round($enrollment->progress_percentage ?? 0, 2),
            ]);
    }

    private function getRecentActivity($courseIds)
    {
        $recent = collect();
        
        // Recent enrollments
        CourseEnrollment::whereIn('course_id', $courseIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->with(['user', 'course'])
            ->latest()
            ->limit(5)
            ->get()
            ->each(fn($e) => $recent->push([
                'type' => 'enrollment',
                'user' => $e->user->name,
                'course' => $e->course->title,
                'time' => $e->created_at,
            ]));
        
        // Recent submissions
        AssignmentSubmission::whereHas('assignment', fn($q) => $q->whereIn('course_id', $courseIds))
            ->where('status', 'submitted')
            ->where('submitted_at', '>=', now()->subDays(7))
            ->with(['user', 'assignment'])
            ->latest('submitted_at')
            ->limit(5)
            ->get()
            ->each(fn($s) => $recent->push([
                'type' => 'submission',
                'user' => $s->user->name,
                'course' => $s->assignment->title,
                'time' => $s->submitted_at,
            ]));
        
        return $recent->sortByDesc('time')->take(10)->values();
    }

    private function getUserRoleDistribution()
    {
        return User::select('roles.name as role', DB::raw('COUNT(*) as count'))
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->groupBy('roles.name')
            ->get()
            ->map(fn($item) => [
                'role' => ucfirst($item->role),
                'count' => $item->count,
            ]);
    }

    private function getSystemHealth()
    {
        return [
            'active_users_30d' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
            'new_users_7d' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'active_courses' => Course::whereHas('enrollments')->count(),
            'avg_enrollments_per_course' => round(CourseEnrollment::count() / max(Course::count(), 1), 2),
        ];
    }

    private function getScoreTrend($userId)
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return Grade::where('user_id', $userId)
                ->where('graded_at', '>=', now()->subDays((int)$this->timeRange))
                ->selectRaw("strftime('%Y-%m-%d', graded_at) as date, AVG(percentage) as avg_score")
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => ['date' => $item->date, 'score' => round($item->avg_score, 2)]);
        } else {
            return Grade::where('user_id', $userId)
                ->where('graded_at', '>=', now()->subDays((int)$this->timeRange))
                ->selectRaw('DATE(graded_at) as date, AVG(percentage) as avg_score')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => ['date' => $item->date, 'score' => round($item->avg_score, 2)]);
        }
    }

    private function getCourseBreakdown($userId)
    {
        return CourseEnrollment::where('user_id', $userId)
            ->with('course')
            ->get()
            ->map(fn($e) => [
                'course' => $e->course->title ?? 'N/A',
                'progress' => round($e->progress_percentage ?? 0, 2),
                'score' => round($e->average_quiz_score ?? 0, 2),
                'status' => $e->completed_at ? 'Completed' : 'In Progress',
            ]);
    }

    private function getRecentGrades($userId)
    {
        return Grade::where('user_id', $userId)
            ->with(['gradeable'])
            ->latest('graded_at')
            ->limit(10)
            ->get()
            ->map(fn($g) => [
                'title' => $g->gradeable->title ?? 'N/A',
                'score' => round($g->percentage, 2),
                'letter' => $g->letter_grade,
                'graded_at' => $g->graded_at,
            ]);
    }

    private function getStreakDays($userId)
    {
        // Simple implementation - can be enhanced with proper streak tracking
        $recentActivity = CourseEnrollment::where('user_id', $userId)
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderByDesc('updated_at')
            ->pluck('updated_at');
        
        if ($recentActivity->isEmpty()) {
            return 0;
        }
        
        $streak = 0;
        $currentDate = now()->format('Y-m-d');
        
        foreach ($recentActivity as $activity) {
            $activityDate = $activity->format('Y-m-d');
            if ($activityDate === $currentDate || $activityDate === now()->subDays($streak)->format('Y-m-d')) {
                $streak++;
            } else {
                break;
            }
        }
        
        return $streak;
    }
}
