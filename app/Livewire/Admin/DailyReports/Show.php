<?php

namespace App\Livewire\Admin\DailyReports;

use App\Models\DailyReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public DailyReport $report;

    public function mount(DailyReport $report): void
    {
        $user = Auth::user();
        if (!(method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('supervisor')))) {
            abort(403);
        }
        $this->report->load(['course', 'instructor', 'attendance.student', 'mentions', 'reportIssues.assignee', 'attachments']);
    }

    public function render()
    {
        return view('livewire.admin.daily-reports.show', [
            'report' => $this->report,
        ]);
    }
}
