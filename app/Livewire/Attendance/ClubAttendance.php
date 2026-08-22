<?php

namespace App\Livewire\Attendance;

use App\Models\CodeClub;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ClubAttendance extends Component
{
    use AuthorizesRequests;

    public string $sessionDate = '';
    public ?int $clubId = null;
    public string $search = '';
    public array $attendanceStatuses = [];

    protected AttendanceService $attendanceService;

    public function boot(AttendanceService $attendance): void
    {
        $this->attendanceService = $attendance;
    }

    public function mount(): void
    {
        abort_unless(config('features.code_club', false), 404);
        abort_unless(Auth::user()->hasCodeClubAccess(), 403);

        $this->sessionDate = now()->toDateString();
        $clubs = $this->availableClubs();

        $requestedClub = request()->integer('club');
        if ($requestedClub && $clubs->contains('id', $requestedClub)) {
            $this->clubId = $requestedClub;
        } elseif ($clubs->count() === 1) {
            $this->clubId = $clubs->first()->id;
        }

        $this->loadRoster();
    }

    public function updatedSessionDate(): void
    {
        $this->loadRoster();
    }

    public function updatedClubId($value): void
    {
        $this->clubId = filled($value) ? (int) $value : null;
        $this->loadRoster();
    }

    public function updatedSearch(): void
    {
        $this->loadRoster();
    }

    private function availableClubs()
    {
        $user = Auth::user();
        $query = CodeClub::query()->where('status', 'active');

        if (! $user->isAdmin() && ! $user->isSupervisor()) {
            $clubIds = $user->activeClubIds();

            if ($clubIds === []) {
                return collect();
            }

            $query->whereIn('id', $clubIds);
        }

        return $query->orderBy('name')->get(['id', 'name']);
    }

    private function authorizeSelectedClub(): CodeClub
    {
        abort_unless($this->clubId, 422);

        $club = CodeClub::findOrFail($this->clubId);
        $this->authorize('view', $club);

        return $club;
    }

    private function authorizeSaveAttendance(): CodeClub
    {
        $club = $this->authorizeSelectedClub();
        $this->authorize('manageAttendance', [$club, Carbon::parse($this->sessionDate ?: now())]);

        return $club;
    }

    private function loadRoster(): void
    {
        if (! $this->clubId) {
            $this->attendanceStatuses = [];

            return;
        }

        $this->authorizeSelectedClub();

        $roster = $this->attendanceService->clubRoster($this->sessionDate, (int) $this->clubId, $this->search ?: null);

        $this->attendanceStatuses = $roster->mapWithKeys(function ($row) {
            return [$row['profile']->id => $row['status'] ?? 'absent'];
        })->all();
    }

    public function saveAttendance(): void
    {
        $this->authorizeSaveAttendance();

        $profiles = $this->attendanceService->clubRoster($this->sessionDate, (int) $this->clubId, $this->search ?: null);

        foreach ($profiles as $row) {
            $profile = $row['profile'];
            $status = $this->attendanceStatuses[$profile->id] ?? 'absent';

            $this->attendanceService->markClubAttendance(
                $profile,
                $this->sessionDate,
                (int) $this->clubId,
                $status,
                Auth::user()
            );
        }

        session()->flash('message', 'Club attendance saved.');
        $this->loadRoster();
    }

    public function render()
    {
        $selectedClub = null;
        $sessionMeetsToday = false;

        if ($this->clubId) {
            $selectedClub = CodeClub::with('schedules')->find($this->clubId);
            if ($selectedClub) {
                $this->authorizeSelectedClub();
                $sessionMeetsToday = $this->attendanceService->clubMeetsOnDate(
                    $selectedClub,
                    Carbon::parse($this->sessionDate ?: now())
                );
            }
        }

        $roster = $this->clubId
            ? $this->attendanceService->clubRoster($this->sessionDate, (int) $this->clubId, $this->search ?: null)
            : collect();

        $todayCode = \App\Models\DailyAttendanceCode::getTodayCode();

        return view('livewire.attendance.club-attendance', [
            'clubs' => $this->availableClubs(),
            'roster' => $roster,
            'selectedClub' => $selectedClub,
            'sessionMeetsToday' => $sessionMeetsToday,
            'todayCode' => $todayCode?->code,
        ]);
    }
}
