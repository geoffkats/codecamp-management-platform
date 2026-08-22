<?php

namespace App\Livewire\Gamification;

use App\Models\DailyChallenge;
use App\Models\DailyChallengeAttempt;
use App\Services\DailyChallengeTrackerService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DailyChallenges extends Component
{
    public $date;

    public function mount()
    {
        $this->date = now()->toDateString();
    }

    public function completeChallenge($challengeId, DailyChallengeTrackerService $tracker)
    {
        $challenge = DailyChallenge::findOrFail($challengeId);
        $result = $tracker->tryComplete($challenge, Auth::id());

        if ($result['success']) {
            session()->flash('message', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function render()
    {
        $today = $this->date ?? now()->toDateString();
        $user = Auth::user();
        $userCourseIds = $user?->enrollments()->pluck('course_id')->filter()->unique() ?? collect();

        $challenges = DailyChallenge::where('is_active', true)
            ->where(function ($query) use ($userCourseIds) {
                $query->whereNull('course_id');

                if ($userCourseIds->isNotEmpty()) {
                    $query->orWhereIn('course_id', $userCourseIds);
                }
            })
            ->where(function ($query) use ($today) {
                $query->where('date', $today)
                    ->orWhereNull('date')
                    ->orWhere(function ($q) use ($today) {
                        $q->where('date', '>=', now()->subDays(30)->toDateString())
                            ->where('date', '<=', now()->addDays(30)->toDateString());
                    });
            })
            ->with('course')
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $userAttempts = DailyChallengeAttempt::where('user_id', Auth::id())
            ->whereIn('challenge_id', $challenges->pluck('id'))
            ->get()
            ->keyBy('challenge_id');

        return view('livewire.gamification.daily-challenges', [
            'challenges' => $challenges,
            'userAttempts' => $userAttempts,
        ]);
    }
}
