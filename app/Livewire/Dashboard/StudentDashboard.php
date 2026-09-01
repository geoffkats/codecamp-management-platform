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
use App\Models\StudentLessonProgress;
use App\Models\User;
use App\Models\UserPoint;
use App\Models\UserProgress;
use App\Support\LevelSystem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class StudentDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public const DAILY_XP_GOAL = 100;

    public function mount()
    {
        Cache::forget('student_dashboard_' . Auth::id());

        $user = Auth::user();
        if ($user?->isCodeClubStudent()) {
            redirect()->route('codeclub.dashboard');
        }
    }

    public function markNotificationAsRead($notificationId)
    {
        Notification::where('user_id', Auth::id())->findOrFail($notificationId)
            ->update(['is_read' => true, 'read_at' => now()]);
        $this->dispatch('notification-read');
    }

    public function markAllNotificationsAsRead()
    {
        Notification::where('user_id', Auth::id())->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        $this->dispatch('notifications-read');
    }

    public function render()
    {
        $user = Auth::user()->load([
            'enrollments.course.modules.lessons',
            'badges' => fn($q) => $q->latest('user_badges.earned_at')->take(6),
            'points',
            'studentProfile',
        ]);

        if ($user->isIctStudent()) {
            $enrollments = CourseEnrollment::where('user_id', $user->id)
                ->with('course')->orderBy('enrolled_at', 'desc')->get();
            $examResults = $user->studentProfile
                ? IcdlExamResult::where('student_profile_id', $user->studentProfile->id)
                    ->with('module')->orderByDesc('exam_date')->get()
                : collect();
            $assessmentAttempts = AssessmentAttempt::where('user_id', $user->id)
                ->where('student_type', 'ict')->where('status', 'completed')
                ->whereHas('assessment')
                ->with(['assessment.questions', 'assessment.lesson', 'assessment.course'])
                ->orderByDesc('completed_at')->take(8)->get();

            return view('livewire.dashboard.icdl-student-dashboard', compact(
                'user', 'enrollments', 'examResults', 'assessmentAttempts'
            ) + ['studentProfile' => $user->studentProfile]);
        }

        $dashboardData = Cache::remember(
            'student_dashboard_' . $user->id,
            now()->addMinutes(5),
            function () use ($user) {
                return [
                    'stats'              => $this->getStats($user),
                    'levelInfo'          => $this->getLevelInfo($user),
                    'streak'             => $this->getLearningStreak($user),
                    'heatmap'            => $this->getActivityHeatmap($user),
                    'dailyXp'            => $this->getDailyXp($user),
                    'recentBadges'       => $user->badges,
                    'dailyChallenges'    => $this->getDailyChallenges($user),
                    'leaderboardPosition'=> $this->getLeaderboardPosition($user),
                    'campLeaderboard'    => $this->getCampLeaderboard($user),
                ];
            }
        );

        $activeEnrollments = CourseEnrollment::where('user_id', $user->id)
            ->with(['course' => fn($q) => $q->with(['instructor', 'modules'])])
            ->orderBy('enrolled_at', 'desc')
            ->paginate(6);

        $notifications   = Notification::where('user_id', $user->id)->latest()->take(10)->get();
        $upcomingDeadlines = $this->getUpcomingDeadlines($user);
        $recentCertificates = \App\Models\Certificate::where('user_id', $user->id)
            ->with('course')->latest('issued_at')->take(3)->get();
        $recentSubmissions = $this->getRecentSubmissions($user);

        return view('livewire.dashboard.student-dashboard', array_merge($dashboardData, [
            'user'               => $user,
            'activeEnrollments'  => $activeEnrollments,
            'notifications'      => $notifications,
            'upcomingDeadlines'  => $upcomingDeadlines,
            'recentCertificates' => $recentCertificates,
            'recentSubmissions'  => $recentSubmissions,
            'dailyXpGoal'        => self::DAILY_XP_GOAL,
            'activeCompetition'  => $this->getActiveCompetition($user),
        ]));
    }

    // ── Level system ─────────────────────────────────────────────────────────

    private function getLevelInfo(User $user): array
    {
        $info = LevelSystem::info($user->points?->total_points ?? 0);

        return [
            'level'      => $info['level'],
            'name'       => $info['name'],
            'color'      => $info['color'],
            'xp'         => $info['xp'],
            'xpInLevel'  => $info['xp_in_rank'],
            'xpNeeded'   => $info['xp_needed'] ?: 1,
            'progress'   => $info['progress'],
            'nextName'   => $info['next_name'] ?? 'Max Rank',
            'isMax'      => $info['is_max'],
        ];
    }

    // ── Streak (lesson completion-based) ─────────────────────────────────────

    private function getLearningStreak(User $user): array
    {
        // Use completed lessons as activity signal (much more accurate than enrollment updates)
        $days = StudentLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subDays(365))
            ->selectRaw('DATE(completed_at) as day')
            ->groupBy('day')
            ->orderByDesc('day')
            ->pluck('day')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $streak   = 0;
        $check    = now()->toDateString();
        $today    = $check;
        $yesterday= now()->subDay()->toDateString();

        // Count from today or yesterday (allow same-day gap)
        if (!in_array($today, $days) && !in_array($yesterday, $days)) {
            return ['current' => 0, 'longest' => $this->longestStreak($days), 'activeDays' => count($days)];
        }

        if (!in_array($today, $days)) {
            $check = $yesterday;
        }

        while (in_array($check, $days)) {
            $streak++;
            $check = Carbon::parse($check)->subDay()->toDateString();
        }

        return [
            'current'    => $streak,
            'longest'    => $this->longestStreak($days),
            'activeDays' => count($days),
        ];
    }

    private function longestStreak(array $days): int
    {
        if (empty($days)) return 0;
        $sorted  = collect($days)->sort()->values()->toArray();
        $longest = $current = 1;
        for ($i = 1; $i < count($sorted); $i++) {
            $diff = Carbon::parse($sorted[$i])->diffInDays(Carbon::parse($sorted[$i - 1]));
            $current = $diff === 1 ? $current + 1 : 1;
            $longest = max($longest, $current);
        }
        return $longest;
    }

    // ── Activity heatmap (last 91 days) ──────────────────────────────────────

    private function getActivityHeatmap(User $user): array
    {
        $from = now()->subDays(29)->startOfDay();

        $completions = StudentLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $from)
            ->selectRaw('DATE(completed_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $grid = [];
        for ($i = 29; $i >= 0; $i--) {
            $date  = now()->subDays($i)->format('Y-m-d');
            $count = $completions[$date] ?? 0;
            $grid[] = [
                'date'  => $date,
                'count' => $count,
                'label' => Carbon::parse($date)->format('D j'),
                'level' => $count === 0 ? 0 : ($count === 1 ? 1 : ($count <= 3 ? 2 : ($count <= 5 ? 3 : 4))),
            ];
        }

        return $grid;
    }

    // ── Daily XP ─────────────────────────────────────────────────────────────

    private function getDailyXp(User $user): int
    {
        // Count XP from lessons completed today
        return StudentLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count() * 10; // 10 XP per lesson as proxy
    }

    // ── Camp leaderboard top-5 ────────────────────────────────────────────────

    private function getCampLeaderboard(User $user): array
    {
        // Get the user's active camp
        $campEnrollment = $user->campEnrollments()
            ->where('status', 'active')
            ->with('camp')
            ->latest()
            ->first();

        if (! $campEnrollment) {
            return [];
        }

        $campmates = $campEnrollment->camp->enrollments()
            ->where('status', 'active')
            ->with(['student'])
            ->get();

        $weeklyXp = UserProgress::query()
            ->whereIn('user_id', $campmates->pluck('student_id'))
            ->where('created_at', '>=', now()->startOfWeek());

        CourseEnrollment::constrainProgressToCurrentClass(
            $weeklyXp,
            (int) $campEnrollment->camp_id
        );

        $weeklyXp = $weeklyXp
            ->selectRaw('user_progress.user_id, COALESCE(SUM(user_progress.points_earned), 0) as xp')
            ->groupBy('user_progress.user_id')
            ->pluck('xp', 'user_id');

        $ranked = $campmates
            ->map(function ($e) use ($weeklyXp) {
                return [
                    'user_id' => $e->student_id,
                    'user'    => $e->student,
                    'name'    => $e->student->name ?? 'Student',
                    'xp'      => (int) ($weeklyXp[$e->student_id] ?? 0),
                ];
            })
            ->sortByDesc('xp')
            ->values();

        $myRank = $ranked->search(fn ($c) => $c['user_id'] === $user->id);

        return [
            'campName' => $campEnrollment->camp->name ?? 'Your Camp',
            'campId'   => $campEnrollment->camp_id,
            'top'      => $ranked->take(5)->all(),
            'myRank'   => $myRank !== false ? $myRank + 1 : null,
            'period'   => 'week',
        ];
    }

    // ── Unchanged helpers ─────────────────────────────────────────────────────

    private function getStats(User $user): array
    {
        $totalPoints     = $user->points?->total_points ?? 0;
        $level           = LevelSystem::levelForXp($totalPoints);
        $totalBadges     = $user->badges()->count();
        $completedLessons= StudentLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')->count();

        return [
            'enrollments'      => $user->enrollments()->count(),
            'completedCourses' => $user->enrollments()->whereNotNull('completed_at')->count(),
            'totalPoints'      => $totalPoints,
            'level'            => $level,
            'totalBadges'      => $totalBadges,
            'completedLessons' => $completedLessons,
        ];
    }

    private function getDailyChallenges(User $user)
    {
        $userCourseIds = $user->enrollments()->pluck('course_id')->filter()->unique();
        return DailyChallenge::where('is_active', true)
            ->where(function($q) use ($userCourseIds) {
                $q->whereNull('course_id');
                if ($userCourseIds->isNotEmpty()) $q->orWhereIn('course_id', $userCourseIds);
            })
            ->where(function($q) {
                $q->where('date', today()->toDateString())->orWhereNull('date');
            })
            ->with(['attempts' => fn($q) => $q->where('user_id', $user->id), 'course'])
            ->orderBy('date', 'asc')
            ->take(3)
            ->get()
            ->map(function ($c) use ($user) {
                $attempt = $c->attempts->first();
                $c->is_completed = $attempt && $attempt->is_completed;
                $c->attempt = $attempt;
                $c->reward_points ??= 100;
                return $c;
            });
    }

    private function getActiveCompetition(User $user): ?DailyChallenge
    {
        $userCourseIds = $user->enrollments()->pluck('course_id')->filter()->unique();

        return DailyChallenge::where('is_active', true)
            ->where('is_competition', true)
            ->where(function ($q) {
                $q->whereNull('competition_ends_at')
                  ->orWhere('competition_ends_at', '>', now());
            })
            ->where(function ($q) use ($userCourseIds) {
                $q->whereNull('course_id');
                if ($userCourseIds->isNotEmpty()) {
                    $q->orWhereIn('course_id', $userCourseIds);
                }
            })
            ->with(['attempts' => fn ($q) => $q->where('user_id', $user->id)])
            ->first();
    }

    private function getLeaderboardPosition(User $user): array
    {
        $campId = $user->currentCamp()?->id;
        $courseIds = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->currentClass($campId)
            ->pluck('course_id')
            ->filter()
            ->all();

        if ($courseIds === []) {
            return ['rank' => 1, 'total' => 1, 'percentage' => 100, 'period' => 'week'];
        }

        $peerIds = CourseEnrollment::query()
            ->currentClass($campId)
            ->whereIn('course_id', $courseIds)
            ->distinct()
            ->pluck('user_id');

        $weeklyXp = UserProgress::query()
            ->whereIn('user_id', $peerIds)
            ->where('created_at', '>=', now()->startOfWeek());

        CourseEnrollment::constrainProgressToCurrentClass($weeklyXp, $campId ? (int) $campId : null);

        $weeklyXp = $weeklyXp
            ->selectRaw('user_progress.user_id, COALESCE(SUM(user_progress.points_earned), 0) as xp')
            ->groupBy('user_progress.user_id')
            ->pluck('xp', 'user_id');

        $myXp = (int) ($weeklyXp[$user->id] ?? 0);
        $ahead = $peerIds->filter(fn ($peerId) => (int) ($weeklyXp[$peerId] ?? 0) > $myXp)->count();
        $total = max($peerIds->count(), 1);

        return [
            'rank' => $ahead + 1,
            'total' => $total,
            'percentage' => round((($ahead + 1) / $total) * 100, 1),
            'period' => 'week',
        ];
    }

    private function getRecentSubmissions(User $user)
    {
        $assessmentAttempts = AssessmentAttempt::query()
            ->where('user_id', $user->id)
            ->whereHas('assessment', fn ($q) => $q->where('assessment_type', 'assignment'))
            ->where(function ($q) {
                $q->where('status', 'completed')->orWhereNotNull('completed_at');
            })
            ->with(['assessment.course'])
            ->latest('completed_at')
            ->take(5)
            ->get()
            ->map(fn ($attempt) => (object) [
                'title' => $attempt->assessment->title,
                'course' => $attempt->assessment->course,
                'status' => $attempt->score === null ? 'pending' : 'graded',
                'submitted_at' => $attempt->completed_at,
                'score' => $attempt->scoreAsPoints(),
                'max_score' => $attempt->maxScore(),
                'submission' => $attempt,
            ]);

        $legacy = \App\Models\AssignmentSubmission::where('user_id', $user->id)
            ->whereIn('status', ['submitted', 'graded', 'returned'])
            ->with(['assignment.course', 'grader'])
            ->latest('submitted_at')
            ->take(5)
            ->get()
            ->map(fn ($submission) => (object) [
                'title' => $submission->assignment->title,
                'course' => $submission->assignment->course,
                'status' => $submission->graded_at ? 'graded' : 'pending',
                'submitted_at' => $submission->submitted_at,
                'score' => $submission->points_earned,
                'max_score' => $submission->assignment->max_points ?? 100,
                'submission' => $submission,
            ]);

        return $assessmentAttempts
            ->concat($legacy)
            ->sortByDesc(fn ($item) => $item->submitted_at?->timestamp ?? 0)
            ->take(5)
            ->values();
    }

    private function getUpcomingDeadlines(User $user): array
    {
        $completedAssessmentIds = AssessmentAttempt::query()
            ->where('user_id', $user->id)
            ->whereNotNull('score')
            ->pluck('assessment_id');

        $assessmentDeadlines = \App\Models\Assessment::query()
            ->where('assessment_type', 'assignment')
            ->whereHas('course.enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->whereNotIn('id', $completedAssessmentIds)
            ->with(['course', 'lesson'])
            ->get()
            ->filter(fn ($assessment) => $assessment->due_date && $assessment->due_date->gte(now()))
            ->sortBy('due_date')
            ->take(5)
            ->values();

        $legacyAssignments = \App\Models\Assignment::whereHas('course.enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->where('due_date', '>=', now())
            ->where('status', 'active')
            ->with(['course', 'lesson'])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        $assignments = $assessmentDeadlines
            ->concat($legacyAssignments)
            ->sortBy(fn ($item) => $item->due_date?->timestamp ?? PHP_INT_MAX)
            ->take(5)
            ->values();

        $quizzes = \App\Models\Quiz::whereHas('lesson.course.enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->where('is_published', true)->with(['lesson.course'])->take(5)->get();

        return ['assignments' => $assignments, 'quizzes' => $quizzes];
    }
}
