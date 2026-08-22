<?php

namespace App\Livewire\Users;

use App\Models\CodeClubMembership;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterRole = 'all';
    public $filterStatus = 'all'; // 'all', 'active', 'inactive'
    public $sortBy = 'recent';
    public $filterAudience = 'staff'; // 'staff', 'students', 'ict_students'
    public string $filterProgram = 'all'; // 'all', 'codecamp', 'ict', 'codeclub'
    public string $filterSchool = 'all';

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
        'filterProgram' => ['except' => 'all'],
        'filterSchool' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterAudience()
    {
        $this->filterRole = 'all';
        $this->filterProgram = 'all';
        $this->filterSchool = 'all';
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function updatingFilterProgram(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSchool(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterRole = 'all';
        $this->filterStatus = 'all';
        $this->sortBy = 'recent';
        $this->filterProgram = 'all';
        $this->filterSchool = 'all';
        $this->resetPage();
    }

    public function deleteUser(int $userId): void
    {
        $user = User::with(['roles', 'studentProfile'])->findOrFail($userId);

        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        if ($user->isAdmin()) {
            $adminCount = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->count();
            if ($adminCount <= 1) {
                session()->flash('error', 'Cannot delete the last administrator account.');
                return;
            }
        }

        if ($user->courses()->exists()) {
            session()->flash('error', 'This user is assigned as instructor on one or more courses. Reassign those courses before deleting.');
            return;
        }

        if ($user->studentProfile) {
            $this->authorize('delete', $user->studentProfile);
        }

        $isStudent = (bool) $user->studentProfile;

        try {
            DB::transaction(function () use ($user) {
                CodeClubMembership::where('student_id', $user->id)->delete();

                if ($user->studentProfile) {
                    $user->studentProfile->delete();
                }

                $user->roles()->detach();
                $user->delete();
            });
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Unable to delete this user because related records still reference their account.');
            return;
        }

        $this->clearUserListCache();

        session()->flash('message', $isStudent
            ? 'Student account deleted successfully.'
            : 'User deleted successfully.');
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => ! $user->is_active]);
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
        if (! $this->selectedUser) {
            return;
        }

        // Generate an easier-to-type password (10 characters: letters and numbers only)
        // Format: 3 letters + 2 numbers + 3 letters + 2 numbers (easier to type and remember)
        $newPassword = strtolower(\Illuminate\Support\Str::random(3))
                       .rand(10, 99)
                       .strtolower(\Illuminate\Support\Str::random(3))
                       .rand(10, 99);

        // Update user's password
        $this->selectedUser->update([
            'password' => Hash::make($newPassword),
        ]);

        // Store the new password to display in modal
        $this->newPassword = $newPassword;
    }

    public function render()
    {
        // Optimize: Select only needed columns and eager load roles
        $query = User::select('id', 'name', 'email', 'student_id', 'is_active', 'created_at', 'updated_at', 'student_type', 'last_login_at', 'email_verified_at')
            ->with([
                'roles:id,name',
                'studentProfile:id,user_id,school_id,program_type,student_id,full_name',
                'studentProfile.school:id,name',
            ]);

        $audienceQuery = User::query();

        if ($this->filterAudience === 'students') {
            $query->whereHas('roles', fn ($q) => $q->where('name', 'student'));
            $audienceQuery->whereHas('roles', fn ($q) => $q->where('name', 'student'));
        } elseif ($this->filterAudience === 'ict_students') {
            $query->whereHas('roles', fn ($q) => $q->where('name', 'student'))
                ->where('student_type', 'ict');
            $audienceQuery->whereHas('roles', fn ($q) => $q->where('name', 'student'))
                ->where('student_type', 'ict');
        } else {
            $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'student'));
            $audienceQuery->whereDoesntHave('roles', fn ($q) => $q->where('name', 'student'));
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('student_id', 'like', '%'.$this->search.'%')
                    ->orWhereHas('studentProfile', function ($profileQuery) {
                        $profileQuery->where('full_name', 'like', '%'.$this->search.'%')
                            ->orWhere('student_id', 'like', '%'.$this->search.'%');
                    });
            });
        }

        if ($this->filterRole !== 'all') {
            $query->whereHas('roles', fn ($q) => $q->where('name', $this->filterRole));
        }

        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }

        if (in_array($this->filterAudience, ['students', 'ict_students'], true)) {
            $this->applyStudentFilters($query);
        }

        match ($this->sortBy) {
            'recent' => $query->latest(),
            'oldest' => $query->oldest(),
            'name' => $query->orderBy('name'),
            'email' => $query->orderBy('email'),
            default => $query->latest(),
        };

        $users = $query->paginate(15);

        $stats = cache()->remember('user_stats_'.$this->filterAudience, 300, function () use ($audienceQuery) {
            $baseQuery = clone $audienceQuery;

            return [
                'total' => $baseQuery->count(),
                'active' => (clone $audienceQuery)->where('is_active', true)->count(),
                'inactive' => (clone $audienceQuery)->where('is_active', false)->count(),
            ];
        });

        $audienceCounts = cache()->remember('user_audience_counts', 300, function () {
            return [
                'staff' => User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'student'))->count(),
                'students' => User::whereHas('roles', fn ($q) => $q->where('name', 'student'))->count(),
                'ict_students' => User::whereHas('roles', fn ($q) => $q->where('name', 'student'))
                    ->where('student_type', 'ict')
                    ->count(),
            ];
        });

        // Cache roles for 10 minutes
        $roles = cache()->remember('all_roles', 600, function () {
            return \App\Models\Role::select('id', 'name')->get();
        });

        $schools = in_array($this->filterAudience, ['students', 'ict_students'], true)
            ? School::orderBy('name')->get(['id', 'name'])
            : collect();

        $staffRoles = $roles->where('name', '!=', 'student');
        $visibleRoles = $this->filterAudience === 'staff' ? $staffRoles : $roles->where('name', 'student');

        return view('livewire.users.index', [
            'users' => $users,
            'stats' => $stats,
            'roles' => $visibleRoles,
            'audienceCounts' => $audienceCounts,
            'schools' => $schools,
        ]);
    }

    private function applyStudentFilters($query): void
    {
        if ($this->filterProgram !== 'all') {
            if ($this->filterProgram === 'ict') {
                $query->where('student_type', 'ict');
            } elseif ($this->filterProgram === 'codecamp') {
                $query->where('student_type', 'codecamp')
                    ->where(function ($sub) {
                        $sub->whereDoesntHave('studentProfile')
                            ->orWhereHas('studentProfile', fn ($p) => $p->where('program_type', 'codecamp'));
                    });
            } elseif ($this->filterProgram === 'codeclub') {
                $query->where(function ($sub) {
                    $sub->where('student_type', 'codeclub')
                        ->orWhereHas('studentProfile', fn ($p) => $p->where('program_type', 'codeclub'));
                });
            }
        }

        if ($this->filterSchool !== 'all') {
            $schoolId = (int) $this->filterSchool;
            $query->whereHas('studentProfile', fn ($p) => $p->where('school_id', $schoolId));
        }
    }

    private function clearUserListCache(): void
    {
        foreach (['staff', 'students', 'ict_students'] as $audience) {
            cache()->forget('user_stats_'.$audience);
        }

        cache()->forget('user_audience_counts');
    }
}
