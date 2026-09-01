<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Notification;
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

    public function mount(Assessment $assessment, AssessmentAttempt $attempt)
    {
        $this->assessment = $assessment->load(['questions.options', 'course', 'lesson']);
        $this->attempt = $attempt->load(['assessment.course', 'assessment.questions.options', 'user']);

        if ($this->attempt->assessment_id !== $this->assessment->id) {
            abort(404, 'Assessment attempt not found.');
        }

        $this->authorizeAccess();

        $this->maxScore = $this->questionRecords()->sum('points') ?: 100;
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
        $this->assessment->loadMissing(['questions.options', 'course', 'lesson']);
        $this->attempt->loadMissing(['user', 'assessment.course', 'assessment.questions.options']);
        $this->maxScore = $this->questionRecords()->sum('points') ?: 100;
        $this->isPending = ! $this->attempt->auto_scored && $this->attempt->score === null;
        $this->percentage = $this->isPending ? null : $this->attempt->scorePercentage();
    }

    protected function hydrateGradeForm(): void
    {
        if ($this->attempt->score === null) {
            // Suggest auto-earned points so instructor only needs to add short-answer credit
            $suggested = $this->autoEarnedPoints();
            $this->gradeScore = $suggested > 0 ? (string) $suggested : '';
            $this->gradeFeedback = '';

            return;
        }

        $this->gradeScore = (string) ($this->attempt->scoreAsPoints() ?? '');
        $answers = $this->attempt->answers ?? [];
        $this->gradeFeedback = (string) ($answers['feedback'] ?? '');
    }

    protected function questionRecords()
    {
        if ($this->assessment->relationLoaded('questions')) {
            return collect($this->assessment->getRelation('questions'))
                ->sortBy('order')
                ->values();
        }

        return $this->assessment->questions()->with('options')->orderBy('order')->get();
    }

    protected function questionType($question): string
    {
        return str_replace(' ', '_', strtolower(trim((string) $question->question_type)));
    }

    protected function isAutoGradableType(string $type): bool
    {
        return in_array($type, [
            'multiple_choice',
            'multiple_select',
            'choice',
            'true_false',
            'fill_blank',
            'ordering',
            'matching',
            'rating',
        ], true);
    }

    protected function isManualType(string $type): bool
    {
        return in_array($type, [
            'short_answer',
            'essay',
            'code_submission',
            'file_upload',
            'rubric_criteria',
        ], true);
    }

    /**
     * Full instructor review rows: auto-graded marked correct/incorrect + manual answers to score.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildReviewQuestions(): array
    {
        $answers = $this->attempt->answers ?? [];
        $rows = [];

        foreach ($this->questionRecords() as $index => $question) {
            $type = $this->questionType($question);
            $userAnswer = $answers[$question->id] ?? null;
            $points = (float) ($question->points ?? 0);
            $needsManual = $this->isManualType($type)
                || ($type === 'short_answer' && ! $this->shortAnswerHasCorrectKey($question));

            if ($type === 'file_upload') {
                $files = is_array($userAnswer) ? ($userAnswer['files'] ?? []) : [];
                $rows[] = [
                    'number' => $index + 1,
                    'question' => $question->question_text,
                    'type' => $type,
                    'type_label' => 'File Upload',
                    'points' => $points,
                    'needs_manual' => true,
                    'is_correct' => null,
                    'earned' => null,
                    'student_answer' => null,
                    'correct_answer' => null,
                    'files' => array_values(array_filter($files)),
                    'options' => [],
                ];
                continue;
            }

            if (in_array($type, ['multiple_choice', 'multiple_select', 'choice', 'true_false'], true)) {
                $evaluation = $this->evaluateChoiceQuestion($question, $userAnswer);
                $rows[] = [
                    'number' => $index + 1,
                    'question' => $question->question_text,
                    'type' => $type,
                    'type_label' => $this->typeLabel($type),
                    'points' => $points,
                    'needs_manual' => false,
                    'is_correct' => $evaluation['is_correct'],
                    'earned' => $evaluation['is_correct'] ? $points : 0,
                    'student_answer' => $evaluation['student_text'],
                    'correct_answer' => $evaluation['correct_text'],
                    'files' => [],
                    'options' => $evaluation['options'],
                ];
                continue;
            }

            if ($type === 'short_answer' && ! $needsManual) {
                $isCorrect = $this->isShortAnswerCorrect($question, is_string($userAnswer) ? $userAnswer : '');
                $correctKey = $this->shortAnswerCorrectText($question);
                $rows[] = [
                    'number' => $index + 1,
                    'question' => $question->question_text,
                    'type' => $type,
                    'type_label' => 'Short Answer',
                    'points' => $points,
                    'needs_manual' => false,
                    'is_correct' => $isCorrect,
                    'earned' => $isCorrect ? $points : 0,
                    'student_answer' => filled($userAnswer) ? (string) $userAnswer : '— No answer —',
                    'correct_answer' => $correctKey,
                    'files' => [],
                    'options' => [],
                ];
                continue;
            }

            // Manual / open response
            $display = '— No answer —';
            if ($type === 'code_submission' && filled($userAnswer)) {
                $display = (string) $userAnswer;
            } elseif (is_array($userAnswer)) {
                $display = json_encode($userAnswer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '—';
            } elseif (filled($userAnswer)) {
                $display = (string) $userAnswer;
            }

            $rows[] = [
                'number' => $index + 1,
                'question' => $question->question_text,
                'type' => $type,
                'type_label' => $this->typeLabel($type),
                'points' => $points,
                'needs_manual' => true,
                'is_correct' => null,
                'earned' => null,
                'student_answer' => $display,
                'correct_answer' => null,
                'files' => [],
                'options' => [],
            ];
        }

        return $rows;
    }

    protected function autoEarnedPoints(): float
    {
        $total = 0.0;
        foreach ($this->buildReviewQuestions() as $row) {
            if ($row['needs_manual']) {
                continue;
            }
            $total += (float) ($row['earned'] ?? 0);
        }

        return round($total, 1);
    }

    protected function autoGradablePoints(): float
    {
        $total = 0.0;
        foreach ($this->buildReviewQuestions() as $row) {
            if ($row['needs_manual']) {
                continue;
            }
            $total += (float) ($row['points'] ?? 0);
        }

        return round($total, 1);
    }

    /**
     * @return array{is_correct: bool, student_text: string, correct_text: string, options: array<int, array{text: string, is_correct: bool, selected: bool}>}
     */
    protected function evaluateChoiceQuestion($question, $userAnswer): array
    {
        $type = $this->questionType($question);
        $options = $question->options ?? collect();
        $correctIds = $options->where('is_correct', true)->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        $selectedIds = [];
        if (is_array($userAnswer)) {
            $selectedIds = array_values(array_filter(array_map(
                fn ($id) => is_numeric($id) ? (int) $id : null,
                $userAnswer
            )));
        } elseif (is_numeric($userAnswer)) {
            $selectedIds = [(int) $userAnswer];
        }

        $optionRows = [];
        foreach ($options->sortBy('order') as $option) {
            $oid = (int) $option->id;
            $optionRows[] = [
                'text' => (string) $option->option_text,
                'is_correct' => (bool) $option->is_correct,
                'selected' => in_array($oid, $selectedIds, true),
            ];
        }

        if ($type === 'multiple_select') {
            $a = $selectedIds;
            $b = $correctIds;
            sort($a);
            sort($b);
            $isCorrect = $a === $b && $a !== [];
        } else {
            $isCorrect = count($selectedIds) === 1 && in_array($selectedIds[0], $correctIds, true);
        }

        $optionMap = $options->pluck('option_text', 'id')->mapWithKeys(
            fn ($text, $id) => [(int) $id => $text]
        )->all();

        $studentText = $selectedIds === []
            ? '— No answer —'
            : collect($selectedIds)->map(fn ($id) => $optionMap[$id] ?? "#{$id}")->implode(', ');

        $correctText = collect($correctIds)->map(fn ($id) => $optionMap[$id] ?? "#{$id}")->implode(', ');

        return [
            'is_correct' => $isCorrect,
            'student_text' => $studentText,
            'correct_text' => $correctText ?: '—',
            'options' => $optionRows,
        ];
    }

    protected function shortAnswerHasCorrectKey($question): bool
    {
        return filled($this->shortAnswerCorrectText($question));
    }

    protected function shortAnswerCorrectText($question): string
    {
        $settings = $question->settings ?? [];
        $correct = trim((string) ($settings['correct_answer'] ?? $settings['short_answer']['correct_answer'] ?? ''));

        if ($correct === '') {
            $correctOption = ($question->options ?? collect())->where('is_correct', true)->first();
            $correct = $correctOption ? trim((string) $correctOption->option_text) : '';
        }

        return $correct;
    }

    protected function isShortAnswerCorrect($question, string $userAnswer): bool
    {
        $userAnswer = trim($userAnswer);
        $correctAnswer = $this->shortAnswerCorrectText($question);
        if ($userAnswer === '' || $correctAnswer === '') {
            return false;
        }

        $settings = $question->settings ?? [];
        $caseSensitive = (bool) ($settings['case_sensitive'] ?? false);
        $userCmp = $caseSensitive ? $userAnswer : mb_strtolower($userAnswer);
        $correctCmp = $caseSensitive ? $correctAnswer : mb_strtolower($correctAnswer);

        if ($userCmp === $correctCmp) {
            return true;
        }

        foreach ($settings['alternative_answers'] ?? [] as $alt) {
            $altCmp = $caseSensitive ? trim((string) $alt) : mb_strtolower(trim((string) $alt));
            if ($userCmp === $altCmp) {
                return true;
            }
        }

        return false;
    }

    protected function typeLabel(string $type): string
    {
        return match ($type) {
            'multiple_choice', 'choice' => 'Multiple Choice',
            'multiple_select' => 'Multiple Select',
            'true_false' => 'True / False',
            'short_answer' => 'Short Answer',
            'essay' => 'Essay',
            'code_submission' => 'Code Submission',
            'file_upload' => 'File Upload',
            'fill_blank' => 'Fill in the Blank',
            'ordering' => 'Ordering',
            'matching' => 'Matching',
            'rating' => 'Rating',
            'rubric_criteria' => 'Rubric',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }

    protected function buildCorrectQuestions(): array
    {
        // Prefer full review for instructors; keep student summary for fully auto-scored attempts
        if (! $this->attempt->auto_scored) {
            return [];
        }

        return collect($this->buildReviewQuestions())
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

        return collect($this->buildReviewQuestions())
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

        $isPassed = ($this->maxScore > 0)
            ? (($this->gradeScore / $this->maxScore) * 100) >= $this->assessment->passing_score
            : false;

        $answers = $this->attempt->answers ?? [];
        $answers['feedback'] = $this->gradeFeedback;
        $answers['graded_at'] = now()->toIso8601String();
        $answers['graded_by'] = Auth::id();

        $this->attempt->update([
            'score'        => (float) $this->gradeScore,
            'is_passed'    => $isPassed,
            'auto_scored'  => false,
            'status'       => 'completed',
            'completed_at' => $this->attempt->completed_at ?? now(),
            'answers'      => $answers,
            'teacher_id'   => Auth::id(),
        ]);

        $percentage = $this->maxScore > 0
            ? min(round(((float) $this->gradeScore / $this->maxScore) * 100, 2), 100)
            : 0;

        \App\Models\Grade::updateOrCreate(
            [
                'user_id' => $this->attempt->user_id,
                'course_id' => $this->assessment->course_id,
                'gradeable_type' => AssessmentAttempt::class,
                'gradeable_id' => $this->attempt->id,
            ],
            [
                'score' => (float) $this->gradeScore,
                'max_score' => $this->maxScore,
                'percentage' => $percentage,
                'letter_grade' => $this->letterGradeFromPercentage($percentage),
                'feedback' => json_encode(['feedback' => $this->gradeFeedback]),
                'graded_by' => Auth::id(),
                'graded_at' => now(),
                'is_final' => true,
            ]
        );

        // Recalculate percentage for this view
        $this->percentage = $percentage;
        $this->isPending = false;
        $this->attempt->refresh();

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

    protected function letterGradeFromPercentage(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A',
            $percentage >= 80 => 'B',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            default => 'F',
        };
    }

    public function render()
    {
        $reviewQuestions = $this->buildReviewQuestions();
        $autoEarned = $this->autoEarnedPoints();
        $autoMax = $this->autoGradablePoints();
        $manualCount = collect($reviewQuestions)->where('needs_manual', true)->count();

        return view('livewire.assessments.results', [
            'canGrade' => Auth::user()->can('grade_submissions'),
            'reviewQuestions' => $reviewQuestions,
            'autoEarnedPoints' => $autoEarned,
            'autoGradablePoints' => $autoMax,
            'manualQuestionCount' => $manualCount,
            'submissionFiles' => $this->attempt->submissionFiles(),
        ]);
    }
}
