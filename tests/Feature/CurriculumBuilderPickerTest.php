<?php

use App\Livewire\Curriculum\NewBuilder;
use App\Models\Course;
use App\Models\CourseModule;
use Livewire\Livewire;

it('lists courses once, with counts, and hides the outline panel until a course is open', function () {
    $admin = userWithRole('admin');
    $course = Course::factory()->create(['title' => 'Scratch Adventures', 'instructor_id' => $admin->id]);
    CourseModule::factory()->count(2)->create(['course_id' => $course->id]);
    Course::factory()->create(['title' => 'Python Basics', 'instructor_id' => $admin->id]);

    $this->actingAs($admin);

    Livewire::test(NewBuilder::class)
        ->assertSee('Scratch Adventures')
        ->assertSee('Python Basics')
        ->assertSee('2 modules')
        ->assertDontSeeHtml('id="curriculum-outline"')
        ->set('courseSearch', 'scratch')
        ->assertSee('Scratch Adventures')
        ->assertDontSee('Python Basics')
        ->set('courseSearch', 'nothing like this')
        ->assertSee('No courses match');
});

it('only shows a trainer their own and shared courses', function () {
    $trainer = userWithRole('codecamp_trainer');
    $other = userWithRole('codecamp_trainer');
    Course::factory()->create(['title' => 'My Robotics', 'instructor_id' => $trainer->id]);
    Course::factory()->create(['title' => 'Someone Elses Course', 'instructor_id' => $other->id]);

    $this->actingAs($trainer);

    Livewire::test(NewBuilder::class)
        ->assertSee('My Robotics')
        ->assertDontSee('Someone Elses Course')
        ->set('courseFilter', 'shared')
        ->assertDontSee('My Robotics');
});
