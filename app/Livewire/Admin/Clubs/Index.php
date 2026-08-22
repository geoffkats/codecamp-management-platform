<?php

namespace App\Livewire\Admin\Clubs;

use App\Models\ClubSchedule;
use App\Models\CodeClub;
use App\Models\CodeClubMembership;
use App\Models\School;
use App\Models\StudentAttendance;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $filterStatus = '';
    public bool $showCreateForm = false;

    public string $name = '';
    public ?int $schoolId = null;
    public string $description = '';
    public string $dayOfWeek = '';
    public string $sessionStart = '';
    public string $sessionEnd = '';
    public string $maxCapacity = '';

    public bool $canCreateClubs = false;

    public function mount(): void
    {
        abort_unless(config('features.code_club', false), 404);
        $this->authorize('viewAny', CodeClub::class);

        $user = Auth::user();
        $this->canCreateClubs = $user->isAdmin() || $user->isSupervisor();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function toggleCreateForm(): void
    {
        $this->showCreateForm = ! $this->showCreateForm;
        if (! $this->showCreateForm) {
            $this->resetCreateForm();
        }
    }

    public function createClub(): void
    {
        $this->authorize('create', CodeClub::class);

        $this->validate([
            'name' => 'required|string|max:255',
            'schoolId' => 'required|exists:schools,id',
            'description' => 'nullable|string',
            'dayOfWeek' => 'nullable|string|max:20',
            'sessionStart' => 'nullable|date_format:H:i',
            'sessionEnd' => 'nullable|date_format:H:i|after:sessionStart',
            'maxCapacity' => 'nullable|integer|min:1',
        ]);

        $club = CodeClub::create([
            'school_id' => $this->schoolId,
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description ?: null,
            'day_of_week' => $this->dayOfWeek ?: null,
            'session_start' => $this->sessionStart ?: null,
            'session_end' => $this->sessionEnd ?: null,
            'max_capacity' => $this->maxCapacity ?: null,
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        if ($this->dayOfWeek) {
            ClubSchedule::create([
                'code_club_id' => $club->id,
                'day_of_week' => strtolower($this->dayOfWeek),
                'session_start' => $this->sessionStart ?: null,
                'session_end' => $this->sessionEnd ?: null,
            ]);
        }

        $this->resetCreateForm();
        $this->showCreateForm = false;
        session()->flash('message', "Club \"{$this->name}\" created successfully.");
    }

    private function resetCreateForm(): void
    {
        $this->reset(['name', 'schoolId', 'description', 'dayOfWeek', 'sessionStart', 'sessionEnd', 'maxCapacity']);
    }

    public function render()
    {
        $user = Auth::user();
        $query = CodeClub::query()->with(['school:id,name', 'activeMemberships']);

        if (! $user->isAdmin() && ! $user->isSupervisor()) {
            $query->whereIn('id', $user->activeClubIds());
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $clubs = $query->orderByDesc('created_at')->paginate(12);
        $clubIds = $clubs->pluck('id');

        $membershipCounts = CodeClubMembership::query()
            ->whereIn('code_club_id', $clubIds)
            ->selectRaw('code_club_id, status, count(*) as total')
            ->groupBy('code_club_id', 'status')
            ->get()
            ->groupBy('code_club_id');

        $attendanceCounts = StudentAttendance::query()
            ->whereIn('club_id', $clubIds)
            ->whereBetween('attendance_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('club_id, status, count(*) as total')
            ->groupBy('club_id', 'status')
            ->get()
            ->groupBy('club_id');

        $clubRetention = [];
        foreach ($clubIds as $clubId) {
            $rows = $membershipCounts->get($clubId, collect());
            $active = (int) ($rows->firstWhere('status', 'active')?->total ?? 0);
            $dropped = (int) ($rows->firstWhere('status', 'dropped')?->total ?? 0);
            $total = $active + $dropped;

            $attRows = $attendanceCounts->get($clubId, collect());
            $attTotal = $attRows->sum('total');
            $attPresent = (int) ($attRows->firstWhere('status', 'present')?->total ?? 0)
                + (int) ($attRows->firstWhere('status', 'late')?->total ?? 0);

            $clubRetention[$clubId] = [
                'active' => $active,
                'dropped' => $dropped,
                'retention_pct' => $total > 0 ? round(($active / $total) * 100, 1) : null,
                'dropped_pct' => $total > 0 ? round(($dropped / $total) * 100, 1) : null,
                'attendance_rate' => $attTotal > 0 ? round(($attPresent / $attTotal) * 100, 1) : null,
            ];
        }

        $stats = [
            'total' => (clone $query)->count(),
            'active' => CodeClub::when(! $user->isAdmin() && ! $user->isSupervisor(), fn ($q) => $q->whereIn('id', $user->activeClubIds()))
                ->where('status', 'active')->count(),
            'students' => CodeClubMembership::where('status', 'active')
                ->when(! $user->isAdmin() && ! $user->isSupervisor(), fn ($q) => $q->whereIn('code_club_id', $user->activeClubIds()))
                ->count(),
            'inactive' => CodeClub::when(! $user->isAdmin() && ! $user->isSupervisor(), fn ($q) => $q->whereIn('id', $user->activeClubIds()))
                ->where('status', 'inactive')->count(),
        ];

        return view('livewire.admin.clubs.index', [
            'clubs' => $clubs,
            'stats' => $stats,
            'clubRetention' => $clubRetention,
            'schools' => School::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
