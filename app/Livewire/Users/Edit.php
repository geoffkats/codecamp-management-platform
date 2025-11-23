<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
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
        ];
    }

    public function update()
    {
        $data = $this->validate();

        $user = User::findOrFail($this->userId);

        if ($this->profile_image) {
            $path = $this->profile_image->store('profiles', 'public');
            // Optionally delete old image
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

        // Sync roles
        $user->roles()->sync($this->selectedRoles);

        session()->flash('message', 'User updated successfully.');

        // Refresh the local copy
        $this->user = $user->toArray();
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
    }

    public function render()
    {
        $roles = \App\Models\Role::all();
        $currentUser = User::with('roles')->findOrFail($this->userId);
        
        return view('livewire.users.edit', [
            'roles' => $roles,
            'currentUser' => $currentUser,
        ]);
    }
}

