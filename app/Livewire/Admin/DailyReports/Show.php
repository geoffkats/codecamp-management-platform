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
        abort_unless($report->canComment(Auth::user()), 403);

        $this->report->load(['course', 'instructor', 'attendance.student', 'mentions.mentionable', 'reportIssues.assignee', 'attachments']);
    }

    public function render()
    {
        return view('livewire.admin.daily-reports.show', [
            'report' => $this->report,
            'isReviewer' => Auth::user()->can('review_daily_reports'),
        ]);
    }
}
