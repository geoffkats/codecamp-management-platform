<?php

namespace App\Services;

use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\UserPoint;
use App\Models\UserProgress;
use App\Support\LevelSystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
            ->where('lesson_id', $lessonId)
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

            try {
                UserProgress::create([
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'lesson_id' => $lessonId,
                    'type' => 'lesson_completed',
                    'points_earned' => $points,
                    'completed_at' => now(),
                    'time_spent' => $timeSpent,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Lesson XP progress row skipped', [
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'lesson_id' => $lessonId,
                    'error' => $e->getMessage(),
                ]);
            }

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

    /**
     * Admin XP attached to a specific course so week/month class ranks can count it.
     */
    public function awardAdminCourseXp(
        int $userId,
        int $courseId,
        int $amount,
        ?string $reason = null,
        ?int $awardedBy = null,
        string $source = 'xp_manager'
    ): int {
        if ($amount === 0) {
            return 0;
        }

        $enrolled = CourseEnrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();

        if (! $enrolled) {
            throw ValidationException::withMessages([
                'course_id' => 'This student is not enrolled in that course.',
            ]);
        }

        return DB::transaction(function () use ($userId, $courseId, $amount, $reason, $awardedBy, $source) {
            $user = User::findOrFail($userId);
            $points = $this->ensureUserPoints($user);
            $applied = $amount;

            if ($amount < 0) {
                $applied = -min(abs($amount), (int) $points->total_points);
            }

            if ($applied === 0) {
                return 0;
            }

            $this->awardXp($user, $applied, false);

            UserProgress::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'type' => $this->resolveAdminProgressType(),
                'points_earned' => $applied,
                'completed_at' => now(),
                'metadata' => [
                    'reason' => $reason,
                    'awarded_by' => $awardedBy,
                    'source' => $source,
                    'intended_type' => 'admin_award',
                ],
            ]);

            Log::info('Admin course XP awarded', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'points' => $applied,
                'awarded_by' => $awardedBy,
            ]);

            return $applied;
        });
    }

    /**
     * Prefer admin_award; fall back if production ENUM was never migrated.
     */
    private function resolveAdminProgressType(): string
    {
        static $resolved = null;

        if ($resolved !== null) {
            return $resolved;
        }

        try {
            $column = collect(DB::select("SHOW COLUMNS FROM user_progress LIKE 'type'"))->first();
            $colType = strtolower((string) ($column->Type ?? ''));

            if (str_starts_with($colType, 'enum') && ! str_contains($colType, 'admin_award')) {
                return $resolved = 'quiz_completed';
            }
        } catch (\Throwable $e) {
            Log::warning('Could not inspect user_progress.type', ['error' => $e->getMessage()]);

            return $resolved = 'quiz_completed';
        }

        return $resolved = 'admin_award';
    }

    /**
     * Award XP and log it against a course so week/month leaderboards can count it.
     */
    public function awardTrackedCourseXp(
        int $userId,
        int $courseId,
        int $amount,
        string $type = 'quiz_completed',
        ?int $lessonId = null,
        array $metadata = []
    ): bool {
        if ($amount <= 0 || ! $courseId) {
            return false;
        }

        return DB::transaction(function () use ($userId, $courseId, $amount, $type, $lessonId, $metadata) {
            $this->awardXp($userId, $amount);

            try {
                UserProgress::create([
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'lesson_id' => $lessonId,
                    'type' => $type,
                    'points_earned' => $amount,
                    'completed_at' => now(),
                    'metadata' => $metadata ?: null,
                ]);
            } catch (\Throwable $e) {
                // Career XP already saved; keep going even if a legacy unique index blocks the log row.
                Log::warning('Tracked XP progress row skipped', [
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'lesson_id' => $lessonId,
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]);
            }

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
