<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssessmentAttempt>
 */
class AssessmentAttemptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assessment_id' => Assessment::factory(),
            'user_id' => User::factory(),
            'student_type' => 'codecamp',
            'auto_scored' => true,
            'is_locked' => false,
            'started_at' => now()->subMinutes(10),
            'status' => 'in_progress',
            'answers' => [],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed', 'completed_at' => now()]);
    }
}
