<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolCourse;
use App\Models\SchoolTeacher;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Models\AssessmentAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SchoolShow extends Component
{
    use WithPagination;

    public School $school;
    public $activeTab = 'details';

    // Teacher assignment
    public $teacherUserId = '';
    public $teacherStatus = 'active';

    // Course assignment
    public $courseId = '';
    public $courseActive = true;

    public function mount(School $school): void
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'Unauthorized - Admin or Supervisor access required');
        }

        $this->school = $school;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function assignTeacher(): void
    {
        $this->validate([
            'teacherUserId' => ['required', 'exists:users,id'],
            'teacherStatus' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $profile = SchoolTeacher::updateOrCreate(
            ['school_id' => $this->school->id, 'teacher_id' => $this->teacherUserId],
            ['role' => 'ict_teacher', 'status' => $this->teacherStatus]
        );

        TeacherProfile::updateOrCreate(
            ['user_id' => $this->teacherUserId],
            ['role' => 'ict_teacher', 'school_id' => $this->school->id]
        );

        $role = Role::where('name', 'ict_teacher')->first();
        if ($role) {
            $user = User::find($this->teacherUserId);
            if ($user && !$user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }
        }

        $this->teacherUserId = '';
        $this->teacherStatus = 'active';
        session()->flash('message', 'Teacher assigned to school.');
    }

    public function toggleTeacherStatus(int $assignmentId): void
    {
        $assignment = SchoolTeacher::where('school_id', $this->school->id)->findOrFail($assignmentId);
        $assignment->status = $assignment->status === 'active' ? 'inactive' : 'active';
        $assignment->save();
    }

    public function removeTeacher(int $assignmentId): void
    {
        SchoolTeacher::where('school_id', $this->school->id)->where('id', $assignmentId)->delete();
        session()->flash('message', 'Teacher removed from school.');
    }

    public function assignCourse(): void
    {
        $this->validate([
            'courseId' => ['required', 'exists:courses,id'],
            'courseActive' => ['boolean'],
        ]);

        SchoolCourse::updateOrCreate(
            ['school_id' => $this->school->id, 'course_id' => $this->courseId],
            ['is_active' => (bool) $this->courseActive]
        );

        $this->courseId = '';
        $this->courseActive = true;
        session()->flash('message', 'Course assigned to school.');
    }

    public function toggleCourseStatus(int $assignmentId): void
    {
        $assignment = SchoolCourse::where('school_id', $this->school->id)->findOrFail($assignmentId);
        $assignment->is_active = !$assignment->is_active;
        $assignment->save();
    }

    public function removeCourse(int $assignmentId): void
    {
        SchoolCourse::where('school_id', $this->school->id)->where('id', $assignmentId)->delete();
        session()->flash('message', 'Course removed from school.');
    }

    public function render()
    {
        $teacherAssignments = SchoolTeacher::with('teacher')
            ->where('school_id', $this->school->id)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'teachersPage');

        $courseAssignments = SchoolCourse::with('course')
            ->where('school_id', $this->school->id)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'coursesPage');

        $availableTeachers = User::whereHas('roles', fn($q) => $q->where('name', 'ict_teacher'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $availableCourses = Course::orderBy('title')->get(['id', 'title']);

        $assessmentStats = DB::table('assessment_attempts')
            ->selectRaw('COUNT(*) as total_attempts, SUM(is_passed = 1) as passed_attempts')
            ->where('student_type', 'ict')
            ->where('status', 'completed')
            ->where('school_id', $this->school->id)
            ->first();

        $totalAttempts = (int) ($assessmentStats->total_attempts ?? 0);
        $passedAttempts = (int) ($assessmentStats->passed_attempts ?? 0);
        $passRate = $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100, 1) : 0;

        $recentAssessmentResults = AssessmentAttempt::query()
            ->where('student_type', 'ict')
            ->where('status', 'completed')
            ->where('school_id', $this->school->id)
            ->with(['assessment.lesson', 'assessment.course', 'assessment.questions', 'user'])
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        return view('livewire.admin.school-show', [
            'teacherAssignments' => $teacherAssignments,
            'courseAssignments' => $courseAssignments,
            'availableTeachers' => $availableTeachers,
            'availableCourses' => $availableCourses,
            'assessmentSummary' => [
                'total_attempts' => $totalAttempts,
                'passed_attempts' => $passedAttempts,
                'pass_rate' => $passRate,
            ],
            'recentAssessmentResults' => $recentAssessmentResults,
        ]);
    }
}
