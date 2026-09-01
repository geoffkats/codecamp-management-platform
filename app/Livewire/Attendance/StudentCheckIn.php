<?php

namespace App\Livewire\Attendance;

use App\Models\StudentProfile;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudentCheckIn extends Component
{
    public $code = '';
    public $todayRecord;
    public $studentProfile;
    public $canCheckOut = false;
    public $minutesRemaining = 0;
    public $canCheckInNow = true;
    public $checkInWindow = [];
    public $isCodeClubStudent = false;
    public $hasSessionToday = true;
    public $checkInStatus = 'closed';

    protected AttendanceService $attendance;

    public function boot(AttendanceService $attendance): void
    {
        $this->attendance = $attendance;
    }

    public function mount(): void
    {
        $this->loadStudentProfile();
        if ($this->studentProfile) {
            $this->attendance->autoCheckOutForgotten(onlyProfile: $this->studentProfile);
        }
        $this->refreshCheckInState();
        $this->loadTodayRecord();
    }

    public function loadStudentProfile(): void
    {
        $this->studentProfile = StudentProfile::where('user_id', Auth::id())->first();
    }

    public function loadTodayRecord(): void
    {
        if (! $this->studentProfile) {
            return;
        }

        $this->todayRecord = $this->attendance->getRecord($this->studentProfile, today());
        $this->refreshCheckInState();
        $this->calculateCheckoutAvailability();
    }

    public function refreshCheckInState(): void
    {
        $this->isCodeClubStudent = $this->studentProfile
            && $this->attendance->isCodeClubProfile($this->studentProfile);

        $this->checkInWindow = $this->studentProfile
            ? $this->attendance->checkInWindowForProfile($this->studentProfile)
            : $this->attendance->checkInWindow();

        $this->hasSessionToday = $this->isCodeClubStudent
            ? (bool) ($this->checkInWindow['session_day'] ?? false)
            : true;

        $this->canCheckInNow = $this->studentProfile
            ? $this->attendance->canCheckInNowForProfile($this->studentProfile)
            : $this->attendance->canCheckInNow();

        $this->checkInStatus = $this->studentProfile
            ? $this->attendance->checkInStatusForProfile($this->studentProfile)
            : ($this->canCheckInNow ? 'open' : 'closed');
    }

    public function calculateCheckoutAvailability(): void
    {
        if (! $this->todayRecord?->clock_in || $this->todayRecord->clock_out) {
            $this->canCheckOut = false;
            $this->minutesRemaining = 0;

            return;
        }

        $checkInTime = Carbon::parse(
            $this->todayRecord->attendance_date->format('Y-m-d') . ' ' . $this->formatClock($this->todayRecord->clock_in)
        );
        $minMinutes = $this->studentProfile
            ? $this->attendance->minCheckoutMinutesForProfile($this->studentProfile)
            : (int) config('attendance.min_checkout_minutes', 60);
        $minutesSinceCheckIn = $checkInTime->diffInMinutes(now());

        $this->canCheckOut = $minutesSinceCheckIn >= $minMinutes;
        $this->minutesRemaining = $this->canCheckOut ? 0 : (int) ceil($minMinutes - $minutesSinceCheckIn);
    }

    public function checkIn(): void
    {
        if (! $this->studentProfile) {
            session()->flash('error', 'Student profile not found. Please contact your administrator.');

            return;
        }

        try {
            $this->attendance->checkIn($this->studentProfile, $this->code);
            $this->loadTodayRecord();
            $this->code = '';
            session()->flash('message', 'Checked in successfully at ' . now()->format('h:i A'));
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while checking in. Please try again.');
            \Log::error('Check-in error: ' . $e->getMessage());
        }
    }

    public function checkOut(): void
    {
        if (! $this->studentProfile) {
            session()->flash('error', 'Student profile not found.');

            return;
        }

        try {
            $this->attendance->checkOut($this->studentProfile, $this->code);
            $this->loadTodayRecord();
            $this->code = '';
            session()->flash('message', 'Checked out successfully at ' . now()->format('h:i A'));
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.attendance.student-check-in');
    }

    private function formatClock(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('H:i:s');
        }

        return (string) $value;
    }
}
