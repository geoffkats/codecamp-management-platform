<?php

namespace App\Services\Assessments;

use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Collection;

/**
 * Builds read-only, non-persisted Question models from an attempt's question set so existing
 * Blade views can render frozen snapshots. These models must never be saved.
 */
class AttemptQuestionModels
{
    public function __construct(private readonly AssessmentGradingService $grading) {}

    /**
     * @return Collection<int, Question>
     */
    public function for(AssessmentAttempt $attempt): Collection
    {
        return $this->grading->questionsFor($attempt)->map(fn (array $data) => $this->toModel($data))->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function toModel(array $data): Question
    {
        $media = $data['media'] ?? [];

        $question = new Question;
        $question->forceFill([
            'id' => (int) $data['question_id'],
            'question_text' => (string) ($data['text'] ?? ''),
            'question_type' => (string) $data['type'],
            'points' => (float) ($data['points'] ?? 0),
            'order' => (int) ($data['position'] ?? $data['order'] ?? 0),
            'explanation' => $data['explanation'] ?? null,
            'media_url' => $media['media_url'] ?? null,
            'media_type' => $media['media_type'] ?? null,
            'image_url' => $media['image_url'] ?? null,
            'image_alt_text' => $media['image_alt_text'] ?? null,
            'image_position' => $media['image_position'] ?? 'top',
            'settings' => $data['settings'] ?? [],
        ]);
        $question->exists = false;

        $options = collect($data['options'] ?? [])->keyBy(fn ($option) => (int) $option['id']);
        $order = $data['presentation']['option_order'] ?? $options->keys()->all();

        $ordered = collect($order)
            ->map(fn ($id) => $options->get((int) $id))
            ->filter()
            ->merge($options->except(array_map('intval', $order))->values())
            ->map(function (array $option) {
                $model = new QuestionOption;
                $model->forceFill([
                    'id' => (int) $option['id'],
                    'question_id' => null,
                    'option_text' => (string) ($option['text'] ?? ''),
                    'image_url' => $option['image_url'] ?? null,
                    'image_alt_text' => $option['image_alt_text'] ?? null,
                    'is_correct' => (bool) ($option['is_correct'] ?? false),
                    'order' => (int) ($option['order'] ?? 0),
                    'explanation' => $option['explanation'] ?? null,
                ]);
                $model->exists = false;

                return $model;
            })
            ->values();

        $question->setRelation('options', new \Illuminate\Database\Eloquent\Collection($ordered->all()));

        return $question;
    }
}
