<?php

namespace App\Livewire\Gamification;

use App\Models\DailyChallenge;
use App\Models\DailyChallengeAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DailyChallenges extends Component
{
    public $date;

    public function mount()
    {
        $this->date = now()->toDateString();
    }

    public function completeChallenge($challengeId)
    {
        $challenge = DailyChallenge::findOrFail($challengeId);

        $attempt = DailyChallengeAttempt::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'challenge_id' => $challengeId,
            ],
            [
                'attempted_at' => now(),
                'is_completed' => false,
                'points_earned' => 0,
            ]
        );

        if (!$attempt->is_completed) {
            $attempt->update([
                'is_completed' => true,
                'completed_at' => now(),
                'points_earned' => $challenge->reward_points,
            ]);

            // Award points to user
            $userPoints = Auth::user()->points ?? Auth::user()->points()->create([
                'total_points' => 0,
                'level' => 1,
                'points_to_next_level' => 100,
            ]);

            $userPoints->increment('total_points', $challenge->reward_points);

            session()->flash('message', 'Challenge completed! You earned ' . $challenge->reward_points . ' points.');
        }
    }

    public function render()
    {
        $today = $this->date ?? now()->toDateString();
        
        // Get challenges for today, challenges with no date (always available), 
        // challenges from the last 30 days, or future challenges up to 30 days ahead
        $challenges = DailyChallenge::where('is_active', true)
            ->where(function ($query) use ($today) {
                $query->where('date', $today)
                      ->orWhereNull('date')
                      ->orWhere(function ($q) use ($today) {
                          $q->where('date', '>=', now()->subDays(30)->toDateString())
                            ->where('date', '<=', now()->addDays(30)->toDateString());
                      });
            })
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

