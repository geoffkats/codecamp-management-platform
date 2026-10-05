<?php

namespace Database\Factories;

use App\Models\Assessment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assessment_id' => Assessment::factory(),
            'question_text' => fake()->sentence().'?',
            'question_type' => 'multiple_choice',
            'points' => 10,
            'order' => 0,
            'settings' => [],
        ];
    }
}
