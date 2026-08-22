<?php

namespace App\Livewire\TestMarks;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\InternalTestMark;
use App\Models\StudentProfile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $student_id;
    public $course_id;
    public $test_name = '';
    public $score = '';
    public $test_date;
    public $teacher_comment = '';
    public $editingId = null;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        if (!Auth::user()?->isIctTeacher()) {
            abort(403);
        }

        $this->test_date = now()->toDateString();
        $this->student_id = request()->query('student');
        $this->course_id = request()->query('course');
    }

    protected function rules(): array
    {
        return [
            'student_id' => 'required|exists:student_profiles,id',
            'course_id' => 'required|exists:courses,id',
            'test_name' => 'required|string|max:255',
            'score' => 'required|numeric|min:0|max:100',
            'test_date' => 'required|date',
            'teacher_comment' => 'nullable|string|max:1000',
        ];
    }

    public function save(): void
    {
        $this->authorize('manage_users');

        $this->validate();
        $student = $this->findStudent($this->student_id);
        $course = $this->findCourseForSchool($this->course_id);

        $passed = (float) $this->score >= 75;

        if (!$this->editingId) {
            $duplicate = InternalTestMark::where('student_profile_id', $student->id)
                ->where('course_id', $course->id)
                ->where('test_name', $this->test_name)
                ->whereDate('test_date', $this->test_date)
                ->exists();

            if ($duplicate && !Auth::user()->hasAnyRole(['admin', 'supervisor'])) {
                session()->flash('message', 'Duplicate test entry detected. Contact admin to override.');
                return;
            }
        }

        $oldValues = null;
        if ($this->editingId) {
            $mark = InternalTestMark::findOrFail($this->editingId);
            $oldValues = $mark->toArray();
            $mark->update([
                'student_profile_id' => $student->id,
                'course_id' => $course->id,
                'test_name' => $this->test_name,
                'score' => $this->score,
                'passed' => $passed,
                'test_date' => $this->test_date,
                'teacher_comment' => $this->teacher_comment,
                'updated_by' => Auth::id(),
            ]);
        } else {
            $mark = InternalTestMark::create([
                'student_profile_id' => $student->id,
                'course_id' => $course->id,
                'test_name' => $this->test_name,
                'score' => $this->score,
                'passed' => $passed,
                'test_date' => $this->test_date,
                'teacher_comment' => $this->teacher_comment,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        }

        $this->updateProgress($student, $course, $passed);
        $this->updateEligibility($student);
        $this->logAudit($mark, $oldValues);

        session()->flash('message', 'Test marks saved successfully.');
        $this->resetForm();
    }

    public function saveAndNeedsPractice(): void
    {
        $this->save();

        if ($this->student_id) {
            $student = $this->findStudent($this->student_id);
            $student->exam_readiness_status = 'needs_practice';
            $student->save();
        }
    }

    public function saveAndContinue(): void
    {
        $this->save();

        $this->test_name = '';
        $this->score = '';
        $this->teacher_comment = '';
        $this->editingId = null;
    }

    public function edit(int $markId): void
    {
        $mark = InternalTestMark::with(['student', 'course'])->findOrFail($markId);

        if (!$this->canEditMarks($mark->student)) {
            session()->flash('message', 'Marks are locked for this student.');
            return;
        }

        $this->editingId = $mark->id;
        $this->student_id = $mark->student_profile_id;
        $this->course_id = $mark->course_id;
        $this->test_name = $mark->test_name;
        $this->score = $mark->score;
        $this->test_date = $mark->test_date?->toDateString();
        $this->teacher_comment = $mark->teacher_comment ?? '';
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->test_name = '';
        $this->score = '';
        $this->teacher_comment = '';
        $this->test_date = now()->toDateString();
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

        $courses = collect();
        if ($schoolId) {
            $courses = Course::whereHas('schools', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId)->where('is_active', true);
                })
                ->orderBy('title')
                ->get(['id', 'title']);
        }

        $historyQuery = InternalTestMark::with(['student', 'course'])
            ->when($this->student_id, fn ($q) => $q->where('student_profile_id', $this->student_id))
            ->when($this->course_id, fn ($q) => $q->where('course_id', $this->course_id))
            ->orderByDesc('test_date');

        $history = $historyQuery->paginate(10);

        $lockedStudent = $this->student_id
            ? StudentProfile::find($this->student_id)
            : null;

        return view('livewire.test-marks.index', [
            'students' => $students,
            'courses' => $courses,
            'history' => $history,
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

    private function findCourseForSchool(int $courseId): Course
    {
        $user = Auth::user();
        $schoolId = $user->ictSchoolId();

        return Course::query()
            ->where('id', $courseId)
            ->whereHas('schools', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->where('is_active', true);
            })
            ->firstOrFail();
    }

    private function updateProgress(StudentProfile $student, Course $course, bool $passed): void
    {
        $enrollment = CourseEnrollment::where('user_id', $student->user_id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return;
        }

        if (!$passed) {
            return;
        }

        $enrollment->progress_percentage = max((float) $enrollment->progress_percentage, 100);
        $enrollment->save();
    }

    private function updateEligibility(StudentProfile $student): void
    {
        $enrolledCourseIds = CourseEnrollment::where('user_id', $student->user_id)
            ->pluck('course_id')
            ->all();

        if (empty($enrolledCourseIds)) {
            return;
        }

        $passedCourseIds = InternalTestMark::where('student_profile_id', $student->id)
            ->where('passed', true)
            ->whereIn('course_id', $enrolledCourseIds)
            ->get()
            ->pluck('course_id')
            ->unique()
            ->values()
            ->all();

        if (count($passedCourseIds) === count($enrolledCourseIds)) {
            $student->exam_readiness_status = 'student_requested';
            $student->save();
        }
    }

    private function canEditMarks(StudentProfile $student): bool
    {
        return ($student->exam_request_status ?? 'not_requested') !== 'approved';
    }

    private function logAudit(InternalTestMark $mark, ?array $oldValues): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $oldValues ? 'update' : 'create',
            'model_type' => 'InternalTestMark',
            'model_id' => $mark->id,
            'model_name' => $mark->test_name,
            'old_values' => $oldValues,
            'new_values' => $mark->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
