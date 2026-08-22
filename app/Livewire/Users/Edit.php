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
class Edit extends Component
{
    use ManagesStaffCourseAccess;
    use WithFileUploads;

    public $userId;
    public $user = [];
    public $password;
    public $password_confirmation;
    public $profile_image;
    public $selectedRoles = [];

    public function mount(User $user)
    {
        $this->userId = $user->id;
        $this->user = $user->toArray();
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        $this->loadStaffCourseIds($user);
    }

    protected function rules()
    {
        return [
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|max:191|unique:users,email,' . $this->userId,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_image' => 'nullable|image|max:2048',
            'user.bio' => 'nullable|string',
            'user.is_active' => 'boolean',
            'selectedRoles' => 'required|array|min:1',
            'selectedCourseIds' => 'array',
            'selectedCourseIds.*' => 'integer|exists:courses,id',
        ];
    }

    public function update()
    {
        $this->validate();

        $user = User::findOrFail($this->userId);

        if ($this->profile_image) {
            $path = $this->profile_image->store('profiles', 'public');
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $user->profile_image = $path;
        }

        $user->name = $this->user['name'];
        $user->email = $this->user['email'];
        $user->bio = $this->user['bio'] ?? null;
        $user->is_active = $this->user['is_active'] ?? false;

        if (!empty($this->password)) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        $user->roles()->sync($this->selectedRoles);
        $this->syncStaffCourseAccess($user);

        session()->flash('message', 'User updated successfully.');

        $this->user = $user->fresh()->toArray();
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        $this->loadStaffCourseIds($user);
    }

    public function render()
    {
        $roles = \App\Models\Role::all();
        $currentUser = User::with('roles')->findOrFail($this->userId);
        $showsCourseAccess = $this->showsCourseAccessPicker();

        return view('livewire.users.edit', [
            'roles' => $roles,
            'currentUser' => $currentUser,
            'showsCourseAccess' => $showsCourseAccess,
            'availableCourses' => $showsCourseAccess ? $this->availableCoursesForPicker() : collect(),
        ]);
    }
}
