<?php

namespace App\Livewire\ContentApprovals;

use App\Models\ContentApproval;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filterStatus = 'pending'; // 'all', 'pending', 'approved', 'rejected'
    public $filterType = 'all'; // 'all', 'course', 'lesson', 'module', 'assessment'
    public $filterPriority = 'all'; // 'all', 'high', 'medium', 'low'
    public $search = '';

    protected $queryString = [
        'filterStatus' => ['except' => 'pending'],
        'filterType' => ['except' => 'all'],
        'filterPriority' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

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

        session()->flash('message', 'Content approved successfully.');
        $this->dispatch('content-approved');
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

        session()->flash('message', 'Content rejected.');
        $this->dispatch('content-rejected');
    }

    public function getApprovableTitle($approval)
    {
        if (!$approval->approvable) {
            return 'Deleted Item';
        }

        return match (class_basename($approval->approvable_type)) {
            'Course' => $approval->approvable->title,
            'Lesson' => $approval->approvable->title,
            'CourseModule' => $approval->approvable->title,
            'Assessment' => $approval->approvable->title,
            default => 'Unknown Content',
        };
    }

    public function mount()
    {
        // Authorization check - only supervisors and admins can access
        $user = Auth::user();
        if (!$user->isSupervisor() && !$user->isAdmin() && !$user->hasPermission('review_content')) {
            abort(403, 'You do not have permission to review content.');
        }
    }

    public function render()
    {
        $query = ContentApproval::with(['submitter', 'approvable']);

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterType !== 'all') {
            $typeMap = [
                'course' => \App\Models\Course::class,
                'lesson' => \App\Models\Lesson::class,
                'module' => \App\Models\CourseModule::class,
                'assessment' => \App\Models\Assessment::class,
            ];
            if (isset($typeMap[$this->filterType])) {
                $query->where('approvable_type', $typeMap[$this->filterType]);
            }
        }

        if ($this->filterPriority !== 'all') {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->search) {
            $query->whereHasMorph('approvable', [
                \App\Models\Course::class,
                \App\Models\Lesson::class,
                \App\Models\CourseModule::class,
                \App\Models\Assessment::class,
            ], function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            });
        }

        $approvals = $query->orderByDesc('submitted_at')->paginate(20);

        $stats = [
            'pending' => ContentApproval::where('status', 'pending')->count(),
            'approved' => ContentApproval::where('status', 'approved')->count(),
            'rejected' => ContentApproval::where('status', 'rejected')->count(),
            'total' => ContentApproval::count(),
        ];

        return view('livewire.content-approvals.index', [
            'approvals' => $approvals,
            'stats' => $stats,
        ]);
    }
}
