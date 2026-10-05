<?php

namespace App\Services\Assessments\QuestionTypes;

use App\Services\Assessments\QuestionGrade;

/**
 * Proportional credit: the answer array holds the chosen right-hand item for each left-hand item.
 */
class MatchingType extends QuestionType
{
    public function key(): string
    {
        return 'matching';
    }

    public function label(): string
    {
        return 'Matching';
    }

    public function aliases(): array
    {
        return ['match', 'match_pairs'];
    }

    public function grade(array $question, mixed $answer): QuestionGrade
    {
        $pairs = $this->pairs($question);
        $answers = is_array($answer) ? $answer : [];

        $display = collect($pairs)
            ->map(fn ($pair, $index) => isset($answers[$index]) && $answers[$index] !== ''
                ? $pair['left_item'].' → '.$answers[$index]
                : null)
            ->filter()
            ->implode('; ') ?: self::NO_ANSWER;
        $correctDisplay = collect($pairs)->map(fn ($pair) => $pair['left_item'].' → '.$pair['right_item'])->implode('; ') ?: '—';

        if ($pairs === [] || $this->isBlank($answer)) {
            return $this->zero($question, $display, $correctDisplay);
        }

        $correct = 0;
        foreach ($pairs as $index => $pair) {
            if (($answers[$index] ?? '') === $pair['right_item']) {
                $correct++;
            }
        }

        return $this->scored($question, ($correct / count($pairs)) * $this->points($question), $display, $correctDisplay);
    }

    /**
     * @param  array<string, mixed>  $question
     * @return array<int, array{left_item: string, right_item: string}>
     */
    public function pairs(array $question): array
    {
        $pairs = $this->settings($question)['matching_pairs'] ?? [];

        if ($pairs === []) {
            foreach ($this->options($question) as $option) {
                $parts = explode('|', (string) ($option['text'] ?? ''));
                if (count($parts) === 2) {
                    $pairs[] = ['left_item' => $parts[0], 'right_item' => $parts[1]];
                }
            }
        }

        return array_values(array_map(fn ($pair) => [
            'left_item' => (string) ($pair['left_item'] ?? ''),
            'right_item' => (string) ($pair['right_item'] ?? ''),
        ], $pairs));
    }
}
