<?php

namespace App\Support;

class LetterGrade
{
    public static function fromPercentage(float $percentage): string
    {
        return match (true) {
            $percentage >= 97 => 'A+',
            $percentage >= 93 => 'A',
            $percentage >= 90 => 'A-',
            $percentage >= 87 => 'B+',
            $percentage >= 83 => 'B',
            $percentage >= 80 => 'B-',
            $percentage >= 77 => 'C+',
            $percentage >= 73 => 'C',
            $percentage >= 70 => 'C-',
            $percentage >= 67 => 'D+',
            $percentage >= 63 => 'D',
            $percentage >= 60 => 'D-',
            default => 'F',
        };
    }
}
