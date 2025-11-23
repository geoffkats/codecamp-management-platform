<?php

namespace App\Livewire\Attendance;

use App\Models\StudentAttendance;
use App\Models\InstructorAttendance;
use App\Models\StudentProfile;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class AttendanceRecords extends Component
{
    use WithPagination;

    public $type = 'student'; // student or instructor
    public $startDate = '';
    public $endDate = '';
    public $status = '';
    public $search = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function exportRecords()
    {
        session()->flash('message', 'Export feature coming soon!');
    }

    public function render()
    {
        if ($this->type === 'student') {
            $records = StudentAttendance::with(['studentProfile', 'recorder'])
                ->whereBetween('attendance_date', [$this->startDate, $this->endDate])
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->when($this->search, function($q) {
                    $q->whereHas('studentProfile', function($query) {
                        $query->where('full_name', 'like', '%' . $this->search . '%')
                              ->orWhere('student_id', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('attendance_date', 'desc')
                ->paginate(20);
        } else {
            $records = InstructorAttendance::with(['instructor', 'recorder'])
                ->whereBetween('attendance_date', [$this->startDate, $this->endDate])
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->when($this->search, function($q) {
                    $q->whereHas('instructor', function($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('attendance_date', 'desc')
                ->paginate(20);
        }

        return view('livewire.attendance.attendance-records', [
            'records' => $records,
        ]);
    }
}
