<?php

namespace App\Livewire\Gamification;

use App\Models\User;
use App\Models\UserPoint;
use Livewire\Component;

class UserPoints extends Component
{
    public $userId;
    public $points;
    public $level;
    public $pointsToNextLevel;

    public function mount($userId = null)
    {
        $this->userId = $userId ?? auth()->id();
        $this->loadPoints();
    }

    public function loadPoints()
    {
        $userPoint = UserPoint::firstOrCreate(
            ['user_id' => $this->userId],
            [
                'total_points' => 0,
                'level' => 1,
                'points_to_next_level' => 100,
            ]
        );

        $this->points = $userPoint->total_points;
        $this->level = $userPoint->level;
        $this->pointsToNextLevel = $userPoint->points_to_next_level;
    }

    public function render()
    {
        $userPoint = UserPoint::with('user')->where('user_id', $this->userId)->first();
        $user = User::find($this->userId);

        return view('livewire.gamification.user-points', [
            'userPoint' => $userPoint,
            'user' => $user,
        ]);
    }
}

