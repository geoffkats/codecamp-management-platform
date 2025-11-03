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
     * Check all badges for a user (useful for retroactive checking)
     */
    public function checkAllBadges(User $user): void
    {
        $this->checkLessonCompletionBadges($user);
        $this->checkCourseCompletionBadges($user);
        $this->checkPerfectQuizBadges($user);
        $this->checkPerfectAssessmentBadge($user);
        $this->checkLevelBadges($user);
        $this->checkPointMilestoneBadges($user);
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
            $user->points->increment('total_points', $badge->points_reward);
        }

        // Send real-time notification
        $notificationService = app(NotificationService::class);
        $notificationService->notifyBadgeEarned($user, $badge);

        // Dispatch event for additional UI updates
        event(new \App\Events\BadgeEarned($user, $badge));
    }
}

