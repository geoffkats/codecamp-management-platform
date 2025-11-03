<?php

namespace App\Livewire\ContentApproval;

use App\Models\ContentApproval;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalList extends Component
{
    use WithPagination;

    public $filterStatus = 'pending';
    public $filterPriority = '';
    public $filterType = '';

    public function render()
    {
        $query = ContentApproval::with(['submitter', 'reviewer'])
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->when($this->filterPriority, function ($q) {
                $q->where('priority', $this->filterPriority);
            })
            ->when($this->filterType, function ($q) {
                $q->where('approvable_type', $this->filterType);
            })
            ->orderByDesc('submitted_at');

        return view('livewire.content-approval.approval-list', [
            'approvals' => $query->paginate(15)
        ]);
    }
}

