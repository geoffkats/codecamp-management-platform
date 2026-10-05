<?php

use App\Livewire\Assessments\Manage;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use Livewire\Livewire;

it('lists only assessments from the teacher\'s courses with grading and empty filters', function () {
    $teacher = userWithRole('teacher');
    $course = Course::factory()->create(['instructor_id' => $teacher->id]);
    $graded = Assessment::factory()->create(['course_id' => $course->id, 'title' => 'Loops Quiz', 'assessment_type' => 'quiz']);
    addQuestion($graded, 'essay');
    $empty = Assessment::factory()->create(['course_id' => $course->id, 'title' => 'Empty Quiz', 'assessment_type' => 'quiz']);
    $foreign = Assessment::factory()->create(['title' => 'Someone Else Quiz']);

    AssessmentAttempt::factory()->create([
        'assessment_id' => $graded->id,
        'status' => 'completed',
        'completed_at' => now(),
        'score' => null,
    ]);

    Livewire::actingAs($teacher)->test(Manage::class)
        ->assertSee('Loops Quiz')
        ->assertSee('Empty Quiz')
        ->assertDontSee('Someone Else Quiz')
        ->assertViewHas('stats', fn ($stats) => $stats['total'] === 2 && $stats['grading'] === 1 && $stats['empty'] === 1)
        ->set('view', 'grading')
        ->assertSee('Loops Quiz')
        ->assertDontSee('Empty Quiz')
        ->set('view', 'empty')
        ->assertSee('Empty Quiz')
        ->assertDontSee('Loops Quiz');
});

it('is linked from the sidebar for staff and forbidden for students', function () {
    $teacher = userWithRole('teacher');

    $this->actingAs($teacher)->get(route('assessments.manage'))
        ->assertOk()
        ->assertSee(route('assessments.manage'), false);

    $this->actingAs(userWithRole('student'))->get(route('assessments.manage'))->assertForbidden();
});

it('opens a specific assessment in the curriculum builder from a link', function () {
    $teacher = userWithRole('teacher');
    $course = Course::factory()->create(['instructor_id' => $teacher->id]);
    $assessment = Assessment::factory()->create(['course_id' => $course->id]);

    $this->actingAs($teacher)
        ->get(route('curriculum.builder', ['course' => $course->id, 'assessment' => $assessment->id]))
        ->assertOk();

    Livewire::actingAs($teacher)
        ->withQueryParams(['assessment' => $assessment->id])
        ->test(\App\Livewire\Curriculum\NewBuilder::class, ['course' => $course->id])
        ->assertSet('selectedType', 'assessment')
        ->assertSet('selectedId', $assessment->id);
});
