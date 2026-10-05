<?php

namespace App\Services\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use Illuminate\Support\Collection;

/**
 * Creates question-set snapshots for attempts that began before snapshots existed.
 *
 * Deliberately conservative: it never changes score, answers, status or is_passed, and it flags
 * the attempt for review whenever it cannot prove the snapshot matches what the student saw.
 * Legacy Take never persisted its shuffles (Collection::shuffle ignored the seed), so a randomized
 * in-progress order cannot be reconstructed and is always flagged.
 */
class LegacyQuestionSetBackfiller
{
    public const RANDOM_ORDER_NOT_PERSISTED = 'legacy_random_question_order_not_persisted';

    public const OPTION_ORDER_NOT_PERSISTED = 'legacy_option_order_not_persisted';

    public const AMBIGUOUS_ORDER = 'ambiguous_legacy_question_order';

    public const QUESTIONS_CHANGED = 'questions_changed_after_attempt';

    public const MISSING_QUESTIONS = 'answers_reference_missing_questions';

    public const MISSING_OPTIONS = 'answers_reference_missing_options';

    /** Keys graders and submissions store alongside question answers. */
    private const META_ANSWER_KEYS = ['feedback', 'graded_at', 'graded_by', 'question_scores', 'xp_awarded', 'submission_text', 'files', 'text'];

    public function __construct(
        private readonly AttemptQuestionSetGenerator $generator,
        private readonly QuestionSnapshotFactory $snapshots,
    ) {}

    public function backfill(AssessmentAttempt $attempt): AssessmentAttempt
    {
        return $this->generator->write($attempt, function (AssessmentAttempt $locked) {
            $plan = $this->plan($locked);

            return [$plan['entries'], 'backfill', $plan['meta'], $plan['reasons']];
        });
    }

    /**
     * Work out the snapshot and review reasons without writing anything.
     *
     * @return array{entries: array<int, array>, reasons: array<int, string>, meta: array<string, mixed>}
     */
    public function plan(AssessmentAttempt $attempt): array
    {
        $assessment = Assessment::withTrashed()->find($attempt->assessment_id);
        $questions = $assessment
            ? $assessment->questions()->with('options')->get()->values()
            : collect();

        $inProgress = $attempt->status === 'in_progress';
        $reference = $inProgress
            ? $attempt->started_at
            : ($attempt->completed_at ?? $attempt->updated_at);

        $reasons = [];

        if ($inProgress && $assessment?->is_randomized && $questions->count() > 1) {
            $reasons[] = self::RANDOM_ORDER_NOT_PERSISTED;
        }

        if ($inProgress && $assessment?->shuffle_options && $this->hasShuffleableOptions($questions)) {
            $reasons[] = self::OPTION_ORDER_NOT_PERSISTED;
        }

        if ($questions->map(fn ($q) => $q->pivot?->position ?? $q->order)->duplicates()->isNotEmpty()) {
            $reasons[] = self::AMBIGUOUS_ORDER;
        }

        if ($reference && $this->changedSince($questions, $reference)) {
            $reasons[] = self::QUESTIONS_CHANGED;
        }

        $entries = $questions->map(function ($question) {
            $snapshot = $this->snapshots->fromQuestion($question);

            return [
                'snapshot' => $snapshot,
                'presentation' => $this->generator->presentation($snapshot, false, null),
                'source' => ['origin' => 'legacy_backfill'],
            ];
        })->all();

        array_push($reasons, ...$this->answerReferenceProblems($attempt->answers ?? [], $entries));

        return [
            'entries' => $entries,
            'reasons' => array_values(array_unique($reasons)),
            'meta' => [
                'mode' => 'backfill',
                'reference_time' => $reference?->toIso8601String(),
                'attempt_status' => $attempt->status,
                'assessment' => $assessment ? [
                    'id' => $assessment->id,
                    'updated_at' => $assessment->updated_at?->toIso8601String(),
                    'is_randomized' => (bool) $assessment->is_randomized,
                    'shuffle_options' => (bool) $assessment->shuffle_options,
                ] : null,
            ],
        ];
    }

    private function hasShuffleableOptions(Collection $questions): bool
    {
        return $questions->contains(function ($question) {
            if ($question->options->count() > 1) {
                return true;
            }

            $settings = is_array($question->settings) ? $question->settings : [];

            return count($settings['matching_pairs'] ?? []) > 1 || count($settings['ordering_items'] ?? []) > 1;
        });
    }

    private function changedSince(Collection $questions, \DateTimeInterface $reference): bool
    {
        foreach ($questions as $question) {
            if ($question->created_at?->gt($reference) || $question->updated_at?->gt($reference)) {
                return true;
            }

            foreach ($question->options as $option) {
                if ($option->created_at?->gt($reference) || $option->updated_at?->gt($reference)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<int|string, mixed>  $answers
     * @param  array<int, array>  $entries
     * @return array<int, string>
     */
    private function answerReferenceProblems(array $answers, array $entries): array
    {
        $byId = collect($entries)->keyBy(fn ($entry) => (int) $entry['snapshot']['question_id']);
        $problems = [];

        foreach ($answers as $key => $answer) {
            if (in_array($key, self::META_ANSWER_KEYS, true) || ! is_numeric($key)) {
                continue;
            }

            $entry = $byId->get((int) $key);
            if (! $entry) {
                $problems[] = self::MISSING_QUESTIONS;

                continue;
            }

            $snapshot = $entry['snapshot'];
            if (! in_array($snapshot['type'], ['multiple_choice', 'choice', 'true_false', 'multiple_select'], true)) {
                continue;
            }

            $optionIds = array_map('intval', array_column($snapshot['options'], 'id'));
            $selected = is_array($answer) ? ($answer['value'] ?? $answer) : $answer;
            foreach ((array) $selected as $id) {
                if (is_numeric($id) && ! in_array((int) $id, $optionIds, true)) {
                    $problems[] = self::MISSING_OPTIONS;
                    break;
                }
            }
        }

        return array_values(array_unique($problems));
    }
}
