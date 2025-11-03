<?php

namespace App\Livewire\Leaderboards;

use App\Models\Leaderboard;
use App\Models\UserPoint;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $leaderboardType = 'overall'; // 'overall', 'course', 'weekly', 'monthly'
    public $courseId = null;
    public $period = 'all'; // 'all', 'weekly', 'monthly', 'yearly'

    protected $queryString = [
        'leaderboardType' => ['except' => 'overall'],
        'courseId' => ['except' => null],
        'period' => ['except' => 'all'],
    ];

    public function filterByType($type)
    {
        $this->leaderboardType = $type;
        $this->resetPage();
    }

    public function filterByPeriod($period)
    {
        $this->period = $period;
        $this->resetPage();
    }

    public function render()
    {
        $query = UserPoint::query()
            ->with('user')
            ->whereHas('user', function ($q) {
                $q->where('is_active', true)
                  ->whereHas('roles', fn($r) => $r->where('name', 'student'));
            });

        // Filter by period
        if ($this->period === 'weekly') {
            $query->where('updated_at', '>=', now()->startOfWeek());
        } elseif ($this->period === 'monthly') {
            $query->where('updated_at', '>=', now()->startOfMonth());
        } elseif ($this->period === 'yearly') {
            $query->where('updated_at', '>=', now()->startOfYear());
        }

        // Get leaderboard based on type
        $leaderboard = match($this->leaderboardType) {
            'overall' => $query->orderByDesc('total_points')->paginate(50),
            'level' => $query->orderByDesc('level')->orderByDesc('total_points')->paginate(50),
            default => $query->orderByDesc('total_points')->paginate(50),
        };

        // Get current user's position (only if user is a student)
        $currentUserRank = null;
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->isStudent() && $currentUser->points) {
            $rank = UserPoint::whereHas('user', function ($q) {
                    $q->where('is_active', true)
                      ->whereHas('roles', fn($r) => $r->where('name', 'student'));
                })
                ->where('total_points', '>', $currentUser->points->total_points ?? 0)
                ->count() + 1;
            $currentUserRank = [
                'rank' => $rank,
                'points' => $currentUser->points->total_points ?? 0,
                'level' => $currentUser->points->level ?? 1,
            ];
        }

        // Get top 3 for podium display (students only)
        $topThree = UserPoint::query()
            ->with('user')
            ->whereHas('user', function ($q) {
                $q->where('is_active', true)
                  ->whereHas('roles', fn($r) => $r->where('name', 'student'));
            })
            ->orderByDesc('total_points')
            ->take(3)
            ->get()
            ->map(function ($userPoint, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $userPoint->user,
                    'points' => $userPoint->total_points ?? 0,
                    'level' => $userPoint->level ?? 1,
                ];
            });

        // Get total active students count for ranking display
        $totalActiveUsers = UserPoint::query()
            ->whereHas('user', function ($q) {
                $q->where('is_active', true)
                  ->whereHas('roles', fn($r) => $r->where('name', 'student'));
            })
            ->count();

        return view('livewire.leaderboards.index', [
            'leaderboard' => $leaderboard,
            'currentUserRank' => $currentUserRank,
            'topThree' => $topThree,
            'totalActiveUsers' => $totalActiveUsers,
        ]);
    }
}
