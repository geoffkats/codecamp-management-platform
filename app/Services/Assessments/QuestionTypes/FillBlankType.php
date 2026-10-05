<?php

namespace App\Services\Assessments\QuestionTypes;

use App\Services\Assessments\QuestionGrade;

/**
 * Proportional credit: each correctly filled blank earns points / number of blanks.
 */
class FillBlankType extends QuestionType
{
    public function key(): string
    {
        return 'fill_blank';
    }

    public function label(): string
    {
        return 'Fill in the Blank';
    }

    public function aliases(): array
    {
        return ['fill_in_the_blank', 'fill_in_blank', 'fill_in_the_blanks', 'fillblank', 'cloze'];
    }

    public function grade(array $question, mixed $answer): QuestionGrade
    {
        $blanks = $this->blanks($question);
        $answers = is_array($answer) ? array_values($answer) : [];
        $display = collect($answers)->map(fn ($a) => trim((string) (is_scalar($a) ? $a : '')))->filter(fn ($a) => $a !== '')->implode(', ') ?: self::NO_ANSWER;
        $correctDisplay = collect($blanks)->pluck('correct_answer')->map(fn ($a) => (string) $a)->implode(', ') ?: '—';

        if ($blanks === [] || $this->isBlank($answer)) {
            return $this->zero($question, $display, $correctDisplay);
        }

        $correct = 0;
        foreach ($blanks as $index => $blank) {
            $given = trim((string) (is_scalar($answers[$index] ?? null) ? $answers[$index] : ''));
            $expected = trim((string) ($blank['correct_answer'] ?? ''));

            if ($given === '' || $expected === '') {
                continue;
            }

            $caseSensitive = (bool) ($blank['case_sensitive'] ?? false);
            $matches = fn (string $candidate) => $caseSensitive
                ? $given === trim($candidate)
                : strtolower($given) === strtolower(trim($candidate));

            $accepted = array_merge([$expected], array_map('strval', (array) ($blank['alternative_answers'] ?? [])));
            foreach ($accepted as $candidate) {
                if ($matches($candidate)) {
                    $correct++;
                    break;
                }
            }
        }

        return $this->scored($question, ($correct / count($blanks)) * $this->points($question), $display, $correctDisplay);
    }

    /**
     * Unlike the generic check, "0" is a real answer to a blank.
     */
    protected function isBlank(mixed $answer): bool
    {
        if (! is_array($answer)) {
            return trim((string) (is_scalar($answer) ? $answer : '')) === '';
        }

        foreach ($answer as $value) {
            if (is_scalar($value) && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $question
     * @return array<int, array<string, mixed>>
     */
    private function blanks(array $question): array
    {
        $blanks = $this->settings($question)['fill_blank']['blanks'] ?? [];

        if ($blanks === [] && $this->options($question) !== []) {
            $blanks = array_map(fn ($option) => [
                'correct_answer' => (string) ($option['text'] ?? ''),
                'case_sensitive' => false,
                'alternative_answers' => [],
            ], $this->options($question));
        }

        return array_values($blanks);
    }
}
