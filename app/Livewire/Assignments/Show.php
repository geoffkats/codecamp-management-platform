<?php

namespace App\Livewire\Assignments;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Show extends Component
{
    use WithFileUploads;

    public Assignment $assignment;
    public $submission = null;
    public $submissionText = '';
    public $submissionFiles = [];
    public $feedback = null;

    public function mount(Assignment $assignment)
    {
        $this->assignment = $assignment->load(['course', 'lesson', 'creator', 'submissions' => fn($q) => $q->where('user_id', Auth::id())]);
        
        // Check access
        $user = Auth::user();
        $isInstructor = $this->assignment->course->instructor_id === $user->id || 
                       $user->hasRole('admin') || 
                       $user->hasRole('supervisor');
        
        // Check if assignment is locked for students
        if (!$isInstructor && $this->assignment->is_locked) {
            // Assignment is locked - students cannot access
            return; // Will show locked view
        }
        
        if ($user->hasRole('student')) {
            $isEnrolled = $this->assignment->course->enrollments()
                ->where('user_id', Auth::id())
                ->exists();
            
            if (!$isEnrolled) {
                abort(403, 'You must be enrolled in this course to view the assignment.');
            }
        }

        // Get existing submission
        $this->submission = $this->assignment->submissions->first();
        if ($this->submission) {
            $this->submissionText = $this->submission->content ?? '';
            $this->submissionFiles = $this->submission->attachments ?? [];
            $this->feedback = $this->submission->feedback;
        }
    }

    public function submit()
    {
        // Check enrollment
        $isEnrolled = $this->assignment->course->enrollments()
            ->where('user_id', Auth::id())
            ->exists();
        
        if (!$isEnrolled) {
            session()->flash('error', 'You must be enrolled in this course to submit assignments.');
            return;
        }
        
        // Check due date
        if ($this->assignment->due_date && now() > $this->assignment->due_date) {
            session()->flash('error', 'Assignment deadline has passed.');
            return;
        }
        
        // Check max submission attempts
        $attempts = $this->assignment->submissions()
            ->where('user_id', Auth::id())
            ->where('status', 'submitted')
            ->count();
        
        if ($this->assignment->max_submissions && $attempts >= $this->assignment->max_submissions) {
            session()->flash('error', 'Maximum submission attempts reached.');
            return;
        }
        
        // Validate that at least one submission method is provided
        $hasText = !empty($this->submissionText) && strlen(trim($this->submissionText)) >= 10;
        $hasFiles = !empty($this->submissionFiles) && count(array_filter($this->submissionFiles)) > 0;
        
        if (!$hasText && !$hasFiles) {
            $this->addError('submissionText', 'Please provide either text submission or upload files.');
            return;
        }
        
        $this->validate([
            'submissionText' => 'nullable|min:10',
            'submissionFiles.*' => 'file|max:10240', // 10MB max
        ], [
            'submissionText.min' => 'Text submission must be at least 10 characters if provided.',
            'submissionFiles.*.max' => 'Each file must not exceed 10MB.',
        ]);

        $files = [];
        if (!empty($this->submissionFiles)) {
            foreach ($this->submissionFiles as $file) {
                if ($file) {
                    $files[] = $file->store('assignments', 'public');
                }
            }
        }

        if ($this->submission) {
            // Update existing submission
            $this->submission->update([
                'content' => $this->submissionText,
                'attachments' => array_merge($this->submission->attachments ?? [], $files),
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        } else {
            // Create new submission
            $this->submission = AssignmentSubmission::create([
                'user_id' => Auth::id(),
                'assignment_id' => $this->assignment->id,
                'content' => $this->submissionText,
                'attachments' => $files,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        }

        // Award XP for submission
        if ($this->assignment->lesson && $this->assignment->lesson->xp_reward) {
            $user = Auth::user();
            if (!$user->points) {
                \App\Models\UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                ]);
            }
            $user->points->increment('total_points', $this->assignment->lesson->xp_reward);
        }

        session()->flash('message', 'Assignment submitted successfully!');
        
        // Dispatch event for lesson view to refresh
        $this->dispatch('assignment-submitted');
        
        $this->mount($this->assignment); // Reload
    }

    public function render()
    {
        $isOverdue = $this->assignment->due_date && $this->assignment->due_date->isPast();
        $isSubmitted = $this->submission && $this->submission->submitted_at;
        $isGraded = $this->submission && $this->submission->graded_at;
        
        // Get submission stats for teachers
        $submissionStats = null;
        if (Auth::user()->hasAnyRole(['teacher', 'admin'])) {
            $submissionStats = [
                'total' => $this->assignment->submissions()->count(),
                'graded' => $this->assignment->submissions()->whereNotNull('graded_at')->count(),
                'pending' => $this->assignment->submissions()->whereNull('graded_at')->count(),
                'average_score' => $this->assignment->submissions()->whereNotNull('points_earned')->avg('points_earned') ?? 0,
            ];
        }

        return view('livewire.assignments.show', [
            'isOverdue' => $isOverdue,
            'isSubmitted' => $isSubmitted,
            'isGraded' => $isGraded,
            'submissionStats' => $submissionStats,
        ]);
    }
}
