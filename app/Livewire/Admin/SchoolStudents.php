<?php

namespace App\Livewire\Admin;

use App\Models\InternalTestMark;
use App\Models\Notification;
use App\Models\School;
use App\Models\SchoolTeacher;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SchoolStudents extends Component
{
    use WithPagination;

    public School $school;

    public $search = '';
    public $showInactive = false;
    public array $examDates = [];

    public function mount(School $school): void
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'supervisor']) && !$user->isIctTeacher()) {
            abort(403, 'Unauthorized');
        }

        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();
            if (!$schoolId || (int) $schoolId !== (int) $school->id) {
                abort(403, 'Unauthorized');
            }
        }

        $this->school = $school;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $studentId): void
    {
        $student = StudentProfile::where('school_id', $this->school->id)
            ->where('program_type', 'ict')
            ->findOrFail($studentId);

        $student->is_active = !$student->is_active;
        $student->save();
    }

    public function verifyPayment(int $studentId): void
    {
        $student = StudentProfile::where('school_id', $this->school->id)
            ->where('program_type', 'ict')
            ->findOrFail($studentId);

        if (($student->payment_status ?? 'pending') !== 'pending') {
            return;
        }

        $student->payment_status = 'verified';
        $student->payment_verified_at = now();
        $student->is_active = true;
        $student->save();

        $this->notifyIctTeachers(
            'Payment Verified',
            "Payment verified for {$student->full_name} ({$student->student_id}).",
            ['student_profile_id' => $student->id]
        );

        $this->notifyStudent(
            $student,
            'Payment Verified',
            'Your ICT registration payment has been verified. You are now active.'
        );
    }

    public function rejectPayment(int $studentId): void
    {
        $student = StudentProfile::where('school_id', $this->school->id)
            ->where('program_type', 'ict')
            ->findOrFail($studentId);

        $student->payment_status = 'rejected';
        $student->is_active = false;
        $student->save();

        $this->notifyIctTeachers(
            'Payment Rejected',
            "Payment rejected for {$student->full_name} ({$student->student_id}).",
            ['student_profile_id' => $student->id]
        );

        $this->notifyStudent(
            $student,
            'Payment Rejected',
            'Your ICT registration payment was rejected. Please contact your teacher.'
        );
    }

    public function approveExamRequest(int $studentId): void
    {
        $student = StudentProfile::where('school_id', $this->school->id)
            ->where('program_type', 'ict')
            ->findOrFail($studentId);

        if ($student->exam_readiness_status !== 'teacher_approved') {
            return;
        }

        if (($student->exam_request_status ?? 'not_requested') !== 'requested') {
            return;
        }

        $student->exam_request_status = 'approved';
        $student->exam_approved_at = now();
        $student->save();

        $this->notifyIctTeachers(
            'Exam Request Approved',
            "Exam request approved for {$student->full_name} ({$student->student_id}).",
            ['student_profile_id' => $student->id]
        );
    }

    public function declineExamRequest(int $studentId): void
    {
        $student = StudentProfile::where('school_id', $this->school->id)
            ->where('program_type', 'ict')
            ->findOrFail($studentId);

        $student->exam_request_status = 'declined';
        $student->save();

        $this->notifyIctTeachers(
            'Exam Request Declined',
            "Exam request declined for {$student->full_name} ({$student->student_id}).",
            ['student_profile_id' => $student->id]
        );
    }

    public function reviewIcdlTest(int $studentId, string $status): void
    {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            return;
        }

        $student = StudentProfile::where('school_id', $this->school->id)
            ->where('program_type', 'ict')
            ->findOrFail($studentId);

        $student->icdl_test_status = $status;
        $student->icdl_test_reviewed_at = now();
        if ($status === 'approved') {
            $student->exam_readiness_status = 'exam_completed';
        }
        $student->save();

        $this->notifyIctTeachers(
            'ICDL Test Reviewed',
            "ICDL test {$status} for {$student->full_name} ({$student->student_id}).",
            ['student_profile_id' => $student->id]
        );
    }

    public function verifyExamPayment(int $studentId): void
    {
        $student = StudentProfile::where('school_id', $this->school->id)
            ->where('program_type', 'ict')
            ->findOrFail($studentId);

        if (($student->exam_payment_status ?? 'not_submitted') !== 'submitted') {
            return;
        }

        $student->exam_payment_status = 'verified';
        $student->exam_payment_verified_at = now();
        $student->save();

        $this->notifyIctTeachers(
            'Exam Payment Verified',
            "Exam payment verified for {$student->full_name} ({$student->student_id}).",
            ['student_profile_id' => $student->id]
        );
    }

    public function saveExamDate(int $studentId): void
    {
        $date = $this->examDates[$studentId] ?? null;
        if (!$date) {
            return;
        }

        $student = StudentProfile::where('school_id', $this->school->id)
            ->where('program_type', 'ict')
            ->findOrFail($studentId);

        if (!in_array($student->exam_payment_status ?? 'not_submitted', ['submitted', 'verified'], true)) {
            return;
        }

        $student->exam_scheduled_for = $date;
        $student->save();

        $this->notifyIctTeachers(
            'Exam Scheduled',
            "Exam scheduled for {$student->full_name} ({$student->student_id}).",
            [
                'student_profile_id' => $student->id,
                'exam_scheduled_for' => $student->exam_scheduled_for,
            ]
        );
    }

    private function notifyIctTeachers(string $title, string $message, array $data = []): void
    {
        $teachers = SchoolTeacher::where('school_id', $this->school->id)
            ->where('role', 'ict_teacher')
            ->where('status', 'active')
            ->with('teacher:id')
            ->get()
            ->pluck('teacher')
            ->filter();

        foreach ($teachers as $teacher) {
            Notification::create([
                'user_id' => $teacher->id,
                'title' => $title,
                'message' => $message,
                'type' => 'info',
                'data' => $data,
                'is_read' => false,
            ]);
        }
    }

    private function notifyStudent(StudentProfile $student, string $title, string $message): void
    {
        if (!$student->user_id) {
            return;
        }

        Notification::create([
            'user_id' => $student->user_id,
            'title' => $title,
            'message' => $message,
            'type' => 'info',
            'data' => [
                'student_profile_id' => $student->id,
            ],
            'is_read' => false,
        ]);
    }

    public function render()
    {
        $students = StudentProfile::query()
            ->with(['user.enrollments.course'])
            ->where('school_id', $this->school->id)
            ->where('program_type', 'ict')
            ->when(!$this->showInactive, fn (Builder $q) => $q->where('is_active', true))
            ->when($this->search, function (Builder $q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                    ->orWhere('student_id', 'like', '%' . $this->search . '%')
                    ->orWhere('icdl_number', 'like', '%' . $this->search . '%');
            })
            ->orderBy('full_name')
            ->paginate(15);

        $studentIds = $students->pluck('id')->filter()->all();
        $userIds = $students->pluck('user_id')->filter()->all();

        $passedModulesByStudent = InternalTestMark::query()
            ->whereIn('student_profile_id', $studentIds)
            ->where('passed', true)
            ->selectRaw('student_profile_id, COUNT(DISTINCT course_module_id) as passed_modules')
            ->groupBy('student_profile_id')
            ->pluck('passed_modules', 'student_profile_id');

        $totalModulesByUser = DB::table('course_enrollments as ce')
            ->join('course_modules as cm', 'cm.course_id', '=', 'ce.course_id')
            ->whereIn('ce.user_id', $userIds)
            ->selectRaw('ce.user_id, COUNT(DISTINCT cm.id) as total_modules')
            ->groupBy('ce.user_id')
            ->pluck('total_modules', 'ce.user_id');

        return view('livewire.admin.school-students', [
            'students' => $students,
            'passedModulesByStudent' => $passedModulesByStudent,
            'totalModulesByUser' => $totalModulesByUser,
        ]);
    }
}
