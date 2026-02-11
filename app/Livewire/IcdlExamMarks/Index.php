<?php

namespace App\Livewire\IcdlExamMarks;

use App\Models\ActivityLog;
use App\Models\CourseModule;
use App\Models\IcdlExamResult;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $student_id;
    public $module_id;
    public $exam_session = '';
    public $score = '';
    public $exam_date;
    public $teacher_comment = '';
    
    public $editingId = null;
    public $editingScore = '';
    public $editingComment = '';

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        if (!Auth::user()?->isIctTeacher()) {
            abort(403);
        }

        $this->exam_date = now()->toDateString();
        $this->student_id = request()->query('student');
        $this->module_id = request()->query('module');
    }

    protected function rules(): array
    {
        return [
            'student_id' => 'required|exists:student_profiles,id',
            'module_id' => 'required|exists:course_modules,id',
            'exam_session' => 'required|string|max:255',
            'score' => 'required|numeric|min:0|max:100',
            'exam_date' => 'required|date',
            'teacher_comment' => 'nullable|string|max:1000',
        ];
    }

    public function save(): void
    {
        if (!Auth::user()?->isIctTeacher()) {
            abort(403);
        }

        $this->validate();

        $student = $this->findStudent($this->student_id);
        $module = $this->findModule($this->module_id);

        $duplicate = IcdlExamResult::where('student_profile_id', $student->id)
            ->where('course_module_id', $module->id)
            ->where('exam_session', $this->exam_session)
            ->whereDate('exam_date', $this->exam_date)
            ->exists();

        if ($duplicate) {
            session()->flash('message', 'Duplicate exam entry detected. Contact admin to override.');
            return;
        }

        $result = (float) $this->score >= 75 ? 'pass' : 'fail';

        $mark = IcdlExamResult::create([
            'student_profile_id' => $student->id,
            'course_module_id' => $module->id,
            'exam_session' => $this->exam_session,
            'score' => $this->score,
            'result' => $result,
            'status' => 'pending_review',
            'is_locked' => true,
            'exam_date' => $this->exam_date,
            'teacher_comment' => $this->teacher_comment,
            'entered_by_teacher_id' => Auth::id(),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        $this->logAudit($mark);

        session()->flash('message', 'ICDL exam marks submitted for admin review.');
        $this->resetForm();
    }

    public function saveAndContinue(): void
    {
        $this->save();
        $this->exam_session = '';
        $this->score = '';
        $this->teacher_comment = '';
    }

    public function resetForm(): void
    {
        $this->exam_session = '';
        $this->score = '';
        $this->teacher_comment = '';
        $this->exam_date = now()->toDateString();
    }

    public function render()
    {
        $user = Auth::user();
        $schoolId = $user->ictSchoolId();

        $students = StudentProfile::query()
            ->where('program_type', 'ict')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('full_name')
            ->get();

        $modules = CourseModule::query()
            ->whereHas('course.schools', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->where('is_active', true);
            })
            ->with('course:id,title')
            ->orderBy('title')
            ->get();

        $history = IcdlExamResult::with(['student', 'module.course'])
            ->when($this->student_id, fn ($q) => $q->where('student_profile_id', $this->student_id))
            ->when($this->module_id, fn ($q) => $q->where('course_module_id', $this->module_id))
            ->orderByDesc('exam_date')
            ->paginate(10);
        
        $unlockedRecords = IcdlExamResult::with(['student', 'module.course'])
            ->where('is_locked', false)
            ->where('entered_by_teacher_id', Auth::id())
            ->when($schoolId, fn ($q) => $q->whereHas('student', fn ($q2) => $q2->where('school_id', $schoolId)))
            ->orderByDesc('exam_date')
            ->get();

        $lockedStudent = $this->student_id
            ? StudentProfile::find($this->student_id)
            : null;

        return view('livewire.icdl-exam-marks.index', [
            'students' => $students,
            'modules' => $modules,
            'history' => $history,
            'unlockedRecords' => $unlockedRecords,
            'lockedStudent' => $lockedStudent,
        ]);
    }

    private function findStudent(int $studentId): StudentProfile
    {
        $user = Auth::user();
        $schoolId = $user->ictSchoolId();

        return StudentProfile::query()
            ->where('program_type', 'ict')
            ->where('school_id', $schoolId)
            ->findOrFail($studentId);
    }

    private function findModule(int $moduleId): CourseModule
    {
        $user = Auth::user();
        $schoolId = $user->ictSchoolId();

        return CourseModule::where('id', $moduleId)
            ->whereHas('course.schools', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->where('is_active', true);
            })
            ->firstOrFail();
    }

    public function editUnlockedRecord(int $recordId): void
    {
        $record = IcdlExamResult::findOrFail($recordId);
        
        if ($record->entered_by_teacher_id !== Auth::id() || $record->is_locked) {
            session()->flash('error', 'You cannot edit this record.');
            return;
        }
        
        $this->editingId = $recordId;
        $this->editingScore = $record->score;
        $this->editingComment = $record->teacher_comment;
    }
    
    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editingScore = '';
        $this->editingComment = '';
    }
    
    public function saveEditedRecord(): void
    {
        if (!$this->editingId) {
            return;
        }
        
        $record = IcdlExamResult::findOrFail($this->editingId);
        
        if ($record->entered_by_teacher_id !== Auth::id() || $record->is_locked) {
            session()->flash('error', 'You cannot edit this record.');
            return;
        }
        
        $this->validate([
            'editingScore' => 'required|numeric|min:0|max:100',
        ]);
        
        $oldValues = $record->toArray();
        
        $result = (float) $this->editingScore >= 75 ? 'pass' : 'fail';
        
        $record->update([
            'score' => $this->editingScore,
            'result' => $result,
            'teacher_comment' => $this->editingComment,
            'updated_by' => Auth::id(),
        ]);
        
        $this->logAuditUpdate($record, $oldValues, $record->toArray());
        
        session()->flash('message', 'Correction submitted for admin review.');
        $this->cancelEdit();
    }

    private function logAudit(IcdlExamResult $mark): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model_type' => 'IcdlExamResult',
            'model_id' => $mark->id,
            'model_name' => $mark->exam_session,
            'old_values' => null,
            'new_values' => $mark->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
    
    private function logAuditUpdate(IcdlExamResult $mark, array $oldValues, array $newValues): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model_type' => 'IcdlExamResult',
            'model_id' => $mark->id,
            'model_name' => $mark->exam_session,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
