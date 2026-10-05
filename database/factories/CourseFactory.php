<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => Str::title($title),
            'slug' => Str::slug($title).'-'.Str::random(6),
            'description' => fake()->sentence(),
            'instructor_id' => User::factory(),
            'is_published' => true,
            'approval_status' => 'approved',
        ];
    }
}
