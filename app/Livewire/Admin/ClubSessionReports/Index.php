<?php

namespace App\Livewire\Admin\ClubSessionReports;

use App\Models\ClubSessionReport;
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
    public ?string $status = null;

    public ?int $reviewingReportId = null;
    public string $reviewNotes = '';

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

    public function openReview(int $reportId): void
    {
        abort_unless(Auth::user()->isAdmin() || Auth::user()->isSupervisor(), 403);
        $this->authorizeReportAccess(ClubSessionReport::findOrFail($reportId));
        $this->reviewingReportId = $reportId;
        $this->reviewNotes = '';
    }

    public function markReviewed(): void
    {
        abort_unless(Auth::user()->isAdmin() || Auth::user()->isSupervisor(), 403);

        $this->validate([
            'reviewingReportId' => 'required|exists:club_session_reports,id',
            'reviewNotes' => 'nullable|string|max:2000',
        ]);

        $report = ClubSessionReport::findOrFail($this->reviewingReportId);
        $this->authorizeReportAccess($report);

        $report->update([
            'status' => 'reviewed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $this->reviewNotes ?: null,
        ]);

        $this->reviewingReportId = null;
        $this->reviewNotes = '';
        session()->flash('message', 'Report marked as reviewed.');
    }

    private function authorizeReportAccess(ClubSessionReport $report): void
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isSupervisor()) {
            return;
        }

        abort_unless(in_array((int) $report->code_club_id, $user->activeClubIds(), true), 403);
    }

    public function updatedClubId(): void
    {
        $this->resetPage();
        $this->validateSelectedClubFilter();
    }

    private function validateSelectedClubFilter(): void
    {
        if (! $this->clubId) {
            return;
        }

        $user = Auth::user();

        if ($user->isAdmin() || $user->isSupervisor()) {
            return;
        }

        abort_unless(in_array((int) $this->clubId, $user->activeClubIds(), true), 403);
    }

    public function render()
    {
        $user = Auth::user();
        $query = ClubSessionReport::with(['club:id,name,school_id', 'facilitator:id,name', 'reviewer:id,name'])
            ->orderByDesc('session_date');

        if (! $user->isAdmin() && ! $user->isSupervisor()) {
            $query->whereIn('code_club_id', $user->activeClubIds());
        }

        if ($this->dateFrom) {
            $query->whereDate('session_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('session_date', '<=', $this->dateTo);
        }
        if ($this->clubId) {
            $this->validateSelectedClubFilter();
        }

        if ($this->clubId) {
            $query->where('code_club_id', $this->clubId);
        }
        if ($this->schoolId) {
            $query->whereHas('club', fn ($q) => $q->where('school_id', $this->schoolId));
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }

        $avgRetention = (clone $query)->get()->avg(fn ($r) => $r->retentionRate());

        $visibleClubIds = ProgramScope::visibleClubs($user)->pluck('id');

        return view('livewire.admin.club-session-reports.index', [
            'reports' => $query->paginate(15),
            'clubs' => ProgramScope::visibleClubs($user),
            'schools' => School::when($visibleClubIds->isNotEmpty() && ! $user->isAdmin() && ! $user->isSupervisor(), function ($q) use ($visibleClubIds) {
                $q->whereIn('id', \App\Models\CodeClub::whereIn('id', $visibleClubIds)->pluck('school_id'));
            })->orderBy('name')->get(['id', 'name']),
            'avgRetention' => $avgRetention ? round($avgRetention, 1) : null,
        ]);
    }
}
