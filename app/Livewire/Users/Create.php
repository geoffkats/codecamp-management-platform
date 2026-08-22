<?php

namespace App\Livewire\Users;

use App\Livewire\Users\Concerns\ManagesStaffCourseAccess;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Create extends Component
{
    use ManagesStaffCourseAccess;
    use WithFileUploads;

    public $user = [
        'name' => '',
        'email' => '',
        'bio' => null,
        'is_active' => true,
    ];

    public $password;
    public $password_confirmation;
    public $profile_image;
    public $selectedRoles = [];

    protected function rules()
    {
        return [
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'profile_image' => 'nullable|image|max:2048',
            'user.bio' => 'nullable|string',
            'user.is_active' => 'boolean',
            'selectedRoles' => 'required|array|min:1',
            'selectedCourseIds' => 'array',
            'selectedCourseIds.*' => 'integer|exists:courses,id',
        ];
    }

    public function store()
    {
        $this->validate();

        $imagePath = null;
        if ($this->profile_image) {
            $imagePath = $this->profile_image->store('profiles', 'public');
        }

        $studentRole = \App\Models\Role::where('name', 'student')->first();
        $isStudent = $studentRole && in_array($studentRole->id, $this->selectedRoles);

        $userData = [
            'name' => $this->user['name'],
            'email' => $this->user['email'],
            'password' => Hash::make($this->password),
            'profile_image' => $imagePath,
            'bio' => $this->user['bio'] ?? null,
            'is_active' => $this->user['is_active'] ?? true,
        ];

        if ($isStudent) {
            $userData['student_type'] = 'codecamp';
        }

        $user = User::create($userData);
        $user->roles()->attach($this->selectedRoles);
        $this->syncStaffCourseAccess($user);

        session()->flash('message', 'User created successfully.');

        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        $roles = \App\Models\Role::all();
        $showsCourseAccess = $this->showsCourseAccessPicker();

        return view('livewire.users.create', [
            'roles' => $roles,
            'showsCourseAccess' => $showsCourseAccess,
            'availableCourses' => $showsCourseAccess ? $this->availableCoursesForPicker() : collect(),
        ]);
    }
}
