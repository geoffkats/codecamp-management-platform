<?php

use App\Livewire\Courses\Index;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Livewire\Livewire;

function archivableCourse(int $instructorId): array
{
    $course = Course::factory()->create(['instructor_id' => $instructorId, 'title' => 'Old Robotics']);
    $module = CourseModule::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::create(['course_id' => $course->id, 'module_id' => $module->id, 'title' => 'Motors', 'order_index' => 1]);
    $quiz = Assessment::create([
        'course_id' => $course->id,
        'lesson_id' => $lesson->id,
        'title' => 'Motors quiz',
        'assessment_type' => 'quiz',
        'approval_status' => 'approved',
    ]);

    return [$course, $module, $lesson, $quiz];
}

it('lets an admin delete a course with enrolled students from the course list and restore it', function () {
    $admin = userWithRole('admin');
    [$course, $module, $lesson, $quiz] = archivableCourse($admin->id);
    $course->enrollments()->create(['user_id' => userWithRole('student')->id, 'enrolled_at' => now()]);

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->assertSeeHtml('wire:click="archiveCourse('.$course->id.')"')
        ->call('archiveCourse', $course->id)
        ->assertHasNoErrors();

    expect(Course::find($course->id))->toBeNull()
        ->and(CourseModule::find($module->id))->toBeNull()
        ->and(Lesson::find($lesson->id))->toBeNull()
        ->and(Assessment::find($quiz->id))->toBeNull()
        ->and($course->enrollments()->count())->toBe(1);

    Livewire::test(Index::class)
        ->call('setStatus', 'archived')
        ->assertSee('Old Robotics')
        ->assertSeeHtml('wire:click="restoreCourse('.$course->id.')"')
        ->call('restoreCourse', $course->id);

    expect(Course::find($course->id))->not->toBeNull()
        ->and(Lesson::find($lesson->id))->not->toBeNull()
        ->and(Assessment::find($quiz->id))->not->toBeNull();
});

it('does not let a teacher delete someone else’s course', function () {
    [$course] = archivableCourse(userWithRole('admin')->id);
    $teacher = userWithRole('teacher');
    $course->collaboratorUsers()->attach($teacher->id, ['role' => 'editor', 'invited_at' => now()]);

    $this->actingAs($teacher);

    Livewire::test(Index::class)
        ->assertDontSeeHtml('wire:click="archiveCourse('.$course->id.')"')
        ->call('archiveCourse', $course->id);

    expect(Course::find($course->id))->not->toBeNull();
});
