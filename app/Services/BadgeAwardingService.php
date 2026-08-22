<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class BadgeAwardingService
{
    /**
     * Check and award badges for lesson completion milestones
     */
    public function checkLessonCompletionBadges(User $user): void
    {
        // Count distinct lessons that are completed (check both student_lesson_progress and lesson_progress)
        $completedLessons = \App\Models\StudentLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->distinct('lesson_id')
            ->count('lesson_id');

        // Also check lesson_progress table for backward compatibility
        $completedLessons2 = \App\Models\LessonProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->count();

        // Use the higher count
        $completedLessons = max($completedLessons, $completedLessons2);

        // Check lesson completion badges
        $badges = Badge::where('is_active', true)
            ->whereJsonContains('criteria->type', 'lesson_completion')
            ->get();

        foreach ($badges as $badge) {
            $requiredCount = $badge->criteria['count'] ?? 0;
            
            if ($completedLessons >= $requiredCount && !$user->badges()->where('badge_id', $badge->id)->exists()) {
                $this->awardBadge($user, $badge);
            }
        }
    }

    /**
     * Check and award badges for course completion milestones
     */
    public function checkCourseCompletionBadges(User $user): void
    {
        $completedCourses = \App\Models\CourseEnrollment::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        // Check course completion badges
        $badges = Badge::where('is_active', true)
            ->whereJsonContains('criteria->type', 'course_completion')
            ->get();

        foreach ($badges as $badge) {
            $requiredCount = $badge->criteria['count'] ?? 0;
            
            if ($completedCourses >= $requiredCount && !$user->badges()->where('badge_id', $badge->id)->exists()) {
                $this->awardBadge($user, $badge);
            }
        }
    }

    /**
     * Check and award badges for perfect quiz scores
     */
    public function checkPerfectQuizBadges(User $user): void
    {
        $perfectScores = \App\Models\QuizAttempt::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->where('score', '>=', 100)
            ->count();

        // Check perfect score badges
        $badges = Badge::where('is_active', true)
            ->whereJsonContains('criteria->type', 'perfect_quiz_score')
            ->get();

        foreach ($badges as $badge) {
            $requiredCount = $badge->criteria['count'] ?? 0;
            
            if ($perfectScores >= $requiredCount && !$user->badges()->where('badge_id', $badge->id)->exists()) {
                $this->awardBadge($user, $badge);
            }
        }
    }

    /**
     * Check and award badges for perfect assessment scores
     */
    public function checkPerfectAssessmentBadge(User $user): void
    {
        // This is already handled in Assessments/Take.php but we can add it here for consistency
        $badge = Badge::where('is_active', true)
            ->where('slug', 'perfect-score')
            ->first();

        if ($badge && !$user->badges()->where('badge_id', $badge->id)->exists()) {
            // Check if user has any perfect score
            $hasPerfectScore = DB::table('assessment_attempts')
                ->where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->where('score', '>=', 100)
                ->exists();

            if ($hasPerfectScore) {
                $this->awardBadge($user, $badge);
            }
        }
    }

    /**
     * Check and award badges for level milestones
     */
    public function checkLevelBadges(User $user): void
    {
        if (!$user->points) {
            return;
        }

        $userLevel = $user->points->level ?? 1;

        $badges = Badge::where('is_active', true)
            ->whereJsonContains('criteria->type', 'level')
            ->get();

        foreach ($badges as $badge) {
            $requiredLevel = $badge->criteria['level'] ?? 0;
            
            if ($userLevel >= $requiredLevel && !$user->badges()->where('badge_id', $badge->id)->exists()) {
                $this->awardBadge($user, $badge);
            }
        }
    }

    /**
     * Check and award badges for point milestones
     */
    public function checkPointMilestoneBadges(User $user): void
    {
        if (!$user->points) {
            return;
        }

        $totalPoints = $user->points->total_points ?? 0;

        $badges = Badge::where('is_active', true)
            ->whereJsonContains('criteria->type', 'points_milestone')
            ->get();

        foreach ($badges as $badge) {
            $requiredPoints = $badge->criteria['points'] ?? 0;
            
            if ($totalPoints >= $requiredPoints && !$user->badges()->where('badge_id', $badge->id)->exists()) {
                $this->awardBadge($user, $badge);
            }
        }
    }

    /**
     * Check lesson count milestone badges (1, 10, 25 lessons).
     */
    public function checkLessonCountBadges(User $user): void
    {
        $count = \App\Models\StudentLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->distinct('lesson_id')
            ->count('lesson_id');

        $badges = Badge::where('is_active', true)
            ->whereJsonContains('criteria->type', 'lesson_count')
            ->get();

        foreach ($badges as $badge) {
            if ($count >= ($badge->criteria['count'] ?? PHP_INT_MAX)
                && ! $user->badges()->where('badge_id', $badge->id)->exists()) {
                $this->awardBadge($user, $badge);
            }
        }
    }

    /**
     * Check streak-based badges (5 days, 14 days).
     */
    public function checkStreakBadges(User $user): int
    {
        $streak = $this->calculateStreak($user);

        $badges = Badge::where('is_active', true)
            ->whereJsonContains('criteria->type', 'streak_days')
            ->get();

        foreach ($badges as $badge) {
            if ($streak >= ($badge->criteria['days'] ?? PHP_INT_MAX)
                && ! $user->badges()->where('badge_id', $badge->id)->exists()) {
                $this->awardBadge($user, $badge);
            }
        }

        return $streak;
    }

    /**
     * Check completed daily challenge count badges.
     */
    public function checkChallengeBadges(User $user): void
    {
        $count = DB::table('daily_challenge_attempts')
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->count();

        $badges = Badge::where('is_active', true)
            ->whereJsonContains('criteria->type', 'challenges_done')
            ->get();

        foreach ($badges as $badge) {
            if ($count >= ($badge->criteria['count'] ?? PHP_INT_MAX)
                && ! $user->badges()->where('badge_id', $badge->id)->exists()) {
                $this->awardBadge($user, $badge);
            }
        }
    }

    /**
     * Check course completion badges.
     */
    public function checkCourseCompleteBadges(User $user): void
    {
        $count = \App\Models\CourseEnrollment::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        $badges = Badge::where('is_active', true)
            ->whereJsonContains('criteria->type', 'course_complete')
            ->get();

        foreach ($badges as $badge) {
            if ($count >= ($badge->criteria['count'] ?? PHP_INT_MAX)
                && ! $user->badges()->where('badge_id', $badge->id)->exists()) {
                $this->awardBadge($user, $badge);
            }
        }
    }

    /**
     * Check Night Owl badge — lesson completed after 22:00 local time.
     */
    public function checkNightOwlBadge(User $user): void
    {
        $badge = Badge::where('is_active', true)->where('slug', 'night-owl')->first();
        if (! $badge || $user->badges()->where('badge_id', $badge->id)->exists()) {
            return;
        }

        $isNight = \App\Models\StudentLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereRaw('HOUR(completed_at) >= 22')
            ->exists();

        if ($isNight) {
            $this->awardBadge($user, $badge);
        }
    }

    /**
     * Check Early Bird badge — lesson completed before 08:00 local time.
     */
    public function checkEarlyBirdBadge(User $user): void
    {
        $badge = Badge::where('is_active', true)->where('slug', 'early-bird')->first();
        if (! $badge || $user->badges()->where('badge_id', $badge->id)->exists()) {
            return;
        }

        $isEarly = \App\Models\StudentLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereRaw('HOUR(completed_at) < 8')
            ->exists();

        if ($isEarly) {
            $this->awardBadge($user, $badge);
        }
    }

    /**
     * Check Speed Demon badge — 3+ lessons completed in a single calendar day.
     */
    public function checkSpeedDemonBadge(User $user): void
    {
        $badge = Badge::where('is_active', true)->where('slug', 'speed-demon')->first();
        if (! $badge || $user->badges()->where('badge_id', $badge->id)->exists()) {
            return;
        }

        $isSpeedDemon = \App\Models\StudentLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('DATE(completed_at) as day, COUNT(*) as cnt')
            ->groupBy('day')
            ->having('cnt', '>=', 3)
            ->exists();

        if ($isSpeedDemon) {
            $this->awardBadge($user, $badge);
        }
    }

    /**
     * Check kudos-related badges.
     */
    public function checkKudosBadges(User $user): void
    {
        // Kind Soul — given kudos to 5 unique people
        $givenBadge = Badge::where('is_active', true)->where('slug', 'kind-soul')->first();
        if ($givenBadge && ! $user->badges()->where('badge_id', $givenBadge->id)->exists()) {
            $given = DB::table('peer_kudos')
                ->where('from_user_id', $user->id)
                ->distinct('to_user_id')
                ->count('to_user_id');

            if ($given >= 5) {
                $this->awardBadge($user, $givenBadge);
            }
        }

        // Class Favourite — received kudos from 10 unique people
        $receivedBadge = Badge::where('is_active', true)->where('slug', 'class-favourite')->first();
        if ($receivedBadge && ! $user->badges()->where('badge_id', $receivedBadge->id)->exists()) {
            $received = DB::table('peer_kudos')
                ->where('to_user_id', $user->id)
                ->distinct('from_user_id')
                ->count('from_user_id');

            if ($received >= 10) {
                $this->awardBadge($user, $receivedBadge);
            }
        }
    }

    /**
     * Called when a lesson is completed. Runs all relevant checks.
     */
    public function onLessonComplete(User $user): void
    {
        $this->checkLessonCountBadges($user);
        $this->checkStreakBadges($user);
        $this->checkNightOwlBadge($user);
        $this->checkEarlyBirdBadge($user);
        $this->checkSpeedDemonBadge($user);
    }

    /**
     * Calculate the current consecutive-day streak for a user.
     */
    private function calculateStreak(User $user): int
    {
        $dates = \App\Models\StudentLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('DATE(completed_at) as day')
            ->groupBy('day')
            ->orderByDesc('day')
            ->pluck('day')
            ->map(fn ($d) => \Carbon\Carbon::parse($d));

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $check  = now()->startOfDay();

        foreach ($dates as $date) {
            if ($date->eq($check) || $date->eq($check->copy()->subDay())) {
                $streak++;
                $check = $date->copy()->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Check all badges for a user (useful for retroactive checking)
     */
    public function checkAllBadges(User $user): void
    {
        $this->checkLessonCompletionBadges($user);
        $this->checkLessonCountBadges($user);
        $this->checkCourseCompletionBadges($user);
        $this->checkCourseCompleteBadges($user);
        $this->checkPerfectQuizBadges($user);
        $this->checkPerfectAssessmentBadge($user);
        $this->checkLevelBadges($user);
        $this->checkPointMilestoneBadges($user);
        $this->checkStreakBadges($user);
        $this->checkChallengeBadges($user);
        $this->checkNightOwlBadge($user);
        $this->checkEarlyBirdBadge($user);
        $this->checkSpeedDemonBadge($user);
        $this->checkKudosBadges($user);
    }

    /**
     * Award a badge to a user
     */
    private function awardBadge(User $user, Badge $badge): void
    {
        // Check if user already has this badge
        if ($user->badges()->where('badge_id', $badge->id)->exists()) {
            return;
        }

        // Attach badge
        $user->badges()->attach($badge->id, ['earned_at' => now()]);

        // Award points if badge has points reward
        if ($badge->points_reward > 0 && $user->points) {
            $user->points->addPoints((int) $badge->points_reward);
        }

        // Send real-time notification
        $notificationService = app(NotificationService::class);
        $notificationService->notifyBadgeEarned($user, $badge);

        // Dispatch event for additional UI updates
        event(new \App\Events\BadgeEarned($user, $badge));
    }
}

