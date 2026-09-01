<?php

namespace App\Http\Controllers\Lessons;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\StudentLessonProgress;
use App\Services\BadgeAwardingService;
use App\Services\PointsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CompletionController extends Controller
{
    public function store(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = Auth::user();

        $course = $lesson->module->course;

        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $isInstructor = $course->instructor_id === $user->id
            || $user->hasRole('admin')
            || $user->hasRole('supervisor');

        $isIctTeacherWithAccess = false;
        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();
            $isIctTeacherWithAccess = $schoolId
                && $course->schools
                    ->where('id', (int) $schoolId)
                    ->where('pivot.is_active', true)
                    ->isNotEmpty();
        }

        if (!$enrollment && !$isInstructor && !$isIctTeacherWithAccess) {
            abort(403, 'You must be enrolled in this course to complete lessons.');
        }

        if (! $isInstructor && ! $isIctTeacherWithAccess) {
            $check = app(\App\Services\LessonCompletionService::class)->canCompleteLesson($lesson, $user);
            if (! $check['can_complete']) {
                return back()->with('error', 'Submit the lesson assignment (and any required quizzes) before marking this lesson complete.');
            }
        }

        DB::transaction(function () use ($lesson, $course, $user) {
            $timeSpent = $this->calculateTimeSpent($lesson->id, $user->id);
            $points = $this->resolvePoints($lesson->difficulty_level);

            StudentLessonProgress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'status' => 'completed',
                    'progress_percentage' => 100,
                    'completed_at' => now(),
                    'time_spent_minutes' => round($timeSpent / 60, 2),
                    'last_accessed_at' => now(),
                    'completion_data' => [
                        'completed_at' => now()->toIso8601String(),
                        'video_completed' => true,
                        'points_earned' => $points,
                    ],
                ]
            );

            LessonProgress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'is_completed' => true,
                    'completed_at' => now(),
                    'time_spent' => $timeSpent,
                ]
            );

            app(PointsService::class)->awardLessonPoints(
                $user->id,
                $course->id,
                $lesson->id,
                $points,
                $timeSpent
            );

            $this->updateCourseProgress($course->id, $user->id);

            $badgeService = app(BadgeAwardingService::class);
            $badgeService->checkLessonCompletionBadges($user);
            $badgeService->checkLevelBadges($user);
            $badgeService->checkPointMilestoneBadges($user);

            Cache::forget("lesson.{$lesson->id}.user." . $user->id);
        });

        return back()->with('message', 'Lesson marked as complete.');
    }

    private function calculateTimeSpent(int $lessonId, int $userId): int
    {
        $progress = StudentLessonProgress::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();

        if ($progress && $progress->started_at) {
            return abs(now()->diffInSeconds($progress->started_at));
        }

        return 0;
    }

    private function resolvePoints(?string $difficultyLevel): int
    {
        if (!$difficultyLevel) {
            return 10;
        }

        return match (strtolower($difficultyLevel)) {
            'beginner' => 5,
            'intermediate' => 10,
            'advanced' => 15,
            default => 10,
        };
    }

    private function updateCourseProgress(int $courseId, int $userId): void
    {
        $totalLessons = Lesson::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->count();

        if ($totalLessons === 0) {
            return;
        }

        $allLessonIds = Lesson::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->pluck('id')->toArray();

        $completedLessonIds1 = StudentLessonProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $allLessonIds)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->toArray();

        $completedLessonIds2 = LessonProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $allLessonIds)
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->toArray();

        $completedLessonIds = array_unique(array_merge($completedLessonIds1, $completedLessonIds2));
        $completedLessons = count($completedLessonIds);

        $progressPercentage = round(($completedLessons / $totalLessons) * 100, 2);

        $enrollment = CourseEnrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return;
        }

        $isCompleted = $enrollment->completed_at !== null || $completedLessons >= $totalLessons;

        $enrollment->update([
            'progress_percentage' => $isCompleted ? 100 : $progressPercentage,
            'lessons_completed' => $completedLessons,
        ]);

        if ($completedLessons >= $totalLessons && !$enrollment->completed_at) {
            $enrollment->update([
                'completed_at' => now(),
                'progress_percentage' => 100,
            ]);
            app(PointsService::class)->awardCourseCompletionPoints($userId, $courseId);
        }
    }
}
