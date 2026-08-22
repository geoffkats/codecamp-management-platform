<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseInvitation;
use App\Models\CodeCamp;
use App\Models\CodeClub;
use App\Models\EnrollmentRequest;
use App\Models\User;
use App\Support\ProgramScope;
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
    public $invitationFilter = 'active'; // active, expired, accepted, declined, all
    public $enrollmentFilter = 'active'; // active, completed, all
    public $selectedCourseId = null;
    public $selectedCampId = null;
    public $selectedClubId = null;
    public string $filterProgram = 'all';

    // For invitations / direct enroll
    public $showInviteModal = false;
    public $enrollMode = 'invite'; // invite | direct
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

    public function updatedSelectedCampId(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedClubId(): void
    {
        $this->resetPage();
    }

    public function updatedFilterProgram(): void
    {
        $this->selectedCampId = null;
        $this->selectedClubId = null;
        $this->resetPage();
    }

    public function updatedInvitationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedEnrollmentFilter(): void
    {
        $this->resetPage();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
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
        if ($this->enrollMode === 'direct') {
            return $this->enrollStudentsDirectly();
        }

        $this->validate([
            'selectedStudents' => 'required|array|min:1',
            'selectedCourseId' => 'required|exists:courses,id',
            'invitationMessage' => 'nullable|string|max:500',
            'expiresInDays' => 'required|integer|min:1|max:90',
        ]);

        CourseInvitation::expireStale();

        $course = Course::findOrFail($this->selectedCourseId);
        $sentCount = 0;

        foreach ($this->selectedStudents as $studentId) {
            if (CourseEnrollment::where('course_id', $course->id)->where('user_id', $studentId)->exists()) {
                continue;
            }

            $existingActive = CourseInvitation::where('course_id', $course->id)
                ->where('user_id', $studentId)
                ->activePending()
                ->first();

            if ($existingActive) {
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

            $this->notifyStudentOfInvitation($studentId, $course);

            $sentCount++;
        }

        $this->closeEnrollModal("Sent {$sentCount} invitation(s)!");
    }

    public function enrollStudentsDirectly(): void
    {
        $this->validate([
            'selectedStudents' => 'required|array|min:1',
            'selectedCourseId' => 'required|exists:courses,id',
        ]);

        $course = Course::findOrFail($this->selectedCourseId);
        $pointsService = app(\App\Services\PointsService::class);
        $enrolledCount = 0;

        foreach ($this->selectedStudents as $studentId) {
            if (CourseEnrollment::where('course_id', $course->id)->where('user_id', $studentId)->exists()) {
                continue;
            }

            CourseEnrollment::create([
                'user_id' => $studentId,
                'course_id' => $course->id,
                'enrolled_at' => now(),
                'progress_percentage' => 0,
            ]);

            $pointsService->awardEnrollmentPoints($studentId, $course->id);

            \App\Models\Notification::create([
                'user_id' => $studentId,
                'title' => 'Enrolled in Course',
                'message' => 'You have been enrolled in "' . $course->title . '" by an administrator.',
                'type' => 'success',
                'data' => ['course_id' => $course->id],
                'is_read' => false,
            ]);

            $enrolledCount++;
        }

        $this->closeEnrollModal("Enrolled {$enrolledCount} student(s) directly.");
    }

    public function resendInvitation(int $invitationId): void
    {
        CourseInvitation::expireStale();

        $invitation = CourseInvitation::findOrFail($invitationId);

        if (CourseEnrollment::where('course_id', $invitation->course_id)
            ->where('user_id', $invitation->user_id)
            ->exists()) {
            session()->flash('message', 'Student is already enrolled in this course.');

            return;
        }

        $invitation->renew($this->expiresInDays, Auth::id(), $invitation->message);
        $this->notifyStudentOfInvitation($invitation->user_id, $invitation->course);

        session()->flash('message', 'Invitation resent to ' . $invitation->user->name . '.');
    }

    protected function notifyStudentOfInvitation(int $studentId, Course $course): void
    {
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
    }

    protected function closeEnrollModal(string $message): void
    {
        $this->showInviteModal = false;
        $this->selectedStudents = [];
        $this->invitationMessage = '';
        $this->enrollMode = 'invite';
        session()->flash('message', $message);
    }

    public function cancelInvitation($invitationId)
    {
        $invitation = CourseInvitation::findOrFail($invitationId);
        $invitation->update([
            'status' => 'expired',
            'responded_at' => now(),
        ]);
        session()->flash('message', 'Invitation cancelled.');
    }

    public function unenrollStudent($enrollmentId)
    {
        $enrollment = CourseEnrollment::findOrFail($enrollmentId);
        $enrollment->delete();
        session()->flash('message', 'Student unenrolled successfully.');
    }

    private function applyEnrollmentProgramScope($query)
    {
        if ($this->filterProgram === 'codecamp') {
            return $query->whereHas('user.studentProfile', fn ($q) => $q->where('program_type', 'codecamp'));
        }

        if ($this->filterProgram === 'codeclub') {
            return $query->whereHas('user.studentProfile', fn ($q) => $q->where('program_type', 'codeclub'));
        }

        if ($this->filterProgram === 'ict') {
            return $query->whereHas('user.studentProfile', fn ($q) => $q->where('program_type', 'ict'));
        }

        if (! config('features.code_club', false)) {
            return $query->whereHas('user.studentProfile', fn ($q) => $q->where('program_type', '!=', 'codeclub'));
        }

        return $query;
    }

    public function render()
    {
        CourseInvitation::expireStale();

        // Overview stats
        $stats = [
            'total_enrollments' => CourseEnrollment::count(),
            'pending_requests' => EnrollmentRequest::where('status', 'pending')->count(),
            'pending_invitations' => CourseInvitation::activePending()->count(),
            'expired_invitations' => CourseInvitation::where('status', 'expired')->count(),
            'active_students' => CourseEnrollment::whereNull('completed_at')->distinct('user_id')->count(),
        ];

        // All enrollment requests
        $requests = EnrollmentRequest::query()
            ->with(['user', 'course'])
            ->when($this->filterStatus !== 'all', fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->selectedCourseId, fn($q) => $q->where('course_id', $this->selectedCourseId))
            ->orderByDesc('requested_at')
            ->paginate(15);

        // Invitations (filtered by lifecycle state)
        $invitations = CourseInvitation::query()
            ->with(['user', 'course', 'inviter'])
            ->when($this->selectedCourseId, fn ($q) => $q->where('course_id', $this->selectedCourseId))
            ->when($this->invitationFilter === 'active', fn ($q) => $q->activePending())
            ->when($this->invitationFilter === 'expired', fn ($q) => $q->where('status', 'expired'))
            ->when($this->invitationFilter === 'accepted', fn ($q) => $q->where('status', 'accepted'))
            ->when($this->invitationFilter === 'declined', fn ($q) => $q->where('status', 'declined'))
            ->orderByDesc('invited_at')
            ->paginate(15, ['*'], 'invitationsPage');

        // All enrollments
        $enrollments = CourseEnrollment::query()
            ->with(['user', 'course', 'camp', 'club'])
            ->when($this->searchStudent, fn ($q) =>
                $q->whereHas('user', fn ($sq) =>
                    $sq->where('name', 'like', '%' . $this->searchStudent . '%')
                        ->orWhere('email', 'like', '%' . $this->searchStudent . '%')
                )
            )
            ->when($this->selectedCourseId, fn ($q) => $q->where('course_id', $this->selectedCourseId))
            ->when($this->selectedCampId, fn ($q) => $q->where('camp_id', $this->selectedCampId))
            ->when($this->selectedClubId, fn ($q) => $q->where('club_id', $this->selectedClubId))
            ->tap(fn ($q) => $this->applyEnrollmentProgramScope($q))
            ->when($this->enrollmentFilter === 'active', fn ($q) => $q->whereNull('completed_at'))
            ->when($this->enrollmentFilter === 'completed', fn ($q) => $q->whereNotNull('completed_at'))
            ->orderByDesc('enrolled_at')
            ->paginate(15, ['*'], 'enrollmentsPage');

        // Get all courses for dropdown
        $courses = Course::where('is_published', true)
            ->orderBy('title')
            ->get();

        // Available students for invitations
        $availableStudents = [];
        if ($this->selectedCourseId) {
            $enrolledIds = CourseEnrollment::where('course_id', $this->selectedCourseId)->pluck('user_id')->toArray();
            $invitedIds = CourseInvitation::where('course_id', $this->selectedCourseId)
                ->activePending()
                ->pluck('user_id')->toArray();
            $excludeIds = array_unique(array_merge($enrolledIds, $invitedIds));

            $availableStudents = User::whereHas('roles', fn($q) => $q->where('name', 'student'))
                ->whereHas('studentProfile', function ($profileQuery) {
                    ProgramScope::applyStudentProfileScope($profileQuery, Auth::user());
                    if ($this->filterProgram !== 'all') {
                        $profileQuery->where('program_type', $this->filterProgram);
                    }
                })
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
            'camps' => CodeCamp::orderByDesc('start_date')->get(['id', 'name']),
            'clubs' => config('features.code_club', false)
                ? CodeClub::orderBy('name')->get(['id', 'name'])
                : collect(),
            'availableStudents' => $availableStudents,
        ]);
    }
}
