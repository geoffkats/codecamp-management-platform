<?php

namespace App\Livewire\Dashboard;

use App\Models\Badge;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\DailyChallenge;
use App\Models\AssessmentAttempt;
use App\Models\IcdlExamResult;
use App\Models\Leaderboard;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class StudentDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        // Refresh cache on mount
        Cache::forget('student_dashboard_' . Auth::id());
    }

    public function markNotificationAsRead($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        $this->dispatch('notification-read');
    }

    public function markAllNotificationsAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $this->dispatch('notifications-read');
    }

    public function render()
    {
        $user = Auth::user()->load([
            'enrollments.course.modules.lessons',
            'badges' => fn($q) => $q->latest('user_badges.earned_at')->take(5),
            'points',
            'studentProfile',
        ]);

        if ($user->isIctStudent()) {
            $enrollments = CourseEnrollment::where('user_id', $user->id)
                ->with('course')
                ->orderBy('enrolled_at', 'desc')
                ->get();

            $examResults = $user->studentProfile
                ? IcdlExamResult::where('student_profile_id', $user->studentProfile->id)
                    ->with('module')
                    ->orderByDesc('exam_date')
                    ->get()
                : collect();

            $assessmentAttempts = AssessmentAttempt::where('user_id', $user->id)
                ->where('student_type', 'ict')
                ->where('status', 'completed')
                ->with(['assessment.questions', 'assessment.lesson', 'assessment.course'])
                ->orderByDesc('completed_at')
                ->take(8)
                ->get();

            return view('livewire.dashboard.icdl-student-dashboard', [
                'user' => $user,
                'studentProfile' => $user->studentProfile,
                'enrollments' => $enrollments,
                'examResults' => $examResults,
                'assessmentAttempts' => $assessmentAttempts,
            ]);
        }

        // Cache dashboard data for 5 minutes
        $dashboardData = Cache::remember(
            'student_dashboard_' . $user->id,
            now()->addMinutes(5),
            function () use ($user) {
                return [
                    'stats' => $this->getStats($user),
                    'recentBadges' => $user->badges,
                    'dailyChallenges' => $this->getDailyChallenges($user),
                    'leaderboardPosition' => $this->getLeaderboardPosition($user),
                    'learningStreak' => $this->getLearningStreak($user),
                ];
            }
        );

        // Get active enrollments with progress
        $activeEnrollments = CourseEnrollment::where('user_id', $user->id)
            ->with([
                'course' => fn($q) => $q->with(['instructor', 'modules']),
            ])
            ->orderBy('enrolled_at', 'desc')
            ->paginate(6);

        // Get recent notifications
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        // Get upcoming deadlines (assignments/quizzes)
        $upcomingDeadlines = $this->getUpcomingDeadlines($user);

        // Get recommended courses
        $recommendedCourses = $this->getRecommendedCourses($user);

        // Get recent certificates
        $recentCertificates = \App\Models\Certificate::where('user_id', $user->id)
            ->with('course')
            ->latest('issued_at')
            ->take(3)
            ->get();

        // Get recent submissions
        $recentSubmissions = \App\Models\AssignmentSubmission::where('user_id', $user->id)
            ->whereIn('status', ['submitted', 'graded', 'returned'])
            ->with(['assignment.course', 'grader'])
            ->latest('submitted_at')
            ->take(5)
            ->get();

        return view('livewire.dashboard.student-dashboard', [
            'user' => $user,
            'stats' => $dashboardData['stats'],
            'activeEnrollments' => $activeEnrollments,
            'recentBadges' => $dashboardData['recentBadges'],
            'dailyChallenges' => $dashboardData['dailyChallenges'],
            'leaderboardPosition' => $dashboardData['leaderboardPosition'],
            'learningStreak' => $dashboardData['learningStreak'],
            'notifications' => $notifications,
            'upcomingDeadlines' => $upcomingDeadlines,
            'recommendedCourses' => $recommendedCourses,
            'recentCertificates' => $recentCertificates,
            'recentSubmissions' => $recentSubmissions,
        ]);
    }

    private function getStats($user): array
    {
        $enrollments = $user->enrollments()->count();
        $completedCourses = $user->enrollments()
            ->whereNotNull('completed_at')
            ->count();
        
        $totalPoints = $user->points?->total_points ?? 0;
        $level = $user->points?->level ?? 1;
        
        $totalBadges = $user->badges()->count();
        $completedLessons = $user->enrollments()
            ->with('course.lessons')
            ->get()
            ->sum(function ($enrollment) {
                return $enrollment->lessons_completed ?? 0;
            });

        return [
            'enrollments' => $enrollments,
            'completedCourses' => $completedCourses,
            'totalPoints' => $totalPoints,
            'level' => $level,
            'totalBadges' => $totalBadges,
            'completedLessons' => $completedLessons,
        ];
    }

    private function getDailyChallenges($user)
    {
        $userCourseIds = $user->enrollments()->pluck('course_id')->filter()->unique();

        $challenges = DailyChallenge::where('is_active', true)
            ->where(function($q) use ($userCourseIds) {
                $q->whereNull('course_id');

                if ($userCourseIds->isNotEmpty()) {
                    $q->orWhereIn('course_id', $userCourseIds);
                }
            })
            ->where(function($q) {
                $q->where('date', '>=', now()->toDateString())
                  ->orWhereNull('date');
            })
            ->with([
                'attempts' => fn($q) => $q->where('user_id', $user->id),
                'course',
            ])
            ->orderBy('date', 'asc')
            ->take(3)
            ->get()
            ->map(function ($challenge) use ($user) {
                $attempt = $challenge->attempts->first();
                $challenge->is_completed = $attempt && $attempt->completed_at !== null;
                $challenge->attempt = $attempt;
                // Ensure reward_points has a default value
                if (is_null($challenge->reward_points)) {
                    $challenge->reward_points = 100;
                }
                return $challenge;
            });
        
        return $challenges;
    }

    private function getLeaderboardPosition($user)
    {
        $leaderboard = Leaderboard::where('type', 'overall')
            ->where('user_id', $user->id)
            ->first();

        if (!$leaderboard) {
            // Calculate and cache position
            $totalUsers = User::whereHas('points', fn($q) => $q->where('total_points', '>', 0))
                ->count();
            
            $rank = User::whereHas('points', function($q) use ($user) {
                $q->where('total_points', '>', $user->points?->total_points ?? 0);
            })->count() + 1;

            return [
                'rank' => $rank,
                'total' => $totalUsers,
                'percentage' => $totalUsers > 0 ? round(($rank / $totalUsers) * 100, 2) : 0,
            ];
        }

        $totalUsers = Leaderboard::where('type', 'overall')->count();

        return [
            'rank' => $leaderboard->rank ?? 0,
            'total' => $totalUsers,
            'percentage' => $totalUsers > 0 ? round((($leaderboard->rank ?? 0) / $totalUsers) * 100, 2) : 0,
        ];
    }

    private function getLearningStreak($user)
    {
        // Get last 30 days of activity
        $activities = CourseEnrollment::where('user_id', $user->id)
            ->where('updated_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(updated_at) as date')
            ->groupBy('date')
            ->pluck('date')
            ->map(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        // Calculate current streak
        $streak = 0;
        $checkDate = now()->toDateString();

        while (in_array($checkDate, $activities)) {
            $streak++;
            $checkDate = \Carbon\Carbon::parse($checkDate)->subDay()->toDateString();
        }

        return [
            'current' => $streak,
            'longest' => $streak, // Can be improved with historical tracking
            'lastActivity' => !empty($activities) ? $activities[0] : null,
        ];
    }

    private function getUpcomingDeadlines($user)
    {
        // Get assignments with due dates
        $assignments = \App\Models\Assignment::whereHas('course.enrollments', fn($q) => $q->where('user_id', $user->id))
            ->where('due_date', '>=', now())
            ->whereNull('status')
            ->with(['course', 'lesson'])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Get quizzes with time limits
        $quizzes = \App\Models\Quiz::whereHas('lesson.course.enrollments', fn($q) => $q->where('user_id', $user->id))
            ->where('is_published', true)
            ->with(['lesson.course'])
            ->take(5)
            ->get();

        return [
            'assignments' => $assignments,
            'quizzes' => $quizzes,
        ];
    }

    private function getRecommendedCourses($user)
    {
        // Get courses not enrolled by user, ordered by popularity and ratings
        return Course::where('is_published', true)
            ->where('approval_status', 'approved')
            ->whereDoesntHave('enrollments', fn($q) => $q->where('user_id', $user->id))
            ->with(['instructor', 'modules'])
            ->withCount(['enrollments', 'lessons'])
            ->orderBy('enrollments_count', 'desc')
            ->orderBy('is_featured', 'desc')
            ->take(4)
            ->get();
    }
}
