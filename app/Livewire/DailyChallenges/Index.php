<?php

namespace App\Livewire\DailyChallenges;

use App\Models\DailyChallenge;
use App\Models\DailyChallengeAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filterStatus = 'active'; // 'all', 'active', 'completed', 'available'
    public $filterDifficulty = 'all'; // 'all', 'easy', 'medium', 'hard'
    public $search = '';

    protected $queryString = [
        'filterStatus' => ['except' => 'active'],
        'filterDifficulty' => ['except' => 'all'],
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

    public function filterByDifficulty($difficulty)
    {
        $this->filterDifficulty = $difficulty;
        $this->resetPage();
    }

    public function render()
    {
        $query = DailyChallenge::query()->where('is_active', true);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Difficulty filter
        if ($this->filterDifficulty !== 'all') {
            $query->where('difficulty_level', strtolower($this->filterDifficulty));
        }

        // Status filter
        $today = now()->toDateString();
        if ($this->filterStatus === 'active') {
            $query->where('date', '<=', $today)
                  ->where(function ($q) use ($today) {
                      $q->whereNull('date')
                        ->orWhere('date', '>=', now()->subDays(7)->toDateString());
                  });
        } elseif ($this->filterStatus === 'available') {
            $query->where('date', '>=', $today);
        }

        $challenges = $query->orderBy('date', 'desc')->paginate(12);

        // Get user attempts for challenges
        $userAttempts = DailyChallengeAttempt::where('user_id', Auth::id())
            ->whereIn('challenge_id', $challenges->pluck('id'))
            ->get()
            ->keyBy('challenge_id');

        // Calculate stats
        $stats = [
            'total' => DailyChallenge::where('is_active', true)->count(),
            'completed' => DailyChallengeAttempt::where('user_id', Auth::id())
                ->where('is_completed', true)
                ->count(),
            'active' => DailyChallenge::where('is_active', true)
                ->where('date', '<=', $today)
                ->count(),
            'totalPoints' => DailyChallengeAttempt::where('user_id', Auth::id())
                ->where('is_completed', true)
                ->sum('points_earned') ?? 0,
        ];

        // Get today's challenges
        $todayChallenges = DailyChallenge::where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->where('date', $today)
                  ->orWhereNull('date');
            })
            ->with(['attempts' => fn($q) => $q->where('user_id', Auth::id())])
            ->get();

        return view('livewire.daily-challenges.index', [
            'challenges' => $challenges,
            'userAttempts' => $userAttempts,
            'stats' => $stats,
            'todayChallenges' => $todayChallenges,
        ]);
    }
}
