<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Lesson;
use App\Models\StudentLessonProgress;
use App\Models\User;
use App\Models\VideoProgress;

class LessonCompletionService
{
    /**
     * Check if a user can complete a lesson
     * 
     * @return array ['can_complete' => bool, 'requirements' => array, 'missing' => array]
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

            $minutesSpent = $progress && $progress->started_at
                ? now()->diffInMinutes($progress->started_at)
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
            $hasCompleted = false;
            
            // For quizzes and other assessments, check if there's a passed attempt
            $bestAttempt = $assessment->attempts()
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('is_passed', true)
                ->first();
            
            $hasCompleted = (bool) $bestAttempt;
            
            if (!$hasCompleted) {
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
                ];
            }
        }
        
        $canComplete = empty($missing);
        
        return [
            'can_complete' => $canComplete,
            'requirements' => $requirements,
            'missing' => $missing,
        ];
    }

    /**
     * Whether a student may start an assessment (index "ready" state / take access).
     *
     * Code Club students skip lesson-order progression when enrolled in the course.
     * CodeCamp students must satisfy lesson completion prerequisites first.
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

    /**
     * Get completion requirements summary
     */
    public function getCompletionSummary(Lesson $lesson, User $user): string
    {
        $check = $this->canCompleteLesson($lesson, $user);
        
        if ($check['can_complete']) {
            return 'All requirements met. Ready to complete!';
        }
        
        $messages = [];
        
        if (!empty($check['requirements']['required_assessments'])) {
            $count = count($check['requirements']['required_assessments']);
            $messages[] = "Complete {$count} required assessment(s)";
        }

        if (!empty($check['requirements']['required_assignments'])) {
            $count = count($check['requirements']['required_assignments']);
            $messages[] = "Submit {$count} assignment(s)";
        }

        if (!empty($check['requirements']['minimum_minutes']) && isset($check['requirements']['time_spent_minutes'])) {
            $remaining = max(0, $check['requirements']['minimum_minutes'] - $check['requirements']['time_spent_minutes']);
            if ($remaining > 0) {
                $messages[] = "Spend {$remaining} more minute(s) in this lesson";
            }
        }
        
        return implode(', ', $messages);
    }
}


