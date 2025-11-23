<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceLog;
use App\Models\StudentProfile;
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

    public function exportCsv()
    {
        $logs = $this->getFilteredLogs()->get();
        
        $filename = 'attendance_report_' . $this->dateFrom . '_to_' . $this->dateTo . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, ['Student Name', 'Student ID', 'Date', 'Check-In', 'Check-Out', 'Total Hours', 'Status']);
            
            foreach ($logs as $log) {
                $totalHours = '';
                if ($log->check_in_time && $log->check_out_time) {
                    $checkIn = \Carbon\Carbon::parse($log->check_in_time);
                    $checkOut = \Carbon\Carbon::parse($log->check_out_time);
                    $hours = $checkIn->diffInHours($checkOut, true);
                    $totalHours = number_format($hours, 2) . ' hours';
                }
                
                $status = 'Incomplete';
                if ($log->check_in_time && $log->check_out_time) {
                    $status = 'Present';
                } elseif ($log->check_in_time) {
                    $status = 'Checked In';
                }
                
                fputcsv($file, [
                    $log->studentProfile->full_name ?? 'Unknown',
                    $log->studentProfile->student_id ?? 'N/A',
                    $log->attendance_date->format('Y-m-d'),
                    $log->check_in_time ? \Carbon\Carbon::parse($log->check_in_time)->format('h:i A') : '--:--',
                    $log->check_out_time ? \Carbon\Carbon::parse($log->check_out_time)->format('h:i A') : '--:--',
                    $totalHours,
                    $status,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function getFilteredLogs()
    {
        $query = AttendanceLog::with('studentProfile')
            ->whereBetween('attendance_date', [$this->dateFrom, $this->dateTo])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc');

        if ($this->search) {
            $query->whereHas('studentProfile', function ($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                    ->orWhere('student_id', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            if ($this->statusFilter === 'present') {
                $query->whereNotNull('check_in_time')->whereNotNull('check_out_time');
            } elseif ($this->statusFilter === 'checked_in') {
                $query->whereNotNull('check_in_time')->whereNull('check_out_time');
            } elseif ($this->statusFilter === 'incomplete') {
                $query->whereNull('check_out_time');
            }
        }

        return $query;
    }

    public function mount()
    {
        $this->dateFrom = today()->subDays(7)->format('Y-m-d');
        $this->dateTo = today()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function setDateRange($range)
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
        $logs = $this->getFilteredLogs()->paginate(20);

        // Calculate statistics
        $totalLogs = AttendanceLog::whereBetween('attendance_date', [$this->dateFrom, $this->dateTo])->count();
        $completedLogs = AttendanceLog::whereBetween('attendance_date', [$this->dateFrom, $this->dateTo])
            ->whereNotNull('check_in_time')
            ->whereNotNull('check_out_time')
            ->count();
        $incompleteLogs = AttendanceLog::whereBetween('attendance_date', [$this->dateFrom, $this->dateTo])
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->count();

        // Calculate total hours
        $totalHours = AttendanceLog::whereBetween('attendance_date', [$this->dateFrom, $this->dateTo])
            ->whereNotNull('check_in_time')
            ->whereNotNull('check_out_time')
            ->get()
            ->sum(function ($log) {
                if ($log->check_in_time && $log->check_out_time) {
                    $checkIn = \Carbon\Carbon::parse($log->check_in_time);
                    $checkOut = \Carbon\Carbon::parse($log->check_out_time);
                    return $checkIn->diffInHours($checkOut, true);
                }
                return 0;
            });

        // Get currently present students (checked in today but not checked out)
        $currentlyPresent = AttendanceLog::with('studentProfile')
            ->where('attendance_date', today())
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->orderBy('check_in_time', 'desc')
            ->get();

        return view('livewire.attendance.attendance-dashboard', [
            'logs' => $logs,
            'currentlyPresent' => $currentlyPresent,
            'stats' => [
                'total' => $totalLogs,
                'completed' => $completedLogs,
                'incomplete' => $incompleteLogs,
                'total_hours' => round($totalHours, 2),
                'currently_present' => $currentlyPresent->count(),
            ],
        ]);
    }
}
