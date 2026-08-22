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
        $user = Auth::user();
        $userId = $user?->id;
        $userCourseIds = $user?->enrollments()->pluck('course_id')->filter()->unique() ?? collect();

        $query = DailyChallenge::query()->where('is_active', true);

        // Only show general challenges or those tied to the student's enrolled courses
        $query->where(function ($q) use ($userCourseIds) {
            $q->whereNull('course_id');

            if ($userCourseIds->isNotEmpty()) {
                $q->orWhereIn('course_id', $userCourseIds);
            }
        });

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

        $challenges = $query->with('course')->orderBy('date', 'desc')->paginate(12);

        // Get user attempts for challenges
        $userAttempts = DailyChallengeAttempt::where('user_id', $userId)
            ->whereIn('challenge_id', $challenges->pluck('id'))
            ->get()
            ->keyBy('challenge_id');

        // Calculate stats
        $baseAccessibleQuery = DailyChallenge::where('is_active', true)
            ->where(function ($q) use ($userCourseIds) {
                $q->whereNull('course_id');

                if ($userCourseIds->isNotEmpty()) {
                    $q->orWhereIn('course_id', $userCourseIds);
                }
            });

        $stats = [
            'total' => (clone $baseAccessibleQuery)->count(),
            'completed' => DailyChallengeAttempt::where('user_id', $userId)
                ->where('is_completed', true)
                ->count(),
            'active' => (clone $baseAccessibleQuery)
                ->where('date', '<=', $today)
                ->count(),
            'totalPoints' => DailyChallengeAttempt::where('user_id', $userId)
                ->where('is_completed', true)
                ->sum('points_earned') ?? 0,
        ];

        // Get today's challenges
        $todayChallenges = DailyChallenge::where('is_active', true)
            ->where(function ($q) use ($userCourseIds) {
                $q->whereNull('course_id');

                if ($userCourseIds->isNotEmpty()) {
                    $q->orWhereIn('course_id', $userCourseIds);
                }
            })
            ->where(function ($q) use ($today) {
                $q->where('date', $today)
                  ->orWhereNull('date');
            })
            ->with([
                'attempts' => fn($q) => $q->where('user_id', $userId),
                'course',
            ])
            ->get();

        // Active competition challenge (if any)
        $activeCompetition = DailyChallenge::where('is_active', true)
            ->where('is_competition', true)
            ->where(function ($q) {
                $q->whereNull('competition_ends_at')
                  ->orWhere('competition_ends_at', '>', now());
            })
            ->first();

        $competitionLeaderboard = $activeCompetition
            ? $activeCompetition->competitionLeaderboard(10)
            : collect();

        $myCompetitionAttempt = $activeCompetition && $userId
            ? DailyChallengeAttempt::where('challenge_id', $activeCompetition->id)
                ->where('user_id', $userId)
                ->first()
            : null;

        return view('livewire.daily-challenges.index', [
            'challenges'             => $challenges,
            'userAttempts'           => $userAttempts,
            'stats'                  => $stats,
            'todayChallenges'        => $todayChallenges,
            'activeCompetition'      => $activeCompetition,
            'competitionLeaderboard' => $competitionLeaderboard,
            'myCompetitionAttempt'   => $myCompetitionAttempt,
        ]);
    }
}
