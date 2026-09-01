<?php

namespace App\Livewire\Attendance;

use App\Models\StudentProfile;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MorningCheckInPrompt extends Component
{
    public bool $checkedIn = false;
    public bool $canCheckIn = false;
    public bool $show = false;
    public string $checkedInAt = '';

    public function mount(AttendanceService $attendance): void
    {
        $this->refresh($attendance);
    }

    public function checkInNow(AttendanceService $attendance): void
    {
        $profile = StudentProfile::where('user_id', Auth::id())->first();
        if (! $profile) {
            session()->flash('error', 'Student profile not found.');

            return;
        }

        try {
            $record = $attendance->checkIn($profile);
            $this->checkedIn = true;
            $this->show = false;
            $this->checkedInAt = $record->formattedClockIn() ?? now()->format('g:i A');
            session()->flash('message', 'You are checked in. Welcome!');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            $this->refresh($attendance);
        }
    }

    public function render()
    {
        return view('livewire.attendance.morning-check-in-prompt');
    }

    private function refresh(AttendanceService $attendance): void
    {
        $profile = StudentProfile::where('user_id', Auth::id())->first();
        if (! $profile) {
            $this->show = false;

            return;
        }

        $record = $attendance->getRecord($profile, today());
        $this->checkedIn = (bool) $record?->clock_in;
        $this->checkedInAt = $record?->formattedClockIn() ?? '';
        $this->canCheckIn = $attendance->canCheckInNowForProfile($profile);
        $this->show = ! $this->checkedIn && $this->canCheckIn;
    }
}
