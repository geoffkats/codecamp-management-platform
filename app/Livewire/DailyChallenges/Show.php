<?php

namespace App\Livewire\DailyChallenges;

use App\Models\DailyChallenge;
use App\Models\DailyChallengeAttempt;
use App\Services\DailyChallengeTrackerService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public DailyChallenge $dailyChallenge;
    public $attempt = null;
    public $requirementStatus = [];

    public function mount(DailyChallenge $dailyChallenge)
    {
        $this->dailyChallenge = $dailyChallenge->load('attempts');

        if ($this->dailyChallenge->course_id && ! $this->userEnrolledInCourse($this->dailyChallenge->course_id)) {
            session()->flash('error', 'This challenge is restricted to its course.');

            return redirect()->route('daily-challenges.index');
        }

        if (! $this->dailyChallenge->is_active) {
            session()->flash('error', 'This challenge is not currently available.');

            return redirect()->route('daily-challenges.index');
        }

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

        $this->refreshProgress();
    }

    public function completeChallenge(DailyChallengeTrackerService $tracker)
    {
        $result = $tracker->tryComplete($this->dailyChallenge, Auth::id());

        if ($result['success']) {
            session()->flash('success', $result['message']);
            $this->attempt->refresh();
            $this->refreshProgress();
        } elseif (str_contains($result['message'], 'already completed')) {
            session()->flash('info', $result['message']);
        } else {
            session()->flash('error', $result['message']);
            $this->refreshProgress();
        }
    }

    protected function refreshProgress(): void
    {
        $this->requirementStatus = app(DailyChallengeTrackerService::class)
            ->evaluate($this->dailyChallenge, Auth::id());
    }

    protected function userEnrolledInCourse(int $courseId): bool
    {
        return Auth::user()?->enrollments()->where('course_id', $courseId)->exists() ?? false;
    }

    public function render()
    {
        return view('livewire.daily-challenges.show', [
            'requirementStatus' => $this->requirementStatus,
        ]);
    }
}
