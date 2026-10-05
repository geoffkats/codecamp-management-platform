<?php

namespace App\Livewire\ContentApprovals;

use App\Models\ContentApproval;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Review extends Component
{
    public ContentApproval $approval;
    public $approvable;
    public $notes = '';
    public $rejectionReason = '';
    public $action = 'approve'; // 'approve' or 'reject'
    public $pendingSubmissions = [];
    public $submissionCount = 0;

    public function mount(ContentApproval $approval)
    {
        $this->approval = $approval->load(['approvable', 'submitter', 'reviewer']);
        $this->approvable = $approval->approvable;
        
        if (!$this->approvable) {
            session()->flash('error', 'Content not found.');
            return redirect()->route('content-approvals.index');
        }

        // Check permissions
        if (!Auth::user()->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'You do not have permission to review content.');
        }

        $this->notes = $approval->notes ?? '';
        $this->rejectionReason = $approval->rejection_reason ?? '';
        
        // Fetch submissions that need grading
        $this->loadPendingSubmissions();
    }
    
    protected function loadPendingSubmissions()
    {
        $query = null;
        
        if ($this->approvable instanceof \App\Models\Assignment) {
            // If approvable is an Assignment, get its submissions
            $query = AssignmentSubmission::where('assignment_id', $this->approvable->id)
                ->whereNull('graded_at')
                ->where('status', '!=', 'draft');
        } elseif ($this->approvable instanceof \App\Models\Course) {
            // If approvable is a Course, get submissions from all its assignments
            $query = AssignmentSubmission::whereHas('assignment', function($q) {
                    $q->where('course_id', $this->approvable->id);
                })
                ->whereNull('graded_at')
                ->where('status', '!=', 'draft');
        } elseif ($this->approvable instanceof \App\Models\Lesson) {
            // If approvable is a Lesson, get submissions from assignments in that lesson
            $query = AssignmentSubmission::whereHas('assignment', function($q) {
                    $q->where('lesson_id', $this->approvable->id);
                })
                ->whereNull('graded_at')
                ->where('status', '!=', 'draft');
        }
        
        if ($query) {
            // Get total count
            $this->submissionCount = $query->count();
            
            // Get submissions (limit to 5 for display, but count shows total)
            $this->pendingSubmissions = $query
                ->with(['user', 'assignment.course'])
                ->orderBy('submitted_at', 'desc')
                ->limit(5)
                ->get();
        }
    }

    public function approve()
    {
        $this->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->approval->approve(Auth::user(), $this->notes ?: null);

        session()->flash('message', 'Content approved successfully!');
        return $this->redirect(route('content-approvals.index'), navigate: true);
    }

    public function reject()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:10|max:1000',
        ], [
            'rejectionReason.required' => 'Please provide a reason for rejection.',
            'rejectionReason.min' => 'Rejection reason must be at least 10 characters.',
        ]);

        $this->approval->reject(Auth::user(), $this->rejectionReason, $this->notes ?: null);

        session()->flash('message', 'Content rejected. The submitter has been notified.');
        return $this->redirect(route('content-approvals.index'), navigate: true);
    }

    public function render()
    {
        // Refresh the approval and approvable to get latest data
        $this->approval->refresh();
        if ($this->approvable) {
            $this->approvable->refresh();
        }
        
        $contentType = match($this->approval->approvable_type) {
            \App\Models\Course::class => 'Course',
            \App\Models\Lesson::class => 'Lesson',
            \App\Models\CourseModule::class => 'Module',
            \App\Models\Assessment::class => 'Assessment',
            default => 'Content',
        };

        return view('livewire.content-approvals.review', [
            'contentType' => $contentType,
        ]);
    }
}
