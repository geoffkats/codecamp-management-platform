<?php

namespace App\Livewire\Dashboard;

use App\Models\ContentApproval;
use App\Models\DailyReport;
use App\Services\TrainerSubmissionQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SupervisorDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filterStatus = 'pending';

    public function filterByStatus($status)
    {
        $this->filterStatus = in_array($status, ['pending', 'approved', 'rejected', 'all'], true) ? $status : 'pending';
        $this->resetPage();
    }

    public function approveContent($approvalId)
    {
        abort_unless(Gate::allows('review_content'), 403);

        $approval = ContentApproval::where('status', 'pending')->findOrFail($approvalId);
        $approval->approve(Auth::user());

        session()->flash('message', 'Approved: '.($approval->approvable->title ?? 'content').'.');
        $this->dispatch('content-approved');
    }

    public function render()
    {
        $user = Auth::user();
        $weekStart = now()->startOfWeek();

        $approvals = ContentApproval::with(['approvable', 'submitter:id,name'])
            ->when($this->filterStatus !== 'all', fn ($q) => $q->where('status', $this->filterStatus))
            ->latest($this->filterStatus === 'pending' ? 'submitted_at' : 'updated_at')
            ->paginate(10);

        $statusCounts = ContentApproval::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $pendingByType = ContentApproval::where('status', 'pending')
            ->selectRaw('approvable_type, COUNT(*) as total')
            ->groupBy('approvable_type')
            ->pluck('total', 'approvable_type')
            ->mapWithKeys(fn ($total, $type) => [$this->typeLabel($type) => $total])
            ->sortDesc();

        $reviewedThisWeek = ContentApproval::where('reviewed_at', '>=', $weekStart)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $oldestPending = ContentApproval::where('status', 'pending')->min('submitted_at');

        return view('livewire.dashboard.supervisor-dashboard', [
            'user' => $user,
            'approvals' => $approvals,
            'statusCounts' => $statusCounts,
            'pendingCount' => (int) ($statusCounts['pending'] ?? 0),
            'pendingByType' => $pendingByType,
            'approvedThisWeek' => (int) ($reviewedThisWeek['approved'] ?? 0),
            'rejectedThisWeek' => (int) ($reviewedThisWeek['rejected'] ?? 0),
            'oldestPending' => $oldestPending ? \Illuminate\Support\Carbon::parse($oldestPending) : null,
            'waitingForMarks' => app(TrainerSubmissionQueue::class)->pendingCount($user),
            'reportsToday' => DailyReport::whereDate('report_date', today())->count(),
            'reportsThisWeek' => DailyReport::where('report_date', '>=', $weekStart->toDateString())->count(),
        ]);
    }

    public function typeLabel(?string $type): string
    {
        return match (class_basename((string) $type)) {
            'CourseModule' => 'Module',
            '' => 'Content',
            default => class_basename($type),
        };
    }
}
