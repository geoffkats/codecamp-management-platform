<?php

namespace App\Services\Assessments;

use App\Services\Assessments\QuestionTypes\ChoiceType;
use App\Services\Assessments\QuestionTypes\FileUploadType;
use App\Services\Assessments\QuestionTypes\FillBlankType;
use App\Services\Assessments\QuestionTypes\ManualType;
use App\Services\Assessments\QuestionTypes\MatchingType;
use App\Services\Assessments\QuestionTypes\OrderingType;
use App\Services\Assessments\QuestionTypes\QuestionType;
use App\Services\Assessments\QuestionTypes\RatingType;
use App\Services\Assessments\QuestionTypes\ShortAnswerType;

/**
 * Canonical question types. Stored question_type values are never rewritten; they are
 * normalized here on read so legacy spellings ("Multiple Choice", "true/false") grade correctly.
 */
class QuestionTypeRegistry
{
    /** @var array<string, QuestionType> */
    private array $types = [];

    /** @var array<string, string> */
    private array $aliases = [];

    public function __construct()
    {
        $this->register(new ChoiceType('multiple_choice', 'Multiple Choice', ['single_choice', 'mcq', 'multiple_choice_single']));
        $this->register(new ChoiceType('choice', 'Multiple Choice'));
        $this->register(new ChoiceType('multiple_select', 'Multiple Select', ['multiselect', 'multi_select', 'multiple_answer', 'multiple_answers', 'checkbox', 'checkboxes']));
        $this->register(new ChoiceType('true_false', 'True / False', ['true_or_false', 'truefalse', 'boolean', 'yes_no']));
        $this->register(new ShortAnswerType);
        $this->register(new FillBlankType);
        $this->register(new MatchingType);
        $this->register(new OrderingType);
        $this->register(new RatingType);
        $this->register(new ManualType('essay', 'Essay', ['long_answer', 'long_text', 'paragraph', 'reflection']));
        $this->register(new ManualType('code_submission', 'Code Submission', ['code', 'code_question', 'coding']));
        $this->register(new ManualType('rubric_criteria', 'Rubric', ['rubric']));
        $this->register(new FileUploadType);
    }

    public function register(QuestionType $type): void
    {
        $this->types[$type->key()] = $type;

        foreach ($type->aliases() as $alias) {
            $this->aliases[$this->slug($alias)] = $type->key();
        }
    }

    public function normalize(?string $raw): string
    {
        $slug = $this->slug((string) $raw);

        return $this->aliases[$slug] ?? $slug;
    }

    public function for(?string $rawOrKey): QuestionType
    {
        $key = $this->normalize($rawOrKey);

        return $this->types[$key]
            ?? new ManualType($key !== '' ? $key : 'unknown', $key !== '' ? ucwords(str_replace('_', ' ', $key)) : 'Question');
    }

    public function has(?string $rawOrKey): bool
    {
        return isset($this->types[$this->normalize($rawOrKey)]);
    }

    public function label(?string $rawOrKey): string
    {
        return $this->for($rawOrKey)->label();
    }

    /**
     * @return array<string, QuestionType>
     */
    public function all(): array
    {
        return $this->types;
    }

    private function slug(string $value): string
    {
        return trim((string) preg_replace('/[\s\-\/]+/', '_', mb_strtolower(trim($value))), '_');
    }
}
