<?php

namespace App\Services\Assessments;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptQuestion;
use App\Services\Assessments\QuestionTypes\MatchingType;
use App\Services\Assessments\QuestionTypes\OrderingType;
use Illuminate\Support\Facades\DB;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Freezes the exact questions, question order and option order a student receives when an attempt
 * starts. After this runs the attempt never reads the live question tables again.
 */
class AttemptQuestionSetGenerator
{
    public const VERSION = 1;

    public function __construct(
        private readonly QuestionSnapshotFactory $snapshots,
        private readonly QuestionTypeRegistry $types,
    ) {}

    public function generate(AssessmentAttempt $attempt, ?int $seed = null): AssessmentAttempt
    {
        return $this->write($attempt, function (AssessmentAttempt $locked) use ($seed) {
            $assessment = Assessment::withTrashed()->findOrFail($locked->assessment_id);
            $seed ??= random_int(1, 2147483647);
            $randomizer = new Randomizer(new Mt19937($seed));
            $shuffleOptions = (bool) $assessment->shuffle_options;

            $questions = $assessment->questions()->with('options')->get()->all();
            $poolSize = count($questions);
            $drawCount = $assessment->questionsPerAttemptFor($poolSize);
            $drawn = $assessment->drawsFromPool($poolSize);

            if ($drawn || $assessment->is_randomized) {
                $questions = $randomizer->shuffleArray($questions);
            }
            $questions = array_slice($questions, 0, $drawCount);

            $entries = [];
            foreach ($questions as $question) {
                $snapshot = $this->snapshots->fromQuestion($question);
                $entries[] = [
                    'snapshot' => $snapshot,
                    'presentation' => $this->presentation($snapshot, $shuffleOptions, $randomizer),
                    'source' => ['origin' => 'assessment', 'assessment_id' => $assessment->id],
                ];
            }

            return [$entries, 'generated', [
                'mode' => $drawn ? 'random_pool' : 'manual',
                'pool_size' => $poolSize,
                'drawn' => $drawCount,
                'seed' => $seed,
                'algorithm' => 'mt19937',
                'assessment' => [
                    'id' => $assessment->id,
                    'updated_at' => $assessment->updated_at?->toIso8601String(),
                    'is_randomized' => (bool) $assessment->is_randomized,
                    'shuffle_options' => $shuffleOptions,
                    'questions_per_attempt' => $assessment->questions_per_attempt,
                ],
            ], []];
        });
    }

    /**
     * Persist a question set under a row lock so concurrent requests cannot generate two sets.
     *
     * @param  callable(AssessmentAttempt): array{0: array<int, array{snapshot: array, presentation: array, source: array}>, 1: string, 2: array, 3: array<int, string>}  $build
     */
    public function write(AssessmentAttempt $attempt, callable $build): AssessmentAttempt
    {
        return DB::transaction(function () use ($attempt, $build) {
            $locked = AssessmentAttempt::whereKey($attempt->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->hasQuestionSet()) {
                return $locked;
            }

            [$entries, $source, $meta, $reviewReasons] = $build($locked);

            foreach (array_values($entries) as $index => $entry) {
                $snapshot = $entry['snapshot'];

                AssessmentAttemptQuestion::create([
                    'assessment_attempt_id' => $locked->id,
                    'question_id' => $snapshot['question_id'],
                    'question_version' => $snapshot['version'] ?? null,
                    'position' => $index + 1,
                    'question_type' => $snapshot['type'],
                    'points' => $snapshot['points'],
                    'snapshot' => $snapshot,
                    'presentation' => $entry['presentation'],
                    'source' => $entry['source'],
                ]);
            }

            $locked->forceFill([
                'question_set_generated_at' => now(),
                'question_set_version' => self::VERSION,
                'question_set_source' => $source,
                'question_set_meta' => $meta + [
                    'question_versions' => collect($entries)
                        ->mapWithKeys(fn ($e) => [$e['snapshot']['question_id'] => $e['snapshot']['version'] ?? $e['snapshot']['updated_at'] ?? null])
                        ->all(),
                    'review_reasons' => array_values($reviewReasons),
                ],
                'question_set_review_required' => $reviewReasons !== [],
            ])->save();

            return $locked->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, array<int, mixed>>
     */
    public function presentation(array $snapshot, bool $shuffle, ?Randomizer $randomizer): array
    {
        $mix = fn (array $items) => $shuffle && $randomizer && count($items) > 1 ? $randomizer->shuffleArray($items) : $items;

        $presentation = ['option_order' => $mix(array_column($snapshot['options'] ?? [], 'id'))];

        if ($snapshot['type'] === 'matching') {
            /** @var MatchingType $matching */
            $matching = $this->types->for('matching');
            $presentation['right_items'] = $mix(array_column($matching->pairs($snapshot), 'right_item'));
        }

        if ($snapshot['type'] === 'ordering') {
            /** @var OrderingType $ordering */
            $ordering = $this->types->for('ordering');
            $presentation['ordering_items'] = $mix($ordering->items($snapshot));
        }

        return $presentation;
    }
}
