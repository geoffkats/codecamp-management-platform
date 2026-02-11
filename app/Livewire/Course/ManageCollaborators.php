<?php

namespace App\Livewire\Course;

use App\Models\Course;
use App\Models\CourseCollaborator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

    public function addCollaborator()
    {
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
        $collaborator = CourseCollaborator::findOrFail($collaboratorId);
        
        if ($collaborator->course_id !== $this->course->id) {
            session()->flash('error', 'Invalid collaborator.');
            return;
        }

        $collaborator->update(['role' => $newRole]);
        session()->flash('message', 'Role updated successfully!');
        $this->course->refresh();
    }

    public function render()
    {
        // Get available users (teachers who aren't already collaborators)
        $availableUsers = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['teacher', 'ict_teacher']);
            })
            ->where('id', '!=', $this->course->instructor_id)
            ->whereNotIn('id', $this->course->collaborators->pluck('user_id'))
            ->when($this->searchTerm, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('email', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->limit(10)
            ->get();

        return view('livewire.course.manage-collaborators', [
            'collaborators' => $this->course->collaborators()->with('user')->get(),
            'availableUsers' => $availableUsers,
        ]);
    }
}
