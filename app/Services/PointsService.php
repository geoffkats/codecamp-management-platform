<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPoint;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointsService
{
    /**
     * Award points for course enrollment (50 XP)
     * Only awards once per user per course
     */
    public function awardEnrollmentPoints(int $userId, int $courseId): bool
    {
        // Check if already awarded
        $exists = UserProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('type', 'course_enrolled')
            ->exists();

        if ($exists) {
            Log::warning("Duplicate enrollment points prevented", [
                'user_id' => $userId,
                'course_id' => $courseId,
            ]);
            return false;
        }

        return DB::transaction(function () use ($userId, $courseId) {
            $user = User::findOrFail($userId);

            // Ensure UserPoints exists
            if (!$user->points) {
                UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                    'points_to_next_level' => 100,
                ]);
                $user->refresh();
            }

            // Award points and recalc level
            $user->points->increment('total_points', 50);
            $user->points->refresh();
            $this->recalculateLevel($user->points);

            // Create progress record
            UserProgress::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'type' => 'course_enrolled',
                'points_earned' => 50,
            ]);

            Log::info("Enrollment points awarded", [
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
        // Check if already awarded
        $exists = UserProgress::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->where('type', 'lesson_completed')
            ->exists();

        if ($exists) {
            Log::warning("Duplicate lesson points prevented", [
                'user_id' => $userId,
                'lesson_id' => $lessonId,
            ]);
            return false;
        }

        return DB::transaction(function () use ($userId, $courseId, $lessonId, $points, $timeSpent) {
            $user = User::findOrFail($userId);

            // Ensure UserPoints exists
            if (!$user->points) {
                UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                    'points_to_next_level' => 100,
                ]);
                $user->refresh();
            }

            // Award points and recalc level
            $user->points->increment('total_points', $points);
            $user->points->refresh();
            $this->recalculateLevel($user->points);

            // Create progress record
            UserProgress::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'lesson_id' => $lessonId,
                'type' => 'lesson_completed',
                'points_earned' => $points,
                'completed_at' => now(),
                'time_spent' => $timeSpent,
            ]);

            Log::info("Lesson points awarded", [
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
        // Check if already awarded
        $exists = UserProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('type', 'course_completed')
            ->exists();

        if ($exists) {
            Log::warning("Duplicate course completion points prevented", [
                'user_id' => $userId,
                'course_id' => $courseId,
            ]);
            return false;
        }

        return DB::transaction(function () use ($userId, $courseId) {
            $user = User::findOrFail($userId);

            // Ensure UserPoints exists
            if (!$user->points) {
                UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                    'points_to_next_level' => 100,
                ]);
                $user->refresh();
            }

            // Award points and recalc level
            $user->points->increment('total_points', 100);
            $user->points->refresh();
            $this->recalculateLevel($user->points);

            // Create progress record
            UserProgress::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'type' => 'course_completed',
                'points_earned' => 100,
                'completed_at' => now(),
            ]);

            Log::info("Course completion points awarded", [
                'user_id' => $userId,
                'course_id' => $courseId,
                'points' => 100,
            ]);

            return true;
        });
    }

    private function recalculateLevel(UserPoint $points): void
    {
        // Simple leveling: every 100 XP raises a level
        $level = max(1, (int) floor(($points->total_points ?? 0) / 100) + 1);
        $points->level = $level;
        $points->points_to_next_level = 100;
        $points->save();
    }

    /**
     * Ensure a user has UserPoint record initialized
     */
    public function ensureUserPoints(User $user): UserPoint
    {
        if (!$user->points) {
            $points = UserPoint::create([
                'user_id' => $user->id,
                'total_points' => 0,
                'level' => 1,
                'points_to_next_level' => 100,
            ]);
            $user->setRelation('points', $points);
            return $points;
        }

        return $user->points;
    }
}
