<?php

namespace App\Livewire\Attendance;

use App\Models\StudentProfile;
use App\Models\StudentAttendance as StudentAttendanceModel;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentAttendance extends Component
{
    public $attendanceDate;
    public $courseId = null;
    public $students = [];
    public $attendance = [];
    public $reasons = [];
    public $clockIn = [];
    public $clockOut = [];
    public $search = '';
    public $classFilter = '';
    public $statusFilter = '';
    public $totalMarked = 0;

    public function mount()
    {
        $this->attendanceDate = today()->format('Y-m-d');
        $this->loadStudents();
    }

    public function loadStudents()
    {
        $query = StudentProfile::with('user');

        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('student_id', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->classFilter) {
            $query->where('class_grade', $this->classFilter);
        }

        $this->students = $query->get();
        
        // Load existing attendance for the date
        $existing = StudentAttendanceModel::where('attendance_date', $this->attendanceDate)
            ->when($this->courseId, fn($q) => $q->where('course_id', $this->courseId))
            ->get()
            ->keyBy('student_profile_id');

        foreach ($this->students as $student) {
            $existingRecord = $existing->get($student->id);
            $this->attendance[$student->id] = $existingRecord?->status ?? 'present';
            $this->reasons[$student->id] = $existingRecord?->reason ?? '';
            $this->clockIn[$student->id] = $existingRecord?->clock_in ? date('H:i', strtotime($existingRecord->clock_in)) : '';
            $this->clockOut[$student->id] = $existingRecord?->clock_out ? date('H:i', strtotime($existingRecord->clock_out)) : '';
        }

        $this->calculateTotalMarked();
    }

    public function calculateTotalMarked()
    {
        $this->totalMarked = count(array_filter($this->attendance));
    }

    public function updatedSearch()
    {
        $this->loadStudents();
    }

    public function updatedClassFilter()
    {
        $this->loadStudents();
    }

    public function updatedStatusFilter()
    {
        // This will be used in the view to filter displayed cards
    }

    public function updatedAttendanceDate()
    {
        $this->loadStudents();
    }

    public function updatedCourseId()
    {
        $this->loadStudents();
    }

    public function saveAttendance()
    {
        foreach ($this->attendance as $studentId => $status) {
            $clockIn = $this->normalizeTime($this->clockIn[$studentId] ?? null);
            $clockOut = $this->normalizeTime($this->clockOut[$studentId] ?? null);

            StudentAttendanceModel::updateOrCreate(
                [
                    'student_profile_id' => $studentId,
                    'attendance_date' => $this->attendanceDate,
                    'course_id' => $this->courseId,
                ],
                [
                    'status' => $status,
                    'reason' => $this->reasons[$studentId] ?? null,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'recorded_by' => Auth::id(),
                ]
            );
        }

        session()->flash('message', 'Attendance recorded successfully!');
    }

    public function render()
    {
        $courses = Course::orderBy('title')->get();
        
        return view('livewire.attendance.student-attendance', [
            'courses' => $courses,
        ]);
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
        } catch (\Throwable $e) {
            return null;
        }
    }
}
