<?php

namespace App\Livewire\Students;

use App\Models\StudentProfile;
use App\Models\StudentGadget;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class StudentForm extends Component
{
    use WithFileUploads, AuthorizesRequests;

    public $student = null;
    public $isEdit = false;

    // User fields
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    // Student profile fields
    public $first_name = '';
    public $middle_name = '';
    public $last_name = '';
    public $full_name = '';
    public $date_of_birth = '';
    public $gender = '';
    public $nationality = '';
    public $class_grade = '';
    public $address = '';
    public $scratch_account = '';
    public $scratch_password = '';
    public $github_account = '';
    public $student_category = 'codecamp';
    public $program_type = 'codecamp';
    public $school_id = null;
    public $icdl_number = '';
    public $photo = null;

    // Parent 1
    public $parent1_name = '';
    public $parent1_relationship = '';
    public $parent1_phone = '';
    public $parent1_email = '';

    // Parent 2 (Optional)
    public $parent2_name = '';
    public $parent2_relationship = '';
    public $parent2_phone = '';
    public $parent2_email = '';

    // Gadgets
    public $gadgets = [];
    public $newGadget = [
        'device_type' => '',
        'brand' => '',
        'serial_number' => '',
        'ram' => '',
        'storage' => '',
        'condition' => '',
        'accessories' => '',
        'photo' => null
    ];

    // Uniform & Fees
    public $uniform_size = '';
    public $tshirt_collected = false;
    public $uniform_paid = false;
    public $payment_receipt = null;

    protected function rules()
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'nationality' => 'nullable|string|max:255',
            'class_grade' => 'required|string|max:255',
            'address' => 'nullable|string',
            'scratch_account' => 'nullable|string|max:255',
            'scratch_password' => 'nullable|string|max:255',
            'github_account' => 'nullable|string|max:255',
            'student_category' => 'required|in:codecamp,school_club,ict_school',
            'program_type' => 'required|in:ict,codecamp',
            'school_id' => 'required_if:program_type,ict|nullable|exists:schools,id',
            'icdl_number' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'parent1_name' => 'required|string|max:255',
            'parent1_relationship' => 'required|in:mother,father,guardian',
            'parent1_phone' => 'required|string|max:255',
            'parent1_email' => 'nullable|email|max:255',
            'parent2_name' => 'nullable|string|max:255',
            'parent2_relationship' => 'nullable|in:mother,father,guardian',
            'parent2_phone' => 'nullable|string|max:255',
            'parent2_email' => 'nullable|email|max:255',
            'uniform_size' => 'nullable|string|max:10',
            'tshirt_collected' => 'boolean',
            'uniform_paid' => 'boolean',
            'payment_receipt' => 'nullable|file|max:2048',
        ];

        if (!$this->isEdit) {
            if ($this->program_type === 'ict') {
                $rules['email'] = 'nullable|email|unique:users,email';
                $rules['password'] = 'nullable|min:8|confirmed';
            } else {
                $rules['email'] = 'required|email|unique:users,email';
                $rules['password'] = 'required|min:8|confirmed';
            }
        }

        return $rules;
    }

    public function updatedFirstName()
    {
        $this->updateFullName();
    }

    public function updatedMiddleName()
    {
        $this->updateFullName();
    }

    public function updatedLastName()
    {
        $this->updateFullName();
    }

    private function updateFullName()
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        $this->full_name = implode(' ', $parts);
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

    public function mount($student = null)
    {
        if (auth()->user()->isIctTeacher() && !$student) {
            abort(403, 'ICT teachers cannot access the CodeCamp intake form.');
        }

        if ($student) {
            $this->isEdit = true;
            $this->student = StudentProfile::with('gadgets')->findOrFail($student);
            $this->authorize('view', $this->student);
            $this->loadStudent();
        }
    }

    public function loadStudent()
    {
        // Split full name into parts
        $nameParts = explode(' ', $this->student->full_name);
        if (count($nameParts) === 1) {
            $this->first_name = $nameParts[0];
            $this->last_name = '';
            $this->middle_name = '';
        } elseif (count($nameParts) === 2) {
            $this->first_name = $nameParts[0];
            $this->last_name = $nameParts[1];
            $this->middle_name = '';
        } else {
            $this->first_name = array_shift($nameParts);
            $this->last_name = array_pop($nameParts);
            $this->middle_name = implode(' ', $nameParts);
        }
        
        $this->full_name = $this->student->full_name;
        $this->date_of_birth = $this->student->date_of_birth?->format('Y-m-d');
        $this->gender = $this->student->gender;
        $this->nationality = $this->student->nationality;
        $this->class_grade = $this->student->class_grade;
        $this->address = $this->student->address;
        $this->scratch_account = $this->student->scratch_account;
        $this->scratch_password = $this->student->scratch_password;
        $this->github_account = $this->student->github_account;
        $this->student_category = $this->student->student_category ?? 'codecamp';
        $this->program_type = $this->student->program_type ?? 'codecamp';
        $this->school_id = $this->student->school_id;
        $this->icdl_number = $this->student->icdl_number ?? '';
        $this->email = $this->student->user->email;
        
        // Load parent data
        $parentData = $this->student->parent_data;
        
        if (!empty($parentData) && is_array($parentData) && isset($parentData['parent1'])) {
            $this->parent1_name = $parentData['parent1']['name'] ?? '';
            $this->parent1_relationship = $parentData['parent1']['relationship'] ?? '';
            $this->parent1_phone = $parentData['parent1']['phone'] ?? '';
            $this->parent1_email = $parentData['parent1']['email'] ?? '';
            
            if (isset($parentData['parent2'])) {
                $this->parent2_name = $parentData['parent2']['name'] ?? '';
                $this->parent2_relationship = $parentData['parent2']['relationship'] ?? '';
                $this->parent2_phone = $parentData['parent2']['phone'] ?? '';
                $this->parent2_email = $parentData['parent2']['email'] ?? '';
            }
        } else {
            // Fallback to old fields for existing records
            $this->parent1_name = $this->student->parent_guardian_name ?? '';
            $this->parent1_phone = $this->student->parent_guardian_contact ?? '';
            $this->parent1_relationship = 'guardian';
        }
        
        // Load uniform data
        $this->uniform_size = $this->student->uniform_size;
        $this->tshirt_collected = $this->student->tshirt_collected;
        $this->uniform_paid = $this->student->uniform_paid;
        
        // Load gadgets
        $this->gadgets = $this->student->gadgets->toArray();
    }

    public function addGadget()
    {
        if (!empty($this->newGadget['device_type'])) {
            $this->gadgets[] = $this->newGadget;
            $this->newGadget = ['device_type' => '', 'serial_number' => '', 'specifications' => ''];
        }
    }

    public function removeGadget($index)
    {
        unset($this->gadgets[$index]);
        $this->gadgets = array_values($this->gadgets);
    }

    public function save()
    {
        $this->applyProgramScope();
        $this->validate();

        if (!$this->isEdit) {
            $this->authorize('create', [StudentProfile::class, $this->program_type, $this->school_id]);
        } else {
            $this->authorize('update', $this->student);
        }

        if ($this->isEdit) {
            $this->updateStudent();
        } else {
            $this->createStudent();
        }

        session()->flash('message', $this->isEdit ? 'Student updated successfully!' : 'Student created successfully!');
        return redirect()->route('students.index');
    }

    private function createStudent()
    {
        $this->updateFullName();

        $studentType = $this->program_type === 'ict' ? 'ict' : 'codecamp';
        $studentId = StudentProfile::generateStudentId();
        $email = $this->email ?: ($this->program_type === 'ict' ? $this->generateStudentEmail() : null);
        $password = $this->password ?: ($this->program_type === 'ict' ? $this->generateSimplePassword() : Str::random(12));

        // Create user account
        $user = User::create([
            'name' => $this->full_name,
            'email' => $email,
            'student_type' => $studentType,
            'student_id' => $studentType === 'ict' ? $studentId : null,
            'password' => Hash::make($password),
            'initial_password' => $password,
        ]);

        // Assign student role
        $user->roles()->attach(\App\Models\Role::where('name', 'student')->first()->id);

        // Handle photo upload
        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('student-photos', 'public');
        }

        // Handle payment receipt upload
        $receiptPath = null;
        if ($this->payment_receipt) {
            $receiptPath = $this->payment_receipt->store('payment-receipts', 'public');
        }

        // Prepare parent data
        $parentData = [
            'parent1' => [
                'name' => $this->parent1_name,
                'relationship' => $this->parent1_relationship,
                'phone' => $this->parent1_phone,
                'email' => $this->parent1_email,
            ]
        ];

        if ($this->parent2_name) {
            $parentData['parent2'] = [
                'name' => $this->parent2_name,
                'relationship' => $this->parent2_relationship,
                'phone' => $this->parent2_phone,
                'email' => $this->parent2_email,
            ];
        }

        // Create student profile
        $studentProfile = StudentProfile::create([
            'user_id' => $user->id,
            'school_id' => $this->school_id,
            'program_type' => $this->program_type,
            'student_id' => $studentId,
            'icdl_number' => $this->icdl_number ?: null,
            'exam_readiness_status' => 'not_ready',
            'is_active' => true,
            'full_name' => $this->full_name,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'parent_guardian_name' => $this->parent1_name,
            'parent_guardian_contact' => $this->parent1_phone,
            'parent_data' => $parentData,
            'class_grade' => $this->class_grade,
            'address' => $this->address,
            'scratch_account' => $this->scratch_account,
            'scratch_password' => $this->scratch_password,
            'github_account' => $this->github_account,
            'student_category' => $this->student_category,
            'photo_path' => $photoPath,
            'uniform_size' => $this->uniform_size,
            'tshirt_collected' => $this->tshirt_collected,
            'uniform_paid' => $this->uniform_paid,
            'payment_receipt_path' => $receiptPath,
        ]);

        // Add gadgets
        foreach ($this->gadgets as $gadget) {
            StudentGadget::create([
                'student_profile_id' => $studentProfile->id,
                'device_type' => $gadget['device_type'],
                'brand' => $gadget['brand'] ?? null,
                'serial_number' => $gadget['serial_number'] ?? null,
                'ram' => $gadget['ram'] ?? null,
                'storage' => $gadget['storage'] ?? null,
                'condition' => $gadget['condition'] ?? null,
                'accessories' => $gadget['accessories'] ?? null,
                'specifications' => json_encode($gadget),
            ]);
        }
    }

    private function updateStudent()
    {
        $this->updateFullName();
        
        // Handle photo upload
        if ($this->photo) {
            $photoPath = $this->photo->store('student-photos', 'public');
            $this->student->photo_path = $photoPath;
        }

        // Handle payment receipt upload
        if ($this->payment_receipt) {
            $receiptPath = $this->payment_receipt->store('payment-receipts', 'public');
            $this->student->payment_receipt_path = $receiptPath;
        }

        // Prepare parent data
        $parentData = [
            'parent1' => [
                'name' => $this->parent1_name,
                'relationship' => $this->parent1_relationship,
                'phone' => $this->parent1_phone,
                'email' => $this->parent1_email,
            ]
        ];

        if ($this->parent2_name) {
            $parentData['parent2'] = [
                'name' => $this->parent2_name,
                'relationship' => $this->parent2_relationship,
                'phone' => $this->parent2_phone,
                'email' => $this->parent2_email,
            ];
        }

        $this->student->update([
            'school_id' => $this->school_id,
            'program_type' => $this->program_type,
            'full_name' => $this->full_name,
            'icdl_number' => $this->icdl_number ?: null,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'parent_guardian_name' => $this->parent1_name,
            'parent_guardian_contact' => $this->parent1_phone,
            'parent_data' => $parentData,
            'class_grade' => $this->class_grade,
            'address' => $this->address,
            'scratch_account' => $this->scratch_account,
            'scratch_password' => $this->scratch_password,
            'github_account' => $this->github_account,
            'student_category' => $this->student_category,
            'uniform_size' => $this->uniform_size,
            'tshirt_collected' => $this->tshirt_collected,
            'uniform_paid' => $this->uniform_paid,
        ]);

        // Update gadgets
        $this->student->gadgets()->delete();
        foreach ($this->gadgets as $gadget) {
            StudentGadget::create([
                'student_profile_id' => $this->student->id,
                'device_type' => $gadget['device_type'],
                'brand' => $gadget['brand'] ?? null,
                'serial_number' => $gadget['serial_number'] ?? null,
                'ram' => $gadget['ram'] ?? null,
                'storage' => $gadget['storage'] ?? null,
                'condition' => $gadget['condition'] ?? null,
                'accessories' => $gadget['accessories'] ?? null,
                'specifications' => json_encode($gadget),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.students.student-form', [
            'schools' => School::orderBy('name')->get(),
        ]);
    }

    private function applyProgramScope(): void
    {
        $user = Auth::user();

        if ($user->isIctTeacher()) {
            $this->program_type = 'ict';
            $this->student_category = 'ict_school';
            $this->school_id = $user->ictSchoolId();
            return;
        }

        if ($user->isCodecampTrainer()) {
            $this->program_type = 'codecamp';
            if ($this->student_category === 'ict_school') {
                $this->student_category = 'codecamp';
            }
            $this->school_id = null;
            return;
        }

        if ($this->program_type === 'ict') {
            $this->student_category = 'ict_school';
        } elseif ($this->student_category === 'ict_school') {
            $this->student_category = 'codecamp';
        }
    }
}
