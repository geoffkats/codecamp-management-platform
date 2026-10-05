<?php

namespace App\Services\Assessments;

/**
 * The outcome of grading one answer against one question.
 */
final class QuestionGrade
{
    /**
     * @param  float|null  $earned  null while the question is waiting for a teacher
     * @param  array<int, array{text: string, is_correct: bool, selected: bool}>  $options
     * @param  array<int, mixed>  $files
     */
    public function __construct(
        public readonly ?float $earned,
        public readonly float $max,
        public readonly ?bool $isCorrect,
        public readonly bool $needsManual,
        public readonly string $studentAnswer,
        public readonly ?string $correctAnswer = null,
        public readonly array $options = [],
        public readonly array $files = [],
    ) {}
}
