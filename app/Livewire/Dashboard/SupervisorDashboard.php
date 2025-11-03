<?php

namespace App\Livewire\Dashboard;

use App\Models\ContentApproval;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SupervisorDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filterStatus = 'pending';

    public function mount()
    {
        Cache::forget('supervisor_dashboard_' . Auth::id());
    }

    public function filterByStatus($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function approveContent($approvalId)
    {
        $approval = ContentApproval::findOrFail($approvalId);
        $approval->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Update the approvable item
        $approvable = $approval->approvable;
        if ($approvable) {
            $updateData = [
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ];

            // Handle different content types
            if ($approvable instanceof \App\Models\Assignment) {
                // Assignments use 'status' field
                $updateData['status'] = 'active';
            } else {
                // Courses, Lessons, Assessments, Modules use 'approval_status'
                $updateData['approval_status'] = 'approved';
            }

            // Use fill and save to ensure update persists
            $approvable->fill($updateData);
            $approvable->save();
            $approvable->refresh();
        }

        $this->dispatch('content-approved');
        Cache::forget('supervisor_dashboard_' . Auth::id());
    }

    public function rejectContent($approvalId, $reason = null)
    {
        $approval = ContentApproval::findOrFail($approvalId);
        $approval->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Update the approvable item
        $approvable = $approval->approvable;
        if ($approvable) {
            $updateData = [
                'rejection_reason' => $reason,
            ];

            // Handle different content types
            if ($approvable instanceof \App\Models\Assignment) {
                // Assignments use 'status' field - keep as draft when rejected
                $updateData['status'] = 'draft';
            } else {
                // Courses, Lessons, Assessments, Modules use 'approval_status'
                $updateData['approval_status'] = 'rejected';
            }

            $approvable->update($updateData);
        }

        $this->dispatch('content-rejected');
        Cache::forget('supervisor_dashboard_' . Auth::id());
    }

    public function render()
    {
        $user = Auth::user();

        $dashboardData = Cache::remember(
            'supervisor_dashboard_' . $user->id,
            now()->addMinutes(5),
            function () use ($user) {
                return [
                    'stats' => $this->getStats(),
                    'approvalBreakdown' => $this->getApprovalBreakdown(),
                ];
            }
        );

        // Get pending approvals
        $approvals = ContentApproval::with(['approvable', 'submitter'])
            ->when($this->filterStatus !== 'all', fn($q) => $q->where('status', $this->filterStatus))
            ->latest('submitted_at')
            ->paginate(15);

        return view('livewire.dashboard.supervisor-dashboard', [
            'user' => $user,
            'stats' => $dashboardData['stats'],
            'approvalBreakdown' => $dashboardData['approvalBreakdown'],
            'approvals' => $approvals,
        ]);
    }

    private function getStats(): array
    {
        return [
            'pendingApprovals' => ContentApproval::where('status', 'pending')->count(),
            'approvedToday' => ContentApproval::where('status', 'approved')
                ->whereDate('reviewed_at', today())
                ->count(),
            'rejectedToday' => ContentApproval::where('status', 'rejected')
                ->whereDate('reviewed_at', today())
                ->count(),
            'totalReviewed' => ContentApproval::whereNotNull('reviewed_at')->count(),
            'approvalRate' => $this->getApprovalRate(),
        ];
    }

    private function getApprovalBreakdown()
    {
        return [
            'courses' => ContentApproval::where('status', 'pending')
                ->where('approvable_type', Course::class)
                ->count(),
            'modules' => ContentApproval::where('status', 'pending')
                ->where('approvable_type', \App\Models\CourseModule::class)
                ->count(),
            'lessons' => ContentApproval::where('status', 'pending')
                ->where('approvable_type', \App\Models\Lesson::class)
                ->count(),
            'assessments' => ContentApproval::where('status', 'pending')
                ->where('approvable_type', \App\Models\Assessment::class)
                ->count(),
        ];
    }

    private function getApprovalRate(): float
    {
        $total = ContentApproval::whereNotNull('reviewed_at')->count();
        if ($total === 0) return 0;

        $approved = ContentApproval::where('status', 'approved')->count();
        return round(($approved / $total) * 100, 2);
    }

    public function getApprovableTitle($approval): string
    {
        $approvable = $approval->approvable;
        if (!$approvable) return 'Unknown';
        
        return match(class_basename($approval->approvable_type)) {
            'Course' => $approvable->title,
            'CourseModule' => $approvable->title,
            'Lesson' => $approvable->title,
            'Assessment' => $approvable->title,
            default => 'Unknown Content',
        };
    }
}
