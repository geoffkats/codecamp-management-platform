<?php

namespace App\Livewire\Admin;

use App\Models\Notification;
use App\Models\SchoolTeacher;
use App\Models\StudentProfile;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class IcdlWorkflow extends Component
{
    use WithPagination;

    public $search = '';
    public array $examDates = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function verifyPayment(int $studentId): void
    {
        $student = $this->findIctStudent($studentId);

        if (($student->payment_status ?? 'pending') !== 'pending') {
            return;
        }

        $student->payment_status = 'verified';
        $student->payment_verified_at = now();
        $student->is_active = true;
        $student->save();

        $this->notifyIctTeachers($student, 'Payment Verified', "Payment verified for {$student->full_name} ({$student->student_id}).");
        $this->notifyStudent($student, 'Payment Verified', 'Your ICT registration payment has been verified. You are now active.');
    }

    public function rejectPayment(int $studentId): void
    {
        $student = $this->findIctStudent($studentId);

        $student->payment_status = 'rejected';
        $student->is_active = false;
        $student->save();

        $this->notifyIctTeachers($student, 'Payment Rejected', "Payment rejected for {$student->full_name} ({$student->student_id}).");
        $this->notifyStudent($student, 'Payment Rejected', 'Your ICT registration payment was rejected. Please contact your teacher.');
    }

    public function approveExamRequest(int $studentId): void
    {
        $student = $this->findIctStudent($studentId);

        if ($student->exam_readiness_status !== 'teacher_approved') {
            return;
        }

        if (($student->exam_request_status ?? 'not_requested') !== 'requested') {
            return;
        }

        $student->exam_request_status = 'approved';
        $student->exam_approved_at = now();
        $student->save();

        $this->notifyIctTeachers($student, 'Exam Request Approved', "Exam request approved for {$student->full_name} ({$student->student_id}).");
    }

    public function declineExamRequest(int $studentId): void
    {
        $student = $this->findIctStudent($studentId);

        $student->exam_request_status = 'declined';
        $student->save();

        $this->notifyIctTeachers($student, 'Exam Request Declined', "Exam request declined for {$student->full_name} ({$student->student_id}).");
    }

    public function reviewIcdlTest(int $studentId, string $status): void
    {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            return;
        }

        $student = $this->findIctStudent($studentId);

        $student->icdl_test_status = $status;
        $student->icdl_test_reviewed_at = now();
        if ($status === 'approved') {
            $student->exam_readiness_status = 'exam_completed';
        }
        $student->save();

        $this->notifyIctTeachers($student, 'ICDL Test Reviewed', "ICDL test {$status} for {$student->full_name} ({$student->student_id}).");
    }

    public function verifyExamPayment(int $studentId): void
    {
        $student = $this->findIctStudent($studentId);

        if (($student->exam_payment_status ?? 'not_submitted') !== 'submitted') {
            return;
        }

        $student->exam_payment_status = 'verified';
        $student->exam_payment_verified_at = now();
        $student->save();

        $this->notifyIctTeachers($student, 'Exam Payment Verified', "Exam payment verified for {$student->full_name} ({$student->student_id}).");
    }

    public function saveExamDate(int $studentId): void
    {
        $date = $this->examDates[$studentId] ?? null;
        if (!$date) {
            return;
        }

        $student = $this->findIctStudent($studentId);

        if (!in_array($student->exam_payment_status ?? 'not_submitted', ['submitted', 'verified'], true)) {
            return;
        }

        $student->exam_scheduled_for = $date;
        $student->save();

        $this->notifyIctTeachers($student, 'Exam Scheduled', "Exam scheduled for {$student->full_name} ({$student->student_id}).");
    }

    public function render()
    {
        $baseQuery = StudentProfile::query()
            ->where('program_type', 'ict')
            ->when($this->search, function ($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                    ->orWhere('student_id', 'like', '%' . $this->search . '%')
                    ->orWhere('icdl_number', 'like', '%' . $this->search . '%');
            })
            ->orderBy('full_name');

        $paymentPending = (clone $baseQuery)
            ->where('payment_status', 'pending')
            ->paginate(10, ['*'], 'paymentPending');

        $icdlPending = (clone $baseQuery)
            ->where('icdl_test_status', 'pending_review')
            ->paginate(10, ['*'], 'icdlPending');

        $examRequests = (clone $baseQuery)
            ->where('exam_request_status', 'requested')
            ->paginate(10, ['*'], 'examRequests');

        $examPayments = (clone $baseQuery)
            ->where('exam_payment_status', 'submitted')
            ->paginate(10, ['*'], 'examPayments');

        return view('livewire.admin.icdl-workflow', [
            'paymentPending' => $paymentPending,
            'icdlPending' => $icdlPending,
            'examRequests' => $examRequests,
            'examPayments' => $examPayments,
        ]);
    }

    private function findIctStudent(int $studentId): StudentProfile
    {
        return StudentProfile::where('program_type', 'ict')->findOrFail($studentId);
    }

    private function notifyIctTeachers(StudentProfile $student, string $title, string $message): void
    {
        if (!$student->school_id) {
            return;
        }

        $teachers = SchoolTeacher::where('school_id', $student->school_id)
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
                'data' => [
                    'student_profile_id' => $student->id,
                ],
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
}
