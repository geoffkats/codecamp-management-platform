<?php

namespace App\Livewire\Leaderboards;

use App\Models\CodeCamp;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\UserPoint;
use App\Models\UserProgress;
use App\Support\ProgramScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    public $campId = null;
    public $clubId = null;
    public $courseId = null;
    public string $search = '';

    protected $queryString = [
        'leaderboardType' => ['except' => 'overall'],
        'period' => ['except' => 'all'],
        'campId' => ['except' => null],
        'clubId' => ['except' => null],
        'courseId' => ['except' => null],
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        $this->normalizeFilters();

        if ($user?->isStudent()) {
            $this->lockStudentClassScope($user);

            if (! request()->has('period')) {
                $this->period = 'weekly';
            }
        }
    }

    public const LEVELS = [
        1 => ['name' => 'Beginner', 'color' => '#9CA3AF'],
        2 => ['name' => 'Explorer', 'color' => '#3B82F6'],
        3 => ['name' => 'Coder', 'color' => '#14B8A6'],
        4 => ['name' => 'Builder', 'color' => '#22C55E'],
        5 => ['name' => 'Developer', 'color' => '#EAB308'],
        6 => ['name' => 'Pro', 'color' => '#F97316'],
        7 => ['name' => 'Expert', 'color' => '#EF4444'],
        8 => ['name' => 'Master', 'color' => '#8B5CF6'],
        9 => ['name' => 'Legend', 'color' => '#D97706'],
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

    public function updatedClubId($value): void
    {
        $this->clubId = $this->nullableInt($value);
        $this->resetPage();
    }

    public function updatedCampId($value): void
    {
        $this->campId = $this->nullableInt($value);
        $this->courseId = null;
        $this->resetPage();
    }

    public function updatedCourseId($value): void
    {
        $this->courseId = $this->nullableInt($value);

        $user = Auth::user();
        if ($user?->isStudent()) {
            $allowed = $this->studentCourseIds($user, $this->nullableInt($this->campId));
            if ($this->courseId && ! in_array($this->courseId, $allowed, true)) {
                $this->courseId = $allowed[0] ?? null;
            }
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $user = Auth::user();
        $this->search = '';
        $this->leaderboardType = 'overall';
        $this->clubId = null;

        if ($user?->isStudent()) {
            $this->campId = null;
            $this->courseId = null;
            $this->period = 'weekly';
            $this->lockStudentClassScope($user);
        } else {
            $this->campId = null;
            $this->courseId = null;
            $this->period = 'all';
        }

        $this->resetPage();
    }

    public function render()
    {
        $this->normalizeFilters();

        $currentUser = Auth::user();
        $isStaff = $currentUser && ! $currentUser->isStudent();

        if ($currentUser?->isStudent()) {
            $this->lockStudentClassScope($currentUser);
        }

        $campId = $this->nullableInt($this->campId);
        $courseId = $this->nullableInt($this->courseId);
        $clubId = $this->nullableInt($this->clubId);

        $baseQuery = UserPoint::query()
            ->with(['user.studentProfile', 'user.badges'])
            ->whereHas('user', function ($q) use ($currentUser, $isStaff, $campId, $courseId, $clubId) {
                $q->where('is_active', true)
                    ->whereHas('roles', fn ($r) => $r->where('name', 'student'));

                $this->applyScope($q, $currentUser, $isStaff, $campId, $courseId, $clubId);

                if ($this->search !== '') {
                    $q->where('name', 'like', '%' . trim($this->search) . '%');
                }
            });

        $usesScopedXp = $this->usesScopedXp();

        if ($usesScopedXp) {
            $progressQuery = UserProgress::query()
                ->select('user_id', DB::raw('COALESCE(SUM(points_earned), 0) as period_points'))
                ->groupBy('user_id');

            if ($this->period === 'weekly') {
                $progressQuery->where('created_at', '>=', now()->startOfWeek());
            } elseif ($this->period === 'monthly') {
                $progressQuery->where('created_at', '>=', now()->startOfMonth());
            }

            CourseEnrollment::constrainProgressToCurrentClass(
                $progressQuery,
                $campId,
                $courseId
            );

            $baseQuery->leftJoinSub($progressQuery, 'period_xp', function ($join) {
                $join->on('period_xp.user_id', '=', 'user_points.user_id');
            })->addSelect(
                'user_points.*',
                DB::raw('COALESCE(period_xp.period_points, 0) as period_points')
            );
        }

        $pointsColumn = $usesScopedXp
            ? DB::raw('COALESCE(period_xp.period_points, 0)')
            : 'total_points';

        $leaderboard = match ($this->leaderboardType) {
            'level' => (clone $baseQuery)->orderByDesc('level')->orderByDesc($pointsColumn)->paginate(25),
            default => (clone $baseQuery)->orderByDesc($pointsColumn)->paginate(25),
        };

        $leaderboard->getCollection()->transform(function ($userPoint, $index) use ($leaderboard, $usesScopedXp) {
            $rank = $leaderboard->firstItem() + $index;
            $rankedPoints = $usesScopedXp
                ? (int) ($userPoint->period_points ?? 0)
                : null;

            return (object) array_merge(
                ['userPoint' => $userPoint],
                $this->formatEntry($userPoint, $rank, $rankedPoints)
            );
        });

        $currentUserRank = null;
        if ($currentUser?->isStudent() && $currentUser->points) {
            $myPoints = $usesScopedXp
                ? (int) ((clone $baseQuery)->where('user_points.user_id', $currentUser->id)->value(DB::raw('COALESCE(period_xp.period_points, 0)')) ?? 0)
                : (int) ($currentUser->points->total_points ?? 0);

            $rank = (clone $baseQuery)
                ->where($pointsColumn, '>', $myPoints)
                ->count() + 1;

            $currentUserRank = $this->formatEntry($currentUser->points, $rank, $usesScopedXp ? $myPoints : null);
        }

        $topThree = (clone $baseQuery)
            ->orderByDesc($pointsColumn)
            ->take(3)
            ->get()
            ->map(function ($userPoint, $index) use ($usesScopedXp) {
                $rankedPoints = $usesScopedXp ? (int) ($userPoint->period_points ?? 0) : null;

                return $this->formatEntry($userPoint, $index + 1, $rankedPoints);
            });

        $totalActiveUsers = (clone $baseQuery)->count();

        $stats = [
            'totalXp' => (int) (clone $baseQuery)->sum($pointsColumn),
            'avgXp' => $totalActiveUsers > 0 ? (int) round((clone $baseQuery)->avg($pointsColumn)) : 0,
            'topXp' => $topThree->first()['points'] ?? 0,
            'participants' => $totalActiveUsers,
        ];

        $camps = $isStaff && ProgramScope::context($currentUser) !== 'codeclub'
            ? CodeCamp::orderBy('name')->get(['id', 'name', 'status'])
            : collect();

        $clubs = $isStaff && $currentUser->hasCodeClubAccess() && ProgramScope::context($currentUser) === 'codeclub'
            ? ProgramScope::visibleClubs($currentUser)
            : collect();

        $courses = $this->availableCourses($currentUser, $isStaff, $campId);

        return view('livewire.leaderboards.index', [
            'leaderboard' => $leaderboard,
            'currentUserRank' => $currentUserRank,
            'topThree' => $topThree,
            'totalActiveUsers' => $totalActiveUsers,
            'stats' => $stats,
            'camps' => $camps,
            'clubs' => $clubs,
            'courses' => $courses,
            'isStaff' => $isStaff,
            'usesScopedXp' => $usesScopedXp,
            'pointsLabel' => $this->pointsLabel(),
            'scopeCaption' => $this->scopeCaption($courses, $campId, $courseId),
            'selectedCourseTitle' => $courseId
                ? ($courses->firstWhere('id', $courseId)?->title ?? Course::query()->find($courseId)?->title)
                : null,
        ]);
    }

    private function applyScope($q, $currentUser, bool $isStaff, ?int $campId, ?int $courseId, ?int $clubId): void
    {
        $q->whereHas('studentProfile', function ($profileQuery) use ($currentUser) {
            ProgramScope::applyStudentProfileScope($profileQuery, $currentUser);
        });

        if ($clubId) {
            $q->whereHas('codeClubMemberships', fn ($e) => $e
                ->where('code_club_id', $clubId)
                ->where('status', 'active'));
        }

        if ($campId) {
            $q->whereHas('campEnrollments', fn ($e) => $e
                ->where('camp_id', $campId)
                ->where('status', 'active'));
        }

        if ($courseId) {
            $q->whereHas('enrollments', function ($e) use ($campId, $courseId, $isStaff) {
                $e->where('course_id', $courseId)->whereNull('completed_at');

                // Staff camp filter is already applied via campEnrollments.
                // For students, keep camp soft-match so legacy null camp_id rows still count.
                if (! $isStaff && $campId) {
                    $e->where(function ($inner) use ($campId) {
                        $inner->where('camp_id', $campId)->orWhereNull('camp_id');
                    });
                }
            });

            return;
        }

        if ($currentUser?->isStudent()) {
            $programType = $currentUser->studentProfile?->program_type ?? 'codecamp';

            if (in_array($programType, ['codeclub', 'ict'], true)) {
                return;
            }

            $courseIds = $this->studentCourseIds($currentUser, $campId);
            if ($courseIds === []) {
                $q->whereRaw('1 = 0');

                return;
            }

            $q->whereHas('enrollments', fn ($e) => $e
                ->whereNull('completed_at')
                ->whereIn('course_id', $courseIds));

            return;
        }

        if ($isStaff && ! $campId && ! $courseId) {
            if ($currentUser->isTeacher()
                && ! $currentUser->isAdmin()
                && ! $currentUser->isSupervisor()) {
                $courseIds = Course::query()->accessibleBy($currentUser)->pluck('id');
                if ($courseIds->isNotEmpty()) {
                    $q->whereHas('enrollments', fn ($e) => $e
                        ->whereNull('completed_at')
                        ->whereIn('course_id', $courseIds));
                }
            }
        }
    }

    private function availableCourses($currentUser, bool $isStaff, ?int $campId)
    {
        if ($currentUser?->isStudent() && ! $currentUser->isCodeClubStudent() && ! $currentUser->isIctStudent()) {
            $ids = $this->studentCourseIds($currentUser, $campId);

            if ($ids === []) {
                return collect();
            }

            return Course::query()->whereIn('id', $ids)->orderBy('title')->get(['id', 'title']);
        }

        if (! $isStaff || ! $currentUser) {
            return collect();
        }

        $query = Course::query()->orderBy('title');

        if ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
            // all courses
        } elseif ($currentUser->isTeacher() || $currentUser->isCodecampTrainer()) {
            $query->accessibleBy($currentUser);
        }

        if ($campId) {
            $query->whereHas('enrollments', function ($e) use ($campId) {
                $e->whereNull('completed_at')
                    ->where(function ($inner) use ($campId) {
                        $inner->where('camp_id', $campId)->orWhereNull('camp_id');
                    });
            });
        }

        return $query->get(['id', 'title']);
    }

    /**
     * @return array<int, int>
     */
    private function studentCourseIds($user, ?int $campId = null): array
    {
        $base = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->whereNotNull('course_id')
            ->orderByDesc('enrolled_at');

        if ($campId) {
            $scoped = (clone $base)
                ->where(function ($q) use ($campId) {
                    $q->where('camp_id', $campId)->orWhereNull('camp_id');
                })
                ->pluck('course_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if ($scoped !== []) {
                return $scoped;
            }
        }

        return $base
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function usesScopedXp(): bool
    {
        return $this->period !== 'all';
    }

    private function lockStudentClassScope($user): void
    {
        $programType = $user->studentProfile?->program_type ?? 'codecamp';
        if (in_array($programType, ['codeclub', 'ict'], true)) {
            return;
        }

        $camp = $user->currentCamp();
        $this->campId = $camp ? (int) $camp->id : null;

        $allowed = $this->studentCourseIds($user, $this->nullableInt($this->campId));

        if ($this->courseId && ! in_array((int) $this->courseId, $allowed, true)) {
            $this->courseId = null;
        }

        // Always pin students onto a real enrolled class board.
        if (! $this->courseId && $allowed !== []) {
            $this->courseId = (int) $allowed[0];
        }
    }

    private function scopeCaption($courses, ?int $campId, ?int $courseId): ?string
    {
        $parts = [];

        if ($campId) {
            $camp = CodeCamp::query()->find($campId);
            if ($camp) {
                $parts[] = $camp->name;
            }
        }

        if ($courseId) {
            $course = $courses->firstWhere('id', $courseId) ?? Course::query()->find($courseId);
            if ($course) {
                $parts[] = $course->title;
            }
        }

        if ($parts === []) {
            return null;
        }

        if ($this->period === 'all') {
            return implode(' · ', $parts) . ' · ranked by career XP';
        }

        return implode(' · ', $parts) . ' · current class XP only';
    }

    private function pointsLabel(): string
    {
        if ($this->period === 'weekly') {
            return 'This week';
        }
        if ($this->period === 'monthly') {
            return 'This month';
        }
        if ($this->courseId) {
            return 'This course';
        }
        if ($this->campId) {
            return 'This camp';
        }

        return 'Career';
    }

    private function normalizeFilters(): void
    {
        $this->campId = $this->nullableInt($this->campId);
        $this->courseId = $this->nullableInt($this->courseId);
        $this->clubId = $this->nullableInt($this->clubId);
    }

    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }

        return (int) $value;
    }

    public function formatEntry(UserPoint $userPoint, int $rank, ?int $rankedPoints = null): array
    {
        $info = $userPoint->levelInfo();
        $careerXp = $info['xp'];

        return [
            'rank' => $rank,
            'user' => $userPoint->user,
            'profileId' => $userPoint->user?->studentProfile?->id,
            'points' => $rankedPoints ?? $careerXp,
            'careerPoints' => $careerXp,
            'level' => $info['level'],
            'levelName' => $info['name'],
            'levelColor' => $info['hex'],
            'badgeCount' => $userPoint->user?->badges?->count() ?? 0,
            'levelProgress' => $info['level_progress'],
        ];
    }

    public static function levelName(int $level): string
    {
        return self::LEVELS[$level]['name'] ?? 'Beginner';
    }

    public static function levelColor(int $level): string
    {
        return self::LEVELS[$level]['color'] ?? '#9CA3AF';
    }
}
