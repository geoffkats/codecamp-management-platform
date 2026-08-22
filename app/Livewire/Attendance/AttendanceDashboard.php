<?php

namespace App\Livewire\Attendance;

use App\Models\CodeCamp;
use App\Models\Course;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Services\AttendanceService;
use App\Support\ProgramScope;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class AttendanceDashboard extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $search = '';
    public $statusFilter = '';
    public $studentSearch = '';
    public $courseFilter = 'all';
    public $campFilter = '';

    protected AttendanceService $attendance;

    public function boot(AttendanceService $attendance): void
    {
        $this->attendance = $attendance;
    }

    public function markFilteredPresentForRange(string $range): void
    {
        $students = $this->getFilteredStudentsQuery()->get();

        if ($students->isEmpty()) {
            session()->flash('error', 'No students matched your filters.');

            return;
        }

        [$startDate, $endDate] = $this->resolveRange($range);
        $days = collect(CarbonPeriod::create($startDate, $endDate))
            ->filter(fn (Carbon $date) => ! $date->isWeekend())
            ->values();

        if ($days->isEmpty()) {
            session()->flash('error', 'No weekdays found in that range.');

            return;
        }

        $campId = filled($this->campFilter) ? (int) $this->campFilter : null;
        $updated = 0;
        $lockedDays = 0;

        foreach ($days as $day) {
            if ($this->attendance->isLocked($day)) {
                $lockedDays++;

                continue;
            }

            $updated += $this->attendance->markBulkPresent(
                $students,
                $day,
                Auth::user(),
                $campId
            );
        }

        if ($updated === 0) {
            if ($lockedDays === $days->count()) {
                session()->flash('error', 'Could not mark attendance — all days in that range are locked. If today is past the cutoff time, try again tomorrow or ask an admin.');
            } else {
                session()->flash('message', 'No new records created. Filtered students may already be marked present for those days.');
            }

            return;
        }

        $label = $range === 'last_week' ? 'last week' : 'this week';
        session()->flash('message', "Marked {$students->count()} students present for {$label} ({$updated} attendance records created/updated).");
    }

    public function markStudentPresentForRange(int $studentProfileId, string $range): void
    {
        $student = StudentProfile::find($studentProfileId);

        if (! $student) {
            session()->flash('error', 'Student not found.');

            return;
        }

        [$startDate, $endDate] = $this->resolveRange($range);
        $days = collect(CarbonPeriod::create($startDate, $endDate))
            ->filter(fn (Carbon $date) => ! $date->isWeekend())
            ->values();

        $campId = filled($this->campFilter) ? (int) $this->campFilter : null;
        $updated = 0;
        $lockedDays = 0;

        foreach ($days as $day) {
            if ($this->attendance->isLocked($day)) {
                $lockedDays++;

                continue;
            }

            $updated += $this->attendance->markBulkPresent(
                collect([$student]),
                $day,
                Auth::user(),
                $campId
            );
        }

        if ($updated === 0) {
            if ($lockedDays === $days->count()) {
                session()->flash('error', "Could not mark {$student->full_name} — all days in that range are locked.");
            } else {
                session()->flash('message', "{$student->full_name} already had attendance for those days.");
            }

            return;
        }

        session()->flash('message', "Marked {$student->full_name} present for {$updated} day(s).");
    }

    public function markToday(int $studentProfileId, string $status): void
    {
        $student = StudentProfile::find($studentProfileId);

        if (! $student) {
            session()->flash('error', 'Student not found.');

            return;
        }

        try {
            $this->attendance->markManual(
                $student,
                $status,
                today(),
                Auth::user(),
                $this->campFilter ?: null,
                $status === 'absent' ? 'Marked absent from dashboard' : null,
                in_array($status, ['present', 'late'], true) ? '08:00:00' : null,
                in_array($status, ['present', 'late'], true) ? config('attendance.default_clock_out', '17:00') . ':00' : null,
            );
            session()->flash('message', "{$student->full_name} marked as {$status} for today.");
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function exportCsv()
    {
        $records = $this->getFilteredRecords()->get();

        $filename = 'attendance_report_' . $this->dateFrom . '_to_' . $this->dateTo . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Student Name', 'Student ID', 'Category', 'Date', 'Check-In', 'Check-Out', 'Total Hours', 'Status', 'Source']);

            foreach ($records as $record) {
                $totalHours = '';
                if ($record->clock_in && $record->clock_out) {
                    $checkIn = Carbon::parse($record->attendance_date->format('Y-m-d') . ' ' . $this->clockString($record->clock_in));
                    $checkOut = Carbon::parse($record->attendance_date->format('Y-m-d') . ' ' . $this->clockString($record->clock_out));
                    $totalHours = number_format($checkIn->diffInHours($checkOut, true), 2) . ' hours';
                }

                $status = ucfirst($record->status ?? 'unknown');
                if ($record->clock_in && ! $record->clock_out) {
                    $status = 'Checked In';
                }

                fputcsv($file, [
                    $record->studentProfile->full_name ?? 'Unknown',
                    $record->studentProfile->student_id ?? 'N/A',
                    $this->formatStudentCategory($record->studentProfile),
                    $record->attendance_date->format('Y-m-d'),
                    $record->clock_in ? Carbon::parse($record->attendance_date->format('Y-m-d') . ' ' . $this->clockString($record->clock_in))->format('h:i A') : '--:--',
                    $record->clock_out ? Carbon::parse($record->attendance_date->format('Y-m-d') . ' ' . $this->clockString($record->clock_out))->format('h:i A') : '--:--',
                    $totalHours,
                    $status,
                    $record->source ?? 'manual',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function getFilteredRecords()
    {
        $query = $this->attendance->exportQuery(
            $this->dateFrom,
            $this->dateTo,
            $this->campFilter ?: null,
            $this->search ?: null
        );

        if ($this->courseFilter && $this->courseFilter !== 'all') {
            $query->whereHas('studentProfile.user.enrollments', function ($q) {
                $q->where('course_id', (int) $this->courseFilter);
            });
        }

        if ($this->statusFilter) {
            if ($this->statusFilter === 'present') {
                $query->whereIn('status', ['present', 'late'])
                    ->whereNotNull('clock_in')
                    ->whereNotNull('clock_out');
            } elseif ($this->statusFilter === 'checked_in') {
                $query->whereNotNull('clock_in')->whereNull('clock_out');
            } elseif ($this->statusFilter === 'incomplete') {
                $query->where(function ($q) {
                    $q->whereNull('clock_out')->orWhereNull('clock_in');
                });
            } elseif ($this->statusFilter === 'absent') {
                $query->where('status', 'absent');
            }
        }

        return $query;
    }

    protected function getFilteredStudentsQuery()
    {
        $query = ProgramScope::applyStudentProfileScope(StudentProfile::query())
            ->with([
                'user:id,name,email',
                'user.enrollments:id,user_id,course_id',
                'user.enrollments.course:id,title',
            ])
            ->where('is_active', true)
            ->orderBy('full_name');

        if ($this->studentSearch) {
            $search = '%' . $this->studentSearch . '%';
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', $search)
                    ->orWhere('student_id', 'like', $search)
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
            });
        }

        if ($this->courseFilter && $this->courseFilter !== 'all') {
            $query->whereHas('user.enrollments', function ($q) {
                $q->where('course_id', (int) $this->courseFilter);
            });
        }

        if ($this->campFilter) {
            $query->whereHas('user.campEnrollments', function ($q) {
                $q->where('camp_id', (int) $this->campFilter)->where('status', 'active');
            });
        }

        return $query;
    }

    protected function resolveRange(string $range): array
    {
        $today = today();

        if ($range === 'last_week') {
            return [
                $today->copy()->subWeek()->startOfWeek(),
                $today->copy()->subWeek()->endOfWeek(),
            ];
        }

        return [
            $today->copy()->startOfWeek(),
            $today->copy()->endOfWeek(),
        ];
    }

    public function mount(): void
    {
        $this->dateFrom = today()->subDays(7)->format('Y-m-d');
        $this->dateTo = today()->format('Y-m-d');

        $activeCamps = CodeCamp::where('status', 'active')->get();
        if ($activeCamps->count() === 1) {
            $this->campFilter = (string) $activeCamps->first()->id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStudentSearch(): void
    {
        $this->resetPage('studentsPage');
    }

    public function updatingCourseFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCampFilter(): void
    {
        $this->resetPage();
    }

    public function setDateRange($range): void
    {
        switch ($range) {
            case 'today':
                $this->dateFrom = today()->format('Y-m-d');
                $this->dateTo = today()->format('Y-m-d');
                break;
            case 'week':
                $this->dateFrom = today()->startOfWeek()->format('Y-m-d');
                $this->dateTo = today()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->dateFrom = today()->startOfMonth()->format('Y-m-d');
                $this->dateTo = today()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_week':
                $this->dateFrom = today()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->dateTo = today()->subWeek()->endOfWeek()->format('Y-m-d');
                break;
            case 'last_month':
                $this->dateFrom = today()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->dateTo = today()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    public function render()
    {
        $records = $this->getFilteredRecords()->paginate(20);

        $students = $this->getFilteredStudentsQuery()->paginate(20, ['*'], 'studentsPage');
        $courseOptions = Course::query()->orderBy('title')->get(['id', 'title']);
        $campOptions = CodeCamp::whereIn('status', ['upcoming', 'active'])->orderBy('start_date')->get(['id', 'name']);

        $todayRecords = StudentAttendance::query()
            ->where('attendance_date', today())
            ->whereNull('course_id')
            ->when($this->campFilter, fn ($q) => $q->where('camp_id', (int) $this->campFilter))
            ->get()
            ->keyBy('student_profile_id');

        $currentlyPresent = StudentAttendance::with('studentProfile')
            ->where('attendance_date', today())
            ->whereNull('course_id')
            ->when($this->campFilter, fn ($q) => $q->where('camp_id', (int) $this->campFilter))
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->orderBy('clock_in', 'desc')
            ->get();

        $rangeStats = $this->attendance->stats(
            $this->dateFrom,
            $this->dateTo,
            $this->campFilter ?: null
        );

        $totalHours = StudentAttendance::whereNull('course_id')
            ->whereBetween('attendance_date', [$this->dateFrom, $this->dateTo])
            ->when($this->campFilter, fn ($q) => $q->where('camp_id', (int) $this->campFilter))
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->get()
            ->sum(function ($record) {
                $checkIn = Carbon::parse($record->attendance_date->format('Y-m-d') . ' ' . $this->clockString($record->clock_in));
                $checkOut = Carbon::parse($record->attendance_date->format('Y-m-d') . ' ' . $this->clockString($record->clock_out));

                return $checkIn->diffInHours($checkOut, true);
            });

        return view('livewire.attendance.attendance-dashboard', [
            'records'          => $records,
            'students'         => $students,
            'courseOptions'    => $courseOptions,
            'campOptions'      => $campOptions,
            'todayRecords'     => $todayRecords,
            'currentlyPresent' => $currentlyPresent,
            'stats'            => [
                'total'              => $rangeStats['total'],
                'completed'          => StudentAttendance::whereNull('course_id')
                    ->whereBetween('attendance_date', [$this->dateFrom, $this->dateTo])
                    ->when($this->campFilter, fn ($q) => $q->where('camp_id', (int) $this->campFilter))
                    ->whereNotNull('clock_in')
                    ->whereNotNull('clock_out')
                    ->count(),
                'incomplete'         => StudentAttendance::whereNull('course_id')
                    ->whereBetween('attendance_date', [$this->dateFrom, $this->dateTo])
                    ->when($this->campFilter, fn ($q) => $q->where('camp_id', (int) $this->campFilter))
                    ->whereNotNull('clock_in')
                    ->whereNull('clock_out')
                    ->count(),
                'total_hours'        => round($totalHours, 2),
                'currently_present'  => $currentlyPresent->count(),
                'checked_in_today'   => $rangeStats['checked_in_today'],
            ],
        ]);
    }

    private function formatStudentCategory(?StudentProfile $profile): string
    {
        $category = $profile?->student_category ?? 'codecamp';

        return match ($category) {
            'school_club' => 'School Club',
            'ict_school' => 'ICT School',
            default => 'Codecamp',
        };
    }

    private function clockString(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('H:i:s');
        }

        return (string) $value;
    }
}
