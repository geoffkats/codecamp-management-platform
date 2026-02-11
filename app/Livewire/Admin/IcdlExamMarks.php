<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\IcdlExamResult;
use App\Models\Notification;
use App\Models\StudentProfile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class IcdlExamMarks extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $statusFilter = 'pending_review';
    public $selectedMarkId = null;

    public $exam_session = '';
    public $score = '';
    public $exam_date = '';
    public $teacher_comment = '';

    public $unlock_reason = '';
    public $reject_reason = '';

    protected $paginationTheme = 'tailwind';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function mount(): void
    {
        if (!Auth::user()?->hasAnyRole(['admin', 'supervisor'])) {
            abort(403);
        }
    }

    public function selectMark(int $markId): void
    {
        $mark = IcdlExamResult::with(['student', 'module.course'])->findOrFail($markId);

        $this->selectedMarkId = $mark->id;
        $this->exam_session = $mark->exam_session;
        $this->score = (string) $mark->score;
        $this->exam_date = $mark->exam_date?->toDateString() ?? '';
        $this->teacher_comment = $mark->teacher_comment ?? '';
        $this->unlock_reason = '';
        $this->reject_reason = '';
    }

    public function clearSelection(): void
    {
        $this->selectedMarkId = null;
        $this->exam_session = '';
        $this->score = '';
        $this->exam_date = '';
        $this->teacher_comment = '';
        $this->unlock_reason = '';
        $this->reject_reason = '';
    }

    public function unlock(): void
    {
        $this->validate([
            'unlock_reason' => 'required|string|max:1000',
        ]);

        $mark = $this->getSelectedMark();

        if (!$mark->is_locked) {
            return;
        }

        $oldValues = $mark->toArray();
        $mark->update([
            'is_locked' => false,
            'unlock_reason' => $this->unlock_reason,
            'unlocked_by_admin_id' => Auth::id(),
            'unlocked_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        $this->logAudit($mark, $oldValues, 'unlock');
        session()->flash('message', 'Record unlocked for admin correction.');
    }

    public function saveCorrection(): void
    {
        $this->validate([
            'exam_session' => 'required|string|max:255',
            'score' => 'required|numeric|min:0|max:100',
            'exam_date' => 'required|date',
            'teacher_comment' => 'nullable|string|max:1000',
        ]);

        $mark = $this->getSelectedMark();

        if ($mark->is_locked) {
            session()->flash('message', 'Unlock the record before correcting it.');
            return;
        }

        $oldValues = $mark->toArray();
        $result = (float) $this->score >= 75 ? 'pass' : 'fail';

        $mark->update([
            'exam_session' => $this->exam_session,
            'score' => $this->score,
            'result' => $result,
            'exam_date' => $this->exam_date,
            'teacher_comment' => $this->teacher_comment,
            'updated_by' => Auth::id(),
        ]);

        $this->logAudit($mark, $oldValues, 'correct');
        session()->flash('message', 'Correction saved.');
    }

    public function approveAndLock(): void
    {
        $mark = $this->getSelectedMark();

        $oldValues = $mark->toArray();
        $mark->update([
            'status' => 'approved',
            'is_locked' => true,
            'reviewed_by_admin_id' => Auth::id(),
            'reviewed_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        $this->updateProgress($mark);
        $this->updateEligibility($mark->student);

        if ($mark->student) {
            $mark->student->icdl_test_score = $mark->score;
            $mark->student->icdl_test_status = 'approved';
            $mark->student->icdl_test_reviewed_at = now();
            $mark->student->save();
        }
        $this->logAudit($mark, $oldValues, 'approve');

        session()->flash('message', 'Approved and locked successfully.');
        $this->clearSelection();
        $this->resetPage();
    }

    public function reject(): void
    {
        $this->validate([
            'reject_reason' => 'required|string|max:1000',
        ]);

        $mark = $this->getSelectedMark();

        if ($mark->entered_by_teacher_id) {
            Notification::create([
                'user_id' => $mark->entered_by_teacher_id,
                'title' => 'ICDL exam marks rejected',
                'message' => $this->reject_reason,
                'type' => 'icdl_exam_rejected',
                'data' => [
                    'mark_id' => $mark->id,
                    'student_id' => $mark->student_profile_id,
                    'module_id' => $mark->course_module_id,
                ],
                'is_read' => false,
            ]);
        }

        $this->logAudit($mark, $mark->toArray(), 'reject');
        session()->flash('message', 'Rejected and sent back to teacher.');
    }

    public function render()
    {
        $marksQuery = IcdlExamResult::with(['student.school', 'module.course', 'creator'])
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderByDesc('created_at');

        $marks = $marksQuery->paginate(12);

        $selectedMark = $this->selectedMarkId
            ? IcdlExamResult::with(['student.school', 'module.course', 'creator'])->find($this->selectedMarkId)
            : null;

        return view('livewire.admin.icdl-exam-marks', [
            'marks' => $marks,
            'selectedMark' => $selectedMark,
        ]);
    }

    private function getSelectedMark(): IcdlExamResult
    {
        if (!$this->selectedMarkId) {
            abort(404);
        }

        return IcdlExamResult::with(['student', 'module'])->findOrFail($this->selectedMarkId);
    }

    private function updateProgress(IcdlExamResult $mark): void
    {
        $module = $mark->module;
        if (!$module) {
            return;
        }

        $enrollment = CourseEnrollment::where('user_id', $mark->student?->user_id)
            ->where('course_id', $module->course_id)
            ->first();

        if (!$enrollment) {
            return;
        }

        $totalModules = CourseModule::where('course_id', $module->course_id)->count();
        if ($totalModules === 0) {
            return;
        }

        $passedApproved = IcdlExamResult::where('student_profile_id', $mark->student_profile_id)
            ->where('status', 'approved')
            ->where('is_locked', true)
            ->where('result', 'pass')
            ->whereHas('module', function ($q) use ($module) {
                $q->where('course_id', $module->course_id);
            })
            ->distinct('course_module_id')
            ->count('course_module_id');

        $enrollment->progress_percentage = round(($passedApproved / $totalModules) * 100, 0);
        $enrollment->save();
    }

    private function updateEligibility(?StudentProfile $student): void
    {
        if (!$student || !$student->user_id) {
            return;
        }

        $enrolledCourseIds = CourseEnrollment::where('user_id', $student->user_id)
            ->pluck('course_id')
            ->all();

        if (empty($enrolledCourseIds)) {
            return;
        }

        $approvedCourseIds = IcdlExamResult::where('student_profile_id', $student->id)
            ->where('status', 'approved')
            ->where('is_locked', true)
            ->where('result', 'pass')
            ->whereHas('module', function ($q) use ($enrolledCourseIds) {
                $q->whereIn('course_id', $enrolledCourseIds);
            })
            ->get()
            ->pluck('module.course_id')
            ->unique()
            ->values()
            ->all();

        if (count($approvedCourseIds) === count($enrolledCourseIds)) {
            $student->exam_readiness_status = 'exam_completed';
            $student->save();
        }
    }

    private function logAudit(IcdlExamResult $mark, ?array $oldValues, string $action): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => 'IcdlExamResult',
            'model_id' => $mark->id,
            'model_name' => $mark->exam_session,
            'old_values' => $oldValues,
            'new_values' => $mark->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
