<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\School;
use App\Models\SchoolTeacher;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SchoolManagement extends Component
{
    use WithPagination;

    public $activeTab = 'schools';

    // School form
    public $schoolId = null;
    public $name = '';
    public $code = '';
    public $address = '';

    // Teacher assignment form
    public $teacherUserId = '';
    public $teacherRole = 'ict_teacher';
    public $teacherSchoolId = '';
    public $teacherSearch = '';
    public $teacherRoleFilter = 'all';
    public $createNewTeacher = false;
    public $newTeacherName = '';
    public $newTeacherEmail = '';
    public $newTeacherPassword = '';
    public $newTeacherPasswordConfirmation = '';

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'Unauthorized - Admin or Supervisor access required');
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updatedCreateNewTeacher(bool $value): void
    {
        if ($value) {
            $this->teacherUserId = '';
        } else {
            $this->newTeacherName = '';
            $this->newTeacherEmail = '';
            $this->newTeacherPassword = '';
            $this->newTeacherPasswordConfirmation = '';
        }
    }

    public function editSchool(int $schoolId): void
    {
        $school = School::findOrFail($schoolId);
        $this->schoolId = $school->id;
        $this->name = $school->name;
        $this->code = $school->code ?? '';
        $this->address = $school->address ?? '';
    }

    public function resetSchoolForm(): void
    {
        $this->schoolId = null;
        $this->name = '';
        $this->code = '';
        $this->address = '';
    }

    public function saveSchool(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('schools', 'code')->ignore($this->schoolId)->whereNotNull('code')],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $data = [
            'name' => $this->name,
            'code' => $this->code ?: null,
            'address' => $this->address ?: null,
        ];

        if ($this->schoolId) {
            School::where('id', $this->schoolId)->update($data);
            session()->flash('message', 'School updated successfully.');
        } else {
            School::create($data);
            session()->flash('message', 'School created successfully.');
        }

        $this->resetSchoolForm();
    }

    public function deleteSchool(int $schoolId): void
    {
        $school = School::withCount(['students', 'schoolTeachers'])->findOrFail($schoolId);

        if ($school->students_count > 0 || $school->school_teachers_count > 0) {
            session()->flash('error', 'Cannot delete a school with assigned students or teachers.');
            return;
        }

        $school->delete();
        session()->flash('message', 'School deleted successfully.');
    }

    public function assignTeacher(): void
    {
        $rules = [
            'teacherRole' => ['required', Rule::in(['ict_teacher', 'codecamp_trainer'])],
            'teacherSchoolId' => ['required_if:teacherRole,ict_teacher', 'nullable', 'exists:schools,id'],
        ];

        if ($this->createNewTeacher) {
            $rules['newTeacherName'] = ['required', 'string', 'max:255'];
            $rules['newTeacherEmail'] = ['required', 'email', 'max:255', 'unique:users,email'];
            $rules['newTeacherPassword'] = ['required', 'min:8', 'same:newTeacherPasswordConfirmation'];
            $rules['newTeacherPasswordConfirmation'] = ['required'];
        } else {
            $rules['teacherUserId'] = ['required', 'exists:users,id'];
        }

        $this->validate($rules);

        if ($this->createNewTeacher) {
            $user = User::create([
                'name' => $this->newTeacherName,
                'email' => $this->newTeacherEmail,
                'password' => Hash::make($this->newTeacherPassword),
            ]);

            $this->teacherUserId = $user->id;
        }

        $schoolId = $this->teacherRole === 'ict_teacher' ? (int) $this->teacherSchoolId : null;

        TeacherProfile::updateOrCreate(
            ['user_id' => $this->teacherUserId],
            ['role' => $this->teacherRole, 'school_id' => $schoolId]
        );

        if ($this->teacherRole === 'ict_teacher' && $schoolId) {
            SchoolTeacher::updateOrCreate(
                ['school_id' => $schoolId, 'teacher_id' => $this->teacherUserId],
                ['role' => 'ict_teacher', 'status' => 'active']
            );
        }

        if ($this->teacherRole === 'ict_teacher' && $schoolId) {
            SchoolTeacher::updateOrCreate(
                ['school_id' => $schoolId, 'teacher_id' => $this->teacherUserId],
                ['role' => 'ict_teacher', 'status' => 'active']
            );
        }

        $role = Role::where('name', $this->teacherRole)->first();
        if ($role) {
            $user = User::find($this->teacherUserId);
            if ($user && !$user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }
        }

        session()->flash('message', 'Teacher assignment saved.');
        $this->resetTeacherForm();
    }

    public function removeTeacherProfile(int $profileId): void
    {
        $profile = TeacherProfile::find($profileId);
        if ($profile) {
            SchoolTeacher::where('teacher_id', $profile->user_id)->delete();
            $profile->delete();
        }
        session()->flash('message', 'Teacher assignment removed.');
    }

    public function resetTeacherForm(): void
    {
        $this->teacherUserId = '';
        $this->teacherRole = 'ict_teacher';
        $this->teacherSchoolId = '';
        $this->createNewTeacher = false;
        $this->newTeacherName = '';
        $this->newTeacherEmail = '';
        $this->newTeacherPassword = '';
        $this->newTeacherPasswordConfirmation = '';
    }

    public function render()
    {
        $schools = School::withCount(['students', 'schoolTeachers'])
            ->orderBy('name')
            ->paginate(10, ['*'], 'schoolsPage');

        $schoolIds = $schools->getCollection()->pluck('id')->filter()->values();
        $performanceBySchool = collect();

        if ($schoolIds->isNotEmpty()) {
            $performanceBySchool = DB::table('assessment_attempts')
                ->selectRaw('school_id, COUNT(*) as total_attempts, SUM(is_passed = 1) as passed_attempts')
                ->where('student_type', 'ict')
                ->where('status', 'completed')
                ->whereIn('school_id', $schoolIds)
                ->groupBy('school_id')
                ->get()
                ->keyBy('school_id');
        }

        $schoolOptions = School::orderBy('name')->get();

        $teacherProfiles = TeacherProfile::with(['user', 'school'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'teachersPage');

        $availableTeachers = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'student');
        })
            ->when($this->teacherRoleFilter !== 'all', function ($query) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', $this->teacherRoleFilter);
                });
            })
            ->when($this->teacherSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->teacherSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->teacherSearch . '%');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('livewire.admin.school-management', [
            'schools' => $schools,
            'performanceBySchool' => $performanceBySchool,
            'schoolOptions' => $schoolOptions,
            'teacherProfiles' => $teacherProfiles,
            'availableTeachers' => $availableTeachers,
        ]);
    }
}
