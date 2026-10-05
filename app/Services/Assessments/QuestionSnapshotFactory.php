<?php

namespace App\Services\Assessments;

use App\Models\Question;
use App\Models\QuestionOption;

/**
 * Converts a live Question into the self-contained array stored in attempt snapshots.
 * Everything needed to render, review and grade the question is copied, including the answer key.
 */
class QuestionSnapshotFactory
{
    public function __construct(private readonly QuestionTypeRegistry $types) {}

    /**
     * @return array<string, mixed>
     */
    public function fromQuestion(Question $question): array
    {
        $options = $question->relationLoaded('options')
            ? $question->options
            : $question->options()->get();

        return [
            'question_id' => (int) $question->id,
            'version' => isset($question->version) ? (int) $question->version : null,
            'type' => $this->types->normalize($question->question_type),
            'original_type' => (string) $question->question_type,
            'text' => (string) $question->question_text,
            'explanation' => $question->explanation,
            'points' => (float) ($question->points ?? 0),
            'difficulty' => $question->difficulty,
            'order' => (int) ($question->pivot?->position ?? $question->order ?? 0),
            'media' => [
                'image_url' => $question->image_url,
                'image_alt_text' => $question->image_alt_text,
                'image_position' => $question->image_position,
                'media_url' => $question->media_url,
                'media_type' => $question->media_type,
            ],
            'settings' => is_array($question->settings) ? $question->settings : [],
            'options' => $options
                ->sortBy([['order', 'asc'], ['id', 'asc']])
                ->map(fn (QuestionOption $option) => [
                    'id' => (int) $option->id,
                    'text' => (string) $option->option_text,
                    'image_url' => $option->image_url,
                    'image_alt_text' => $option->image_alt_text,
                    'is_correct' => (bool) $option->is_correct,
                    'order' => (int) ($option->order ?? 0),
                    'explanation' => $option->explanation,
                ])
                ->values()
                ->all(),
            'updated_at' => $question->updated_at?->toIso8601String(),
        ];
    }
}
