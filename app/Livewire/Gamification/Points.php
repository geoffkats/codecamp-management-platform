<?php

namespace App\Livewire\Gamification;

use App\Models\UserProgress;
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
        
        $totalPoints = $user->points->total_points ?? 0;
        
        // Calculate level (assuming 100 points per level)
        $currentLevel = floor($totalPoints / 100) + 1;
        $pointsInCurrentLevel = $totalPoints % 100;
        $pointsNeededForNextLevel = 100 - $pointsInCurrentLevel;
        
        // Get points history
        $pointsHistory = UserProgress::where('user_id', Auth::id())
            ->where('points_earned', '>', 0)
            ->with(['course', 'lesson'])
            ->latest()
            ->paginate(20);

        // Calculate points breakdown by type
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
            'totalPoints' => $totalPoints,
            'currentLevel' => $currentLevel,
            'pointsInCurrentLevel' => $pointsInCurrentLevel,
            'pointsNeededForNextLevel' => $pointsNeededForNextLevel,
            'pointsHistory' => $pointsHistory,
            'breakdown' => $breakdown,
        ]);
    }
}
