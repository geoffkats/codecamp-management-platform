<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseModule>
 */
class CourseModuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->words(2, true),
            'order_index' => 0,
            'is_active' => true,
        ];
    }
}
