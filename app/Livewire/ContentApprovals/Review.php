<?php

namespace App\Livewire\ContentApprovals;

use App\Models\ContentApproval;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        $this->approval->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'notes' => $this->notes,
        ]);

        // Update the approvable item
        if ($this->approvable) {
            // Update approval_status for models that have it
            $updateData = [
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'approval_notes' => $this->notes,
            ];

            // Handle different content types
            if ($this->approvable instanceof \App\Models\Assignment) {
                // Assignments use 'status' field, not 'approval_status'
                $updateData['status'] = 'active'; // Change from 'draft' to 'active'
            } else {
                // Courses, Lessons, Assessments, Modules use 'approval_status'
                $updateData['approval_status'] = 'approved';
            }

            // Update the approvable item - ensure it saves
            $this->approvable->fill($updateData);
            $this->approvable->save();
            
            // Refresh to ensure the update is reflected
            $this->approvable->refresh();
            
            // Double-check the update took
            if ($this->approvable instanceof \App\Models\Assignment) {
                // For assignments, verify status changed
                if ($this->approvable->status !== 'active') {
                    Log::warning('Assignment status not updated after approval', [
                        'assignment_id' => $this->approvable->id,
                        'expected_status' => 'active',
                        'actual_status' => $this->approvable->status,
                    ]);
                }
            } else {
                // For other content, verify approval_status changed
                if ($this->approvable->approval_status !== 'approved') {
                    Log::warning('Content approval_status not updated after approval', [
                        'content_type' => get_class($this->approvable),
                        'content_id' => $this->approvable->id,
                        'expected_status' => 'approved',
                        'actual_status' => $this->approvable->approval_status,
                    ]);
                    // Force update again
                    $this->approvable->approval_status = 'approved';
                    $this->approvable->save();
                    $this->approvable->refresh();
                }
            }

            // Optionally auto-publish when approved (if content supports it)
            if ($this->approvable instanceof \App\Models\Course) {
                // Course approval_status is now 'approved'
                // Optionally, we could auto-publish here, but let instructor decide
            } elseif ($this->approvable instanceof \App\Models\Lesson) {
                // Optionally auto-publish lessons when approved
                if (!$this->approvable->is_published) {
                    // Set to published when approved
                    $this->approvable->update(['is_published' => true]);
                    $this->approvable->refresh();
                }
            }
        }

        // Create notification for submitter
        if ($this->approval->submitted_by) {
            \App\Models\Notification::create([
                'user_id' => $this->approval->submitted_by,
                'title' => 'Content Approved',
                'message' => 'Your ' . $this->approval->category . ' "' . ($this->approvable->title ?? 'Content') . '" has been approved.',
                'type' => 'success',
                'data' => [
                    'approval_id' => $this->approval->id,
                    'content_type' => $this->approval->category,
                    'content_id' => $this->approvable->id ?? null,
                ],
                'is_read' => false,
            ]);
        }

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

        $this->approval->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $this->rejectionReason,
            'notes' => $this->notes,
        ]);

        // Update the approvable item
        if ($this->approvable) {
            $updateData = [
                'rejection_reason' => $this->rejectionReason,
            ];

            // Handle different content types
            if ($this->approvable instanceof \App\Models\Assignment) {
                // Assignments use 'status' field - keep as draft when rejected
                $updateData['status'] = 'draft';
            } else {
                // Courses, Lessons, Assessments, Modules use 'approval_status'
                $updateData['approval_status'] = 'rejected';
            }

            $this->approvable->update($updateData);
        }

        // Create notification for submitter
        if ($this->approval->submitted_by) {
            \App\Models\Notification::create([
                'user_id' => $this->approval->submitted_by,
                'title' => 'Content Rejected',
                'message' => 'Your ' . $this->approval->category . ' "' . ($this->approvable->title ?? 'Content') . '" has been rejected. Reason: ' . $this->rejectionReason,
                'type' => 'warning',
                'data' => [
                    'approval_id' => $this->approval->id,
                    'content_type' => $this->approval->category,
                    'content_id' => $this->approvable->id ?? null,
                    'rejection_reason' => $this->rejectionReason,
                ],
                'is_read' => false,
            ]);
        }

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
