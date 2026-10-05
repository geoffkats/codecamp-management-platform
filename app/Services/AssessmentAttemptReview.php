<?php

namespace App\Services;

use App\Models\AssessmentAttempt;
use App\Services\Assessments\AssessmentGradingService;

class AssessmentAttemptReview
{
    public function __construct(private readonly AssessmentGradingService $grading) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rows(AssessmentAttempt $attempt): array
    {
        return $this->grading->reviewRows($attempt);
    }

    public function autoEarnedPoints(AssessmentAttempt $attempt): float
    {
        return round($this->grading->gradeAttempt($attempt)->earned, 1);
    }
}
