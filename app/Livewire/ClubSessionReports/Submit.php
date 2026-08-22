<?php

namespace App\Livewire\ClubSessionReports;

use App\Models\ClubSessionReport;
use App\Models\CodeClub;
use App\Models\CodeClubMembership;
use App\Services\AttendanceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Submit extends Component
{
    use AuthorizesRequests;

    public string $sessionDate = '';
    public ?int $clubId = null;
    public string $summary = '';
    public string $challenges = '';
    public string $topicsCovered = '';
    public string $newTechniques = '';
    public bool $followUpRequired = false;
    public int $attendanceCount = 0;
    public ?int $teamworkRating = null;
    public ?int $collaborationRating = null;

    public function mount(): void
    {
        abort_unless(config('features.code_club', false), 404);
        abort_unless(Auth::user()->hasCodeClubAccess(), 403);

        $this->sessionDate = now()->toDateString();
        $clubs = $this->availableClubs();
        if ($clubs->count() === 1) {
            $this->clubId = $clubs->first()->id;
            $this->prefillCounts();
        }
    }

    public function updatedClubId(): void
    {
        $this->prefillCounts();
    }

    public function updatedSessionDate(): void
    {
        $this->prefillCounts();
    }

    private function authorizeSelectedClub(): CodeClub
    {
        abort_unless($this->clubId, 422);

        $club = CodeClub::findOrFail($this->clubId);
        $this->authorize('submitSessionReport', $club);

        return $club;
    }

    private function prefillCounts(): void
    {
        if (! $this->clubId) {
            return;
        }

        $this->authorizeSelectedClub();

        $this->attendanceCount = app(AttendanceService::class)
            ->clubRoster($this->sessionDate, (int) $this->clubId)
            ->filter(fn ($row) => in_array($row['status'], ['present', 'late'], true))
            ->count();
    }

    private function availableClubs()
    {
        $user = Auth::user();
        $query = CodeClub::query()->where('status', 'active');

        if (! $user->isAdmin() && ! $user->isSupervisor()) {
            $query->whereIn('id', $user->activeClubIds());
        }

        return $query->orderBy('name')->get(['id', 'name']);
    }

    public function submit(): void
    {
        $this->validate([
            'sessionDate' => 'required|date',
            'clubId' => 'required|exists:code_clubs,id',
            'summary' => 'required|string|min:10',
            'challenges' => 'nullable|string',
            'topicsCovered' => 'nullable|string',
            'newTechniques' => 'nullable|string',
            'attendanceCount' => 'required|integer|min:0',
            'teamworkRating' => 'required|integer|min:1|max:5',
            'collaborationRating' => 'required|integer|min:1|max:5',
            'followUpRequired' => 'boolean',
        ]);

        $club = $this->authorizeSelectedClub();
        $clubId = (int) $club->id;

        $enrolledCount = CodeClubMembership::where('code_club_id', $clubId)
            ->where('status', 'active')
            ->count();

        ClubSessionReport::updateOrCreate(
            [
                'code_club_id' => $clubId,
                'session_date' => $this->sessionDate,
            ],
            [
                'facilitator_id' => Auth::id(),
                'status' => 'submitted',
                'summary' => $this->summary,
                'challenges' => $this->challenges ?: null,
                'topics_covered' => $this->topicsCovered ?: null,
                'new_techniques' => $this->newTechniques ?: null,
                'teamwork_rating' => $this->teamworkRating,
                'collaboration_rating' => $this->collaborationRating,
                'attendance_count' => $this->attendanceCount,
                'enrolled_count' => $enrolledCount,
                'follow_up_required' => $this->followUpRequired,
                'submitted_at' => now(),
            ]
        );

        session()->flash('message', 'Session report submitted successfully.');
        $this->reset(['summary', 'challenges', 'topicsCovered', 'newTechniques', 'followUpRequired', 'teamworkRating', 'collaborationRating']);
        $this->prefillCounts();
    }

    public function render()
    {
        return view('livewire.club-session-reports.submit', [
            'clubs' => $this->availableClubs(),
        ]);
    }
}
