<?php

namespace App\Livewire\Submissions;

use App\Models\AssignmentSubmission;
use App\Models\AssessmentAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public $submission;
    public $type;

    public function mount($submissionId, $type = 'assignment')
    {
        // Load the correct submission model based on type
        if ($type === 'assessment') {
            $this->submission = AssessmentAttempt::with(['assessment.course', 'user'])
                ->findOrFail($submissionId);
        } else {
            $this->submission = AssignmentSubmission::with(['assignment.course', 'user', 'grader'])
                ->findOrFail($submissionId);
        }
        
        $this->type = $type;
        
        // Authorization check
        $user = Auth::user();
        
        if ($user->hasRole('student')) {
            // Students can only view their own submissions
            if ($this->submission->user_id !== $user->id) {
                abort(403, 'You can only view your own submissions.');
            }
        } elseif ($user->hasRole('teacher')) {
            // Teachers can only view submissions from their courses
            if ($type === 'assessment') {
                $course = $this->submission->assessment->course ?? null;
                if (!$course || $course->instructor_id !== $user->id) {
                    abort(403, 'You can only view submissions from your own courses.');
                }
            } else {
                $course = $this->submission->assignment->course ?? null;
                if (!$course || $course->instructor_id !== $user->id) {
                    abort(403, 'You can only view submissions from your own courses.');
                }
            }
        } elseif (!$user->isAdmin()) {
            abort(403, 'You do not have permission to view this submission.');
        }
    }

    public function downloadAttachment($filePath)
    {
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found.');
        }
        
        // Authorization check
        $user = Auth::user();
        if ($user->hasRole('student') && $this->submission->user_id !== $user->id) {
            abort(403, 'You can only download attachments from your own submissions.');
        }
        
        return Storage::disk('public')->download($filePath);
    }

    public function render()
    {
        $isOverdue = false;
        $isGraded = false;
        $percentage = null;
        
        if ($this->type === 'assignment') {
            $isOverdue = $this->submission->assignment 
                && $this->submission->assignment->due_date 
                && $this->submission->assignment->due_date->isPast() 
                && !$this->submission->graded_at;
            
            $isGraded = $this->submission->graded_at !== null;
            $percentage = $this->submission->assignment 
                && $this->submission->points_earned 
                && $this->submission->assignment->max_points
                ? ($this->submission->points_earned / $this->submission->assignment->max_points) * 100
                : null;
        } else {
            // Assessment attempt
            $isGraded = $this->submission->score !== null;
            $percentage = $this->submission->score;
        }

        return view('livewire.submissions.show', [
            'isOverdue' => $isOverdue,
            'isGraded' => $isGraded,
            'percentage' => $percentage,
        ]);
    }
}
