<?php

namespace App\Livewire\Attendance;

use App\Models\DailyAttendanceCode;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TeacherCodeDisplay extends Component
{
    public $code;
    public $date;

    public function mount()
    {
        $this->loadTodayCode();
    }

    public function loadTodayCode()
    {
        $todayCode = DailyAttendanceCode::getTodayCode();
        
        if (!$todayCode) {
            $todayCode = DailyAttendanceCode::createTodayCode();
        }

        $this->code = $todayCode->code;
        $this->date = $todayCode->date->format('l, F j, Y');
    }

    public function generateNewCode()
    {
        $newCode = DailyAttendanceCode::createTodayCode();
        $this->code = $newCode->code;
        session()->flash('message', 'New code generated successfully!');
    }

    public function render()
    {
        return view('livewire.attendance.teacher-code-display');
    }
}
