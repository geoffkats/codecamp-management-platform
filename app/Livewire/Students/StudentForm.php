<?php

namespace App\Livewire\Students;

use App\Models\School;
use App\Models\StudentGadget;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\CauRegistrationLookupService;
use App\Support\StudentPassword;
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
    public bool $isCodeClubForm = false;
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

    public string $registrationSearch = '';

    /** @var array<int, array<string, mixed>> */
    public array $registrationResults = [];

    public ?string $selectedExternalRegistrationId = null;

    public string $registrationLookupMessage = '';

    public string $registrationLookupError = '';

    protected CauRegistrationLookupService $cauRegistrationLookup;

    public function boot(CauRegistrationLookupService $cauRegistrationLookup): void
    {
        $this->cauRegistrationLookup = $cauRegistrationLookup;
    }

    protected function rules()
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'nationality' => 'nullable|string|max:255',
            'class_grade' => 'required|string|max:255',
            'address' => 'nullable|string',
            'scratch_account' => 'nullable|string|max:255',
            'scratch_password' => 'nullable|string|max:255',
            'github_account' => 'nullable|string|max:255',
            'student_category' => 'required|in:codecamp,school_club,ict_school',
            'program_type' => 'required|in:ict,codecamp,codeclub',
            'school_id' => 'required_if:program_type,ict,codeclub|nullable|exists:schools,id',
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
            if (in_array($this->program_type, ['ict', 'codeclub'], true)) {
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

    public function mount($student = null, bool $codeclub = false)
    {
        $this->isCodeClubForm = $codeclub;

        if ($this->isCodeClubForm) {
            abort_unless(config('features.code_club', false), 404);
            abort_unless(auth()->user()->hasCodeClubAccess() || auth()->user()->isAdmin() || auth()->user()->isSupervisor() || auth()->user()->isOperationsManager(), 403);
            $this->program_type = 'codeclub';
            $this->student_category = 'school_club';
        }

        if (auth()->user()->isIctTeacher() && ! $student) {
            abort(403, 'ICT teachers cannot access the CodeCamp intake form.');
        }

        if ($student) {
            $this->isEdit = true;
            $this->student = StudentProfile::with('gadgets')->findOrFail($student);
            $this->authorize('view', $this->student);
            $this->loadStudent();
        }
    }

    public function updatedRegistrationSearch(): void
    {
        $this->registrationLookupMessage = '';
        $this->registrationLookupError = '';
        $this->registrationResults = [];

        if ($this->isEdit || ! $this->cauRegistrationLookup->isEnabled()) {
            return;
        }

        if (mb_strlen(trim($this->registrationSearch)) < 2) {
            return;
        }

        $this->registrationResults = $this->cauRegistrationLookup->search($this->registrationSearch);
        $this->registrationLookupError = $this->cauRegistrationLookup->lastError() ?? '';
    }

    public function selectExternalRegistration(string $externalId): void
    {
        $this->registrationLookupMessage = '';
        $this->registrationLookupError = '';
        $record = $this->cauRegistrationLookup->find($externalId);

        if ($record === null) {
            $this->registrationLookupError = $this->cauRegistrationLookup->lastError()
                ?? 'Could not load that registration. Please try again.';

            return;
        }

        $this->prefillFromExternalRegistration($record);
        $this->selectedExternalRegistrationId = $externalId;
        $this->registrationSearch = $record['full_name'] ?? $this->registrationSearch;
        $this->registrationResults = [];
        $this->registrationLookupMessage = 'Registration details loaded. Review the form and save when ready.';
    }

    public function clearExternalRegistration(): void
    {
        $this->selectedExternalRegistrationId = null;
        $this->registrationSearch = '';
        $this->registrationResults = [];
        $this->registrationLookupMessage = '';
        $this->registrationLookupError = '';
    }

    /**
     * @param  array<string, mixed>  $record
     */
    protected function prefillFromExternalRegistration(array $record): void
    {
        $this->first_name = (string) ($record['first_name'] ?? '');
        $this->middle_name = (string) ($record['middle_name'] ?? '');
        $this->last_name = (string) ($record['last_name'] ?? '');
        $this->full_name = (string) ($record['full_name'] ?? '');

        if ($this->full_name === '') {
            $this->updateFullName();
        }

        if (! empty($record['date_of_birth'])) {
            $this->date_of_birth = (string) $record['date_of_birth'];
        }

        if (! empty($record['gender'])) {
            $this->gender = (string) $record['gender'];
        }

        if (! empty($record['nationality'])) {
            $this->nationality = (string) $record['nationality'];
        }

        if (! empty($record['class_grade'])) {
            $this->class_grade = (string) $record['class_grade'];
        }

        if (! empty($record['address'])) {
            $this->address = (string) $record['address'];
        }

        $parent1 = is_array($record['parent1'] ?? null) ? $record['parent1'] : [];

        if (! empty($parent1['name'])) {
            $this->parent1_name = (string) $parent1['name'];
        }

        if (! empty($parent1['relationship']) && in_array($parent1['relationship'], ['mother', 'father', 'guardian'], true)) {
            $this->parent1_relationship = (string) $parent1['relationship'];
        } elseif ($this->parent1_relationship === '') {
            $this->parent1_relationship = 'guardian';
        }

        if (! empty($parent1['phone'])) {
            $this->parent1_phone = (string) $parent1['phone'];
        }

        if (! empty($parent1['email'])) {
            $this->parent1_email = (string) $parent1['email'];
        }

        if (! $this->isEdit && ! empty($record['email']) && filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
            $this->email = (string) $record['email'];
        }

        if (! empty($record['tshirt_size'])) {
            $this->uniform_size = strtolower((string) $record['tshirt_size']);
        }

        if (($record['registration_category'] ?? null) === 'camp') {
            $this->program_type = 'codecamp';
            $this->student_category = 'codecamp';
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

    protected function createStudent()
    {
        $this->updateFullName();

        $studentType = match ($this->program_type) {
            'ict' => 'ict',
            'codeclub' => 'codeclub',
            default => 'codecamp',
        };
        $studentId = StudentProfile::generateStudentId(
            $this->program_type === 'codeclub' ? 'codeclub' : null
        );
        $autoCredentials = in_array($this->program_type, ['ict', 'codeclub'], true);
        $email = match ($this->program_type) {
            'codeclub' => trim((string) $this->email) !== '' ? trim($this->email) : null,
            'ict' => $this->email ?: $this->generateStudentEmail(),
            default => $this->email ?: null,
        };
        $password = $this->password ?: ($autoCredentials
            ? ($this->program_type === 'codeclub'
                ? StudentPassword::generateKidFriendly()
                : StudentPassword::generateSimple())
            : Str::random(12));

        // Create user account
        $user = User::create([
            'name' => $this->full_name,
            'email' => $email,
            'student_type' => $studentType,
            'student_id' => in_array($studentType, ['ict', 'codeclub'], true) ? $studentId : null,
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

        $parentFields = $this->resolveParentFields();

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
            'date_of_birth' => $this->resolveDateOfBirthForProfile(),
            'age' => $this->resolveAgeForProfile(),
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'parent_guardian_name' => $parentFields['parent_guardian_name'],
            'parent_guardian_contact' => $parentFields['parent_guardian_contact'],
            'parent_data' => $parentFields['parent_data'],
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

        if (! $this->isCodeClubForm) {
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

        $this->afterCreateStudent($user, $studentProfile);
    }

    protected function afterCreateStudent(User $user, StudentProfile $studentProfile): void
    {
        //
    }

    protected function updateStudent()
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

        $parentFields = $this->resolveParentFields();

        $this->student->update([
            'school_id' => $this->school_id,
            'program_type' => $this->program_type,
            'full_name' => $this->full_name,
            'icdl_number' => $this->icdl_number ?: null,
            'date_of_birth' => $this->resolveDateOfBirthForProfile(),
            'age' => $this->resolveAgeForProfile(),
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'parent_guardian_name' => $parentFields['parent_guardian_name'],
            'parent_guardian_contact' => $parentFields['parent_guardian_contact'],
            'parent_data' => $parentFields['parent_data'],
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
        if (! $this->isCodeClubForm) {
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

        $this->afterUpdateStudent($this->student->user, $this->student);
    }

    protected function afterUpdateStudent(User $user, StudentProfile $studentProfile): void
    {
        //
    }

    protected function resolveDateOfBirthForProfile(): ?string
    {
        return $this->date_of_birth ?: null;
    }

    protected function resolveAgeForProfile(): ?int
    {
        return null;
    }

    public function render()
    {
        return view('livewire.students.student-form', [
            'schools' => School::orderBy('name')->get(),
            'cauRegistrationEnabled' => $this->cauRegistrationLookup->isEnabled(),
        ]);
    }

  /**
     * @return array{parent_guardian_name: ?string, parent_guardian_contact: ?string, parent_data: ?array}
     */
    protected function resolveParentFields(): array
    {
        $hasParent1 = $this->parent1_name !== '' || $this->parent1_phone !== '';

        if ($this->isCodeClubForm && ! $hasParent1) {
            return [
                'parent_guardian_name' => '',
                'parent_guardian_contact' => '',
                'parent_data' => null,
            ];
        }

        $parentData = [
            'parent1' => [
                'name' => $this->parent1_name,
                'relationship' => $this->parent1_relationship,
                'phone' => $this->parent1_phone,
                'email' => $this->parent1_email,
            ],
        ];

        if ($this->parent2_name) {
            $parentData['parent2'] = [
                'name' => $this->parent2_name,
                'relationship' => $this->parent2_relationship,
                'phone' => $this->parent2_phone,
                'email' => $this->parent2_email,
            ];
        }

        return [
            'parent_guardian_name' => $this->parent1_name ?: null,
            'parent_guardian_contact' => $this->parent1_phone ?: null,
            'parent_data' => $parentData,
        ];
    }

    protected function applyProgramScope(): void
    {
        $user = Auth::user();

        if ($this->isCodeClubForm || $this->program_type === 'codeclub') {
            $this->program_type = 'codeclub';
            $this->student_category = 'school_club';

            return;
        }

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
