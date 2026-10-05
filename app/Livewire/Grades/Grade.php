<?php

namespace App\Livewire\Grades;

use App\Models\AssignmentSubmission;
use App\Models\AssessmentAttempt;
use App\Models\Grade as GradeModel;
use App\Services\AssessmentAttemptReview;
use App\Services\Assessments\AttemptGradeRecorder;
use App\Services\TrainerSubmissionQueue;
use App\Support\SubmissionAccess;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Grade extends Component
{
    public $submission; // Can be AssignmentSubmission or AssessmentAttempt
    public $submissionType = 'assignment'; // 'assignment' or 'assessment'
    public $rubricScores = [];
    public $totalScore = 0;
    public $maxScore = 0;
    public $percentage = 0;
    public $letterGrade = '';
    public $feedback = '';
    public $rubricCriteria = [];
    public $submissionContent = '';
    public $submissionFiles = [];
    public $assessmentQuestions = []; // For displaying question-based file uploads
    public array $questionScores = [];
    public array $reviewQuestions = [];

    public function mount($submission)
    {
        // Authorization check
        $user = Auth::user();
        
        // Determine submission type and load accordingly
        if ($submission instanceof AssignmentSubmission) {
            $this->submissionType = 'assignment';
            $this->submission = $submission->load(['assignment.course', 'user']);
            $assignment = $this->submission->assignment;
            $course = $assignment->course ?? null;
            
            // Load submission content
            $this->submissionContent = $this->submission->content ?? '';
            $this->submissionFiles = $this->submission->attachments ?? [];
            
        } elseif ($submission instanceof AssessmentAttempt) {
            $this->submissionType = 'assessment';
            $this->submission = $submission->load(['assessment.course', 'assessment.questions.options', 'user']);
            
            // Allow any assessment attempt that requires manual grading
            // (assignment-type OR mixed assessments with file/essay questions)
            
            $assignment = $this->submission->assessment; // Treat assessment as assignment
            $course = $assignment->course ?? null;
            
            // Extract content and files from answers JSON
            $this->submissionContent = $this->submission->submissionText();
            $this->submissionFiles = $this->submission->submissionFiles();
            $this->assessmentQuestions = $this->submission->assessment->questions ?? collect();
        } else {
            $user = Auth::user();
            $assignmentSubmission = AssignmentSubmission::with('assignment.course')->find($submission);
            $assessmentAttempt = AssessmentAttempt::with(['assessment.course', 'assessment.questions.options'])->find($submission);

            if ($assessmentAttempt && $this->attemptCanBeGraded($assessmentAttempt) && $user && SubmissionAccess::canView($user, $assessmentAttempt)) {
                $this->mount($assessmentAttempt);
                return;
            }

            if ($assignmentSubmission && $user && SubmissionAccess::canView($user, $assignmentSubmission)) {
                $this->mount($assignmentSubmission);
                return;
            }

            if ($assessmentAttempt && $this->attemptCanBeGraded($assessmentAttempt)) {
                $this->mount($assessmentAttempt);
                return;
            }

            if ($assignmentSubmission) {
                $this->mount($assignmentSubmission);
                return;
            }

            abort(404, 'Submission not found.');
        }

        SubmissionAccess::authorizeGrade($user, $this->submission);
        
        // Get assignment/assessment reference
        $assignment = $this->submissionType === 'assignment' 
            ? $this->submission->assignment 
            : $this->submission->assessment;
        
        // Check if assignment has a linked rubric assessment
        if ($this->submissionType === 'assignment') {
            $rubricAssessment = $assignment->lesson?->assessments()
                ->where('assessment_type', 'rubric_assessment')
                ->first();
        } else {
            // For assessment attempts, check if assessment itself has rubric
            $rubricAssessment = null;
            if (isset($assignment->rubric_criteria) && is_array($assignment->rubric_criteria)) {
                $this->rubricCriteria = $assignment->rubric_criteria;
            }
        }
        
        if (isset($rubricAssessment) && $rubricAssessment && $rubricAssessment->rubric_criteria) {
            $this->rubricCriteria = $rubricAssessment->rubric_criteria;
            // Initialize rubric scores
            foreach ($this->rubricCriteria as $index => $criterion) {
                $this->rubricScores[$index] = [
                    'score' => 0,
                    'max_points' => $criterion['max_points'] ?? 0,
                    'feedback' => '',
                ];
            }
        }

        // Load existing grade if exists
        $gradeableType = $this->submissionType === 'assignment' 
            ? AssignmentSubmission::class 
            : AssessmentAttempt::class;
            
        $existingGrade = GradeModel::where('user_id', $this->submission->user_id)
            ->where('course_id', $assignment->course_id)
            ->where('gradeable_type', $gradeableType)
            ->where('gradeable_id', $this->submission->id)
            ->first();

        if ($existingGrade) {
            $this->totalScore = (float) $existingGrade->score;
            $this->maxScore = (float) $existingGrade->max_score;
            $this->percentage = (float) $existingGrade->percentage;
            $this->letterGrade = $existingGrade->letter_grade;
            
            // Extract feedback from JSON
            $feedbackData = json_decode($existingGrade->feedback, true);
            if (is_array($feedbackData)) {
                $this->feedback = $feedbackData['feedback'] ?? $existingGrade->feedback;
                
                // Load rubric scores from feedback or grade data
                if (isset($feedbackData['rubric_scores'])) {
                    $this->rubricScores = $feedbackData['rubric_scores'];
                }
            } else {
                $this->feedback = $existingGrade->feedback;
            }
        } elseif ($this->submissionType === 'assessment' && $this->submission->isGraded()) {
            $this->maxScore = $this->submission->maxScore();
            $this->totalScore = (float) ($this->submission->scoreAsPoints() ?? 0);
            $this->percentage = (float) ($this->submission->scorePercentage() ?? 0);
            $this->letterGrade = $this->calculateLetterGrade($this->percentage);
            $this->feedback = $this->submission->graderFeedback() ?? '';
        } else {
            // Set max score based on assignment/assessment
            if ($this->submissionType === 'assignment') {
                $this->maxScore = $assignment->max_points ?? 100;
            } else {
                $this->maxScore = $this->submission->maxScore();
            }
        }

        $this->loadQuestionScores();
        $this->calculateTotal();
    }

    public function updatedTotalScore()
    {
        $this->totalScore = (float) ($this->totalScore ?? 0);
        $this->calculateTotal();
    }

    public function updatedRubricScores()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        if (!empty($this->rubricCriteria)) {
            // Calculate from rubric
            $total = 0;
            $maxTotal = 0;

            foreach ($this->rubricScores as $index => $score) {
                $total += (float) ($score['score'] ?? 0);
                $maxTotal += (float) ($score['max_points'] ?? 0);
            }
            
            $this->totalScore = $total;
            $assignment = $this->submissionType === 'assignment' 
                ? $this->submission->assignment 
                : $this->submission->assessment;
            $this->maxScore = $maxTotal > 0 ? $maxTotal : ($assignment->max_points ?? 100);
        } else {
            // Manual scoring - max score already set in mount
            // Just ensure it's not 0
            if ($this->maxScore == 0) {
                $assignment = $this->submissionType === 'assignment' 
                    ? $this->submission->assignment 
                    : $this->submission->assessment;
                $this->maxScore = $assignment->max_points ?? 100;
            }
        }

        $this->percentage = $this->maxScore > 0 
            ? round(($this->totalScore / $this->maxScore) * 100, 2) 
            : 0;

        // Calculate letter grade
        $this->letterGrade = $this->calculateLetterGrade($this->percentage);
    }

    public function updatedQuestionScores(): void
    {
        $this->recalculateFromQuestionScores();
    }

    private function loadQuestionScores(): void
    {
        if ($this->submissionType !== 'assessment') {
            return;
        }

        $review = app(AssessmentAttemptReview::class)->rows($this->submission);
        $this->reviewQuestions = $review;
        $saved = $this->submission->answers['question_scores'] ?? [];

        foreach ($review as $row) {
            $id = (string) $row['id'];
            if ($row['needs_manual']) {
                $this->questionScores[$id] = (float) ($saved[$row['id']] ?? $saved[$id] ?? 0);
            } else {
                $this->questionScores[$id] = (float) ($row['earned'] ?? 0);
            }
        }

        if ($this->submission->score === null && $this->questionScores !== []) {
            $this->recalculateFromQuestionScores();
        }
    }

    private function recalculateFromQuestionScores(): void
    {
        if ($this->questionScores === []) {
            return;
        }

        // Livewire number inputs arrive as strings; cast before summing.
        $this->questionScores = collect($this->questionScores)
            ->mapWithKeys(fn ($score, $id) => [(string) $id => (float) ($score === '' || $score === null ? 0 : $score)])
            ->all();

        $this->totalScore = round(array_sum($this->questionScores), 2);
        $this->calculateTotal();
    }

    private function attemptCanBeGraded(AssessmentAttempt $attempt): bool
    {
        return $attempt->status === 'completed' || $attempt->completed_at !== null;
    }

    private function calculateLetterGrade($percentage)
    {
        if ($percentage >= 97) return 'A+';
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 63) return 'D';
        if ($percentage >= 60) return 'D-';
        return 'F';
    }

    public function save()
    {
        $this->validate([
            'totalScore' => 'required|numeric|min:0|max:' . $this->maxScore,
            'feedback' => 'nullable|string',
        ]);

        $assignment = $this->submissionType === 'assignment' 
            ? $this->submission->assignment 
            : $this->submission->assessment;
        
        // Prepare feedback data
        $feedbackData = [
            'feedback' => $this->feedback,
        ];
        
        if (!empty($this->rubricScores)) {
            $feedbackData['rubric_scores'] = $this->rubricScores;
        }

        if ($this->submissionType === 'assignment') {
            GradeModel::updateOrCreate(
                [
                    'user_id' => $this->submission->user_id,
                    'course_id' => $assignment->course_id,
                    'gradeable_type' => AssignmentSubmission::class,
                    'gradeable_id' => $this->submission->id,
                ],
                [
                    'score' => $this->totalScore,
                    'max_score' => $this->maxScore,
                    'percentage' => $this->percentage,
                    'letter_grade' => $this->letterGrade,
                    'feedback' => json_encode($feedbackData),
                    'graded_by' => Auth::id(),
                    'graded_at' => now(),
                    'is_final' => true,
                ]
            );

            $this->submission->update([
                'points_earned' => $this->totalScore,
                'feedback' => $this->feedback,
                'status' => 'graded',
                'graded_at' => now(),
                'graded_by' => Auth::id(),
            ]);
        } else {
            app(AttemptGradeRecorder::class)->record(
                $this->submission,
                (float) $this->totalScore,
                $this->feedback,
                Auth::user(),
                $this->questionScores !== [] ? $this->questionScores : null,
                (float) $this->maxScore,
                array_diff_key($feedbackData, ['feedback' => true]),
            );
        }

        // Award XP once when first passed (avoid re-awarding on grade edits)
        $xpReward = $assignment->xp_reward ?? ($assignment->lesson?->xp_reward ?? null);
        $answersForXp = $this->submissionType === 'assessment' ? ($this->submission->answers ?? []) : [];
        $alreadyAwarded = ! empty($answersForXp['xp_awarded']);
        if ($this->percentage >= 70 && $xpReward && ! $alreadyAwarded) {
            $user = $this->submission->user;
            $courseId = (int) ($assignment->course_id ?? $assignment->lesson?->module?->course_id ?? 0);
            $lessonId = $assignment->lesson_id ? (int) $assignment->lesson_id : null;

            if ($courseId > 0) {
                app(\App\Services\PointsService::class)->awardTrackedCourseXp(
                    (int) $user->id,
                    $courseId,
                    (int) $xpReward,
                    'quiz_completed',
                    $lessonId,
                    ['source' => 'grading']
                );
            } else {
                $points = app(\App\Services\PointsService::class)->ensureUserPoints($user);
                $points->addPoints((int) $xpReward);
            }

            if ($this->submissionType === 'assessment') {
                $answersForXp['xp_awarded'] = true;
                $this->submission->update(['answers' => $answersForXp]);
            }
        }

        // Send notification to student
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notify(
                $this->submission->user,
                'Assignment Graded',
                "Your " . ($this->submissionType === 'assignment' ? 'assignment' : 'assessment') . " '{$assignment->title}' has been graded. You received {$this->totalScore}/{$this->maxScore} ({$this->percentage}%).",
                'success',
                [
                    'assignment_id' => $assignment->id,
                    'score' => $this->totalScore,
                    'max_score' => $this->maxScore,
                    'percentage' => $this->percentage,
                    'action_url' => route('submissions.show', $this->submission),
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send grade notification', ['error' => $e->getMessage()]);
        }

        session()->flash('message', 'Grade saved successfully!');

        TrainerSubmissionQueue::forgetCache(Auth::user());

        return $this->redirect(route('submissions.index', ['filter' => 'pending']), navigate: true);
    }

    public function render()
    {
        return view('livewire.grades.grade');
    }
}
