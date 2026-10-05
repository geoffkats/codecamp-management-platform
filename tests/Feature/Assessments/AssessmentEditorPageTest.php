<?php

use App\Livewire\Assessments\Edit;
use App\Models\AssessmentAttempt;
use App\Models\User;
use Livewire\Livewire;

it('shows the embedded editor with tabs, questions and a working lock toggle', function () {
    [$assessment, $instructor] = assessmentForInstructor(['title' => 'Loops quiz', 'is_locked' => false]);
    $instructor->roles()->attach(\App\Models\Role::firstOrCreate(['name' => 'teacher'], ['display_name' => 'Teacher'])->id);
    addQuestion($assessment, 'multiple_choice', ['question_text' => 'What does a loop do?', 'points' => 2], [['Repeats code', true], ['Deletes code', false]]);

    Livewire::actingAs($instructor)->test(Edit::class, ['assessment' => $assessment, 'embedded' => true])
        ->assertSee('Loops quiz')
        ->assertSee('What does a loop do?')
        ->assertSee('Repeats code')
        ->assertSee('Open to students')
        ->call('toggleLock')
        ->assertSee('Locked');

    expect($assessment->fresh()->is_locked)->toBeTrue();
});

it('saves settings from the settings tab', function () {
    [$assessment, $instructor] = assessmentForInstructor();
    $instructor->roles()->attach(\App\Models\Role::firstOrCreate(['name' => 'teacher'], ['display_name' => 'Teacher'])->id);

    Livewire::actingAs($instructor)->test(Edit::class, ['assessment' => $assessment, 'embedded' => true])
        ->call('setTab', 'settings')
        ->assertSee('Save changes')
        ->set('title', 'Renamed quiz')
        ->set('passing_score', 80)
        ->call('updateAssessment')
        ->assertHasNoErrors();

    expect($assessment->fresh())
        ->title->toBe('Renamed quiz')
        ->passing_score->toEqual(80);
});

it('lets the bank picker filter unused questions, find questions used in another course, select all shown and add them', function () {
    [$assessment] = assessmentForInstructor();
    $instructor = userWithRole('admin');
    [$otherAssessment] = assessmentForInstructor();

    $usedElsewhere = addQuestion($otherAssessment, 'multiple_choice', ['question_text' => 'Used in the other course', 'created_by' => $instructor->id], [['Right answer', true], ['Wrong', false]]);
    $unused = \App\Models\Question::factory()->create(['assessment_id' => null, 'question_text' => 'Fresh bank question', 'created_by' => $instructor->id, 'status' => 'active']);

    $picker = Livewire::actingAs($instructor)->test(Edit::class, ['assessment' => $assessment, 'embedded' => true])
        ->call('openBankPicker')
        ->set('bankCourse', (string) $otherAssessment->course_id)
        ->assertSee('Used in the other course')
        ->assertSee('Right answer')
        ->set('bankCourse', 'all')
        ->set('bankUnusedOnly', true)
        ->assertSee('Fresh bank question')
        ->assertDontSee('Used in the other course')
        ->set('bankUnusedOnly', false)
        ->call('toggleAllBank');

    expect(array_map('intval', $picker->get('bankSelected')))->toEqualCanonicalizing([$usedElsewhere->id, $unused->id]);

    $picker->call('addSelectedFromBank')->assertHasNoErrors();
    expect($assessment->questions()->pluck('questions.id')->all())->toEqualCanonicalizing([$usedElsewhere->id, $unused->id]);
});

it('opens the bank picker on the current course only and keeps it there when filters are cleared', function () {
    [$assessment] = assessmentForInstructor();
    $admin = userWithRole('admin');
    [$otherAssessment] = assessmentForInstructor();
    addQuestion($otherAssessment, 'multiple_choice', ['question_text' => 'Other course question'], [['A', true], ['B', false]]);
    $mine = \App\Models\Question::factory()->create(['assessment_id' => null, 'question_text' => 'This course question', 'status' => 'active']);
    \App\Models\QuestionPlacement::create(['question_id' => $mine->id, 'course_id' => $assessment->course_id]);

    Livewire::actingAs($admin)->test(Edit::class, ['assessment' => $assessment, 'embedded' => true])
        ->call('openBankPicker')
        ->assertSet('bankCourse', 'this')
        ->assertSee('This course question')
        ->assertDontSee('Other course question')
        ->assertViewHas('bank', fn ($bank) => $bank['thisCourseCount'] === 1 && $bank['allCount'] === 2)
        ->set('bankSearch', 'question')
        ->call('clearBankFilters')
        ->assertSet('bankCourse', 'this')
        ->assertDontSee('Other course question')
        ->set('bankCourse', 'all')
        ->assertSee('Other course question');
});

it('lists submissions as percentages and counts attempts waiting for a grade', function () {
    [$assessment, $instructor] = assessmentForInstructor(['passing_score' => 70]);
    $instructor->roles()->attach(\App\Models\Role::firstOrCreate(['name' => 'teacher'], ['display_name' => 'Teacher'])->id);
    addQuestion($assessment, 'multiple_choice', ['points' => 2], [['A', true], ['B', false]]);
    addQuestion($assessment, 'multiple_choice', ['points' => 2], [['A', true], ['B', false]]);

    $graded = User::factory()->create(['name' => 'Amina Graded']);
    $waiting = User::factory()->create(['name' => 'Brian Waiting']);
    AssessmentAttempt::factory()->create([
        'assessment_id' => $assessment->id, 'user_id' => $graded->id,
        'status' => 'completed', 'completed_at' => now(), 'score' => 3, 'is_passed' => true,
    ]);
    AssessmentAttempt::factory()->create([
        'assessment_id' => $assessment->id, 'user_id' => $waiting->id,
        'status' => 'completed', 'completed_at' => now(), 'score' => null, 'is_passed' => false,
    ]);

    Livewire::actingAs($instructor)->test(Edit::class, ['assessment' => $assessment, 'embedded' => true])
        ->call('setTab', 'submissions')
        ->assertSee('Amina Graded')
        ->assertSee('75%')
        ->assertDontSee('3.0%')
        ->assertSee('Brian Waiting')
        ->assertSee('Awaiting grade')
        ->assertViewHas('submissionStats', fn ($stats) => $stats['total'] === 2 && $stats['pending'] === 1 && $stats['average'] === 75);
});
