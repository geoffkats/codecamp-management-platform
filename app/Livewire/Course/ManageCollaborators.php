<?php

namespace App\Livewire\Course;

use App\Models\Course;
use App\Models\CourseCollaborator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class ManageCollaborators extends Component
{
    public Course $course;
    public $showAddModal = false;
    public $selectedUserId = null;
    public $selectedRole = 'editor';
    public $searchTerm = '';

    protected $rules = [
        'selectedUserId' => 'required|exists:users,id',
        'selectedRole' => 'required|in:editor,viewer',
    ];

    public function mount(Course $course)
    {
        $this->course = $course;
        $this->authorizeCollaboratorManagement();
    }

    protected function authorizeCollaboratorManagement(): void
    {
        $user = Auth::user();

        $canManage = $user->isAdmin()
            || $user->isSupervisor()
            || $this->course->instructor_id === $user->id
            || $user->hasPermission('edit_courses');

        abort_unless($canManage, 403, 'You do not have permission to manage course collaborators.');
    }

    public function openAddModal()
    {
        $this->showAddModal = true;
        $this->reset(['selectedUserId', 'selectedRole', 'searchTerm']);
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->reset(['selectedUserId', 'selectedRole', 'searchTerm']);
    }

    public function selectUser(int $userId): void
    {
        $this->authorizeCollaboratorManagement();

        $user = $this->availableUsers->firstWhere('id', $userId);

        if (!$user) {
            session()->flash('error', 'Selected user is not available for this course.');
            return;
        }

        $this->selectedUserId = $userId;
        $this->searchTerm = $user->name;
    }

    public function clearSelectedUser(): void
    {
        $this->selectedUserId = null;
        $this->searchTerm = '';
    }

    public function addCollaborator()
    {
        $this->authorizeCollaboratorManagement();
        $this->validate();

        // Check if user is already a collaborator
        $exists = CourseCollaborator::where('course_id', $this->course->id)
            ->where('user_id', $this->selectedUserId)
            ->exists();

        if ($exists) {
            session()->flash('error', 'This user is already a collaborator.');
            return;
        }

        // Check if user is the course instructor
        if ($this->course->instructor_id == $this->selectedUserId) {
            session()->flash('error', 'The course instructor is already an owner.');
            return;
        }

        CourseCollaborator::create([
            'course_id' => $this->course->id,
            'user_id' => $this->selectedUserId,
            'role' => $this->selectedRole,
            'invited_at' => now(),
            'invited_by' => Auth::id(),
        ]);

        session()->flash('message', 'Collaborator added successfully!');
        $this->closeAddModal();
        $this->course->refresh();
    }

    public function removeCollaborator($collaboratorId)
    {
        $this->authorizeCollaboratorManagement();
        $collaborator = CourseCollaborator::findOrFail($collaboratorId);
        
        if ($collaborator->course_id !== $this->course->id) {
            session()->flash('error', 'Invalid collaborator.');
            return;
        }

        $collaborator->delete();
        session()->flash('message', 'Collaborator removed successfully!');
        $this->course->refresh();
    }

    public function updateRole($collaboratorId, $newRole)
    {
        $this->authorizeCollaboratorManagement();
        $collaborator = CourseCollaborator::findOrFail($collaboratorId);
        
        if ($collaborator->course_id !== $this->course->id) {
            session()->flash('error', 'Invalid collaborator.');
            return;
        }

        $collaborator->update(['role' => $newRole]);
        session()->flash('message', 'Role updated successfully!');
        $this->course->refresh();
    }

    public function getAvailableUsersProperty()
    {
        $baseQuery = User::query()
            ->with('roles:id,name,display_name')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['teacher', 'ict_teacher', 'codecamp_trainer']);
            })
            ->where('id', '!=', $this->course->instructor_id)
            ->whereNotIn('id', $this->course->collaborators->pluck('user_id'));

        $search = trim($this->searchTerm);

        if ($search !== '') {
            $normalized = Str::lower($search);
            $tokens = collect(preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY))
                ->filter()
                ->values();

            foreach ($tokens as $token) {
                $baseQuery->where(function ($query) use ($token) {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$token}%"])
                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$token}%"])
                        ->orWhereHas('roles', function ($roleQuery) use ($token) {
                            $roleQuery->whereRaw('LOWER(name) LIKE ?', ["%{$token}%"])
                                ->orWhereRaw('LOWER(display_name) LIKE ?', ["%{$token}%"]);
                        });
                });
            }

            $escaped = addcslashes($normalized, '%_');

            $baseQuery->orderByRaw(
                "CASE
                    WHEN LOWER(name) = ? THEN 0
                    WHEN LOWER(email) = ? THEN 1
                    WHEN LOWER(name) LIKE ? THEN 2
                    WHEN LOWER(email) LIKE ? THEN 3
                    ELSE 4
                END",
                [$normalized, $normalized, "{$escaped}%", "{$escaped}%"]
            );
        }

        return $baseQuery
            ->orderBy('name')
            ->limit($search === '' ? 6 : 10)
            ->get();
    }

    public function getSelectedUserProperty(): ?User
    {
        if (!$this->selectedUserId) {
            return null;
        }

        return $this->availableUsers->firstWhere('id', (int) $this->selectedUserId)
            ?? User::with('roles:id,name,display_name')->find($this->selectedUserId);
    }

    public function render()
    {
        $this->authorizeCollaboratorManagement();

        return view('livewire.course.manage-collaborators', [
            'collaborators' => $this->course->collaborators()->with('user')->get(),
            'availableUsers' => $this->availableUsers,
            'selectedUser' => $this->selectedUser,
        ]);
    }
}
