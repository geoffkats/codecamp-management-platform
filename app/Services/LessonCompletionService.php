<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Lesson;
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
        
        // Check video completion
        if ($lesson->video_url || $lesson->lesson_type === 'video') {
            $videoProgress = VideoProgress::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();
            
            $requirements['video_completed'] = $videoProgress?->is_completed ?? false;
            
            if (!$requirements['video_completed']) {
                $missing[] = [
                    'type' => 'video',
                    'message' => 'Complete watching the video',
                    'progress' => $videoProgress?->progress_percentage ?? 0,
                ];
            }
        }
        
        // Check required assessments
        $requiredAssessments = $lesson->assessments()->where('is_required', true)->get();
        
        foreach ($requiredAssessments as $assessment) {
            $hasCompleted = false;
            
            if ($assessment->assessment_type === 'assignment') {
                // For assignment-type assessments, check if there's a completed attempt with a score
                $attempt = $assessment->attempts()
                    ->where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->whereNotNull('score')
                    ->where('score', '>', 0)
                    ->first();
                
                $hasCompleted = (bool) $attempt;
            } else {
                // For quizzes and other assessments, check if there's a passed attempt
                $bestAttempt = $assessment->attempts()
                    ->where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->where('is_passed', true)
                    ->first();
                
                $hasCompleted = (bool) $bestAttempt;
            }
            
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
        
        // Check assignments (separate Assignment model)
        $requiredAssignments = $lesson->assignments;
        
        foreach ($requiredAssignments as $assignment) {
            $submission = $assignment->submissions()
                ->where('user_id', $user->id)
                ->first();
            
            $isGraded = $submission && $submission->status === 'graded';
            
            if (!$isGraded) {
                $requirements['required_assignments'][] = [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                ];
                
                $missing[] = [
                    'type' => 'assignment',
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'type_label' => 'Assignment',
                    'route' => 'assignments.show',
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
     * Get completion requirements summary
     */
    public function getCompletionSummary(Lesson $lesson, User $user): string
    {
        $check = $this->canCompleteLesson($lesson, $user);
        
        if ($check['can_complete']) {
            return 'All requirements met. Ready to complete!';
        }
        
        $messages = [];
        
        if (!$check['requirements']['video_completed']) {
            $progress = VideoProgress::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();
            
            $remainingPercent = 100 - ($progress?->progress_percentage ?? 0);
            $messages[] = "Complete watching the video ({$remainingPercent}% remaining)";
        }
        
        if (!empty($check['requirements']['required_assessments'])) {
            $count = count($check['requirements']['required_assessments']);
            $messages[] = "Complete {$count} required assessment(s)";
        }
        
        if (!empty($check['requirements']['required_assignments'])) {
            $count = count($check['requirements']['required_assignments']);
            $messages[] = "Complete {$count} required assignment(s)";
        }
        
        return implode(', ', $messages);
    }
}


