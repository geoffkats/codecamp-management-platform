<?php

namespace App\Livewire\Dashboard;

use App\Models\StudentProfile;
use App\Models\StudentAttendance;
use App\Models\InstructorAttendance;
use Illuminate\Database\Eloquent\Builder;
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
            fputcsv($file, ['Total Students', $this->codecampStudentQuery()->count()]);
            fputcsv($file, ['Present Today', StudentAttendance::where('attendance_date', today())->whereNull('course_id')->where('status', 'present')->count()]);
            fputcsv($file, ['Absent Today', StudentAttendance::where('attendance_date', today())->whereNull('course_id')->where('status', 'absent')->count()]);
            fputcsv($file, ['Late Today', StudentAttendance::where('attendance_date', today())->whereNull('course_id')->where('status', 'late')->count()]);
            fputcsv($file, ['Instructors Present', InstructorAttendance::where('attendance_date', today())->where('status', 'present')->count()]);
            fputcsv($file, ['Uniform Payments Pending', $this->codecampStudentQuery()->where('uniform_paid', false)->count()]);
            fputcsv($file, []);
            
            // Student Attendance
            fputcsv($file, ['Student Attendance Today']);
            fputcsv($file, ['Student ID', 'Name', 'Category', 'Status', 'Reason', 'Time']);
            
            $attendance = StudentAttendance::with('studentProfile')
                ->where('attendance_date', today())
                ->whereNull('course_id')
                ->whereHas('studentProfile', fn (Builder $q) => $q->where('program_type', '!=', 'codeclub'))
                ->get();
                
            foreach ($attendance as $record) {
                fputcsv($file, [
                    $record->studentProfile->student_id,
                    $record->studentProfile->full_name,
                    $this->formatStudentCategory($record->studentProfile),
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
            fputcsv($file, ['Date', 'Student ID', 'Student Name', 'Category', 'Class', 'Status', 'Reason', 'Recorded By', 'Time']);
            
            $records = StudentAttendance::with(['studentProfile', 'recorder'])
                ->where('attendance_date', today())
                ->whereHas('studentProfile', fn (Builder $q) => $q->where('program_type', '!=', 'codeclub'))
                ->get();
                
            foreach ($records as $record) {
                fputcsv($file, [
                    $record->attendance_date->format('Y-m-d'),
                    $record->studentProfile->student_id,
                    $record->studentProfile->full_name,
                    $this->formatStudentCategory($record->studentProfile),
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
            'total_students' => $this->codecampStudentQuery()->count(),
            'present_today' => StudentAttendance::where('attendance_date', today())
                ->whereNull('course_id')
                ->whereIn('status', ['present', 'late'])
                ->whereHas('studentProfile', fn (Builder $q) => $q->where('program_type', '!=', 'codeclub'))
                ->count(),
            'absent_today' => StudentAttendance::where('attendance_date', today())
                ->whereNull('course_id')
                ->where('status', 'absent')
                ->whereHas('studentProfile', fn (Builder $q) => $q->where('program_type', '!=', 'codeclub'))
                ->count(),
            'uniform_pending' => $this->codecampStudentQuery()->where('uniform_paid', false)->count(),
            'instructors_present' => InstructorAttendance::where('attendance_date', today())
                ->where('status', 'present')
                ->count(),
            'total_gadgets' => $this->codecampStudentQuery()->withCount('gadgets')->get()->sum('gadgets_count'),
        ];

        $recentAttendance = StudentAttendance::with(['studentProfile', 'course'])
            ->where('attendance_date', today())
            ->whereNull('course_id')
            ->whereHas('studentProfile', fn (Builder $q) => $q->where('program_type', '!=', 'codeclub'))
            ->latest()
            ->limit(10)
            ->get();

        $uniformPending = $this->codecampStudentQuery()
            ->where('uniform_paid', false)
            ->limit(10)
            ->get();

        return view('livewire.dashboard.operations-manager-dashboard', [
            'stats' => $stats,
            'recentAttendance' => $recentAttendance,
            'uniformPending' => $uniformPending,
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

    private function codecampStudentQuery(): Builder
    {
        return StudentProfile::query()->where('program_type', '!=', 'codeclub');
    }
}
