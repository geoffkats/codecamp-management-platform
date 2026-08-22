<?php

namespace App\Livewire\Students;

use App\Models\CodeClub;
use App\Models\CodeClubMembership;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class CodeClubStudentForm extends StudentForm
{
    public ?int $code_club_id = null;
    public $age = '';

    public function mount($student = null, bool $codeclub = true): void
    {
        parent::mount($student, true);

        if ($this->isEdit && $this->student) {
            $this->age = $this->student->age ?? '';

            $membership = CodeClubMembership::query()
                ->where('student_id', $this->student->user_id)
                ->where('status', 'active')
                ->first();

            $this->code_club_id = $membership?->code_club_id;
        } elseif (! $this->isEdit) {
            $clubId = request()->query('club_id');
            $this->code_club_id = $clubId ? (int) $clubId : null;

            $schoolId = request()->query('school_id');
            if ($schoolId) {
                $this->school_id = (int) $schoolId;
            }

            if ($this->code_club_id) {
                $club = CodeClub::find($this->code_club_id);
                if ($club) {
                    $this->school_id = $club->school_id;
                }
            }
        }
    }

    protected function rules()
    {
        $rules = parent::rules();
        $rules['school_id'] = 'required|exists:schools,id';
        $rules['code_club_id'] = 'required|exists:code_clubs,id';
        $rules['parent1_name'] = 'nullable|string|max:255';
        $rules['parent1_relationship'] = 'nullable|in:mother,father,guardian';
        $rules['parent1_phone'] = 'nullable|string|max:255';
        $rules['parent1_email'] = 'nullable|email|max:255';
        $rules['date_of_birth'] = 'nullable|date';
        $rules['age'] = 'nullable|integer|min:4|max:25';

        return $rules;
    }

    protected function resolveDateOfBirthForProfile(): ?string
    {
        return null;
    }

    protected function resolveAgeForProfile(): ?int
    {
        if ($this->age === '' || $this->age === null) {
            return null;
        }

        return (int) $this->age;
    }

    public function updatedSchoolId(): void
    {
        if (! $this->code_club_id) {
            return;
        }

        $club = CodeClub::find($this->code_club_id);
        if ($club && (int) $club->school_id !== (int) $this->school_id) {
            $this->code_club_id = null;
        }
    }

    public function save()
    {
        $this->applyProgramScope();
        $this->validate();
        $this->authorizeClubSelection();

        if (! $this->isEdit) {
            $this->authorize('create', [StudentProfile::class, $this->program_type, $this->school_id]);
        } else {
            $this->authorize('update', $this->student);
        }

        if ($this->isEdit) {
            $this->updateStudent();
        } else {
            $this->createStudent();
        }

        session()->flash('message', $this->isEdit ? 'Student updated successfully!' : 'Code Club student registered successfully!');

        if ($this->code_club_id) {
            $club = CodeClub::find($this->code_club_id);

            return redirect()->route('admin.code-clubs.show', $club);
        }

        return redirect()->route('students.index', ['filterProgram' => 'codeclub']);
    }

    protected function afterCreateStudent(User $user, StudentProfile $studentProfile): void
    {
        $this->syncClubMembership($user);

        session()->flash('student_credentials', [
            'full_name' => $studentProfile->full_name,
            'student_id' => $studentProfile->student_id,
            'password' => $user->initial_password,
            'print_url' => route('students.print-credentials', $studentProfile->id),
        ]);
    }

    protected function afterUpdateStudent(User $user, StudentProfile $studentProfile): void
    {
        $this->syncClubMembership($user);
    }

    private function syncClubMembership(User $user): void
    {
        if (! $this->code_club_id) {
            return;
        }

        CodeClubMembership::query()
            ->where('student_id', $user->id)
            ->where('code_club_id', '!=', $this->code_club_id)
            ->where('status', 'active')
            ->update([
                'status' => 'dropped',
                'dropped_at' => now(),
            ]);

        CodeClubMembership::updateOrCreate(
            [
                'code_club_id' => $this->code_club_id,
                'student_id' => $user->id,
            ],
            [
                'status' => 'active',
                'enrolled_at' => now(),
                'dropped_at' => null,
            ]
        );
    }

    private function authorizeClubSelection(): void
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isSupervisor() || $user->isOperationsManager()) {
            return;
        }

        $allowed = $user->activeClubIds();

        if (! in_array((int) $this->code_club_id, $allowed, true)) {
            abort(403, 'You cannot assign students to this club.');
        }

        $club = CodeClub::find($this->code_club_id);

        if ($club && (int) $club->school_id !== (int) $this->school_id) {
            abort(422, 'The selected club must belong to the chosen school.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        $clubsQuery = CodeClub::query()
            ->where('status', 'active')
            ->orderBy('name');

        if ($this->school_id) {
            $clubsQuery->where('school_id', $this->school_id);
        }

        if (! $user->isAdmin() && ! $user->isSupervisor() && ! $user->isOperationsManager()) {
            $clubsQuery->whereIn('id', $user->activeClubIds());
        }

        return view('livewire.students.code-club-student-form', [
            'schools' => School::orderBy('name')->get(),
            'clubs' => $clubsQuery->get(['id', 'name', 'school_id', 'day_of_week']),
        ]);
    }
}
