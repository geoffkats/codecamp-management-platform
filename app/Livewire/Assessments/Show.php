<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Show extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public Assessment $assessment;
    public $userAttempts = [];
    public $hasTaken = false;
    public $bestScore = null;
    public $attemptsRemaining = null;

    public function mount(Assessment $assessment)
    {
        $this->assessment = $assessment->load([
            'course',
            'lesson',
            'questions' => function ($q) {
                $q->orderBy('order');
            },
            'questions.options',
            'attempts' => function ($q) {
                $q->where('user_id', Auth::id())->latest();
            },
        ]);
        
        // Ensure questions relationship is properly loaded
        if (!$this->assessment->relationLoaded('questions')) {
            $this->assessment->load(['questions' => function ($q) {
                $q->orderBy('order');
            }, 'questions.options']);
        }

        // Check if user has taken this assessment
        $this->userAttempts = $this->assessment->attempts->take(5);
        $this->hasTaken = $this->userAttempts->count() > 0;
        
        if ($this->hasTaken) {
            // For assignment-type assessments, check if all attempts are pending
            if ($this->assessment->assessment_type === 'assignment') {
                $gradedAttempts = $this->assessment->attempts->filter(fn($attempt) => $attempt->score !== null);
                if ($gradedAttempts->isEmpty()) {
                    // All attempts are pending - set bestScore to null to indicate pending
                    $this->bestScore = null;
                } else {
                    // Calculate percentage score from score and total points
                    $maxScore = $this->assessment->questions ? $this->assessment->questions->sum('points') : 100;
                    if (!$maxScore || $maxScore <= 0) {
                        $maxScore = 100; // Default fallback
                    }
                    $bestScore = $gradedAttempts->max('score') ?? 0;
                    $this->bestScore = $maxScore > 0 ? ($bestScore / $maxScore) * 100 : 0;
                }
            } else {
                // Calculate percentage score from score and total points
                $maxScore = $this->assessment->questions ? $this->assessment->questions->sum('points') : 100;
                if (!$maxScore || $maxScore <= 0) {
                    $maxScore = 100; // Default fallback
                }
                $bestScore = $this->assessment->attempts->max('score') ?? 0;
                $this->bestScore = $maxScore > 0 ? ($bestScore / $maxScore) * 100 : 0;
            }
        }

        // Calculate remaining attempts
        $attemptCount = $this->assessment->attempts->count();
        if ($this->assessment->max_attempts > 0) {
            $this->attemptsRemaining = max(0, $this->assessment->max_attempts - $attemptCount);
        } else {
            $this->attemptsRemaining = 'unlimited';
        }
    }

    public function render()
    {
        // Get statistics based on assessment type
        $stats = $this->getStatistics();
        
        return view('livewire.assessments.show', [
            'stats' => $stats,
        ]);
    }

    private function getStatistics()
    {
        $allAttempts = AssessmentAttempt::where('assessment_id', $this->assessment->id)->get();
        
        if ($allAttempts->isEmpty()) {
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'pass_rate' => 0,
                'completion_rate' => 0,
            ];
        }

        $totalAttempts = $allAttempts->count();
        $maxScore = $this->assessment->questions ? $this->assessment->questions->sum('points') : 100;
        if (!$maxScore || $maxScore <= 0) {
            $maxScore = 100; // Default fallback
        }
        $avgScore = $allAttempts->avg('score') ?? 0;
        $averageScore = $maxScore > 0 ? ($avgScore / $maxScore) * 100 : 0;
        $passedCount = $allAttempts->where('is_passed', true)->count();
        $passRate = ($passedCount / $totalAttempts) * 100;

        return [
            'total_attempts' => $totalAttempts,
            'average_score' => round($averageScore, 2),
            'pass_rate' => round($passRate, 2),
            'completion_rate' => 100, // All attempts are completed
        ];
    }
}
