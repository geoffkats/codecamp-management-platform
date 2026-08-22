<?php

namespace App\Livewire\Attendance;

use App\Models\CodeCamp;
use App\Models\InstructorAttendance;
use App\Services\AttendanceService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class AttendanceRecords extends Component
{
    use WithPagination;

    public $type = 'student';
    public $startDate = '';
    public $endDate = '';
    public $status = '';
    public $search = '';
    public $campId = '';

    protected AttendanceService $attendance;

    public function boot(AttendanceService $attendance): void
    {
        $this->attendance = $attendance;
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');

        $activeCamps = CodeCamp::where('status', 'active')->get();
        if ($activeCamps->count() === 1) {
            $this->campId = (string) $activeCamps->first()->id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function exportRecords()
    {
        if ($this->type !== 'student') {
            session()->flash('message', 'Instructor export coming soon.');

            return;
        }

        $records = $this->attendance->exportQuery(
            $this->startDate,
            $this->endDate,
            $this->campId ?: null,
            $this->search ?: null
        )->when($this->status, fn ($q) => $q->where('status', $this->status))->get();

        $filename = "attendance-records-{$this->startDate}-to-{$this->endDate}.csv";

        return response()->streamDownload(function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Student ID', 'Name', 'Status', 'Clock In', 'Clock Out', 'Source', 'Camp']);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->attendance_date->format('Y-m-d'),
                    $record->studentProfile->student_id ?? '',
                    $record->studentProfile->full_name ?? '',
                    $record->status,
                    $record->clock_in ? (string) $record->clock_in : '',
                    $record->clock_out ? (string) $record->clock_out : '',
                    $record->source ?? '',
                    $record->camp?->name ?? '',
                ]);
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        if ($this->type === 'student') {
            $records = $this->attendance->exportQuery(
                $this->startDate,
                $this->endDate,
                $this->campId ?: null,
                $this->search ?: null
            )
                ->when($this->status, fn ($q) => $q->where('status', $this->status))
                ->paginate(20);
        } else {
            $records = InstructorAttendance::with(['instructor', 'recorder'])
                ->whereBetween('attendance_date', [$this->startDate, $this->endDate])
                ->when($this->status, fn ($q) => $q->where('status', $this->status))
                ->when($this->search, function ($q) {
                    $q->whereHas('instructor', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('attendance_date', 'desc')
                ->paginate(20);
        }

        $camps = CodeCamp::whereIn('status', ['upcoming', 'active'])->orderBy('start_date')->get(['id', 'name']);

        return view('livewire.attendance.attendance-records', [
            'records' => $records,
            'camps'   => $camps,
        ]);
    }
}
