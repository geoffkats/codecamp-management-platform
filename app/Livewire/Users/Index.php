<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterRole = 'all';
    public $filterStatus = 'all'; // 'all', 'active', 'inactive'
    public $sortBy = 'recent';
    public $filterAudience = 'staff'; // 'staff', 'students', 'ict_students'
    
    // Password reset modal
    public $showResetModal = false;
    public $selectedUser = null;
    public $newPassword = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterRole' => ['except' => 'all'],
        'filterStatus' => ['except' => 'all'],
        'sortBy' => ['except' => 'recent'],
        'filterAudience' => ['except' => 'staff'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterAudience()
    {
        $this->resetPage();
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        $user->delete();
        session()->flash('message', 'User deleted successfully.');
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);
        session()->flash('message', 'User status updated.');
    }

    public function openResetModal($userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->showResetModal = true;
        $this->newPassword = null;
    }

    public function closeResetModal()
    {
        $this->showResetModal = false;
        $this->selectedUser = null;
        $this->newPassword = null;
    }

    public function confirmResetPassword()
    {
        if (!$this->selectedUser) {
            return;
        }
        
        // Generate an easier-to-type password (10 characters: letters and numbers only)
        // Format: 3 letters + 2 numbers + 3 letters + 2 numbers (easier to type and remember)
        $newPassword = strtolower(\Illuminate\Support\Str::random(3)) . 
                       rand(10, 99) . 
                       strtolower(\Illuminate\Support\Str::random(3)) . 
                       rand(10, 99);
        
        // Update user's password
        $this->selectedUser->update([
            'password' => Hash::make($newPassword)
        ]);
        
        // Store the new password to display in modal
        $this->newPassword = $newPassword;
    }

    public function render()
    {
        // Optimize: Select only needed columns and eager load roles
        $query = User::select('id', 'name', 'email', 'is_active', 'created_at', 'updated_at', 'student_type')
            ->with('roles:id,name');

        $audienceQuery = User::query();

        if ($this->filterAudience === 'students') {
            $query->whereHas('roles', fn($q) => $q->where('name', 'student'));
            $audienceQuery->whereHas('roles', fn($q) => $q->where('name', 'student'));
        } elseif ($this->filterAudience === 'ict_students') {
            $query->whereHas('roles', fn($q) => $q->where('name', 'student'))
                ->where('student_type', 'ict');
            $audienceQuery->whereHas('roles', fn($q) => $q->where('name', 'student'))
                ->where('student_type', 'ict');
        } else {
            $query->whereDoesntHave('roles', fn($q) => $q->where('name', 'student'));
            $audienceQuery->whereDoesntHave('roles', fn($q) => $q->where('name', 'student'));
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterRole !== 'all') {
            $query->whereHas('roles', fn($q) => $q->where('name', $this->filterRole));
        }

        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }

        match($this->sortBy) {
            'recent' => $query->latest(),
            'oldest' => $query->oldest(),
            'name' => $query->orderBy('name'),
            'email' => $query->orderBy('email'),
            default => $query->latest(),
        };

        $users = $query->paginate(15);

        $stats = cache()->remember('user_stats_' . $this->filterAudience, 300, function () use ($audienceQuery) {
            $baseQuery = clone $audienceQuery;

            return [
                'total' => $baseQuery->count(),
                'active' => (clone $audienceQuery)->where('is_active', true)->count(),
                'inactive' => (clone $audienceQuery)->where('is_active', false)->count(),
            ];
        });

        $audienceCounts = cache()->remember('user_audience_counts', 300, function () {
            return [
                'staff' => User::whereDoesntHave('roles', fn($q) => $q->where('name', 'student'))->count(),
                'students' => User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count(),
                'ict_students' => User::whereHas('roles', fn($q) => $q->where('name', 'student'))
                    ->where('student_type', 'ict')
                    ->count(),
            ];
        });

        // Cache roles for 10 minutes
        $roles = cache()->remember('all_roles', 600, function () {
            return \App\Models\Role::select('id', 'name')->get();
        });

        return view('livewire.users.index', [
            'users' => $users,
            'stats' => $stats,
            'roles' => $roles,
            'audienceCounts' => $audienceCounts,
        ]);
    }
}
