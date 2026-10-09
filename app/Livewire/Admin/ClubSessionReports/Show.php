<?php

namespace App\Livewire\Admin\ClubSessionReports;

use App\Models\ClubSessionReport;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public ClubSessionReport $report;

    public function mount(ClubSessionReport $report): void
    {
        abort_unless(config('features.code_club', false), 404);
        abort_unless($report->canBeViewedBy(Auth::user()), 403);

        $this->report = $report;
    }

    public function markReviewed(NotificationService $notifications): void
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->isSupervisor(), 403);

        $this->report->update([
            'status' => 'reviewed',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        if ($this->report->facilitator && (int) $this->report->facilitator_id !== (int) $user->id) {
            $notifications->notify(
                $this->report->facilitator,
                'Report reviewed',
                $user->name.' reviewed '.$this->report->commentSubject().'.',
                'info',
                ['action_url' => $this->report->commentUrl()]
            );
        }

        session()->flash('message', 'Report marked as reviewed.');
    }

    public function render()
    {
        $this->report->load(['club.school', 'facilitator', 'reviewer']);
        $user = Auth::user();

        return view('livewire.admin.club-session-reports.show', [
            'isReviewer' => $user->isAdmin() || $user->isSupervisor(),
        ]);
    }
}
