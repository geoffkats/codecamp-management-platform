<?php

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\StudentLessonProgress;
use App\Services\LessonCompletionService;
use Illuminate\Support\Carbon;

function lessonWithEnrollment(array $lessonAttrs = []): array
{
    $student = userWithRole('student');
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::create(array_merge([
        'course_id' => $course->id,
        'module_id' => $module->id,
        'title' => 'Intro to Scratch',
        'order_index' => 1,
        'duration_minutes' => 15,
        'is_locked' => false,
    ], $lessonAttrs));

    CourseEnrollment::create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'enrolled_at' => now(),
        'progress_percentage' => 0,
        'lessons_completed' => 0,
    ]);

    return [$student, $course, $lesson];
}

it('does not demote a completed lesson when the student reopens it', function () {
    [$student, $course, $lesson] = lessonWithEnrollment();
    $service = app(LessonCompletionService::class);

    StudentLessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lesson->id,
        'status' => 'completed',
        'progress_percentage' => 100,
        'started_at' => now()->subHour(),
        'completed_at' => now()->subMinutes(10),
    ]);

    LessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lesson->id,
        'is_completed' => true,
        'completed_at' => now()->subMinutes(10),
    ]);

    $service->ensureStarted($student, $lesson);

    $progress = StudentLessonProgress::where('user_id', $student->id)
        ->where('lesson_id', $lesson->id)
        ->first();

    expect($progress->status)->toBe('completed')
        ->and($progress->completed_at)->not->toBeNull()
        ->and($service->isLessonCompleted($student, $lesson))->toBeTrue();
});

it('heals wiped student progress when lesson_progress still says completed', function () {
    [$student, , $lesson] = lessonWithEnrollment();
    $service = app(LessonCompletionService::class);

    StudentLessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lesson->id,
        'status' => 'in_progress',
        'progress_percentage' => 100,
        'started_at' => now()->subHour(),
        'completed_at' => now()->subMinutes(10),
    ]);

    LessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lesson->id,
        'is_completed' => true,
        'completed_at' => now()->subMinutes(10),
    ]);

    $service->ensureStarted($student, $lesson);

    expect(StudentLessonProgress::where('user_id', $student->id)->where('lesson_id', $lesson->id)->value('status'))
        ->toBe('completed');
});

it('uses absolute minutes for time-only lesson completion', function () {
    [$student, , $lesson] = lessonWithEnrollment(['duration_minutes' => 10]);
    $service = app(LessonCompletionService::class);

    StudentLessonProgress::create([
        'user_id' => $student->id,
        'lesson_id' => $lesson->id,
        'status' => 'in_progress',
        'started_at' => Carbon::now()->subMinutes(12),
    ]);

    $check = $service->canCompleteLesson($lesson, $student);

    expect($check['can_complete'])->toBeTrue()
        ->and($check['requirements']['time_spent_minutes'])->toBeGreaterThanOrEqual(10);
});

it('auto-completes a lesson after an assignment assessment is submitted', function () {
    [$student, $course, $lesson] = lessonWithEnrollment();
    $service = app(LessonCompletionService::class);

    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'lesson_id' => $lesson->id,
        'assessment_type' => 'assignment',
        'title' => 'Submit your project',
    ]);

    AssessmentAttempt::factory()->create([
        'assessment_id' => $assessment->id,
        'user_id' => $student->id,
        'status' => 'completed',
        'completed_at' => now(),
        'is_passed' => false,
    ]);

    $result = $service->completeIfEligible($student, $lesson);

    expect($result['completed'])->toBeTrue()
        ->and($service->isLessonCompleted($student, $lesson))->toBeTrue()
        ->and(StudentLessonProgress::where('user_id', $student->id)->where('lesson_id', $lesson->id)->value('status'))
        ->toBe('completed')
        ->and(CourseEnrollment::where('user_id', $student->id)->where('course_id', $course->id)->value('lessons_completed'))
        ->toBe(1);
});

it('auto-completes a lesson after a quiz is passed', function () {
    [$student, $course, $lesson] = lessonWithEnrollment();
    $service = app(LessonCompletionService::class);

    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'lesson_id' => $lesson->id,
        'assessment_type' => 'quiz',
        'title' => 'Scratch quiz',
        'passing_score' => 70,
    ]);

    AssessmentAttempt::factory()->create([
        'assessment_id' => $assessment->id,
        'user_id' => $student->id,
        'status' => 'completed',
        'completed_at' => now(),
        'is_passed' => true,
        'score' => 80,
    ]);

    $result = $service->completeIfEligible($student, $lesson);

    expect($result['completed'])->toBeTrue()
        ->and($service->isLessonCompleted($student, $lesson))->toBeTrue();
});

it('does not complete a lesson when the quiz was submitted but not passed', function () {
    [$student, $course, $lesson] = lessonWithEnrollment();
    $service = app(LessonCompletionService::class);

    $assessment = Assessment::factory()->create([
        'course_id' => $course->id,
        'lesson_id' => $lesson->id,
        'assessment_type' => 'quiz',
        'passing_score' => 70,
    ]);

    AssessmentAttempt::factory()->create([
        'assessment_id' => $assessment->id,
        'user_id' => $student->id,
        'status' => 'completed',
        'completed_at' => now(),
        'is_passed' => false,
        'score' => 40,
    ]);

    $result = $service->completeIfEligible($student, $lesson);

    expect($result['completed'])->toBeFalse()
        ->and($service->isLessonCompleted($student, $lesson))->toBeFalse();
});
