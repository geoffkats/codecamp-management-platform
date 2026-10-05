<?php

namespace App\Services\Assessments;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptQuestion;
use Illuminate\Support\Collection;

/**
 * The single place where assessment answers are scored and reviewed. Attempts with a frozen
 * question set are graded from their snapshot; older attempts fall back to the live questions.
 */
class AssessmentGradingService
{
    public function __construct(
        private readonly QuestionTypeRegistry $types,
        private readonly QuestionSnapshotFactory $snapshots,
    ) {}

    /**
     * The questions this attempt is graded against, in display order.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function questionsFor(AssessmentAttempt $attempt): Collection
    {
        if ($attempt->hasQuestionSet()) {
            $rows = $attempt->relationLoaded('questionSet') ? $attempt->questionSet : $attempt->questionSet()->get();

            return $rows->map(fn (AssessmentAttemptQuestion $row) => $row->toQuestionData())->values();
        }

        $assessment = $attempt->relationLoaded('assessment') ? $attempt->assessment : $attempt->assessment()->first();
        if (! $assessment) {
            return collect();
        }

        $questions = $assessment->relationLoaded('questions')
            ? $assessment->getRelation('questions')->loadMissing('options')
            : $assessment->questions()->with('options')->get();

        return $questions
            ->values()
            ->map(fn ($question, $index) => $this->snapshots->fromQuestion($question) + ['position' => $index + 1, 'presentation' => []]);
    }

    /**
     * @param  array<string, mixed>  $question
     */
    public function gradeQuestion(array $question, mixed $answer): QuestionGrade
    {
        return $this->types->for($question['type'] ?? null)->grade($question, $answer);
    }

    /**
     * @param  array<int|string, mixed>|null  $answers  defaults to the answers saved on the attempt
     */
    public function gradeAttempt(AssessmentAttempt $attempt, ?array $answers = null): AttemptGrade
    {
        $answers ??= $attempt->answers ?? [];
        $rows = [];
        $earned = 0.0;
        $max = 0.0;
        $autoMax = 0.0;
        $needsManual = false;

        foreach ($this->questionsFor($attempt)->values() as $index => $question) {
            $id = (int) $question['question_id'];
            $answer = $answers[$id] ?? $answers[(string) $id] ?? null;
            $grade = $this->gradeQuestion($question, $answer);

            $max += $grade->max;
            if ($grade->needsManual) {
                $needsManual = true;
            } else {
                $earned += (float) $grade->earned;
                $autoMax += $grade->max;
            }

            $rows[] = [
                'id' => $id,
                'number' => $index + 1,
                'question' => (string) ($question['text'] ?? ''),
                'type' => (string) $question['type'],
                'type_label' => $this->types->label($question['type']),
                'points' => $grade->max,
                'needs_manual' => $grade->needsManual,
                'is_correct' => $grade->isCorrect,
                'earned' => $grade->earned,
                'student_answer' => $question['type'] === 'file_upload' ? null : $grade->studentAnswer,
                'correct_answer' => $grade->correctAnswer,
                'files' => $grade->files,
                'options' => $grade->options,
            ];
        }

        return new AttemptGrade($rows, round($earned, 2), round($max, 2), round($autoMax, 2), $needsManual);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function reviewRows(AssessmentAttempt $attempt): array
    {
        return $this->gradeAttempt($attempt)->rows;
    }

    /**
     * Copy per-question results onto the attempt's snapshot rows.
     */
    public function storeResults(AssessmentAttempt $attempt, AttemptGrade $grade): void
    {
        if (! $attempt->hasQuestionSet()) {
            return;
        }

        $byQuestion = collect($grade->rows)->keyBy('id');

        foreach ($attempt->questionSet()->get() as $row) {
            $result = $byQuestion->get((int) ($row->snapshot['question_id'] ?? $row->question_id));
            if (! $result) {
                continue;
            }

            $row->update([
                'earned_points' => $result['earned'],
                'is_correct' => $result['is_correct'],
                'needs_manual' => $result['needs_manual'],
                'graded_at' => $result['needs_manual'] ? null : now(),
            ]);
        }
    }
}
