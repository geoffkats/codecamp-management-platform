<?php

namespace App\Livewire\ContentApproval;

use App\Models\ContentApproval;
use Livewire\Component;

class ApprovalReview extends Component
{
    public $approval;
    public $status = 'pending';
    public $notes = '';
    public $rejection_reason = '';

    public function mount(ContentApproval $approval)
    {
        $this->approval = $approval;
        $this->status = $approval->status;
        $this->notes = $approval->notes;
        $this->rejection_reason = $approval->rejection_reason;
    }

    public function approve()
    {
        $this->validate([
            'notes' => 'nullable|string',
        ]);

        $this->approval->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'notes' => $this->notes,
        ]);

        // Update the approvable model's approval status
        $approvable = $this->approval->approvable;
        if ($approvable) {
            $approvable->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        }

        session()->flash('message', 'Content approved successfully!');
        return redirect()->route('approvals.index');
    }

    public function reject()
    {
        $this->validate([
            'rejection_reason' => 'required|string',
        ]);

        $this->approval->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $this->rejection_reason,
        ]);

        // Update the approvable model's approval status
        $approvable = $this->approval->approvable;
        if ($approvable) {
            $approvable->update([
                'approval_status' => 'rejected',
                'rejection_reason' => $this->rejection_reason,
            ]);
        }

        session()->flash('message', 'Content rejected.');
        return redirect()->route('approvals.index');
    }

    public function render()
    {
        return view('livewire.content-approval.approval-review', [
            'approvable' => $this->approval->approvable,
        ]);
    }
}

