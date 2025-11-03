<?php

namespace App\Livewire\Badges;

use App\Models\Badge;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filterStatus = 'all'; // 'all', 'earned', 'available'
    public $search = '';

    protected $queryString = [
        'filterStatus' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function filterByStatus($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();

        $query = Badge::query()->where('is_active', true);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by status
        if ($this->filterStatus === 'earned') {
            $query->whereIn('id', $earnedBadgeIds);
        } elseif ($this->filterStatus === 'available') {
            $query->whereNotIn('id', $earnedBadgeIds);
        }

        $badges = $query->orderBy('name')->paginate(12);

        // Get user badges with pivot data
        $userBadges = $user->badges()
            ->whereIn('badges.id', $badges->pluck('id'))
            ->get()
            ->map(function ($badge) {
                // Ensure earned_at is cast to Carbon
                if ($badge->pivot->earned_at && is_string($badge->pivot->earned_at)) {
                    $badge->pivot->earned_at = \Carbon\Carbon::parse($badge->pivot->earned_at);
                }
                return $badge;
            })
            ->keyBy('id');

        // Calculate stats
        $stats = [
            'total' => Badge::where('is_active', true)->count(),
            'earned' => count($earnedBadgeIds),
            'available' => Badge::where('is_active', true)
                ->whereNotIn('id', $earnedBadgeIds)
                ->count(),
            'completion' => Badge::where('is_active', true)->count() > 0 
                ? round((count($earnedBadgeIds) / Badge::where('is_active', true)->count()) * 100, 1)
                : 0,
        ];

        // Get recently earned badges
        $recentBadges = $user->badges()
            ->orderBy('user_badges.earned_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($badge) {
                // Ensure earned_at is cast to Carbon
                if ($badge->pivot->earned_at && is_string($badge->pivot->earned_at)) {
                    $badge->pivot->earned_at = \Carbon\Carbon::parse($badge->pivot->earned_at);
                }
                return $badge;
            });

        return view('livewire.badges.index', [
            'badges' => $badges,
            'userBadges' => $userBadges,
            'stats' => $stats,
            'recentBadges' => $recentBadges,
            'earnedBadgeIds' => $earnedBadgeIds,
        ]);
    }
}
