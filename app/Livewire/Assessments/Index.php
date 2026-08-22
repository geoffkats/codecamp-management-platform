<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Services\LessonCompletionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $user = Auth::user();
        $completionService = app(LessonCompletionService::class);
        
        // Get all assessments and categorize them
        $assessments = Assessment::with(['lesson', 'questions'])
            ->whereHas('lesson.module.course.enrollments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('lesson_id')
            ->get();
        
        $assessmentsByStatus = [
            'ready' => [],
            'locked' => [],
            'completed' => [],
        ];
        
        foreach ($assessments as $assessment) {
            $lesson = $assessment->lesson;
            
            // Check if assessment has been completed
            $completedAttempt = AssessmentAttempt::where('user_id', $user->id)
                ->where('assessment_id', $assessment->id)
                ->where('status', 'completed')
                ->latest('completed_at')
                ->first();
            
            if ($completedAttempt) {
                // Calculate percentage from points
                $percentage = $completedAttempt->scorePercentage() ?? 0;
                
                $assessmentsByStatus['completed'][] = [
                    'assessment' => $assessment,
                    'lesson' => $lesson,
                    'attempt' => $completedAttempt,
                    'score' => round($percentage, 2),
                    'passed' => $completedAttempt->is_passed,
                ];
            } else {
                $accessCheck = $completionService->canAccessAssessment($assessment, $user);

                if ($accessCheck['can_access']) {
                    $assessmentsByStatus['ready'][] = [
                        'assessment' => $assessment,
                        'lesson' => $lesson,
                    ];
                } else {
                    $assessmentsByStatus['locked'][] = [
                        'assessment' => $assessment,
                        'lesson' => $lesson,
                        'missing' => $accessCheck['missing'],
                    ];
                }
            }
        }
        
        return view('livewire.assessments.index', [
            'assessmentsByStatus' => $assessmentsByStatus,
        ]);
    }
}
