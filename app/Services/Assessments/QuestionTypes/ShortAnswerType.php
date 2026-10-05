<?php

namespace App\Services\Assessments\QuestionTypes;

use App\Services\Assessments\QuestionGrade;

/**
 * Auto-graded when an answer key exists (settings.correct_answer, the older
 * settings.short_answer.correct_answer, or a correct option); otherwise left for a teacher.
 */
class ShortAnswerType extends QuestionType
{
    public function key(): string
    {
        return 'short_answer';
    }

    public function label(): string
    {
        return 'Short Answer';
    }

    public function aliases(): array
    {
        return ['text', 'short_text', 'one_word'];
    }

    public function isAutoGradable(array $question): bool
    {
        return $this->correctAnswer($question) !== '';
    }

    public function grade(array $question, mixed $answer): QuestionGrade
    {
        $text = $this->textAnswer($answer);
        $display = $text !== '' ? $text : self::NO_ANSWER;

        if (! $this->isAutoGradable($question)) {
            return new QuestionGrade(null, $this->points($question), null, true, $display);
        }

        $correct = $this->correctAnswer($question);

        if ($text === '') {
            return $this->zero($question, $display, $correct);
        }

        $settings = $this->settings($question);
        $caseSensitive = (bool) ($settings['case_sensitive'] ?? $settings['short_answer']['case_sensitive'] ?? false);
        $normalize = fn (string $value) => $caseSensitive ? trim($value) : mb_strtolower(trim($value));

        $accepted = array_merge(
            [$correct],
            array_map('strval', (array) ($settings['alternative_answers'] ?? [])),
            array_map('strval', (array) ($settings['short_answer']['alternative_answers'] ?? [])),
        );

        $given = $normalize($text);
        foreach ($accepted as $candidate) {
            if ($given === $normalize($candidate)) {
                return $this->scored($question, $this->points($question), $display, $correct);
            }
        }

        return $this->zero($question, $display, $correct);
    }

    /**
     * @param  array<string, mixed>  $question
     */
    public function correctAnswer(array $question): string
    {
        $settings = $this->settings($question);
        $correct = trim((string) ($settings['correct_answer'] ?? $settings['short_answer']['correct_answer'] ?? ''));

        if ($correct === '') {
            $option = collect($this->options($question))->first(fn ($o) => (bool) ($o['is_correct'] ?? false));
            $correct = $option ? trim((string) ($option['text'] ?? '')) : '';
        }

        return $correct;
    }
}
