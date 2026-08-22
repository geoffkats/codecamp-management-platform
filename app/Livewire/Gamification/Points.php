<?php

namespace App\Livewire\Gamification;

use App\Models\UserProgress;
use App\Support\LevelSystem;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Points extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $user = Auth::user()->load('points');
        $info = LevelSystem::info($user->points->total_points ?? 0);

        $pointsHistory = UserProgress::where('user_id', Auth::id())
            ->where('points_earned', '>', 0)
            ->with(['course', 'lesson'])
            ->latest()
            ->paginate(20);

        $breakdown = [
            'lesson_completed' => UserProgress::where('user_id', Auth::id())
                ->where('type', 'lesson_completed')
                ->sum('points_earned'),
            'quiz_completed' => UserProgress::where('user_id', Auth::id())
                ->where('type', 'quiz_completed')
                ->sum('points_earned'),
            'course_enrolled' => UserProgress::where('user_id', Auth::id())
                ->where('type', 'course_enrolled')
                ->sum('points_earned'),
        ];

        return view('livewire.gamification.points', [
            'totalPoints' => $info['xp'],
            'currentLevel' => $info['level'],
            'rankName' => $info['name'],
            'pointsInCurrentLevel' => $info['xp_in_level'],
            'pointsNeededForNextLevel' => $info['xp_to_next_level'],
            'pointsHistory' => $pointsHistory,
            'breakdown' => $breakdown,
        ]);
    }
}
