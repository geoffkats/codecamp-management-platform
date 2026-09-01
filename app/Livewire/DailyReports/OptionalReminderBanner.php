<?php

namespace App\Livewire\DailyReports;

use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OptionalReminderBanner extends Component
{
    public bool $show = false;
    public bool $dismissed = false;

    public function mount(): void
    {
        $this->refresh();
    }

    public function dismiss(): void
    {
        $this->dismissed = true;
        $this->show = false;
        session()->put('daily_report_reminder_dismissed_'.today()->toDateString(), true);
    }

    public function render()
    {
        return view('livewire.daily-reports.optional-reminder-banner');
    }

    private function refresh(): void
    {
        if (session('daily_report_reminder_dismissed_'.today()->toDateString())) {
            $this->dismissed = true;
            $this->show = false;

            return;
        }

        $user = Auth::user();
        if (! $user) {
            return;
        }

        $isTrainer = method_exists($user, 'isCodecampTrainer') && $user->isCodecampTrainer()
            && ! (method_exists($user, 'isIctTeacher') && $user->isIctTeacher());

        if (! $isTrainer) {
            return;
        }

        $cutoff = Carbon::parse(now()->toDateString().' '.(config('reports.reminder_time') ?? '16:00'));
        if (now()->lt($cutoff)) {
            return;
        }

        $hasReport = DailyReport::whereDate('report_date', today())
            ->where('instructor_id', $user->id)
            ->exists();

        $this->show = ! $hasReport;
    }
}
