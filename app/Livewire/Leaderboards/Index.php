<?php

namespace App\Livewire\Leaderboards;

use App\Models\CodeCamp;
use App\Models\CodeClub;
use App\Models\Course;
use App\Support\ProgramScope;
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

    public string $leaderboardType = 'overall';
    public string $period = 'all';
    public ?int $campId = null;
    public ?int $clubId = null;
    public ?int $courseId = null;
    public string $search = '';

    protected $queryString = [
        'leaderboardType' => ['except' => 'overall'],
        'period'          => ['except' => 'all'],
        'campId'          => ['except' => null],
        'clubId'          => ['except' => null],
        'courseId'        => ['except' => null],
        'search'          => ['except' => ''],
    ];

    /** XP tier names — always look up by XP, never by raw level index */
    public const LEVELS = [
        1 => ['name' => 'Beginner',  'color' => '#9CA3AF'],
        2 => ['name' => 'Explorer',  'color' => '#3B82F6'],
        3 => ['name' => 'Coder',     'color' => '#14B8A6'],
        4 => ['name' => 'Builder',   'color' => '#22C55E'],
        5 => ['name' => 'Developer', 'color' => '#EAB308'],
        6 => ['name' => 'Pro',       'color' => '#F97316'],
        7 => ['name' => 'Expert',    'color' => '#EF4444'],
        8 => ['name' => 'Master',    'color' => '#8B5CF6'],
        9 => ['name' => 'Legend',    'color' => '#D97706'],
    ];

    public function filterByType(string $type): void
    {
        $this->leaderboardType = $type;
        $this->resetPage();
    }

    public function filterByPeriod(string $period): void
    {
        $this->period = $period;
        $this->resetPage();
    }

    public function updatedClubId(): void
    {
        $this->resetPage();
    }

    public function updatedCampId(): void
    {
        $this->resetPage();
    }

    public function updatedCourseId(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->campId = null;
        $this->clubId = null;
        $this->courseId = null;
        $this->search = '';
        $this->period = 'all';
        $this->leaderboardType = 'overall';
        $this->resetPage();
    }

    public function render()
    {
        $currentUser = Auth::user();
        $isStaff = $currentUser && ! $currentUser->isStudent();

        $baseQuery = UserPoint::query()
            ->with(['user.studentProfile', 'user.badges'])
            ->whereHas('user', function ($q) use ($currentUser, $isStaff) {
                $q->where('is_active', true)
                    ->whereHas('roles', fn ($r) => $r->where('name', 'student'));

                $this->applyScope($q, $currentUser, $isStaff);

                if ($this->search !== '') {
                    $q->where('name', 'like', '%' . trim($this->search) . '%');
                }
            });

        if ($this->period === 'weekly') {
            $baseQuery->where('updated_at', '>=', now()->startOfWeek());
        } elseif ($this->period === 'monthly') {
            $baseQuery->where('updated_at', '>=', now()->startOfMonth());
        }

        $leaderboard = match ($this->leaderboardType) {
            'level' => (clone $baseQuery)->orderByDesc('level')->orderByDesc('total_points')->paginate(25),
            default => (clone $baseQuery)->orderByDesc('total_points')->paginate(25),
        };

        $leaderboard->getCollection()->transform(function ($userPoint, $index) use ($leaderboard) {
            $rank = $leaderboard->firstItem() + $index;

            return (object) array_merge(
                ['userPoint' => $userPoint],
                $this->formatEntry($userPoint, $rank)
            );
        });

        $currentUserRank = null;
        if ($currentUser?->isStudent() && $currentUser->points) {
            $rank = (clone $baseQuery)
                ->where('total_points', '>', $currentUser->points->total_points ?? 0)
                ->count() + 1;

            $currentUserRank = $this->formatEntry($currentUser->points, $rank);
        }

        $topThree = (clone $baseQuery)
            ->orderByDesc('total_points')
            ->take(3)
            ->get()
            ->map(fn ($userPoint, $index) => $this->formatEntry($userPoint, $index + 1));

        $totalActiveUsers = (clone $baseQuery)->count();

        $stats = [
            'totalXp'     => (clone $baseQuery)->sum('total_points'),
            'avgXp'       => $totalActiveUsers > 0 ? round((clone $baseQuery)->avg('total_points')) : 0,
            'topXp'       => $topThree->first()['points'] ?? 0,
            'participants'=> $totalActiveUsers,
        ];

        $camps = $isStaff && ProgramScope::context($currentUser) !== 'codeclub'
            ? CodeCamp::orderBy('name')->get(['id', 'name', 'status'])
            : collect();

        $clubs = $isStaff && $currentUser->hasCodeClubAccess() && ProgramScope::context($currentUser) === 'codeclub'
            ? ProgramScope::visibleClubs($currentUser)
            : collect();

        $courses = $this->availableCourses($currentUser, $isStaff);

        return view('livewire.leaderboards.index', [
            'leaderboard'      => $leaderboard,
            'currentUserRank'  => $currentUserRank,
            'topThree'         => $topThree,
            'totalActiveUsers' => $totalActiveUsers,
            'stats'            => $stats,
            'camps'            => $camps,
            'clubs'            => $clubs,
            'courses'          => $courses,
            'isStaff'          => $isStaff,
        ]);
    }

    private function applyScope($q, $currentUser, bool $isStaff): void
    {
        $q->whereHas('studentProfile', function ($profileQuery) use ($currentUser) {
            ProgramScope::applyStudentProfileScope($profileQuery, $currentUser);
        });

        if ($this->clubId) {
            $q->whereHas('codeClubMemberships', fn ($e) => $e
                ->where('code_club_id', $this->clubId)
                ->where('status', 'active'));
        }

        if ($this->campId) {
            $q->whereHas('campEnrollments', fn ($e) => $e
                ->where('camp_id', $this->campId)
                ->where('status', 'active'));
        }

        if ($this->courseId) {
            $q->whereHas('enrollments', fn ($e) => $e->where('course_id', $this->courseId));
        }

        if ($currentUser?->isStudent()) {
            $programType = $currentUser->studentProfile?->program_type ?? 'codecamp';

            if (in_array($programType, ['codeclub', 'ict'], true)) {
                return;
            }

            $courseIds = $currentUser->enrollments()->pluck('course_id')->filter()->all();
            if (empty($courseIds)) {
                $q->whereRaw('1 = 0');

                return;
            }
            if (! $this->campId && ! $this->courseId) {
                $q->whereHas('enrollments', fn ($e) => $e->whereIn('course_id', $courseIds));
            }

            return;
        }

        if ($isStaff && ! $this->campId && ! $this->courseId) {
            if ($currentUser->isTeacher()
                && ! $currentUser->isAdmin()
                && ! $currentUser->isSupervisor()) {
                $courseIds = Course::where('instructor_id', $currentUser->id)->pluck('id');
                if ($courseIds->isNotEmpty()) {
                    $q->whereHas('enrollments', fn ($e) => $e->whereIn('course_id', $courseIds));
                }
            }
        }
    }

    private function availableCourses($currentUser, bool $isStaff)
    {
        if (! $isStaff || ! $currentUser) {
            return collect();
        }

        $query = Course::query()->orderBy('title');

        if ($currentUser->isTeacher() || $currentUser->isCodecampTrainer()) {
            $query->where('instructor_id', $currentUser->id);
        }

        return $query->get(['id', 'title']);
    }

    public function formatEntry(UserPoint $userPoint, int $rank): array
    {
        $info = $userPoint->levelInfo();

        return [
            'rank'           => $rank,
            'user'           => $userPoint->user,
            'profileId'      => $userPoint->user?->studentProfile?->id,
            'points'         => $info['xp'],
            'level'          => $info['level'],
            'levelName'      => $info['name'],
            'levelColor'     => $info['hex'],
            'badgeCount'     => $userPoint->user?->badges?->count() ?? 0,
            'levelProgress'  => $info['level_progress'],
        ];
    }

    public static function levelName(int $level): string
    {
        // Deprecated for display — titles come from XP. Kept for callers that still pass a level index.
        return self::LEVELS[$level]['name'] ?? 'Beginner';
    }

    public static function levelColor(int $level): string
    {
        return self::LEVELS[$level]['color'] ?? '#9CA3AF';
    }
}
