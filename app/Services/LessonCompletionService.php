<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\StudentLessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LessonCompletionService
{
    /**
     * Check if a user can complete a lesson.
     *
     * @return array{can_complete: bool, requirements: array, missing: array}
     */
    public function canCompleteLesson(Lesson $lesson, User $user): array
    {
        $requirements = [
            'video_completed' => true,
            'required_assessments' => [],
            'required_assignments' => [],
        ];

        $missing = [];

        $lesson->loadMissing(['assessments', 'assignments']);
        $requiredAssessments = $lesson->assessments;
        $quizAssessments = $requiredAssessments->where('assessment_type', '!=', 'assignment');
        $assignmentAssessments = $requiredAssessments->where('assessment_type', 'assignment');
        $legacyAssignments = $lesson->assignments;

        $hasRequiredWork = $quizAssessments->isNotEmpty()
            || $assignmentAssessments->isNotEmpty()
            || $legacyAssignments->isNotEmpty();

        $minimumMinutes = $lesson->duration_minutes ? max(1, (int) $lesson->duration_minutes) : 15;

        if (! $hasRequiredWork) {
            $progress = StudentLessonProgress::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();

            // Carbon 3 returns signed diffs; use absolute minutes so time gates work.
            $minutesSpent = $progress && $progress->started_at
                ? (int) abs(now()->diffInMinutes($progress->started_at))
                : 0;

            $requirements['minimum_minutes'] = $minimumMinutes;
            $requirements['time_spent_minutes'] = $minutesSpent;

            if ($minutesSpent < $minimumMinutes) {
                $remaining = $minimumMinutes - $minutesSpent;
                $missing[] = [
                    'type' => 'time',
                    'message' => "Spend at least {$minimumMinutes} minutes on this lesson ({$remaining} min remaining).",
                    'required_minutes' => $minimumMinutes,
                    'remaining_minutes' => $remaining,
                ];
            }
        }

        foreach ($assignmentAssessments as $assessment) {
            $submitted = $assessment->attempts()
                ->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->where('status', 'completed')->orWhereNotNull('completed_at');
                })
                ->exists();

            if (! $submitted) {
                $requirements['required_assignments'][] = [
                    'id' => $assessment->id,
                    'title' => $assessment->title,
                    'type' => 'assignment',
                ];

                $missing[] = [
                    'type' => 'assignment',
                    'id' => $assessment->id,
                    'title' => $assessment->title,
                    'type_label' => 'Assignment',
                    'route' => 'assessments.take',
                    'message' => 'Submit: '.$assessment->title,
                ];
            }
        }

        foreach ($legacyAssignments as $assignment) {
            $submitted = $assignment->submissions()
                ->where('user_id', $user->id)
                ->whereIn('status', ['submitted', 'graded', 'returned'])
                ->exists();

            if (! $submitted) {
                $requirements['required_assignments'][] = [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'type' => 'assignment',
                ];

                $missing[] = [
                    'type' => 'assignment',
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'type_label' => 'Assignment',
                    'route' => 'assignments.submit',
                    'message' => 'Submit: '.$assignment->title,
                ];
            }
        }

        foreach ($quizAssessments as $assessment) {
            $bestAttempt = $assessment->attempts()
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('is_passed', true)
                ->first();

            if (! $bestAttempt) {
                $requirements['required_assessments'][] = [
                    'id' => $assessment->id,
                    'title' => $assessment->title,
                    'type' => $assessment->assessment_type,
                ];

                $missing[] = [
                    'type' => 'assessment',
                    'id' => $assessment->id,
                    'title' => $assessment->title,
                    'type_label' => ucfirst(str_replace('_', ' ', $assessment->assessment_type)),
                    'route' => 'assessments.show',
                    'message' => 'Pass: '.$assessment->title,
                ];
            }
        }

        return [
            'can_complete' => empty($missing),
            'requirements' => $requirements,
            'missing' => $missing,
        ];
    }

    public function isLessonCompleted(User $user, Lesson|int $lesson): bool
    {
        $lessonId = $lesson instanceof Lesson ? $lesson->id : $lesson;

        $studentProgress = StudentLessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->first();

        if ($studentProgress && ($studentProgress->status === 'completed' || $studentProgress->completed_at)) {
            return true;
        }

        return LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->where('is_completed', true)
            ->exists();
    }

    /**
     * Record that the student opened the lesson without wiping a completed status
     * or resetting started_at (which would break time-based completion).
     */
    public function ensureStarted(User $user, Lesson $lesson): void
    {
        $progress = StudentLessonProgress::firstOrNew([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        $completedElsewhere = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->where('is_completed', true)
            ->exists();

        $alreadyCompleted = ($progress->exists && ($progress->status === 'completed' || filled($progress->completed_at)))
            || $completedElsewhere;

        if ($alreadyCompleted) {
            $needsHeal = ! $progress->exists || $progress->status !== 'completed';

            $progress->status = 'completed';
            $progress->progress_percentage = 100;
            $progress->completed_at = $progress->completed_at ?? now();
            $progress->started_at = $progress->started_at ?? now();
            $progress->last_accessed_at = now();
            $progress->save();

            if ($needsHeal) {
                $lesson->loadMissing('module.course');
                $course = $lesson->module?->course ?? Course::find($lesson->course_id);
                if ($course) {
                    $this->updateCourseProgress($user, $course);
                }
            }

            return;
        }

        if (! $progress->exists) {
            $progress->status = 'in_progress';
            $progress->started_at = now();
            $progress->last_accessed_at = now();
            $progress->save();

            return;
        }

        if ($progress->status !== 'completed') {
            $progress->status = 'in_progress';
        }
        if (! $progress->started_at) {
            $progress->started_at = now();
        }
        $progress->last_accessed_at = now();
        $progress->save();
    }

    /**
     * Complete the lesson when requirements are met. Safe to call after assessment submit/grade.
     *
     * @return array{completed: bool, already: bool, points: int, check?: array}
     */
    public function completeIfEligible(User $user, Lesson $lesson): array
    {
        if ($this->isLessonCompleted($user, $lesson)) {
            // Heal inconsistent student_lesson_progress rows left by older bugs.
            $this->ensureStarted($user, $lesson);

            return ['completed' => true, 'already' => true, 'points' => 0];
        }

        $check = $this->canCompleteLesson($lesson, $user);
        if (! $check['can_complete']) {
            return ['completed' => false, 'already' => false, 'points' => 0, 'check' => $check];
        }

        return $this->forceComplete($user, $lesson);
    }

    /**
     * Write completion regardless of requirements (staff QA / already-validated callers).
     *
     * @return array{completed: bool, already: bool, points: int}
     */
    public function forceComplete(User $user, Lesson $lesson): array
    {
        if ($this->isLessonCompleted($user, $lesson)) {
            $this->ensureStarted($user, $lesson);

            return ['completed' => true, 'already' => true, 'points' => 0];
        }

        $lesson->loadMissing('module.course');
        $course = $lesson->module?->course ?? Course::find($lesson->course_id);
        if (! $course) {
            return ['completed' => false, 'already' => false, 'points' => 0];
        }

        $points = $this->resolvePoints($lesson->difficulty_level);
        $timeSpent = $this->calculateTimeSpent($user->id, $lesson->id);
        $existingStartedAt = StudentLessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->value('started_at');

        DB::transaction(function () use ($user, $lesson, $course, $points, $timeSpent, $existingStartedAt) {
            StudentLessonProgress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'status' => 'completed',
                    'progress_percentage' => 100,
                    'completed_at' => now(),
                    'started_at' => $existingStartedAt ?? now(),
                    'time_spent_minutes' => round($timeSpent / 60, 2),
                    'last_accessed_at' => now(),
                    'completion_data' => [
                        'completed_at' => now()->toIso8601String(),
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

            $this->updateCourseProgress($user, $course);

            $badgeService = app(BadgeAwardingService::class);
            $badgeService->onLessonComplete($user);
            $badgeService->checkLessonCompletionBadges($user);
            $badgeService->checkLevelBadges($user);
            $badgeService->checkPointMilestoneBadges($user);

            Cache::forget("lesson.{$lesson->id}.user.{$user->id}");
        });

        return ['completed' => true, 'already' => false, 'points' => $points];
    }

    /**
     * @param  array<int>  $lessonIds
     * @return array<int>
     */
    public function completedLessonIds(User $user, array $lessonIds): array
    {
        if ($lessonIds === []) {
            return [];
        }

        $fromStudent = StudentLessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where(function ($q) {
                $q->where('status', 'completed')->orWhereNotNull('completed_at');
            })
            ->pluck('lesson_id')
            ->all();

        $fromLegacy = LessonProgress::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->all();

        return array_values(array_unique(array_merge($fromStudent, $fromLegacy)));
    }

    public function updateCourseProgress(User $user, Course $course): void
    {
        $allLessonIds = Lesson::whereHas('module', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })->pluck('id')->all();

        $totalLessons = count($allLessonIds);
        if ($totalLessons === 0) {
            return;
        }

        $completedLessonIds = $this->completedLessonIds($user, $allLessonIds);
        $completedLessons = count($completedLessonIds);
        $progressPercentage = round(($completedLessons / $totalLessons) * 100, 2);

        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            return;
        }

        $isCompleted = $enrollment->completed_at !== null || $completedLessons >= $totalLessons;

        $enrollment->update([
            'progress_percentage' => $isCompleted ? 100 : $progressPercentage,
            'lessons_completed' => $completedLessons,
        ]);

        if ($completedLessons >= $totalLessons && ! $enrollment->completed_at) {
            $enrollment->update([
                'completed_at' => now(),
                'progress_percentage' => 100,
            ]);

            app(PointsService::class)->awardCourseCompletionPoints($user->id, $course->id);

            $badgeService = app(BadgeAwardingService::class);
            $badgeService->checkCourseCompletionBadges($user);
            $badgeService->checkLevelBadges($user);
            $badgeService->checkPointMilestoneBadges($user);
        }
    }

    /**
     * Whether a student may start an assessment (index "ready" state / take access).
     *
     * @return array{can_access: bool, missing: array<int, array<string, mixed>>}
     */
    public function canAccessAssessment(Assessment $assessment, User $user): array
    {
        if ($user->hasAnyRole(['admin', 'supervisor', 'teacher'])) {
            return ['can_access' => true, 'missing' => []];
        }

        $course = $assessment->course ?? $assessment->lesson?->course;

        if ($course && ! $course->enrollments()->where('user_id', $user->id)->exists()) {
            return [
                'can_access' => false,
                'missing' => [[
                    'type' => 'enrollment',
                    'message' => 'You must be enrolled in this course.',
                ]],
            ];
        }

        if ($assessment->is_locked) {
            return [
                'can_access' => false,
                'missing' => [[
                    'type' => 'locked',
                    'message' => 'This quiz is locked. Wait for your instructor to unlock it.',
                ]],
            ];
        }

        $lesson = $assessment->lesson;
        if ($lesson && $lesson->is_locked) {
            return [
                'can_access' => false,
                'missing' => [[
                    'type' => 'locked',
                    'message' => 'This lesson is locked. Wait for your instructor to unlock it.',
                ]],
            ];
        }

        return ['can_access' => true, 'missing' => []];
    }

    public function getCompletionSummary(Lesson $lesson, User $user): string
    {
        $check = $this->canCompleteLesson($lesson, $user);

        if ($check['can_complete']) {
            return 'All requirements met. Ready to complete!';
        }

        $messages = [];

        if (! empty($check['requirements']['required_assessments'])) {
            $count = count($check['requirements']['required_assessments']);
            $messages[] = "Complete {$count} required assessment(s)";
        }

        if (! empty($check['requirements']['required_assignments'])) {
            $count = count($check['requirements']['required_assignments']);
            $messages[] = "Submit {$count} assignment(s)";
        }

        if (! empty($check['requirements']['minimum_minutes']) && isset($check['requirements']['time_spent_minutes'])) {
            $remaining = max(0, $check['requirements']['minimum_minutes'] - $check['requirements']['time_spent_minutes']);
            if ($remaining > 0) {
                $messages[] = "Spend {$remaining} more minute(s) in this lesson";
            }
        }

        return implode(', ', $messages);
    }

    private function calculateTimeSpent(int $userId, int $lessonId): int
    {
        $progress = StudentLessonProgress::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();

        if ($progress && $progress->started_at) {
            return (int) abs(now()->diffInSeconds($progress->started_at));
        }

        return 0;
    }

    private function resolvePoints(?string $difficultyLevel): int
    {
        if (! $difficultyLevel) {
            return 10;
        }

        return match (strtolower($difficultyLevel)) {
            'beginner' => 5,
            'intermediate' => 10,
            'advanced' => 15,
            default => 10,
        };
    }
}
