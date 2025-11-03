<?php

namespace App\Livewire\DailyChallenges;

use App\Models\DailyChallenge;
use App\Models\DailyChallengeAttempt;
use App\Models\StudentLessonProgress;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\CourseEnrollment;
use App\Models\DiscussionReply;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Carbon\Carbon;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public DailyChallenge $dailyChallenge;
    public $attempt = null;
    public $progress = [];
    public $requirementStatus = [];

    public function mount(DailyChallenge $dailyChallenge)
    {
        $this->dailyChallenge = $dailyChallenge->load('attempts');
        
        // Check if challenge is active
        if (!$this->dailyChallenge->is_active) {
            session()->flash('error', 'This challenge is not currently available.');
            return redirect()->route('daily-challenges.index');
        }

        // Get or create user attempt
        $this->attempt = DailyChallengeAttempt::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'challenge_id' => $this->dailyChallenge->id,
            ],
            [
                'attempted_at' => now(),
                'is_completed' => false,
                'points_earned' => 0,
            ]
        );

        // Load progress data
        $this->progress = $this->attempt->progress_data ?? [];
        
        // Calculate requirement status for display
        $this->updateRequirementStatus();
    }

    public function completeChallenge()
    {
        if ($this->attempt->is_completed) {
            session()->flash('info', 'You have already completed this challenge!');
            return;
        }

        // Re-validate requirements before allowing completion
        $this->updateRequirementStatus();
        
        // Check if challenge requirements are met
        if ($this->checkRequirements()) {
            $this->attempt->update([
                'is_completed' => true,
                'completed_at' => now(),
                'points_earned' => $this->dailyChallenge->reward_points,
                'progress_data' => $this->requirementStatus,
            ]);

            // Award points to user
            $userPoints = Auth::user()->points ?? Auth::user()->points()->create([
                'total_points' => 0,
                'level' => 1,
                'points_to_next_level' => 100,
            ]);

            $userPoints->increment('total_points', $this->dailyChallenge->reward_points);

            session()->flash('success', 'Challenge completed! You earned ' . $this->dailyChallenge->reward_points . ' points.');
            $this->attempt->refresh();
        } else {
            session()->flash('error', 'Challenge requirements not yet met. ' . $this->getRequirementMessage());
        }
    }

    protected function updateRequirementStatus()
    {
        $requirements = $this->dailyChallenge->requirements ?? [];
        $type = $this->dailyChallenge->type;
        $today = Carbon::today();
        $userId = Auth::id();

        $this->requirementStatus = [
            'met' => false,
            'current' => 0,
            'required' => 0,
            'message' => '',
        ];

        switch ($type) {
            case 'lesson_completion':
                $requiredCount = $requirements['count'] ?? 0;
                
                // Count lessons completed today
                $completedToday = StudentLessonProgress::where('user_id', $userId)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', $today)
                    ->count();
                
                // Also check lesson_progress table
                $completedToday += LessonProgress::where('user_id', $userId)
                    ->where('is_completed', true)
                    ->whereDate('completed_at', $today)
                    ->count();
                
                $this->requirementStatus = [
                    'met' => $completedToday >= $requiredCount,
                    'current' => $completedToday,
                    'required' => $requiredCount,
                    'message' => "Completed {$completedToday} of {$requiredCount} lessons today",
                ];
                break;

            case 'quiz_score':
                $requiredScore = $requirements['score'] ?? 100;
                
                // Check if student achieved the required score on any quiz today
                $perfectAttempts = QuizAttempt::where('user_id', $userId)
                    ->where('score', '>=', $requiredScore)
                    ->whereDate('completed_at', $today)
                    ->exists();
                
                $this->requirementStatus = [
                    'met' => $perfectAttempts,
                    'current' => $perfectAttempts ? 1 : 0,
                    'required' => 1,
                    'message' => $perfectAttempts 
                        ? "Achieved {$requiredScore}% on a quiz today" 
                        : "Need to achieve {$requiredScore}% on any quiz today",
                ];
                break;

            case 'study_time':
                $requiredMinutes = $requirements['minutes'] ?? 0;
                
                // Sum time spent today from StudentLessonProgress
                $timeSpentToday = StudentLessonProgress::where('user_id', $userId)
                    ->whereDate('last_accessed_at', $today)
                    ->sum('time_spent_minutes');
                
                // Also check lesson_progress
                $timeSpentToday += LessonProgress::where('user_id', $userId)
                    ->whereDate('updated_at', $today)
                    ->sum('time_spent') / 60; // Convert seconds to minutes
                
                $this->requirementStatus = [
                    'met' => $timeSpentToday >= $requiredMinutes,
                    'current' => (int) $timeSpentToday,
                    'required' => $requiredMinutes,
                    'message' => "Studied {$timeSpentToday} of {$requiredMinutes} minutes today",
                ];
                break;

            case 'course_progress':
                $requiredPercentage = $requirements['percentage'] ?? 0;
                
                // Check if any course has the required progress increase today
                $enrollmentProgress = CourseEnrollment::where('user_id', $userId)
                    ->where('progress_percentage', '>=', $requiredPercentage)
                    ->whereDate('updated_at', $today)
                    ->exists();
                
                $this->requirementStatus = [
                    'met' => $enrollmentProgress,
                    'current' => $enrollmentProgress ? $requiredPercentage : 0,
                    'required' => $requiredPercentage,
                    'message' => $enrollmentProgress
                        ? "Completed {$requiredPercentage}% of a course today"
                        : "Need to complete {$requiredPercentage}% of any course today",
                ];
                break;

            case 'forum_participation':
                $requiredPosts = $requirements['posts'] ?? 0;
                
                // Count discussion replies created today
                $postsToday = DiscussionReply::where('user_id', $userId)
                    ->whereDate('created_at', $today)
                    ->count();
                
                $this->requirementStatus = [
                    'met' => $postsToday >= $requiredPosts,
                    'current' => $postsToday,
                    'required' => $requiredPosts,
                    'message' => "Posted {$postsToday} of {$requiredPosts} replies today",
                ];
                break;

            default:
                $this->requirementStatus = [
                    'met' => false,
                    'current' => 0,
                    'required' => 0,
                    'message' => 'Unknown challenge type',
                ];
        }
    }

    protected function checkRequirements(): bool
    {
        $this->updateRequirementStatus();
        return $this->requirementStatus['met'] ?? false;
    }

    protected function getRequirementMessage(): string
    {
        return $this->requirementStatus['message'] ?? 'Please complete the required activities first.';
    }

    public function render()
    {
        return view('livewire.daily-challenges.show', [
            'requirementStatus' => $this->requirementStatus,
        ]);
    }
}
