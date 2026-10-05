<?php

namespace App\Services\Assessments\QuestionTypes;

use App\Services\Assessments\QuestionGrade;

/**
 * Open responses that always need a teacher (essay, code_submission, rubric_criteria, and any
 * unrecognised legacy type).
 */
class ManualType extends QuestionType
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

    public function isAutoGradable(array $question): bool
    {
        return false;
    }

    public function grade(array $question, mixed $answer): QuestionGrade
    {
        return new QuestionGrade(null, $this->points($question), null, true, $this->display($answer));
    }

    private function display(mixed $answer): string
    {
        if (is_array($answer)) {
            $text = $this->textAnswer($answer);
            if ($text !== '') {
                return $text;
            }

            return $this->isBlank($answer)
                ? self::NO_ANSWER
                : (json_encode($answer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: self::NO_ANSWER);
        }

        return filled($answer) ? (string) $answer : self::NO_ANSWER;
    }
}
