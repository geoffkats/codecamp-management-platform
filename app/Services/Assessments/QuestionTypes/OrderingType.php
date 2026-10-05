<?php

namespace App\Services\Assessments\QuestionTypes;

use App\Services\Assessments\QuestionGrade;

/**
 * All-or-nothing: the answer must list every item in the configured order.
 */
class OrderingType extends QuestionType
{
    public function key(): string
    {
        return 'ordering';
    }

    public function label(): string
    {
        return 'Ordering';
    }

    public function aliases(): array
    {
        return ['sequence', 'sequencing', 'order'];
    }

    public function grade(array $question, mixed $answer): QuestionGrade
    {
        $items = $this->items($question);
        $answers = is_array($answer) ? array_values($answer) : [];
        $display = $answers !== [] ? implode(' → ', array_map('strval', $answers)) : self::NO_ANSWER;
        $correctDisplay = $items !== [] ? implode(' → ', $items) : '—';

        if ($items === [] || $this->isBlank($answer) || count($answers) !== count($items)) {
            return $this->zero($question, $display, $correctDisplay);
        }

        foreach ($items as $index => $expected) {
            if (($answers[$index] ?? '') !== $expected) {
                return $this->zero($question, $display, $correctDisplay);
            }
        }

        return $this->scored($question, $this->points($question), $display, $correctDisplay);
    }

    /**
     * Item texts in their correct order.
     *
     * @param  array<string, mixed>  $question
     * @return array<int, string>
     */
    public function items(array $question): array
    {
        $items = $this->settings($question)['ordering_items'] ?? [];

        if ($items === []) {
            return collect($this->options($question))
                ->sortBy('order')
                ->map(fn ($option) => (string) ($option['text'] ?? ''))
                ->values()
                ->all();
        }

        return array_values(array_map(fn ($item) => (string) ($item['item_text'] ?? ''), $items));
    }
}
