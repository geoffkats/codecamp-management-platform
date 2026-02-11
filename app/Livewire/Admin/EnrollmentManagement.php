<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseInvitation;
use App\Models\EnrollmentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class EnrollmentManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $activeTab = 'overview'; // 'overview', 'requests', 'invitations', 'enrollments'
    public $searchCourse = '';
    public $searchStudent = '';
    public $filterStatus = 'all';
    public $selectedCourseId = null;
    
    // For invitations
    public $showInviteModal = false;
    public $selectedStudents = [];
    public $invitationMessage = '';
    public $expiresInDays = 7;
    
    // For approval/rejection
    public $rejectionReason = '';

    public function mount()
    {
        // Check if user is admin or supervisor
        if (!Auth::user()->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'Unauthorized - Admin or Supervisor access required');
        }
    }

    public function approveRequest($requestId)
    {
        $request = EnrollmentRequest::findOrFail($requestId);
        
        $request->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Create enrollment
        CourseEnrollment::create([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
            'enrolled_at' => now(),
            'progress_percentage' => 0,
        ]);

        // Award enrollment points (safe from duplicates)
        $pointsService = app(\App\Services\PointsService::class);
        $pointsService->awardEnrollmentPoints($request->user_id, $request->course_id);

        // Notify student
        \App\Models\Notification::create([
            'user_id' => $request->user_id,
            'title' => 'Enrollment Approved',
            'message' => 'Your enrollment request for "' . $request->course->title . '" has been approved!',
            'type' => 'success',
            'data' => [
                'course_id' => $request->course_id,
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
        ]);

        $request = EnrollmentRequest::findOrFail($requestId);
        
        $request->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        // Notify student
        \App\Models\Notification::create([
            'user_id' => $request->user_id,
            'title' => 'Enrollment Request Not Approved',
            'message' => 'Your enrollment request for "' . $request->course->title . '" was not approved. Reason: ' . $this->rejectionReason,
            'type' => 'warning',
            'data' => [
                'course_id' => $request->course_id,
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
            'selectedCourseId' => 'required|exists:courses,id',
            'invitationMessage' => 'nullable|string|max:500',
            'expiresInDays' => 'required|integer|min:1|max:90',
        ]);

        $course = Course::findOrFail($this->selectedCourseId);
        $sentCount = 0;

        foreach ($this->selectedStudents as $studentId) {
            // Check if invitation already exists
            $existing = CourseInvitation::where('course_id', $course->id)
                ->where('user_id', $studentId)
                ->where('status', 'pending')
                ->first();

            if ($existing) {
                continue;
            }

            CourseInvitation::create([
                'course_id' => $course->id,
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
                'message' => 'You have been invited to join "' . $course->title . '"',
                'type' => 'info',
                'data' => [
                    'course_id' => $course->id,
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
        $invitation->update(['status' => 'expired']);
        session()->flash('message', 'Invitation cancelled.');
    }

    public function unenrollStudent($enrollmentId)
    {
        $enrollment = CourseEnrollment::findOrFail($enrollmentId);
        
        if (confirm('Are you sure you want to unenroll this student? This action cannot be undone.')) {
            $enrollment->delete();
            session()->flash('message', 'Student unenrolled successfully.');
        }
    }

    public function render()
    {
        // Overview stats
        $stats = [
            'total_enrollments' => CourseEnrollment::count(),
            'pending_requests' => EnrollmentRequest::where('status', 'pending')->count(),
            'pending_invitations' => CourseInvitation::where('status', 'pending')->count(),
            'active_students' => CourseEnrollment::whereNull('completed_at')->distinct('user_id')->count(),
        ];

        // All enrollment requests
        $requests = EnrollmentRequest::query()
            ->with(['user', 'course'])
            ->when($this->filterStatus !== 'all', fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->selectedCourseId, fn($q) => $q->where('course_id', $this->selectedCourseId))
            ->orderByDesc('requested_at')
            ->paginate(15);

        // All invitations
        $invitations = CourseInvitation::query()
            ->with(['user', 'course', 'inviter'])
            ->when($this->selectedCourseId, fn($q) => $q->where('course_id', $this->selectedCourseId))
            ->orderByDesc('invited_at')
            ->paginate(15);

        // All enrollments
        $enrollments = CourseEnrollment::query()
            ->with(['user', 'course'])
            ->when($this->searchStudent, fn($q) => 
                $q->whereHas('user', fn($sq) => 
                    $sq->where('name', 'like', '%' . $this->searchStudent . '%')
                      ->orWhere('email', 'like', '%' . $this->searchStudent . '%')
                )
            )
            ->when($this->selectedCourseId, fn($q) => $q->where('course_id', $this->selectedCourseId))
            ->orderByDesc('enrolled_at')
            ->paginate(15);

        // Get all courses for dropdown
        $courses = Course::where('is_published', true)
            ->orderBy('title')
            ->get();

        // Available students for invitations
        $availableStudents = [];
        if ($this->selectedCourseId) {
            $enrolledIds = CourseEnrollment::where('course_id', $this->selectedCourseId)->pluck('user_id')->toArray();
            $invitedIds = CourseInvitation::where('course_id', $this->selectedCourseId)
                ->where('status', 'pending')
                ->pluck('user_id')->toArray();
            $excludeIds = array_unique(array_merge($enrolledIds, $invitedIds));

            $availableStudents = User::whereHas('roles', fn($q) => $q->where('name', 'student'))
                ->whereNotIn('id', $excludeIds)
                ->when($this->searchStudent, fn($q) => 
                    $q->where('name', 'like', '%' . $this->searchStudent . '%')
                      ->orWhere('email', 'like', '%' . $this->searchStudent . '%')
                )
                ->with('points')
                ->limit(20)
                ->get();
        }

        return view('livewire.admin.enrollment-management', [
            'stats' => $stats,
            'requests' => $requests,
            'invitations' => $invitations,
            'enrollments' => $enrollments,
            'courses' => $courses,
            'availableStudents' => $availableStudents,
        ]);
    }
}
