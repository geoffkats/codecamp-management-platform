<?php

use App\Livewire\Courses\Show;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Livewire\Livewire;

function courseWithContent(): Course
{
    $course = Course::factory()->create(['title' => 'COMPUTER ESSENTIALS', 'is_published' => false, 'enrollment_type' => 'open']);
    $module = CourseModule::factory()->create(['course_id' => $course->id, 'title' => 'FILE MANAGEMENT']);

    foreach (['Folders', 'Copying files', 'Deleting files'] as $i => $title) {
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => $title,
            'order_index' => $i,
            'is_locked' => $i === 2,
        ]);
    }

    Assessment::factory()->create(['course_id' => $course->id, 'lesson_id' => $lesson->id, 'title' => 'Files quiz']);

    return $course;
}

it('shows managers the builder actions, full outline and quizzes', function () {
    $course = courseWithContent();

    $this->actingAs(userWithRole('admin'))->get(route('courses.show', $course))
        ->assertOk()
        ->assertSee('Computer Essentials')
        ->assertSee('File Management')
        ->assertSee('Open builder')
        ->assertSee('Not published')
        ->assertSee('Deleting files')
        ->assertSee('Files quiz')
        ->assertDontSee('Similar Courses');
});

it('shows other users two lessons per module and an enroll button', function () {
    $course = courseWithContent();

    Livewire::actingAs(userWithRole('operations_manager'))
        ->test(Show::class, ['course' => $course])
        ->assertSee('Enroll')
        ->assertDontSee('Open builder')
        ->assertSee('Copying files')
        ->assertDontSee('Deleting files')
        ->assertSee('1 more lesson after you enroll');
});
