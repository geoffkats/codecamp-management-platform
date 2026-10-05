<?php

namespace App\Services\Assessments;

use App\Models\Assessment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

/**
 * The one place questions are written. Options are edited in place (ids survive), so answers
 * stored against option ids stay meaningful; any content change bumps the question version.
 */
class QuestionWriter
{
    private const CHOICE_TYPES = ['multiple_choice', 'multiple_select', 'true_false', 'choice'];

    /**
     * @param  array<string, mixed>  $data  question columns (question_text, question_type, points, difficulty, status, explanation, image_url, settings, order)
     * @param  array<int, array{id?: int|null, option_text: string, is_correct?: bool, image_url?: string|null}>  $options  answer options for choice types
     * @param  array<int, string>|null  $tagNames  null leaves tags untouched
     * @param  array<int, array{course_id: int, course_module_id?: int|null}>|null  $placements  null leaves placements untouched
     */
    public function save(
        ?Question $question,
        array $data,
        array $options = [],
        ?array $tagNames = null,
        ?array $placements = null,
        ?Assessment $attachTo = null,
    ): Question {
        return DB::transaction(function () use ($question, $data, $options, $tagNames, $placements, $attachTo) {
            $type = (string) ($data['question_type'] ?? $question?->question_type);
            $settings = is_array($data['settings'] ?? null) ? $data['settings'] : [];

            if ($type === 'ordering' && ! empty($settings['ordering_items'])) {
                // Grading compares against list order, so the list must be in correct order.
                $settings['ordering_items'] = collect($settings['ordering_items'])
                    ->filter(fn ($item) => trim((string) ($item['item_text'] ?? '')) !== '')
                    ->sortBy(fn ($item, $index) => [(int) ($item['correct_order'] ?? $index + 1), $index])
                    ->values()
                    ->map(fn ($item, $index) => ['item_text' => trim((string) $item['item_text']), 'correct_order' => $index + 1])
                    ->all();
            }
            $data['settings'] = $settings !== [] ? $settings : null;

            if ($question) {
                $question->fill($data);
                $contentChanged = $question->isDirty(Question::CONTENT_FIELDS);
                $question->save();
            } else {
                $question = Question::create($data);
                $contentChanged = false;
            }

            $optionsChanged = $this->syncOptions($question, $type, $this->optionRows($type, $options, $settings));

            if ($optionsChanged && ! $contentChanged && ! $question->wasRecentlyCreated) {
                $question->forceFill(['version' => $question->version + 1])->saveQuietly();
            }

            if ($tagNames !== null) {
                $question->tags()->sync(Tag::findOrCreateMany($tagNames)->pluck('id'));
            }

            if ($placements !== null) {
                $this->syncPlacements($question, $placements);
            }

            if ($attachTo) {
                $attachTo->pinQuestions([$question->id]);
            }

            return $question->fresh(['options', 'tags', 'placements']);
        });
    }

    /**
     * A new, independent copy (draft) with the same content, options and tags.
     */
    public function duplicate(Question $source): Question
    {
        $source->loadMissing(['options', 'tags', 'placements']);

        return $this->save(
            null,
            [
                'question_text' => $source->question_text,
                'question_type' => $source->question_type,
                'points' => $source->points,
                'difficulty' => $source->difficulty,
                'status' => 'draft',
                'explanation' => $source->explanation,
                'image_url' => $source->image_url,
                'image_alt_text' => $source->image_alt_text,
                'image_position' => $source->image_position ?? 'top',
                'media_url' => $source->media_url,
                'media_type' => $source->media_type,
                'settings' => $source->settings ?? [],
            ],
            $source->options->map(fn (QuestionOption $o) => [
                'option_text' => $o->option_text,
                'is_correct' => (bool) $o->is_correct,
                'image_url' => $o->image_url,
            ])->all(),
            $source->tags->pluck('name')->all(),
            $source->placements->map(fn ($p) => ['course_id' => $p->course_id, 'course_module_id' => $p->course_module_id])->all(),
        );
    }

    /**
     * @return array<int, array{id: int|null, option_text: string, is_correct: bool, image_url: string|null}>|null  null when this type stores no options
     */
    private function optionRows(string $type, array $options, array $settings): ?array
    {
        if (in_array($type, self::CHOICE_TYPES, true)) {
            return collect($options)
                ->filter(fn ($o) => trim((string) ($o['option_text'] ?? '')) !== '')
                ->map(fn ($o) => [
                    'id' => isset($o['id']) ? (int) $o['id'] : null,
                    'option_text' => trim((string) $o['option_text']),
                    'is_correct' => (bool) ($o['is_correct'] ?? false),
                    'image_url' => $o['image_url'] ?? null,
                ])
                ->values()
                ->all();
        }

        // Matching and ordering keep a legacy copy in options; settings remain the source of truth.
        if ($type === 'matching') {
            return collect($settings['matching_pairs'] ?? [])
                ->filter(fn ($p) => filled($p['left_item'] ?? null) && filled($p['right_item'] ?? null))
                ->map(fn ($p) => ['id' => null, 'option_text' => $p['left_item'].'|'.$p['right_item'], 'is_correct' => true, 'image_url' => null])
                ->values()
                ->all();
        }

        if ($type === 'ordering') {
            return collect($settings['ordering_items'] ?? [])
                ->map(fn ($i) => ['id' => null, 'option_text' => $i['item_text'], 'is_correct' => true, 'image_url' => null])
                ->values()
                ->all();
        }

        return null;
    }

    /**
     * Update options by id where possible (positionally for matching/ordering), create the rest,
     * delete leftovers. Image files are never deleted: attempt snapshots may still reference them.
     *
     * @param  array<int, array{id: int|null, option_text: string, is_correct: bool, image_url: string|null}>|null  $rows
     */
    private function syncOptions(Question $question, string $type, ?array $rows): bool
    {
        $existing = $question->options()->reorder()->orderBy('order')->orderBy('id')->get();

        if ($rows === null) {
            if ($existing->isEmpty()) {
                return false;
            }
            $question->options()->delete();

            return true;
        }

        $byId = $existing->keyBy('id');
        $positional = ! in_array($type, self::CHOICE_TYPES, true);
        $kept = [];
        $changed = false;

        foreach (array_values($rows) as $index => $row) {
            $option = $positional
                ? $existing->get($index)
                : ($row['id'] ? $byId->get($row['id']) : null);

            $attributes = [
                'option_text' => $row['option_text'],
                'is_correct' => $row['is_correct'],
                'order' => $index + 1,
                'image_url' => $row['image_url'],
            ];

            if ($option) {
                $option->fill($attributes);
                if ($option->isDirty()) {
                    $option->save();
                    $changed = true;
                }
                $kept[] = $option->id;
            } else {
                $kept[] = $question->options()->create($attributes)->id;
                $changed = true;
            }
        }

        $removed = $existing->whereNotIn('id', $kept);
        if ($removed->isNotEmpty()) {
            QuestionOption::whereIn('id', $removed->pluck('id'))->delete();
            $changed = true;
        }

        return $changed;
    }

    /**
     * @param  array<int, array{course_id: int, course_module_id?: int|null}>  $placements
     */
    private function syncPlacements(Question $question, array $placements): void
    {
        $wanted = collect($placements)
            ->filter(fn ($p) => ! empty($p['course_id']))
            ->map(fn ($p) => ['course_id' => (int) $p['course_id'], 'course_module_id' => ! empty($p['course_module_id']) ? (int) $p['course_module_id'] : null])
            ->unique(fn ($p) => $p['course_id'].'-'.($p['course_module_id'] ?? 0));

        $current = $question->placements()->get();

        foreach ($current as $placement) {
            $keep = $wanted->contains(fn ($p) => $p['course_id'] === (int) $placement->course_id && $p['course_module_id'] === ($placement->course_module_id ? (int) $placement->course_module_id : null));
            if (! $keep) {
                $placement->delete();
            }
        }

        foreach ($wanted as $p) {
            $question->placements()->firstOrCreate($p);
        }
    }
}
