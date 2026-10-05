<?php

namespace App\Services\Assessments\QuestionTypes;

use App\Services\Assessments\QuestionGrade;

/**
 * One kind of question. Types grade the normalized question array produced by
 * QuestionSnapshotFactory (or stored in an attempt snapshot), never live models.
 */
abstract class QuestionType
{
    public const NO_ANSWER = '— No answer —';

    abstract public function key(): string;

    abstract public function label(): string;

    /**
     * Legacy or alternative spellings that should resolve to this type.
     *
     * @return array<int, string>
     */
    public function aliases(): array
    {
        return [];
    }

    /**
     * Whether this question, as configured, can be marked without a teacher.
     *
     * @param  array<string, mixed>  $question
     */
    public function isAutoGradable(array $question): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $question
     */
    abstract public function grade(array $question, mixed $answer): QuestionGrade;

    /**
     * @param  array<string, mixed>  $question
     */
    protected function points(array $question): float
    {
        return (float) ($question['points'] ?? 0);
    }

    /**
     * @param  array<string, mixed>  $question
     * @return array<string, mixed>
     */
    protected function settings(array $question): array
    {
        return is_array($question['settings'] ?? null) ? $question['settings'] : [];
    }

    /**
     * @param  array<string, mixed>  $question
     * @return array<int, array<string, mixed>>
     */
    protected function options(array $question): array
    {
        return array_values($question['options'] ?? []);
    }

    /**
     * Blank in the sense the original Take scorer used: null, '' or an array with no truthy values.
     */
    protected function isBlank(mixed $answer): bool
    {
        return $answer === null
            || $answer === ''
            || (is_array($answer) && array_filter($answer) === []);
    }

    /**
     * @param  array<string, mixed>  $question
     */
    protected function zero(array $question, string $studentAnswer, ?string $correctAnswer = null, array $options = []): QuestionGrade
    {
        return new QuestionGrade(0.0, $this->points($question), false, false, $studentAnswer, $correctAnswer, $options);
    }

    /**
     * @param  array<string, mixed>  $question
     */
    protected function scored(array $question, float $earned, string $studentAnswer, ?string $correctAnswer = null, array $options = []): QuestionGrade
    {
        $max = $this->points($question);

        return new QuestionGrade($earned, $max, $max > 0 ? $earned >= $max : $earned > 0, false, $studentAnswer, $correctAnswer, $options);
    }

    protected function textAnswer(mixed $answer): string
    {
        if (is_array($answer)) {
            $answer = $answer['value'] ?? $answer['text'] ?? $answer['answer'] ?? null;
        }

        return is_string($answer) || is_numeric($answer) ? trim((string) $answer) : '';
    }
}
