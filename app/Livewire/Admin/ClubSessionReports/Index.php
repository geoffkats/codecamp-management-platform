<?php

namespace App\Livewire\Admin\ClubSessionReports;

use App\Models\ClubSessionReport;
use App\Models\CodeClub;
use App\Models\School;
use App\Support\ProgramScope;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?int $clubId = null;
    public ?int $schoolId = null;
    public string $status = 'all';

    public function mount(): void
    {
        abort_unless(config('features.code_club', false), 404);
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->isSupervisor() || $user->hasCodeClubAccess(), 403);
    }

    public function updatedSchoolId(): void
    {
        $this->clubId = null;
        $this->resetPage();
    }

    public function updated($property): void
    {
        if (in_array($property, ['dateFrom', 'dateTo', 'clubId', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, ['all', 'submitted', 'reviewed'], true) ? $status : 'all';
        $this->resetPage();
    }

    public function markReviewed(int $reportId): void
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->isSupervisor(), 403);

        $report = ClubSessionReport::findOrFail($reportId);
        abort_unless($report->canBeViewedBy($user), 403);

        $report->update([
            'status' => 'reviewed',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        session()->flash('message', 'Report marked as reviewed.');
    }

    private function isReviewer(): bool
    {
        return Auth::user()->isAdmin() || Auth::user()->isSupervisor();
    }

    private function baseQuery()
    {
        $user = Auth::user();
        $query = ClubSessionReport::query();

        if (! $this->isReviewer()) {
            $query->where(fn ($q) => $q->whereIn('code_club_id', $user->activeClubIds())
                ->orWhere('facilitator_id', $user->id));
        }

        if ($this->clubId) {
            abort_unless($this->isReviewer() || in_array((int) $this->clubId, $user->activeClubIds(), true), 403);
            $query->where('code_club_id', $this->clubId);
        }
        if ($this->schoolId) {
            $query->whereHas('club', fn ($q) => $q->where('school_id', $this->schoolId));
        }
        if ($this->dateFrom) {
            $query->whereDate('session_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('session_date', '<=', $this->dateTo);
        }

        return $query;
    }

    public function render()
    {
        $user = Auth::user();
        $base = $this->baseQuery();

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $reports = (clone $base)
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->with(['club:id,name,school_id', 'facilitator', 'reviewer:id,name'])
            ->withCount('comments')
            ->orderByRaw("case when status = 'submitted' then 0 else 1 end")
            ->orderByDesc('session_date')
            ->paginate(15);

        $recent = (clone $base)->whereDate('session_date', '>=', now()->subDays(30))->get(['attendance_count', 'enrolled_count', 'follow_up_required']);
        $enrolled = $recent->sum('enrolled_count');

        $visibleClubs = ProgramScope::visibleClubs($user);

        return view('livewire.admin.club-session-reports.index', [
            'reports' => $reports,
            'counts' => $counts,
            'isReviewer' => $this->isReviewer(),
            'clubs' => $visibleClubs,
            'schools' => School::when(! $this->isReviewer(), fn ($q) => $q->whereIn('id', CodeClub::whereIn('id', $visibleClubs->pluck('id'))->pluck('school_id')))
                ->orderBy('name')->get(['id', 'name']),
            'monthStats' => [
                'reports' => $recent->count(),
                'attendance' => $enrolled > 0 ? round($recent->sum('attendance_count') / $enrolled * 100) : null,
                'followUps' => $recent->where('follow_up_required', true)->count(),
            ],
        ]);
    }
}
