<?php

namespace App\Livewire\Gamification;

use App\Models\Leaderboard as LeaderboardModel;
use App\Models\UserPoint;
use Livewire\Component;
use Livewire\WithPagination;

class Leaderboard extends Component
{
    use WithPagination;

    public $type = 'overall';
    public $courseId = null;
    public $period = 'all';

    public function render()
    {
        $leaderboard = UserPoint::with('user')
            ->orderByDesc('total_points')
            ->when($this->type === 'course' && $this->courseId, function ($q) {
                $q->whereHas('user.enrollments', function ($query) {
                    $query->where('course_id', $this->courseId);
                });
            })
            ->when($this->period === 'week', function ($q) {
                $q->where('updated_at', '>=', now()->subWeek());
            })
            ->when($this->period === 'month', function ($q) {
                $q->where('updated_at', '>=', now()->subMonth());
            })
            ->paginate(20);

        return view('livewire.gamification.leaderboard', [
            'leaderboard' => $leaderboard
        ]);
    }
}
