<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPoint;
use App\Models\UserProgress;
use App\Support\LevelSystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointsService
{
    /**
     * Award raw XP and keep level/rank in sync. Preferred entry point for all XP grants.
     */
    public function awardXp(User|int $user, int $amount, bool $refreshUser = true): UserPoint
    {
        $user = $user instanceof User ? $user : User::findOrFail($user);
        $points = $this->ensureUserPoints($user);

        if ($amount !== 0) {
            $points->increment('total_points', $amount);
            $points->refresh();
        }

        LevelSystem::sync($points);

        if ($refreshUser) {
            $user->setRelation('points', $points);
        }

        return $points;
    }

    /**
     * Award points for course enrollment (50 XP)
     * Only awards once per user per course
     */
    public function awardEnrollmentPoints(int $userId, int $courseId): bool
    {
        $exists = UserProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('type', 'course_enrolled')
            ->exists();

        if ($exists) {
            Log::warning('Duplicate enrollment points prevented', [
                'user_id' => $userId,
                'course_id' => $courseId,
            ]);

            return false;
        }

        return DB::transaction(function () use ($userId, $courseId) {
            $this->awardXp($userId, 50);

            UserProgress::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'type' => 'course_enrolled',
                'points_earned' => 50,
            ]);

            Log::info('Enrollment points awarded', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'points' => 50,
            ]);

            return true;
        });
    }

    /**
     * Award points for lesson completion
     * Only awards once per user per lesson
     */
    public function awardLessonPoints(int $userId, int $courseId, int $lessonId, int $points, ?int $timeSpent = null): bool
    {
        $exists = UserProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('type', 'lesson_completed')
            ->exists();

        if ($exists) {
            Log::warning('Duplicate lesson points prevented', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'lesson_id' => $lessonId,
            ]);

            return false;
        }

        return DB::transaction(function () use ($userId, $courseId, $lessonId, $points, $timeSpent) {
            $this->awardXp($userId, $points);

            UserProgress::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'lesson_id' => $lessonId,
                'type' => 'lesson_completed',
                'points_earned' => $points,
                'completed_at' => now(),
                'time_spent' => $timeSpent,
            ]);

            Log::info('Lesson points awarded', [
                'user_id' => $userId,
                'lesson_id' => $lessonId,
                'points' => $points,
            ]);

            return true;
        });
    }

    /**
     * Award points for course completion (100 XP)
     * Only awards once per user per course
     */
    public function awardCourseCompletionPoints(int $userId, int $courseId): bool
    {
        return $this->awardCourseCompletionPointsByValue($userId, $courseId, 100);
    }

    /**
     * Award points for manual course completion at half value (50 XP)
     * Only awards once per user per course
     */
    public function awardCourseCompletionPointsHalf(int $userId, int $courseId): bool
    {
        return $this->awardCourseCompletionPointsByValue($userId, $courseId, 50);
    }

    /**
     * Award points for course completion with explicit point value
     * Only awards once per user per course
     */
    private function awardCourseCompletionPointsByValue(int $userId, int $courseId, int $pointsToAward): bool
    {
        $exists = UserProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('type', 'course_completed')
            ->exists();

        if ($exists) {
            Log::warning('Duplicate course completion points prevented', [
                'user_id' => $userId,
                'course_id' => $courseId,
            ]);

            return false;
        }

        return DB::transaction(function () use ($userId, $courseId, $pointsToAward) {
            $this->awardXp($userId, $pointsToAward);

            UserProgress::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'type' => 'course_completed',
                'points_earned' => $pointsToAward,
                'completed_at' => now(),
            ]);

            Log::info('Course completion points awarded', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'points' => $pointsToAward,
            ]);

            return true;
        });
    }

    public function recalculateLevel(UserPoint $points): void
    {
        LevelSystem::sync($points);
    }

    /**
     * Ensure a user has UserPoint record initialized
     */
    public function ensureUserPoints(User $user): UserPoint
    {
        if (! $user->points) {
            $points = UserPoint::create([
                'user_id' => $user->id,
                'total_points' => 0,
                'level' => 1,
                'points_to_next_level' => LevelSystem::XP_PER_LEVEL,
            ]);
            $user->setRelation('points', $points);

            return $points;
        }

        return $user->points;
    }

    public function syncAllLevels(): int
    {
        $count = 0;

        UserPoint::query()->orderBy('id')->chunkById(200, function ($chunk) use (&$count) {
            foreach ($chunk as $points) {
                LevelSystem::sync($points);
                $count++;
            }
        });

        return $count;
    }
}
