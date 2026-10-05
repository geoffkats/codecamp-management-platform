<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Notification;
use App\Services\Assessments\AssessmentGradingService;
use App\Services\Assessments\AttemptGrade;
use App\Services\Assessments\AttemptGradeRecorder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Results extends Component
{
    public Assessment $assessment;
    public AssessmentAttempt $attempt;
    public $percentage = null;
    public $maxScore = 100;
    public $isPending = false;
    public $incorrectQuestions = [];
    public $correctQuestions = [];

    // Teacher grading
    public bool $showGradeForm = false;
    public string $gradeScore = '';
    public string $gradeFeedback = '';

    protected ?AttemptGrade $grade = null;

    public function mount(Assessment $assessment, AssessmentAttempt $attempt)
    {
        $this->assessment = $assessment->load(['course', 'lesson']);
        $this->attempt = $attempt->load(['assessment.course', 'user']);

        if ($this->attempt->assessment_id !== $this->assessment->id) {
            abort(404, 'Assessment attempt not found.');
        }

        $this->authorizeAccess();

        $this->maxScore = $this->attempt->maxScore();
        // Pending = submitted but not yet scored (waiting for teacher review)
        $this->isPending = ! $this->attempt->auto_scored && $this->attempt->score === null;

        if ($this->isPending) {
            $this->percentage = null;
            $this->hydrateGradeForm();

            return;
        }

        $this->percentage = $this->attempt->scorePercentage();
        $this->hydrateGradeForm();

        $this->incorrectQuestions = $this->buildIncorrectQuestions();
        $this->correctQuestions = $this->buildCorrectQuestions();
    }

    public function hydrate(): void
    {
        $this->assessment->loadMissing(['course', 'lesson']);
        $this->attempt->loadMissing(['user', 'assessment.course']);
        $this->maxScore = $this->attempt->maxScore();
        $this->isPending = ! $this->attempt->auto_scored && $this->attempt->score === null;
        $this->percentage = $this->isPending ? null : $this->attempt->scorePercentage();
    }

    protected function hydrateGradeForm(): void
    {
        if ($this->attempt->score === null) {
            // Suggest auto-earned points so instructor only needs to add short-answer credit
            $suggested = round($this->grade()->earned, 1);
            $this->gradeScore = $suggested > 0 ? (string) $suggested : '';
            $this->gradeFeedback = '';

            return;
        }

        $this->gradeScore = (string) ($this->attempt->scoreAsPoints() ?? '');
        $answers = $this->attempt->answers ?? [];
        $this->gradeFeedback = (string) ($answers['feedback'] ?? '');
    }

    protected function grade(): AttemptGrade
    {
        return $this->grade ??= app(AssessmentGradingService::class)->gradeAttempt($this->attempt);
    }

    protected function buildCorrectQuestions(): array
    {
        // Prefer full review for instructors; keep student summary for fully auto-scored attempts
        if (! $this->attempt->auto_scored) {
            return [];
        }

        return collect($this->grade()->rows)
            ->filter(fn ($row) => $row['needs_manual'] === false && $row['is_correct'] === true)
            ->map(fn ($row) => [
                'question' => $row['question'],
                'your_answer' => $row['student_answer'],
            ])
            ->values()
            ->all();
    }

    protected function buildIncorrectQuestions(): array
    {
        if (! $this->attempt->auto_scored) {
            return [];
        }

        return collect($this->grade()->rows)
            ->filter(fn ($row) => $row['needs_manual'] === false && $row['is_correct'] === false)
            ->map(fn ($row) => [
                'question' => $row['question'],
                'your_answer' => $row['student_answer'],
                'correct_answer' => $row['correct_answer'],
            ])
            ->values()
            ->all();
    }

    protected function authorizeAccess(): void
    {
        \App\Support\SubmissionAccess::authorizeView(Auth::user(), $this->attempt);
    }

    public function submitGrade(): void
    {
        $user = Auth::user();
        if (!$user->can('grade_submissions')) {
            return;
        }

        $this->validate([
            'gradeScore'    => 'required|numeric|min:0|max:' . ($this->maxScore ?: 100),
            'gradeFeedback' => 'nullable|string|max:2000',
        ]);

        $result = app(AttemptGradeRecorder::class)->record(
            $this->attempt,
            (float) $this->gradeScore,
            $this->gradeFeedback,
            $user,
            null,
            (float) $this->maxScore,
        );
        $isPassed = $result['passed'];

        // Recalculate percentage for this view
        $this->percentage = $result['percentage'];
        $this->isPending = false;
        $this->attempt->refresh();
        $this->grade = null;

        // Notify the student
        if ($this->attempt->user_id) {
            Notification::create([
                'user_id' => $this->attempt->user_id,
                'title'   => 'Assignment Graded',
                'message' => "Your assignment \"{$this->assessment->title}\" has been graded: "
                           . round($this->percentage, 1) . "% — "
                           . ($isPassed ? 'Passed' : 'Not passed')
                           . ($this->gradeFeedback ? ". Feedback: {$this->gradeFeedback}" : ''),
                'type'    => 'assignment_graded',
                'data'    => [
                    'assessment_id' => $this->assessment->id,
                    'attempt_id'    => $this->attempt->id,
                    'score'         => $this->gradeScore,
                    'percentage'    => $this->percentage,
                    'feedback'      => $this->gradeFeedback,
                ],
            ]);
        }

        $this->showGradeForm = false;
        session()->flash('message', 'Grade submitted and student notified.');
    }

    public function render()
    {
        $grade = $this->grade();
        $reviewQuestions = $grade->rows;

        return view('livewire.assessments.results', [
            'canGrade' => Auth::user()->can('grade_submissions'),
            'reviewQuestions' => $reviewQuestions,
            'autoEarnedPoints' => round($grade->earned, 1),
            'autoGradablePoints' => round($grade->autoMax, 1),
            'manualQuestionCount' => collect($reviewQuestions)->where('needs_manual', true)->count(),
            'submissionFiles' => $this->attempt->submissionFiles(),
        ]);
    }
}
