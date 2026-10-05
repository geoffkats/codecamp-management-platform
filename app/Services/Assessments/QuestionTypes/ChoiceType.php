<?php

namespace App\Services\Assessments\QuestionTypes;

use App\Services\Assessments\QuestionGrade;

/**
 * Option-based questions (multiple_choice, choice, true_false, multiple_select).
 * A scalar answer is correct when it is one of the correct option ids; an array answer must
 * equal the full set of correct option ids.
 */
class ChoiceType extends QuestionType
{
    /**
     * @param  array<int, string>  $aliases
     */
    public function __construct(
        private readonly string $key,
        private readonly string $label,
        private readonly array $aliases = [],
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function aliases(): array
    {
        return $this->aliases;
    }

    public function grade(array $question, mixed $answer): QuestionGrade
    {
        if (is_array($answer) && array_key_exists('value', $answer)) {
            $answer = $answer['value'];
        }

        $options = $this->options($question);
        $correctIds = collect($options)
            ->filter(fn ($option) => (bool) ($option['is_correct'] ?? false))
            ->map(fn ($option) => (int) $option['id'])
            ->sort()
            ->values()
            ->all();

        $selectedIds = $this->selectedIds($answer);
        $optionTexts = collect($options)->mapWithKeys(fn ($option) => [(int) $option['id'] => (string) ($option['text'] ?? '')])->all();

        $optionRows = collect($options)
            ->sortBy('order')
            ->map(fn ($option) => [
                'text' => (string) ($option['text'] ?? ''),
                'is_correct' => (bool) ($option['is_correct'] ?? false),
                'selected' => in_array((int) $option['id'], $selectedIds, true),
            ])
            ->values()
            ->all();

        $studentText = $selectedIds === []
            ? self::NO_ANSWER
            : collect($selectedIds)->map(fn ($id) => $optionTexts[$id] ?? "Option #{$id}")->implode(', ');
        $correctText = collect($correctIds)->map(fn ($id) => $optionTexts[$id] ?? "Option #{$id}")->implode(', ') ?: '—';

        if ($this->isBlank($answer)) {
            return $this->zero($question, self::NO_ANSWER, $correctText, $optionRows);
        }

        if (is_array($answer)) {
            $given = array_map(fn ($value) => is_numeric($value) ? (int) $value : $value, array_filter($answer));
            sort($given);
            $isCorrect = $given === $correctIds;
        } else {
            $isCorrect = in_array(is_numeric($answer) ? (int) $answer : $answer, $correctIds, true);
        }

        return $isCorrect
            ? $this->scored($question, $this->points($question), $studentText, $correctText, $optionRows)
            : $this->zero($question, $studentText, $correctText, $optionRows);
    }

    /**
     * @return array<int, int>
     */
    private function selectedIds(mixed $answer): array
    {
        if (is_array($answer)) {
            return array_values(array_filter(array_map(
                fn ($id) => is_numeric($id) ? (int) $id : null,
                $answer
            )));
        }

        return is_numeric($answer) ? [(int) $answer] : [];
    }
}
