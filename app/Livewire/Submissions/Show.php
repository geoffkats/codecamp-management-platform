<?php

namespace App\Livewire\Submissions;

use App\Models\AssignmentSubmission;
use App\Models\AssessmentAttempt;
use App\Services\AssessmentAttemptReview;
use App\Support\SubmissionAccess;
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
        $type = in_array($type, ['assessment', 'assignment'], true) ? $type : 'assignment';

        if ($type === 'assessment') {
            $this->submission = AssessmentAttempt::with(['assessment.course', 'assessment.questions.options', 'user'])
                ->find($submissionId);

            if (! $this->submission) {
                $this->submission = AssignmentSubmission::with(['assignment.course', 'user', 'grader'])
                    ->findOrFail($submissionId);
                $type = 'assignment';
            }
        } else {
            $this->submission = AssignmentSubmission::with(['assignment.course', 'user', 'grader'])
                ->find($submissionId);

            if (! $this->submission) {
                $this->submission = AssessmentAttempt::with(['assessment.course', 'assessment.questions.options', 'user'])
                    ->findOrFail($submissionId);
                $type = 'assessment';
            }
        }

        $this->type = $type;

        SubmissionAccess::authorizeView(Auth::user(), $this->submission);
    }

    public function downloadAttachment($filePath, $fileName = null)
    {
        SubmissionAccess::authorizeView(Auth::user(), $this->submission);

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
        $reviewQuestions = [];
        $autoCorrectCount = 0;
        $autoIncorrectCount = 0;
        $manualCount = 0;

        if ($this->type === 'assignment') {
            $isOverdue = $this->submission->assignment
                && $this->submission->assignment->due_date
                && $this->submission->assignment->due_date->isPast()
                && ! $this->submission->graded_at;

            $isGraded = $this->submission->graded_at !== null;
            $maxPoints = $this->submission->assignment?->max_points ?? 0;
            $pointsEarned = $this->submission->points_earned;
            $percentage = ($isGraded && $maxPoints > 0)
                ? round(((float) $pointsEarned / $maxPoints) * 100, 1)
                : ($isGraded ? 0.0 : null);
        } else {
            $isGraded = $this->submission->score !== null;
            $percentage = $isGraded ? $this->submission->scorePercentage() : null;
            $this->submission->loadMissing(['assessment.questions.options']);
            $reviewQuestions = app(AssessmentAttemptReview::class)->rows($this->submission);
            $autoCorrectCount = collect($reviewQuestions)->where('needs_manual', false)->where('is_correct', true)->count();
            $autoIncorrectCount = collect($reviewQuestions)->where('needs_manual', false)->where('is_correct', false)->count();
            $manualCount = collect($reviewQuestions)->where('needs_manual', true)->count();
        }

        return view('livewire.submissions.show', [
            'isOverdue' => $isOverdue,
            'isGraded' => $isGraded,
            'percentage' => $percentage,
            'reviewQuestions' => $reviewQuestions,
            'autoCorrectCount' => $autoCorrectCount,
            'autoIncorrectCount' => $autoIncorrectCount,
            'manualCount' => $manualCount,
        ]);
    }
}
