<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceLog;
use App\Models\DailyAttendanceCode;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentCheckIn extends Component
{
    public $code = '';
    public $todayLog;
    public $studentProfile;
    public $canCheckOut = false;
    public $minutesRemaining = 0;

    public function mount()
    {
        $this->loadStudentProfile();
        $this->loadTodayLog();
    }

    public function loadStudentProfile()
    {
        // Cache student profile for 1 hour to reduce queries
        $this->studentProfile = cache()->remember(
            'student_profile_' . Auth::id(),
            3600,
            fn() => StudentProfile::where('user_id', Auth::id())->first()
        );
    }

    public function loadTodayLog()
    {
        if (!$this->studentProfile) {
            return;
        }

        $this->todayLog = AttendanceLog::where('student_profile_id', $this->studentProfile->id)
            ->where('attendance_date', today())
            ->first();

        $this->calculateCheckoutAvailability();
    }

    public function calculateCheckoutAvailability()
    {
        if (!$this->todayLog || !$this->todayLog->check_in_time || $this->todayLog->check_out_time) {
            $this->canCheckOut = false;
            $this->minutesRemaining = 0;
            return;
        }

        $checkInTime = \Carbon\Carbon::parse($this->todayLog->attendance_date->format('Y-m-d') . ' ' . $this->todayLog->check_in_time);
        $now = now();
        $minutesSinceCheckIn = $checkInTime->diffInMinutes($now);
        
        $this->canCheckOut = $minutesSinceCheckIn >= 60;
        $this->minutesRemaining = $this->canCheckOut ? 0 : ceil(60 - $minutesSinceCheckIn);
    }

    public function checkIn()
    {
        // Validate student profile
        if (!$this->studentProfile) {
            session()->flash('error', 'Student profile not found. Please contact your administrator.');
            \Log::error('Check-in failed: No student profile found for user ' . Auth::id());
            return;
        }

        // Get today's code
        $todayCode = DailyAttendanceCode::getTodayCode();

        if (!$todayCode) {
            session()->flash('error', 'No attendance code has been generated for today. Please contact your teacher.');
            \Log::error('Check-in failed: No daily code found for ' . today());
            return;
        }

        // Validate code
        $enteredCode = strtoupper(trim($this->code));
        if ($todayCode->code !== $enteredCode) {
            session()->flash('error', 'Invalid attendance code. Please check the code and try again.');
            \Log::warning('Check-in failed: Invalid code entered. Expected: ' . $todayCode->code . ', Got: ' . $enteredCode);
            return;
        }

        // Check if already checked in
        if ($this->todayLog && $this->todayLog->check_in_time) {
            session()->flash('error', 'You have already checked in today at ' . \Carbon\Carbon::parse($this->todayLog->check_in_time)->format('h:i A'));
            return;
        }

        try {
            // Create or update attendance log
            $log = AttendanceLog::updateOrCreate(
                [
                    'student_profile_id' => $this->studentProfile->id,
                    'attendance_date' => today(),
                ],
                [
                    'check_in_time' => now()->format('H:i:s'),
                    'code_used' => $todayCode->code,
                ]
            );

            \Log::info('Check-in successful for student profile ' . $this->studentProfile->id . ' at ' . now()->format('H:i:s'));

            $this->loadTodayLog();
            $this->code = '';
            session()->flash('message', 'Checked in successfully at ' . now()->format('h:i A'));
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while checking in. Please try again.');
            \Log::error('Check-in error: ' . $e->getMessage());
        }
    }

    public function checkOut()
    {
        if (!$this->studentProfile) {
            session()->flash('error', 'Student profile not found.');
            return;
        }

        $todayCode = DailyAttendanceCode::getTodayCode();

        if (!$todayCode || $todayCode->code !== strtoupper($this->code)) {
            session()->flash('error', 'Invalid attendance code.');
            return;
        }

        if (!$this->todayLog || !$this->todayLog->check_in_time) {
            session()->flash('error', 'You must check in first.');
            return;
        }

        if ($this->todayLog->check_out_time) {
            session()->flash('error', 'You have already checked out today.');
            return;
        }

        // Check if at least 1 hour has passed since check-in
        $checkInTime = \Carbon\Carbon::parse($this->todayLog->attendance_date->format('Y-m-d') . ' ' . $this->todayLog->check_in_time);
        $now = now();
        $hoursSinceCheckIn = $checkInTime->diffInMinutes($now) / 60;

        if ($hoursSinceCheckIn < 1) {
            $minutesRemaining = ceil(60 - ($checkInTime->diffInMinutes($now)));
            session()->flash('error', "You must wait at least 1 hour after check-in before checking out. Please wait {$minutesRemaining} more minute(s).");
            return;
        }

        $this->todayLog->update([
            'check_out_time' => now()->format('H:i:s'),
        ]);

        $this->loadTodayLog();
        $this->code = '';
        session()->flash('message', 'Checked out successfully at ' . now()->format('h:i A'));
    }

    public function render()
    {
        return view('livewire.attendance.student-check-in');
    }
}
