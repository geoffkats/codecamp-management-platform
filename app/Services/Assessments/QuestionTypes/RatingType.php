<?php

namespace App\Services\Assessments\QuestionTypes;

use App\Services\Assessments\QuestionGrade;

/**
 * Opinion scale: any answer earns full points.
 */
class RatingType extends QuestionType
{
    public function key(): string
    {
        return 'rating';
    }

    public function label(): string
    {
        return 'Rating';
    }

    public function aliases(): array
    {
        return ['rating_scale', 'likert'];
    }

    public function grade(array $question, mixed $answer): QuestionGrade
    {
        if ($this->isBlank($answer)) {
            return $this->zero($question, self::NO_ANSWER);
        }

        return $this->scored($question, $this->points($question), $this->textAnswer($answer) ?: self::NO_ANSWER);
    }
}
