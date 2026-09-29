<?php

namespace App\Livewire\Admin;

use App\Models\CodeCamp;
use App\Models\CodeClub;
use App\Models\Course;
use App\Models\CourseCollaborator;
use App\Models\CourseEnrollment;
use App\Models\CourseInvitation;
use App\Models\EnrollmentRequest;
use App\Models\Notification;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
class EnrollmentManagement extends Component
{
    use WithPagination;

    /** Users holding any of these roles are staff, never counted as students. */
    public const STAFF_ROLES = ['admin', 'supervisor', 'teacher', 'ict_teacher', 'codecamp_trainer', 'operations_manager'];

    /** Roles that may lead or co-teach a course. */
    public const INSTRUCTOR_ROLES = ['teacher', 'ict_teacher', 'codecamp_trainer', 'admin', 'supervisor'];

    protected const PAGE_NAMES = ['enrollmentsPage', 'studentsPage', 'coursesPage', 'requestsPage', 'invitationsPage'];

    protected const FILTER_PROPERTIES = [
        'search', 'courseFilter', 'programFilter', 'campFilter', 'clubFilter',
        'enrollmentStatus', 'requestStatus', 'invitationStatus', 'instructorFilter',
    ];

    #[Url]
    public string $tab = 'enrollments';

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'course', except: '')]
    public $courseFilter = '';

    public string $programFilter = 'all';
    public $campFilter = '';
    public $clubFilter = '';
    public string $enrollmentStatus = 'active';
    public string $requestStatus = 'pending';
    public string $invitationStatus = 'active';
    public string $instructorFilter = 'all';

    public array $selectedEnrollments = [];

    public bool $showEnrollPanel = false;
    public string $enrollMode = 'direct';
    public array $enrollCourseIds = [];
    public array $enrollStudentIds = [];
    public string $enrollStudentSearch = '';
    public string $invitationMessage = '';
    public int $expiresInDays = 7;

    public ?int $managingStudentId = null;
    public $addCourseId = '';

    public ?int $managingCourseId = null;
    public $leadInstructorId = '';
    public bool $keepPreviousLead = true;
    public $coInstructorId = '';
    public string $coInstructorRole = 'editor';

    public ?int $rejectingRequestId = null;
    public string $rejectionReason = '';

    public function boot(): void
    {
        abort_unless(
            Auth::user()?->hasAnyRole(['admin', 'supervisor']),
            403,
            'Admin or Supervisor access required.'
        );
    }

    public function updated(string $property): void
    {
        if (in_array($property, self::FILTER_PROPERTIES, true)) {
            $this->resetAllPages();
            $this->selectedEnrollments = [];
        }

        if ($property === 'programFilter') {
            $this->campFilter = '';
            $this->clubFilter = '';
        }
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['enrollments', 'students', 'instructors', 'requests', 'invitations'], true)) {
            return;
        }

        $this->tab = $tab;
        $this->selectedEnrollments = [];
        $this->resetAllPages();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'courseFilter', 'programFilter', 'campFilter', 'clubFilter', 'instructorFilter']);
        $this->selectedEnrollments = [];
        $this->resetAllPages();
    }

    protected function resetAllPages(): void
    {
        foreach (self::PAGE_NAMES as $pageName) {
            $this->resetPage($pageName);
        }
    }

    /* -----------------------------------------------------------------
     |  Enroll students (direct or by invitation)
     | ----------------------------------------------------------------- */

    public function openEnrollPanel(?int $courseId = null, ?int $studentId = null): void
    {
        $this->resetValidation();
        $this->enrollCourseIds = $courseId ? [(string) $courseId] : ($this->courseFilter ? [(string) $this->courseFilter] : []);
        $this->enrollStudentIds = $studentId ? [(string) $studentId] : [];
        $this->enrollStudentSearch = '';
        $this->invitationMessage = '';
        $this->expiresInDays = 7;
        $this->enrollMode = 'direct';
        $this->showEnrollPanel = true;
    }

    public function closeEnrollPanel(): void
    {
        $this->showEnrollPanel = false;
        $this->reset(['enrollCourseIds', 'enrollStudentIds', 'enrollStudentSearch', 'invitationMessage']);
        $this->resetValidation();
    }

    public function toggleEnrollStudent(int $studentId): void
    {
        $id = (string) $studentId;

        $this->enrollStudentIds = in_array($id, $this->enrollStudentIds, true)
            ? array_values(array_diff($this->enrollStudentIds, [$id]))
            : [...$this->enrollStudentIds, $id];
    }

    public function enroll(): void
    {
        $this->validate([
            'enrollMode' => 'required|in:direct,invite',
            'enrollCourseIds' => 'required|array|min:1',
            'enrollCourseIds.*' => 'integer|exists:courses,id',
            'enrollStudentIds' => 'required|array|min:1',
            'enrollStudentIds.*' => 'integer|exists:users,id',
            'invitationMessage' => 'nullable|string|max:500',
            'expiresInDays' => 'required|integer|min:1|max:90',
        ], [
            'enrollCourseIds.required' => 'Choose at least one course.',
            'enrollStudentIds.required' => 'Choose at least one student.',
        ]);

        $studentIds = $this->studentsQuery()
            ->whereIn('id', $this->enrollStudentIds)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            $this->addError('enrollStudentIds', 'None of the selected accounts are student accounts.');

            return;
        }

        $courses = Course::whereIn('id', $this->enrollCourseIds)->get(['id', 'title']);
        $created = 0;
        $skipped = 0;

        if ($this->enrollMode === 'invite') {
            CourseInvitation::expireStale();
        }

        DB::transaction(function () use ($courses, $studentIds, &$created, &$skipped) {
            foreach ($courses as $course) {
                foreach ($studentIds as $studentId) {
                    $done = $this->enrollMode === 'direct'
                        ? $this->enrollStudent($studentId, $course, 'You have been enrolled in "' . $course->title . '" by Code Academy Uganda.')
                        : $this->inviteStudent($studentId, $course);

                    $done ? $created++ : $skipped++;
                }
            }
        });

        $verb = $this->enrollMode === 'direct' ? 'enrollment(s) created' : 'invitation(s) sent';
        $message = "{$created} {$verb}.";

        if ($skipped > 0) {
            $message .= " {$skipped} skipped (already enrolled or already invited).";
        }

        $this->closeEnrollPanel();
        $this->flash($created > 0 ? 'success' : 'warning', $message);
    }

    /**
     * Creates the enrollment when it does not exist yet. Returns false when the
     * student was already enrolled.
     */
    protected function enrollStudent(int $studentId, Course $course, string $notification): bool
    {
        $enrollment = CourseEnrollment::firstOrCreate(
            ['user_id' => $studentId, 'course_id' => $course->id],
            ['enrolled_at' => now(), 'progress_percentage' => 0]
        );

        if (! $enrollment->wasRecentlyCreated) {
            return false;
        }

        app(PointsService::class)->awardEnrollmentPoints($studentId, $course->id);

        CourseInvitation::where('course_id', $course->id)
            ->where('user_id', $studentId)
            ->activePending()
            ->update(['status' => 'accepted', 'responded_at' => now()]);

        EnrollmentRequest::where('course_id', $course->id)
            ->where('user_id', $studentId)
            ->where('status', 'pending')
            ->update(['status' => 'approved', 'reviewed_by' => Auth::id(), 'reviewed_at' => now()]);

        $this->notify($studentId, 'Enrolled in Course', $notification, 'success', ['course_id' => $course->id]);

        return true;
    }

    protected function inviteStudent(int $studentId, Course $course): bool
    {
        if (CourseEnrollment::where('course_id', $course->id)->where('user_id', $studentId)->exists()) {
            return false;
        }

        $invitation = CourseInvitation::where('course_id', $course->id)->where('user_id', $studentId)->first();

        if ($invitation?->isActionable()) {
            return false;
        }

        if ($invitation) {
            $invitation->renew($this->expiresInDays, Auth::id(), $this->invitationMessage ?: null);
        } else {
            CourseInvitation::create([
                'course_id' => $course->id,
                'user_id' => $studentId,
                'invited_by' => Auth::id(),
                'status' => 'pending',
                'invited_at' => now(),
                'expires_at' => now()->addDays($this->expiresInDays),
                'message' => $this->invitationMessage ?: null,
            ]);
        }

        $this->notifyInvitation($studentId, $course, $this->invitationMessage ?: null);

        return true;
    }

    /* -----------------------------------------------------------------
     |  Enrollment removal
     | ----------------------------------------------------------------- */

    public function removeEnrollment(int $enrollmentId): void
    {
        $enrollment = CourseEnrollment::with(['user:id,name', 'course:id,title'])->find($enrollmentId);

        if (! $enrollment) {
            $this->flash('warning', 'That enrollment no longer exists.');

            return;
        }

        $this->deleteStudentEnrollment($enrollment);
        $this->selectedEnrollments = array_values(array_diff($this->selectedEnrollments, [(string) $enrollmentId]));

        $this->flash('success', sprintf(
            'Removed %s from "%s".',
            $enrollment->user?->name ?? 'the student',
            $enrollment->course?->title ?? 'the course'
        ));
    }

    public function removeSelectedEnrollments(): void
    {
        if (empty($this->selectedEnrollments)) {
            return;
        }

        $enrollments = CourseEnrollment::with('course:id,title')
            ->whereIn('id', $this->selectedEnrollments)
            ->whereHas('user', fn (Builder $q) => $this->applyStudentRoles($q))
            ->get();

        DB::transaction(fn () => $enrollments->each(fn ($e) => $this->deleteStudentEnrollment($e)));

        $this->selectedEnrollments = [];
        $this->flash('success', "Removed {$enrollments->count()} enrollment(s).");
    }

    protected function deleteStudentEnrollment(CourseEnrollment $enrollment): void
    {
        $courseTitle = $enrollment->course?->title;
        $userId = $enrollment->user_id;
        $courseId = $enrollment->course_id;

        $enrollment->delete();

        if ($courseTitle) {
            $this->notify($userId, 'Removed from Course', 'You have been removed from "' . $courseTitle . '".', 'info', ['course_id' => $courseId]);
        }
    }

    /* -----------------------------------------------------------------
     |  Student course manager
     | ----------------------------------------------------------------- */

    public function manageStudent(int $studentId): void
    {
        $this->managingStudentId = $studentId;
        $this->addCourseId = '';
        $this->resetValidation();
    }

    public function closeStudentManager(): void
    {
        $this->managingStudentId = null;
        $this->addCourseId = '';
        $this->resetValidation();
    }

    public function addCourseToStudent(): void
    {
        $this->validate(['addCourseId' => 'required|integer|exists:courses,id'], ['addCourseId.required' => 'Choose a course to assign.']);

        $student = $this->studentsQuery()->find($this->managingStudentId);

        if (! $student) {
            $this->flash('error', 'That student account could not be found.');

            return;
        }

        $course = Course::findOrFail($this->addCourseId);
        $created = $this->enrollStudent($student->id, $course, 'You have been enrolled in "' . $course->title . '" by Code Academy Uganda.');

        $this->addCourseId = '';
        $created
            ? $this->flash('success', "{$student->name} is now enrolled in \"{$course->title}\".")
            : $this->flash('warning', "{$student->name} is already enrolled in \"{$course->title}\".");
    }

    public function removeCourseFromStudent(int $enrollmentId): void
    {
        $enrollment = CourseEnrollment::with('course:id,title')
            ->where('id', $enrollmentId)
            ->where('user_id', $this->managingStudentId)
            ->first();

        if (! $enrollment) {
            $this->flash('warning', 'That enrollment no longer exists.');

            return;
        }

        $this->deleteStudentEnrollment($enrollment);
        $this->flash('success', 'Course removed from the student.');
    }

    /* -----------------------------------------------------------------
     |  Instructor manager
     | ----------------------------------------------------------------- */

    public function manageCourse(int $courseId): void
    {
        $course = Course::find($courseId);

        if (! $course) {
            return;
        }

        $this->managingCourseId = $course->id;
        $this->leadInstructorId = (string) ($course->instructor_id ?? '');
        $this->keepPreviousLead = true;
        $this->coInstructorId = '';
        $this->coInstructorRole = 'editor';
        $this->resetValidation();
    }

    public function closeCourseManager(): void
    {
        $this->managingCourseId = null;
        $this->reset(['leadInstructorId', 'coInstructorId']);
        $this->resetValidation();
    }

    public function saveLeadInstructor(): void
    {
        $this->validate(['leadInstructorId' => 'nullable|integer|exists:users,id']);

        $course = Course::findOrFail($this->managingCourseId);
        $newLeadId = $this->leadInstructorId !== '' ? (int) $this->leadInstructorId : null;
        $previousLeadId = $course->instructor_id;

        if ($newLeadId === $previousLeadId) {
            $this->flash('info', 'The lead instructor is unchanged.');

            return;
        }

        if ($newLeadId && ! $this->instructorsQuery()->whereKey($newLeadId)->exists()) {
            $this->addError('leadInstructorId', 'Only teachers, trainers, supervisors or admins can lead a course.');

            return;
        }

        DB::transaction(function () use ($course, $newLeadId, $previousLeadId) {
            $course->update(['instructor_id' => $newLeadId]);

            if ($newLeadId) {
                CourseCollaborator::where('course_id', $course->id)->where('user_id', $newLeadId)->delete();
            }

            if ($previousLeadId && $this->keepPreviousLead) {
                CourseCollaborator::firstOrCreate(
                    ['course_id' => $course->id, 'user_id' => $previousLeadId],
                    ['role' => 'editor', 'invited_by' => Auth::id(), 'invited_at' => now()]
                );
            }
        });

        if ($newLeadId) {
            $this->notify($newLeadId, 'Course Assigned', 'You are now the lead instructor for "' . $course->title . '".', 'info', ['course_id' => $course->id]);
        }

        $this->flash('success', $newLeadId ? 'Lead instructor updated.' : 'Lead instructor removed from the course.');
    }

    public function addCoInstructor(): void
    {
        $this->validate([
            'coInstructorId' => 'required|integer|exists:users,id',
            'coInstructorRole' => 'required|in:editor,viewer',
        ], ['coInstructorId.required' => 'Choose an instructor to add.']);

        $course = Course::findOrFail($this->managingCourseId);
        $userId = (int) $this->coInstructorId;

        if ($userId === $course->instructor_id) {
            $this->addError('coInstructorId', 'This person is already the lead instructor.');

            return;
        }

        if (! $this->instructorsQuery()->whereKey($userId)->exists()) {
            $this->addError('coInstructorId', 'Only teachers, trainers, supervisors or admins can co-teach a course.');

            return;
        }

        CourseCollaborator::updateOrCreate(
            ['course_id' => $course->id, 'user_id' => $userId],
            ['role' => $this->coInstructorRole, 'invited_by' => Auth::id(), 'invited_at' => now()]
        );

        $this->notify($userId, 'Added to Course Team', 'You have been added as a co-instructor on "' . $course->title . '".', 'info', ['course_id' => $course->id]);

        $this->coInstructorId = '';
        $this->flash('success', 'Co-instructor added.');
    }

    public function updateCoInstructorRole(int $userId, string $role): void
    {
        if (! in_array($role, ['editor', 'viewer'], true)) {
            return;
        }

        CourseCollaborator::where('course_id', $this->managingCourseId)
            ->where('user_id', $userId)
            ->update(['role' => $role]);

        $this->flash('success', 'Access level updated.');
    }

    public function removeCoInstructor(int $userId): void
    {
        $courseId = $this->managingCourseId;

        DB::transaction(function () use ($courseId, $userId) {
            CourseCollaborator::where('course_id', $courseId)->where('user_id', $userId)->delete();

            $isStudent = $this->studentsQuery()->whereKey($userId)->exists();
            if (! $isStudent) {
                CourseEnrollment::where('course_id', $courseId)->where('user_id', $userId)->delete();
            }
        });

        $this->flash('success', 'Co-instructor removed from the course.');
    }

    /* -----------------------------------------------------------------
     |  Enrollment requests
     | ----------------------------------------------------------------- */

    public function approveRequest(int $requestId): void
    {
        $request = EnrollmentRequest::with(['course:id,title', 'user:id,name'])->find($requestId);

        if (! $request || $request->status !== 'pending') {
            $this->flash('warning', 'This request has already been handled.');

            return;
        }

        if (! $request->course || ! $request->user) {
            $this->flash('error', 'The course or student for this request no longer exists.');

            return;
        }

        DB::transaction(function () use ($request) {
            $request->update(['status' => 'approved', 'reviewed_by' => Auth::id(), 'reviewed_at' => now()]);

            $this->enrollStudent(
                $request->user_id,
                $request->course,
                'Your enrollment request for "' . $request->course->title . '" has been approved!'
            );
        });

        $this->flash('success', "Approved {$request->user->name} for \"{$request->course->title}\".");
    }

    public function openReject(int $requestId): void
    {
        $this->rejectingRequestId = $requestId;
        $this->rejectionReason = '';
        $this->resetValidation();
    }

    public function closeReject(): void
    {
        $this->rejectingRequestId = null;
        $this->rejectionReason = '';
        $this->resetValidation();
    }

    public function confirmReject(): void
    {
        $this->validate(
            ['rejectionReason' => 'required|string|min:10|max:500'],
            ['rejectionReason.min' => 'Please give the student a reason of at least 10 characters.']
        );

        $request = EnrollmentRequest::with('course:id,title')->find($this->rejectingRequestId);

        if (! $request || $request->status !== 'pending') {
            $this->closeReject();
            $this->flash('warning', 'This request has already been handled.');

            return;
        }

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $this->rejectionReason,
        ]);

        $this->notify(
            $request->user_id,
            'Enrollment Request Not Approved',
            'Your enrollment request for "' . ($request->course?->title ?? 'a course') . '" was not approved. Reason: ' . $this->rejectionReason,
            'warning',
            ['course_id' => $request->course_id, 'request_id' => $request->id, 'reason' => $this->rejectionReason]
        );

        $this->closeReject();
        $this->flash('success', 'Request rejected and the student has been notified.');
    }

    /* -----------------------------------------------------------------
     |  Invitations
     | ----------------------------------------------------------------- */

    public function resendInvitation(int $invitationId): void
    {
        CourseInvitation::expireStale();

        $invitation = CourseInvitation::with(['course:id,title', 'user:id,name'])->find($invitationId);

        if (! $invitation || ! $invitation->course || ! $invitation->user) {
            $this->flash('error', 'This invitation can no longer be resent.');

            return;
        }

        if (CourseEnrollment::where('course_id', $invitation->course_id)->where('user_id', $invitation->user_id)->exists()) {
            $this->flash('info', 'The student is already enrolled in this course.');

            return;
        }

        $invitation->renew($this->expiresInDays, Auth::id(), $invitation->message);
        $this->notifyInvitation($invitation->user_id, $invitation->course, $invitation->message);

        $this->flash('success', "Invitation resent to {$invitation->user->name}.");
    }

    public function cancelInvitation(int $invitationId): void
    {
        $updated = CourseInvitation::whereKey($invitationId)
            ->where('status', 'pending')
            ->update(['status' => 'expired', 'responded_at' => now()]);

        $this->flash($updated ? 'success' : 'warning', $updated ? 'Invitation cancelled.' : 'This invitation is no longer pending.');
    }

    /* -----------------------------------------------------------------
     |  Export
     | ----------------------------------------------------------------- */

    public function exportEnrollments(): StreamedResponse
    {
        $query = $this->enrollmentsQuery()->with(['user.studentProfile', 'course:id,title', 'camp:id,name', 'club:id,name']);

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Student', 'Email', 'Student ID', 'Program', 'Course', 'Camp', 'Club', 'Progress %', 'Enrolled', 'Completed']);

            $query->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $e) {
                    fputcsv($out, [
                        $e->user?->name,
                        $e->user?->email,
                        $e->user?->studentProfile?->student_id,
                        $e->user?->studentProfile?->program_type,
                        $e->course?->title,
                        $e->camp?->name,
                        $e->club?->name,
                        (int) $e->progress_percentage,
                        ($e->enrolled_at ?? $e->created_at)?->format('Y-m-d'),
                        $e->completed_at?->format('Y-m-d'),
                    ]);
                }
            });

            fclose($out);
        }, 'enrollments-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /* -----------------------------------------------------------------
     |  Queries
     | ----------------------------------------------------------------- */

    protected function applyStudentRoles(Builder $query): Builder
    {
        return $query
            ->whereHas('roles', fn (Builder $r) => $r->where('name', 'student'))
            ->whereDoesntHave('roles', fn (Builder $r) => $r->whereIn('name', self::STAFF_ROLES));
    }

    protected function applyProgramFilter(Builder $query): Builder
    {
        if ($this->programFilter !== 'all') {
            return $query->whereHas('studentProfile', fn (Builder $p) => $p->where('program_type', $this->programFilter));
        }

        if (! config('features.code_club', false)) {
            return $query->whereDoesntHave('studentProfile', fn (Builder $p) => $p->where('program_type', 'codeclub'));
        }

        return $query;
    }

    protected function studentsQuery(): Builder
    {
        return $this->applyProgramFilter($this->applyStudentRoles(User::query()));
    }

    protected function instructorsQuery(): Builder
    {
        return User::query()->whereHas('roles', fn (Builder $r) => $r->whereIn('name', self::INSTRUCTOR_ROLES));
    }

    protected function searchUsers(Builder $query, string $term): Builder
    {
        $like = '%' . trim($term) . '%';

        return $query->where(fn (Builder $w) => $w
            ->where('name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orWhereHas('studentProfile', fn (Builder $p) => $p->where('student_id', 'like', $like)));
    }

    protected function enrollmentsQuery(): Builder
    {
        return CourseEnrollment::query()
            ->whereHas('user', fn (Builder $u) => $this->applyProgramFilter($this->applyStudentRoles($u)))
            ->whereHas('course')
            ->when(trim($this->search) !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->whereHas('user', fn (Builder $u) => $this->searchUsers($u, $this->search))
                ->orWhereHas('course', fn (Builder $c) => $c->where('title', 'like', '%' . trim($this->search) . '%'))))
            ->when($this->courseFilter, fn (Builder $q) => $q->where('course_id', $this->courseFilter))
            ->when($this->campFilter, fn (Builder $q) => $q->where('camp_id', $this->campFilter))
            ->when($this->clubFilter, fn (Builder $q) => $q->where('club_id', $this->clubFilter))
            ->when($this->enrollmentStatus === 'active', fn (Builder $q) => $q->whereNull('completed_at'))
            ->when($this->enrollmentStatus === 'completed', fn (Builder $q) => $q->whereNotNull('completed_at'));
    }

    protected function stats(): array
    {
        $activeStudentEnrollments = CourseEnrollment::query()
            ->whereNull('completed_at')
            ->whereHas('course')
            ->whereHas('user', fn (Builder $u) => $this->applyStudentRoles($u));

        return [
            'active_enrollments' => (clone $activeStudentEnrollments)->count(),
            'enrolled_students' => (clone $activeStudentEnrollments)->distinct()->count('user_id'),
            'total_students' => $this->applyStudentRoles(User::query())->count(),
            'unassigned_courses' => Course::whereNull('instructor_id')->count(),
            'pending_requests' => EnrollmentRequest::where('status', 'pending')->count(),
            'active_invitations' => CourseInvitation::activePending()->count(),
        ];
    }

    protected function flash(string $type, string $message): void
    {
        session()->flash('enrollment_flash', ['type' => $type, 'message' => $message]);
    }

    protected function notify(int $userId, string $title, string $message, string $type, array $data = []): void
    {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    protected function notifyInvitation(int $studentId, Course $course, ?string $message): void
    {
        $this->notify($studentId, 'Course Invitation', 'You have been invited to join "' . $course->title . '"', 'info', [
            'course_id' => $course->id,
            'message' => $message,
        ]);
    }

    public function render()
    {
        CourseInvitation::expireStale();

        $data = [
            'stats' => $this->stats(),
            'allCourses' => Course::orderBy('title')->get(['id', 'title', 'is_published', 'instructor_id']),
            'camps' => CodeCamp::orderByDesc('start_date')->get(['id', 'name']),
            'clubs' => config('features.code_club', false) ? CodeClub::orderBy('name')->get(['id', 'name']) : collect(),
            'brandName' => SystemSetting::get('app_name') ?: 'Code Academy Uganda',
            'brandLogo' => SystemSetting::get('logo') ?: SystemSetting::get('logo_dark'),
            'enrollments' => null,
            'students' => null,
            'courses' => null,
            'requests' => null,
            'invitations' => null,
        ];

        match ($this->tab) {
            'students' => $data['students'] = $this->studentsQuery()
                ->when(trim($this->search) !== '', fn (Builder $q) => $this->searchUsers($q, $this->search))
                ->when($this->courseFilter, fn (Builder $q) => $q->whereHas('enrollments', fn (Builder $e) => $e->where('course_id', $this->courseFilter)))
                ->with(['studentProfile', 'enrollments' => fn ($q) => $q->whereHas('course')->with('course:id,title')->latest('id')])
                ->orderBy('name')
                ->paginate(20, ['*'], 'studentsPage'),

            'instructors' => $data['courses'] = Course::query()
                ->with(['instructor:id,name,email', 'collaboratorUsers'])
                ->withCount(['enrollments as students_count' => fn (Builder $q) => $q->whereHas('user', fn (Builder $u) => $this->applyStudentRoles($u))])
                ->when(trim($this->search) !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                    ->where('title', 'like', '%' . trim($this->search) . '%')
                    ->orWhereHas('instructor', fn (Builder $i) => $i->where('name', 'like', '%' . trim($this->search) . '%'))))
                ->when($this->instructorFilter === 'unassigned', fn (Builder $q) => $q->whereNull('instructor_id'))
                ->when($this->instructorFilter === 'published', fn (Builder $q) => $q->where('is_published', true))
                ->orderBy('title')
                ->paginate(15, ['*'], 'coursesPage'),

            'requests' => $data['requests'] = EnrollmentRequest::query()
                ->with(['user.studentProfile', 'course:id,title', 'reviewer:id,name'])
                ->when($this->requestStatus !== 'all', fn (Builder $q) => $q->where('status', $this->requestStatus))
                ->when($this->courseFilter, fn (Builder $q) => $q->where('course_id', $this->courseFilter))
                ->when(trim($this->search) !== '', fn (Builder $q) => $q->whereHas('user', fn (Builder $u) => $this->searchUsers($u, $this->search)))
                ->orderByRaw('COALESCE(requested_at, created_at) DESC')
                ->paginate(15, ['*'], 'requestsPage'),

            'invitations' => $data['invitations'] = CourseInvitation::query()
                ->with(['user:id,name,email', 'course:id,title', 'inviter:id,name'])
                ->when($this->invitationStatus === 'active', fn (Builder $q) => $q->activePending())
                ->when(in_array($this->invitationStatus, ['expired', 'accepted', 'declined'], true), fn (Builder $q) => $q->where('status', $this->invitationStatus))
                ->when($this->courseFilter, fn (Builder $q) => $q->where('course_id', $this->courseFilter))
                ->when(trim($this->search) !== '', fn (Builder $q) => $q->whereHas('user', fn (Builder $u) => $this->searchUsers($u, $this->search)))
                ->orderByRaw('COALESCE(invited_at, created_at) DESC')
                ->paginate(15, ['*'], 'invitationsPage'),

            default => $data['enrollments'] = $this->enrollmentsQuery()
                ->with(['user.studentProfile', 'course:id,title', 'camp:id,name', 'club:id,name'])
                ->orderByRaw('COALESCE(enrolled_at, created_at) DESC')
                ->orderByDesc('id')
                ->paginate(20, ['*'], 'enrollmentsPage'),
        };

        $data['enrollCandidates'] = collect();
        $data['selectedEnrollStudents'] = collect();
        if ($this->showEnrollPanel) {
            $data['enrollCandidates'] = $this->studentsQuery()
                ->when(trim($this->enrollStudentSearch) !== '', fn (Builder $q) => $this->searchUsers($q, $this->enrollStudentSearch))
                ->with('studentProfile:id,user_id,student_id,program_type')
                ->withCount(['enrollments as active_courses_count' => fn (Builder $q) => $q->whereNull('completed_at')->whereHas('course')])
                ->orderBy('name')
                ->limit(40)
                ->get(['id', 'name', 'email']);

            $data['selectedEnrollStudents'] = User::whereIn('id', $this->enrollStudentIds)->orderBy('name')->get(['id', 'name']);
        }

        $data['managedStudent'] = $this->managingStudentId
            ? User::with(['studentProfile', 'enrollments' => fn ($q) => $q->whereHas('course')->with(['course:id,title', 'camp:id,name', 'club:id,name'])->latest('id')])
                ->find($this->managingStudentId)
            : null;

        $data['managedCourse'] = $this->managingCourseId
            ? Course::with(['instructor:id,name,email', 'collaboratorUsers'])->find($this->managingCourseId)
            : null;

        $data['instructorOptions'] = $this->managingCourseId
            ? $this->instructorsQuery()->with('roles')->orderBy('name')->get(['id', 'name', 'email'])
            : collect();

        $data['rejectingRequest'] = $this->rejectingRequestId
            ? EnrollmentRequest::with(['user:id,name', 'course:id,title'])->find($this->rejectingRequestId)
            : null;

        return view('livewire.admin.enrollment-management', $data);
    }
}
