<?php

namespace App\Services\Assessments;

/**
 * Grading result for a whole attempt.
 */
final class AttemptGrade
{
    /**
     * @param  array<int, array<string, mixed>>  $rows  review rows in display order
     */
    public function __construct(
        public readonly array $rows,
        public readonly float $earned,
        public readonly float $max,
        public readonly float $autoMax,
        public readonly bool $needsManual,
    ) {}

    public function percentage(): float
    {
        return $this->max > 0 ? round(($this->earned / $this->max) * 100, 2) : 0.0;
    }
}
