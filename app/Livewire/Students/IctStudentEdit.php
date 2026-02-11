<?php

namespace App\Livewire\Students;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class IctStudentEdit extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public StudentProfile $student;

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
    public $initial_password = '';
    public $payment_amount = '';
    public $payment_reference = '';
    public $payment_receipt = null;
    public $selectedCourses = [];

    public function mount(StudentProfile $student): void
    {
        $this->authorize('update', $student);

        if ($student->program_type !== 'ict') {
            abort(404);
        }

        $this->student = $student->load('user.enrollments');
        $this->hydrateNameFields($student->full_name);
        $this->gender = $student->gender;
        $this->date_of_birth = $student->date_of_birth?->format('Y-m-d') ?? '';
        $this->class_grade = $student->class_grade ?? '';
        $this->nationality = $student->nationality ?? '';
        $this->icdl_number = $student->icdl_number ?? '';
        $this->email = $student->user?->email ?? '';
        $this->initial_password = $student->user?->initial_password ?? '';
        $this->payment_amount = $student->payment_amount ?? '';
        $this->payment_reference = $student->payment_reference ?? '';
        $this->selectedCourses = $student->user?->enrollments?->pluck('course_id')->all() ?? [];
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
            'email' => 'nullable|email|max:191|unique:users,email,' . $this->student->user_id,
            'password' => 'nullable|min:8|confirmed',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_reference' => 'nullable|string|max:255',
            'payment_receipt' => 'nullable|file|max:2048',
            'selectedCourses' => 'array',
            'selectedCourses.*' => 'exists:courses,id',
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

    private function hydrateNameFields(string $fullName): void
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);
        $this->first_name = $parts[0] ?? '';
        $this->last_name = $parts[1] ?? '';
        $this->updateFullName();
    }

    public function save(): void
    {
        $this->updateFullName();
        $this->validate();

        $receiptPath = $this->student->payment_receipt_path;

        if ($this->payment_receipt) {
            $receiptPath = $this->payment_receipt->store('payment-receipts', 'public');
        }

        $updatePayload = [
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'class_grade' => $this->class_grade,
            'nationality' => $this->nationality,
            'icdl_number' => $this->icdl_number,
        ];

        if ($this->payment_amount !== '' && $this->payment_amount !== null) {
            $updatePayload['payment_amount'] = $this->payment_amount;
            $updatePayload['payment_reference'] = $this->payment_reference ?: null;
            $updatePayload['payment_receipt_path'] = $receiptPath;
            $updatePayload['payment_status'] = 'pending';
            $updatePayload['payment_submitted_at'] = now();
        }

        $this->student->update($updatePayload);

        if ($this->student->user) {
            $this->student->user->update([
                'name' => $this->full_name,
                'email' => $this->email ?: $this->generateStudentEmail(),
            ]);

            if ($this->password) {
                $this->student->user->update([
                    'password' => Hash::make($this->password),
                    'initial_password' => $this->password,
                ]);
                $this->initial_password = $this->password;
            } elseif (!$this->student->user->initial_password) {
                $generatedPassword = $this->generateSimplePassword();

                $this->student->user->update([
                    'password' => Hash::make($generatedPassword),
                    'initial_password' => $generatedPassword,
                ]);
                $this->initial_password = $generatedPassword;
            }
        }

        $this->syncEnrollments();

        session()->flash('message', 'ICT student details updated successfully.');
        redirect()->route('students.show', $this->student->id);
    }

    private function availableCourses()
    {
        $schoolId = auth()->user()->ictSchoolId() ?? $this->student->school_id;

        if (!$schoolId) {
            return collect();
        }

        return Course::whereHas('schools', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId)->where('is_active', true);
        })
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    private function syncEnrollments(): void
    {
        if (!$this->student->user) {
            return;
        }

        $allowedIds = $this->availableCourses()->pluck('id')->all();
        $selectedIds = array_values(array_intersect($this->selectedCourses, $allowedIds));

        $currentIds = $this->student->user->enrollments()->pluck('course_id')->all();

        $toDetach = array_diff($currentIds, $selectedIds);
        $toAttach = array_diff($selectedIds, $currentIds);

        if (!empty($toDetach)) {
            CourseEnrollment::where('user_id', $this->student->user_id)
                ->whereIn('course_id', $toDetach)
                ->delete();
        }

        foreach ($toAttach as $courseId) {
            CourseEnrollment::firstOrCreate(
                ['user_id' => $this->student->user_id, 'course_id' => $courseId],
                ['enrolled_at' => now(), 'progress_percentage' => 0]
            );
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

    public function regeneratePassword(): void
    {
        if (!$this->student->user) {
            return;
        }

        $generatedPassword = $this->generateSimplePassword();

        $this->student->user->update([
            'password' => Hash::make($generatedPassword),
            'initial_password' => $generatedPassword,
        ]);

        $this->initial_password = $generatedPassword;
        $this->password = '';
        $this->password_confirmation = '';

        session()->flash('message', 'Student password regenerated successfully.');
    }

    public function render()
    {
        return view('livewire.students.ict-student-edit', [
            'courses' => $this->availableCourses(),
        ]);
    }
}
