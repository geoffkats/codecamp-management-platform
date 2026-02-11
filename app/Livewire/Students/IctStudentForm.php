<?php

namespace App\Livewire\Students;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Role;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class IctStudentForm extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public $first_name = '';
    public $last_name = '';
    public $full_name = '';
    public $gender = '';
    public $date_of_birth = '';
    public $class_grade = '';
    public $nationality = '';

    public $icdl_number = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    public $payment_amount = '';
    public $payment_reference = '';
    public $payment_receipt = null;

    public $school_id = null;
    public $selectedCourses = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->isIctTeacher()) {
            $this->school_id = $user->ictSchoolId();
        } elseif ($user->isAdmin() || $user->isOperationsManager() || $user->isSupervisor()) {
            $this->school_id = request()->query('school_id');
        }
    }

    protected function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date',
            'class_grade' => 'required|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'icdl_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:191|unique:users,email',
            'password' => 'nullable|min:8|confirmed',
            'school_id' => 'required|exists:schools,id',
            'selectedCourses' => 'array',
            'selectedCourses.*' => 'exists:courses,id',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_reference' => 'nullable|string|max:255',
            'payment_receipt' => 'nullable|file|max:2048',
        ];
    }

    public function updatedFirstName(): void
    {
        $this->updateFullName();
    }

    public function updatedLastName(): void
    {
        $this->updateFullName();
    }

    private function updateFullName(): void
    {
        $parts = array_filter([$this->first_name, $this->last_name]);
        $this->full_name = implode(' ', $parts);
    }

    public function save(): void
    {
        $this->updateFullName();
        $this->forceScope();
        $this->validate();

        $this->authorize('create', [StudentProfile::class, 'ict', $this->school_id]);

        $email = $this->email ?: $this->generateStudentEmail();
        $password = $this->password ?: $this->generateSimplePassword();
        $studentId = StudentProfile::generateStudentId();

        $user = User::create([
            'name' => $this->full_name,
            'email' => $email,
            'student_type' => 'ict',
            'student_id' => $studentId,
            'password' => Hash::make($password),
            'initial_password' => $password,
        ]);

        $role = Role::where('name', 'student')->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        $receiptPath = null;
        if ($this->payment_receipt) {
            $receiptPath = $this->payment_receipt->store('payment-receipts', 'public');
        }

        $student = StudentProfile::create([
            'user_id' => $user->id,
            'school_id' => $this->school_id,
            'program_type' => 'ict',
            'student_category' => 'ict_school',
            'student_id' => $studentId,
            'icdl_number' => $this->icdl_number ?: null,
            'exam_readiness_status' => 'not_ready',
            'is_active' => true,
            'full_name' => $this->full_name,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'class_grade' => $this->class_grade,
            'parent_guardian_name' => 'N/A',
            'parent_guardian_contact' => 'N/A',
            'address' => null,
            'scratch_account' => null,
            'scratch_password' => null,
            'github_account' => null,
            'payment_amount' => $this->payment_amount ?: null,
            'payment_reference' => $this->payment_reference ?: null,
            'payment_receipt_path' => $receiptPath,
            'payment_status' => $this->payment_amount ? 'pending' : 'not_submitted',
            'payment_submitted_at' => $this->payment_amount ? now() : null,
        ]);

        $courseIds = $this->filterAllowedCourses($this->selectedCourses);
        foreach ($courseIds as $courseId) {
            CourseEnrollment::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $courseId],
                ['enrolled_at' => now(), 'progress_percentage' => 0]
            );
        }

        session()->flash('message', 'ICT student created successfully.');
        redirect()->route('students.show', $student->id);
    }

    private function forceScope(): void
    {
        $user = Auth::user();

        if ($user->isIctTeacher()) {
            $this->school_id = $user->ictSchoolId();
        }
    }

    private function generateStudentEmail(): string
    {
        $base = Str::slug($this->full_name ?: 'student', '.');
        $base = $base !== '' ? $base : 'student';
        $base = substr($base, 0, 40);

        $candidate = $base . '@outlook.com';
        $suffix = 1;

        while (User::where('email', $candidate)->exists()) {
            $candidate = $base . $suffix . '@outlook.com';
            $suffix++;
        }

        return $candidate;
    }

    private function generateSimplePassword(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';

        for ($i = 0; $i < 8; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    private function filterAllowedCourses(array $courseIds): array
    {
        $allowedIds = $this->availableCourses()->pluck('id')->toArray();
        return array_values(array_intersect($courseIds, $allowedIds));
    }

    private function availableCourses()
    {
        if (!$this->school_id) {
            return collect();
        }

        return Course::whereHas('schools', function ($q) {
            $q->where('school_id', $this->school_id)->where('is_active', true);
        })
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    public function render()
    {
        return view('livewire.students.ict-student-form', [
            'schools' => School::orderBy('name')->get(['id', 'name']),
            'courses' => $this->availableCourses(),
        ]);
    }
}
