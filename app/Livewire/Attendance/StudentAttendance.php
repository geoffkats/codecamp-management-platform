<?php

namespace App\Livewire\Attendance;

use App\Models\CodeCamp;
use App\Models\StudentProfile;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentAttendance extends Component
{
    public $attendanceDate;
    public $campId = null;
    public $roster = [];
    public $attendance = [];
    public $reasons = [];
    public $clockIn = [];
    public $clockOut = [];
    public $initial = [];
    public $search = '';
    public $classFilter = '';
    public $statusFilter = '';
    public $isLocked = false;

    protected AttendanceService $attendanceService;

    public function boot(AttendanceService $attendanceService): void
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount(): void
    {
        $this->attendanceDate = today()->format('Y-m-d');

        $activeCamps = CodeCamp::where('status', 'active')->orderBy('start_date')->get();
        if ($activeCamps->count() === 1) {
            $this->campId = $activeCamps->first()->id;
        }

        $this->loadRoster();
    }

    public function loadRoster(): void
    {
        $this->isLocked = $this->attendanceService->isLocked($this->attendanceDate);

        $rows = $this->attendanceService->roster(
            $this->attendanceDate,
            $this->campId ?: null,
            $this->search ?: null
        );

        if ($this->classFilter) {
            $rows = $rows->filter(fn ($row) => ($row['profile']->class_grade ?? '') === $this->classFilter);
        }

        $this->roster = $rows->values()->all();
        $this->attendance = [];
        $this->reasons = [];
        $this->clockIn = [];
        $this->clockOut = [];
        $this->initial = [];

        foreach ($this->roster as $row) {
            $id = $row['profile']->id;
            $status = $row['status'] ?? '';
            $this->attendance[$id] = $status;
            $this->reasons[$id] = $row['record']?->reason ?? '';
            $this->clockIn[$id] = $row['clock_in'] ? $this->formatTimeForInput($row['clock_in']) : '';
            $this->clockOut[$id] = $row['clock_out'] ? $this->formatTimeForInput($row['clock_out']) : '';
            $this->initial[$id] = [
                'status'    => $status,
                'reason'    => $this->reasons[$id],
                'clock_in'  => $this->clockIn[$id],
                'clock_out' => $this->clockOut[$id],
            ];
        }
    }

    public function updatedSearch(): void
    {
        $this->loadRoster();
    }

    public function updatedClassFilter(): void
    {
        $this->loadRoster();
    }

    public function updatedAttendanceDate(): void
    {
        $this->loadRoster();
    }

    public function updatedCampId(): void
    {
        $this->loadRoster();
    }

    public function saveAttendance(): void
    {
        if ($this->isLocked) {
            session()->flash('error', 'Attendance is locked for this date.');

            return;
        }

        $saved = 0;

        foreach ($this->attendance as $studentId => $status) {
            if ($status === '' || $status === null) {
                continue;
            }

            if (! $this->rowChanged((int) $studentId, $status)) {
                continue;
            }

            $profile = StudentProfile::find($studentId);
            if (! $profile) {
                continue;
            }

            try {
                $this->attendanceService->markManual(
                    $profile,
                    $status,
                    $this->attendanceDate,
                    Auth::user(),
                    $this->campId ?: null,
                    $this->reasons[$studentId] ?? null,
                    $this->normalizeTime($this->clockIn[$studentId] ?? null),
                    $this->normalizeTime($this->clockOut[$studentId] ?? null),
                );
                $saved++;
            } catch (\RuntimeException $e) {
                session()->flash('error', $e->getMessage());

                return;
            }
        }

        session()->flash('message', $saved > 0
            ? "Saved {$saved} attendance record(s)."
            : 'No changes to save.');
        $this->loadRoster();
    }

    public function render()
    {
        $camps = CodeCamp::whereIn('status', ['upcoming', 'active'])
            ->orderBy('start_date')
            ->get(['id', 'name', 'status']);

        $profiles = collect($this->roster)->pluck('profile');
        $markedCount = collect($this->attendance)->filter(fn ($s) => $s !== '')->count();
        $todayStats = $this->attendanceService->stats($this->attendanceDate, $this->attendanceDate, $this->campId ?: null);

        return view('livewire.attendance.student-attendance', [
            'camps'        => $camps,
            'profiles'     => $profiles,
            'markedCount'  => $markedCount,
            'todayStats'   => $todayStats,
        ]);
    }

    private function rowChanged(int $studentId, string $status): bool
    {
        $initial = $this->initial[$studentId] ?? null;

        if (! $initial) {
            return true;
        }

        return $initial['status'] !== $status
            || ($initial['reason'] ?? '') !== ($this->reasons[$studentId] ?? '')
            || ($initial['clock_in'] ?? '') !== ($this->clockIn[$studentId] ?? '')
            || ($initial['clock_out'] ?? '') !== ($this->clockOut[$studentId] ?? '');
    }

    private function normalizeTime(?string $value): ?string
    {
        $value = $value ? trim($value) : '';

        if ($value === '') {
            return null;
        }

        try {
            $format = strlen($value) === 5 ? 'H:i' : 'H:i:s';

            return Carbon::createFromFormat($format, $value)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatTimeForInput(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('H:i');
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return '';
        }
    }
}
