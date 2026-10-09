<?php

use App\Livewire\Assessments\Take;
use App\Models\Assessment;
use App\Models\Course;
use App\Services\Assessments\QuestionWriter;
use Livewire\Livewire;

it('starts multiple select answers as arrays so each checkbox toggles on its own', function () {
    $admin = userWithRole('admin');
    $course = Course::factory()->create(['instructor_id' => $admin->id]);
    $quiz = Assessment::create([
        'course_id' => $course->id,
        'title' => 'Pick many',
        'assessment_type' => 'quiz',
        'max_attempts' => 3,
        'passing_score' => 70,
        'approval_status' => 'approved',
    ]);

    $question = app(QuestionWriter::class)->save(null, [
        'question_text' => 'Pick the even numbers',
        'question_type' => 'multiple_select',
        'points' => 10,
        'status' => 'active',
    ], [
        ['option_text' => '2', 'is_correct' => true],
        ['option_text' => '4', 'is_correct' => true],
        ['option_text' => '5', 'is_correct' => false],
    ], attachTo: $quiz);

    $this->actingAs($admin);

    Livewire::test(Take::class, ['assessment' => $quiz])
        ->assertSet("answers.{$question->id}", []);
});
