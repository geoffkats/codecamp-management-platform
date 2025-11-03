<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\CourseInvitation;
use App\Models\EnrollmentRequest;
use App\Models\User;
use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ManageEnrollments extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public Course $course;
    public $activeTab = 'requests'; // 'requests', 'invitations', 'students'
    
    // For invitations
    public $showInviteModal = false;
    public $searchStudents = '';
    public $selectedStudents = [];
    public $invitationMessage = '';
    public $expiresInDays = 7;

    // For approval/rejection
    public $rejectionReason = '';

    public function mount(Course $course)
    {
        // Check authorization - instructor, admin, or supervisor can manage
        if (!Auth::user()->hasAnyRole(['admin', 'supervisor']) && $course->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $this->course = $course;
    }

    public function approveRequest($requestId)
    {
        $request = EnrollmentRequest::findOrFail($requestId);
        
        if ($request->course_id !== $this->course->id) {
            abort(403);
        }

        // Approve the request
        $request->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Create enrollment
        CourseEnrollment::create([
            'user_id' => $request->user_id,
            'course_id' => $this->course->id,
            'enrolled_at' => now(),
            'progress_percentage' => 0,
        ]);

        // Ensure UserPoints exists
        $user = $request->user;
        if (!$user->points) {
            \App\Models\UserPoint::create([
                'user_id' => $user->id,
                'total_points' => 0,
                'level' => 1,
                'points_to_next_level' => 100,
            ]);
            $user->refresh();
        }

        // Award enrollment points
        $user->points->increment('total_points', 50);

        // Notify student
        \App\Models\Notification::create([
            'user_id' => $request->user_id,
            'title' => 'Enrollment Approved',
            'message' => 'Your enrollment request for "' . $this->course->title . '" has been approved!',
            'type' => 'success',
            'data' => [
                'course_id' => $this->course->id,
                'request_id' => $requestId,
            ],
            'is_read' => false,
        ]);

        session()->flash('message', 'Enrollment request approved!');
        $this->resetPage();
    }

    public function rejectRequest($requestId)
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:10',
        ], [
            'rejectionReason.required' => 'Please provide a reason for rejection.',
        ]);

        $request = EnrollmentRequest::findOrFail($requestId);
        
        if ($request->course_id !== $this->course->id) {
            abort(403);
        }

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        // Notify student
        \App\Models\Notification::create([
            'user_id' => $request->user_id,
            'title' => 'Enrollment Request Rejected',
            'message' => 'Your enrollment request for "' . $this->course->title . '" was not approved. Reason: ' . $this->rejectionReason,
            'type' => 'warning',
            'data' => [
                'course_id' => $this->course->id,
                'request_id' => $requestId,
                'reason' => $this->rejectionReason,
            ],
            'is_read' => false,
        ]);

        $this->rejectionReason = '';
        session()->flash('message', 'Enrollment request rejected.');
        $this->resetPage();
    }

    public function sendInvitations()
    {
        $this->validate([
            'selectedStudents' => 'required|array|min:1',
            'invitationMessage' => 'nullable|string|max:500',
            'expiresInDays' => 'required|integer|min:1|max:90',
        ]);

        $sentCount = 0;
        foreach ($this->selectedStudents as $studentId) {
            // Check if invitation already exists
            $existing = CourseInvitation::where('course_id', $this->course->id)
                ->where('user_id', $studentId)
                ->where('status', 'pending')
                ->first();

            if ($existing) {
                continue; // Skip if already invited
            }

            CourseInvitation::create([
                'course_id' => $this->course->id,
                'user_id' => $studentId,
                'invited_by' => Auth::id(),
                'status' => 'pending',
                'invited_at' => now(),
                'expires_at' => now()->addDays($this->expiresInDays),
                'message' => $this->invitationMessage,
            ]);

            // Notify student
            \App\Models\Notification::create([
                'user_id' => $studentId,
                'title' => 'Course Invitation',
                'message' => 'You have been invited to join "' . $this->course->title . '"',
                'type' => 'info',
                'data' => [
                    'course_id' => $this->course->id,
                    'message' => $this->invitationMessage,
                ],
                'is_read' => false,
            ]);

            $sentCount++;
        }

        $this->showInviteModal = false;
        $this->selectedStudents = [];
        $this->invitationMessage = '';
        session()->flash('message', "Sent {$sentCount} invitation(s)!");
    }

    public function cancelInvitation($invitationId)
    {
        $invitation = CourseInvitation::findOrFail($invitationId);
        
        if ($invitation->course_id !== $this->course->id) {
            abort(403);
        }

        $invitation->update(['status' => 'expired']);
        session()->flash('message', 'Invitation cancelled.');
    }

    public function render()
    {
        // Get enrollment requests
        $requests = EnrollmentRequest::where('course_id', $this->course->id)
            ->with('user')
            ->when($this->activeTab === 'requests', fn($q) => $q->where('status', 'pending'))
            ->orderByDesc('requested_at')
            ->paginate(15);

        // Get invitations
        $invitations = CourseInvitation::where('course_id', $this->course->id)
            ->with(['user', 'inviter'])
            ->orderByDesc('invited_at')
            ->paginate(15);

        // Get enrolled students
        $students = CourseEnrollment::where('course_id', $this->course->id)
            ->with('user')
            ->orderByDesc('enrolled_at')
            ->paginate(15);

        // Get available students for invitation (not already enrolled or invited)
        $enrolledIds = $students->pluck('user_id')->toArray();
        $invitedIds = $invitations->pluck('user_id')->toArray();
        $excludeIds = array_unique(array_merge($enrolledIds, $invitedIds));

        $availableStudents = User::whereHas('roles', fn($q) => $q->where('name', 'student'))
            ->whereNotIn('id', $excludeIds)
            ->when($this->searchStudents, fn($q) => 
                $q->where('name', 'like', '%' . $this->searchStudents . '%')
                  ->orWhere('email', 'like', '%' . $this->searchStudents . '%')
            )
            ->limit(20)
            ->get();

        return view('livewire.courses.manage-enrollments', [
            'requests' => $requests,
            'invitations' => $invitations,
            'students' => $students,
            'availableStudents' => $availableStudents,
        ]);
    }
}
