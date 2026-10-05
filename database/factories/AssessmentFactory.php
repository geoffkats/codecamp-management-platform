<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->words(3, true),
            'assessment_type' => 'quiz',
            'passing_score' => 70,
            'max_attempts' => 3,
            'xp_reward' => 0,
            'is_randomized' => false,
            'shuffle_options' => false,
            'show_results_immediately' => true,
            'show_correct_answers' => true,
            'allow_review' => true,
            'is_locked' => false,
            'approval_status' => 'approved',
        ];
    }

    public function randomized(): static
    {
        return $this->state(fn () => ['is_randomized' => true, 'shuffle_options' => true]);
    }
}
