<?php

namespace App\Livewire\Submissions;

use App\Models\AssignmentSubmission;
use App\Models\AssessmentAttempt;
use App\Support\SubmissionFile;
use Illuminate\Support\Facades\Auth;
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
        
        // Authorization check — staff identity wins over leftover student role
        $user = Auth::user();

        if ($user->isAdmin() || $user->isSupervisor()) {
            // full access
        } elseif ($user->isIctTeacher()) {
            if ($type === 'assessment') {
                $schoolId = $user->ictSchoolId();
                if (! $schoolId || $this->submission->student_type !== 'ict' || (int) $this->submission->school_id !== (int) $schoolId) {
                    abort(403, 'You can only view submissions from your school.');
                }
            } else {
                abort(403, 'You can only view submissions from your school.');
            }
        } elseif ($user->isTeacher()) {
            if ($type === 'assessment') {
                if ($this->submission->student_type !== 'codecamp') {
                    abort(403, 'You can only view submissions from your own courses.');
                }

                $course = $this->submission->assessment->course ?? null;
                if (! $course || ! $course->isStaffFor($user)) {
                    abort(403, 'You can only view submissions from your own courses.');
                }
            } else {
                $course = $this->submission->assignment->course ?? null;
                if (! $course || ! $course->isStaffFor($user)) {
                    abort(403, 'You can only view submissions from your own courses.');
                }
            }
        } elseif ($user->isStudent() || $user->hasRole('student')) {
            if ($this->submission->user_id !== $user->id) {
                abort(403, 'You can only view your own submissions.');
            }
        } else {
            abort(403, 'You do not have permission to view this submission.');
        }
    }

    public function downloadAttachment($filePath, $fileName = null)
    {
        $user = Auth::user();
        if ($user->hasRole('student') && $this->submission->user_id !== $user->id) {
            abort(403, 'You can only download attachments from your own submissions.');
        }

        return SubmissionFile::downloadResponse(
            (string) $filePath,
            $fileName ? (string) $fileName : null
        );
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
            $maxPoints = $this->submission->assignment?->max_points ?? 0;
            $pointsEarned = $this->submission->points_earned;
            $percentage = ($isGraded && $maxPoints > 0)
                ? round(((float) $pointsEarned / $maxPoints) * 100, 1)
                : ($isGraded ? 0.0 : null);
        } else {
            // Assessment attempt
            $isGraded = $this->submission->score !== null;
            $percentage = $isGraded ? $this->submission->scorePercentage() : null;
        }

        return view('livewire.submissions.show', [
            'isOverdue' => $isOverdue,
            'isGraded' => $isGraded,
            'percentage' => $percentage,
        ]);
    }
}
