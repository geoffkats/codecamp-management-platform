<?php

use App\Livewire\Curriculum\NewBuilder;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Livewire\Livewire;

function lessonInCourse(Course $course, array $attributes = []): Lesson
{
    $module = CourseModule::factory()->create(['course_id' => $course->id]);

    return Lesson::create(array_merge([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Loops',
        'is_locked' => false,
    ], $attributes));
}

it('lets supervisors open enrollments, attendance and daily reports', function () {
    $supervisor = userWithRole('supervisor');

    $this->actingAs($supervisor)->get(route('admin.enrollments'))->assertOk();
    $this->actingAs($supervisor)->get(route('attendance.dashboard'))->assertOk();
    $this->actingAs($supervisor)->get(route('admin.daily-reports.index'))->assertOk();
});

it('lets operations managers open analytics', function () {
    $this->actingAs(userWithRole('operations_manager'))->get(route('analytics.dashboard'))->assertOk();
});

it('serves student progress and registration requests to admins', function () {
    $admin = userWithRole('admin');

    $this->actingAs($admin)->get(route('admin.student-progress.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.registration-requests'))->assertOk();
});

it('keeps students out of the staff pages', function () {
    $student = userWithRole('student');

    $this->actingAs($student)->get(route('admin.enrollments'))->assertForbidden();
    $this->actingAs($student)->get(route('admin.daily-reports.index'))->assertForbidden();
    $this->actingAs($student)->get(route('attendance.dashboard'))->assertForbidden();
});

it('toggles a lesson lock from the builder', function () {
    $admin = userWithRole('admin');
    $course = Course::factory()->create();
    $lesson = lessonInCourse($course);

    Livewire::actingAs($admin)
        ->test(NewBuilder::class, ['course' => $course->id])
        ->call('toggleLessonLock', $lesson->id)
        ->assertDispatched('course-structure-updated');

    expect($lesson->fresh()->is_locked)->toBeTrue();
});

it('ignores lock requests for lessons in another course', function () {
    $admin = userWithRole('admin');
    $course = Course::factory()->create();
    $otherLesson = lessonInCourse(Course::factory()->create());

    Livewire::actingAs($admin)
        ->test(NewBuilder::class, ['course' => $course->id])
        ->call('toggleLessonLock', $otherLesson->id);

    expect($otherLesson->fresh()->is_locked)->toBeFalse();
});
