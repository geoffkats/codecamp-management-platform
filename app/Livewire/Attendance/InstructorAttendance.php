<?php

namespace App\Livewire\Attendance;

use App\Models\User;
use App\Models\InstructorAttendance as InstructorAttendanceModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class InstructorAttendance extends Component
{
    public $attendanceDate;
    public $instructors = [];
    public $attendance = [];
    public $reasons = [];
    public $search = '';

    public function mount()
    {
        $this->attendanceDate = today()->format('Y-m-d');
        $this->loadInstructors();
    }

    public function loadInstructors()
    {
        $query = User::whereHas('roles', function($q) {
            $q->where('name', 'teacher');
        });

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $this->instructors = $query->get();
        
        // Load existing attendance for the date
        $existing = InstructorAttendanceModel::where('attendance_date', $this->attendanceDate)
            ->get()
            ->keyBy('user_id');

        foreach ($this->instructors as $instructor) {
            $existingRecord = $existing->get($instructor->id);
            $this->attendance[$instructor->id] = $existingRecord?->status ?? 'present';
            $this->reasons[$instructor->id] = $existingRecord?->reason ?? '';
        }
    }

    public function updatedAttendanceDate()
    {
        $this->loadInstructors();
    }

    public function updatedSearch()
    {
        $this->loadInstructors();
    }

    public function saveAttendance()
    {
        foreach ($this->attendance as $instructorId => $status) {
            InstructorAttendanceModel::updateOrCreate(
                [
                    'user_id' => $instructorId,
                    'attendance_date' => $this->attendanceDate,
                ],
                [
                    'status' => $status,
                    'reason' => $this->reasons[$instructorId] ?? null,
                    'recorded_by' => Auth::id(),
                ]
            );
        }

        session()->flash('message', 'Instructor attendance recorded successfully!');
    }

    public function render()
    {
        return view('livewire.attendance.instructor-attendance');
    }
}
