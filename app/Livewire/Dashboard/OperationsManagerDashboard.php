<?php

namespace App\Livewire\Dashboard;

use App\Models\StudentProfile;
use App\Models\StudentAttendance;
use App\Models\InstructorAttendance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OperationsManagerDashboard extends Component
{
    public function mount()
    {
        if (!Auth::user()->hasRole('operations_manager') && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized - Operations Manager access required');
        }
    }

    public function exportDailyReport()
    {
        $date = today()->format('Y-m-d');
        $filename = "daily-report-{$date}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Daily Operations Report - ' . today()->format('F j, Y')]);
            fputcsv($file, []);
            
            // Summary Stats
            fputcsv($file, ['Summary Statistics']);
            fputcsv($file, ['Total Students', StudentProfile::count()]);
            fputcsv($file, ['Present Today', StudentAttendance::where('attendance_date', today())->where('status', 'present')->count()]);
            fputcsv($file, ['Absent Today', StudentAttendance::where('attendance_date', today())->where('status', 'absent')->count()]);
            fputcsv($file, ['Late Today', StudentAttendance::where('attendance_date', today())->where('status', 'late')->count()]);
            fputcsv($file, ['Instructors Present', InstructorAttendance::where('attendance_date', today())->where('status', 'present')->count()]);
            fputcsv($file, ['Uniform Payments Pending', StudentProfile::where('uniform_paid', false)->count()]);
            fputcsv($file, []);
            
            // Student Attendance
            fputcsv($file, ['Student Attendance Today']);
            fputcsv($file, ['Student ID', 'Name', 'Status', 'Reason', 'Time']);
            
            $attendance = StudentAttendance::with('studentProfile')
                ->where('attendance_date', today())
                ->get();
                
            foreach ($attendance as $record) {
                fputcsv($file, [
                    $record->studentProfile->student_id,
                    $record->studentProfile->full_name,
                    ucfirst($record->status),
                    $record->reason ?? '-',
                    $record->created_at->format('H:i'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadCSV()
    {
        $date = today()->format('Y-m-d');
        $filename = "attendance-data-{$date}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Student Attendance CSV
            fputcsv($file, ['Student Attendance Records']);
            fputcsv($file, ['Date', 'Student ID', 'Student Name', 'Class', 'Status', 'Reason', 'Recorded By', 'Time']);
            
            $records = StudentAttendance::with(['studentProfile', 'recorder'])
                ->where('attendance_date', today())
                ->get();
                
            foreach ($records as $record) {
                fputcsv($file, [
                    $record->attendance_date->format('Y-m-d'),
                    $record->studentProfile->student_id,
                    $record->studentProfile->full_name,
                    $record->studentProfile->class_grade ?? 'N/A',
                    ucfirst($record->status),
                    $record->reason ?? '-',
                    $record->recorder->name ?? 'System',
                    $record->created_at->format('H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function viewAbsentees()
    {
        return redirect()->route('students.index', ['filter' => 'absent_today']);
    }

    public function render()
    {
        $stats = [
            'total_students' => StudentProfile::count(),
            'present_today' => StudentAttendance::where('attendance_date', today())
                ->where('status', 'present')
                ->count(),
            'absent_today' => StudentAttendance::where('attendance_date', today())
                ->where('status', 'absent')
                ->count(),
            'uniform_pending' => StudentProfile::where('uniform_paid', false)->count(),
            'instructors_present' => InstructorAttendance::where('attendance_date', today())
                ->where('status', 'present')
                ->count(),
            'total_gadgets' => StudentProfile::withCount('gadgets')->get()->sum('gadgets_count'),
        ];

        $recentAttendance = StudentAttendance::with(['studentProfile', 'course'])
            ->where('attendance_date', today())
            ->latest()
            ->limit(10)
            ->get();

        $uniformPending = StudentProfile::where('uniform_paid', false)
            ->limit(10)
            ->get();

        return view('livewire.dashboard.operations-manager-dashboard', [
            'stats' => $stats,
            'recentAttendance' => $recentAttendance,
            'uniformPending' => $uniformPending,
        ]);
    }
}
