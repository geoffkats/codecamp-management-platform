<?php

use App\Livewire\Assessments\Create;
use App\Livewire\Assessments\Edit;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Services\Assessments\AttemptQuestionSetGenerator;
use Livewire\Livewire;

function poolAssessment(int $poolSize, ?int $perAttempt): Assessment
{
    [$assessment] = assessmentForInstructor(['questions_per_attempt' => $perAttempt, 'is_randomized' => false]);
    for ($i = 1; $i <= $poolSize; $i++) {
        addQuestion($assessment, 'multiple_choice', ['points' => $i], [['Yes', true], ['No', false]]);
    }

    return $assessment->fresh();
}

function freshAttempt(Assessment $assessment, int $seed): AssessmentAttempt
{
    $attempt = AssessmentAttempt::factory()->create([
        'assessment_id' => $assessment->id,
        'status' => 'in_progress',
        'completed_at' => null,
        'score' => null,
    ]);

    return app(AttemptQuestionSetGenerator::class)->generate($attempt, $seed);
}

it('draws the configured number of distinct questions from the pool for each attempt', function () {
    $assessment = poolAssessment(20, 10);
    $poolIds = $assessment->questions()->pluck('questions.id')->map(fn ($id) => (int) $id)->all();

    $attempt = freshAttempt($assessment, 1234);
    $ids = frozenQuestionIds($attempt);

    expect($ids)->toHaveCount(10)
        ->and(array_unique($ids))->toHaveCount(10)
        ->and(array_diff($ids, $poolIds))->toBe([])
        ->and($attempt->question_set_meta['mode'])->toBe('random_pool')
        ->and($attempt->question_set_meta['pool_size'])->toBe(20)
        ->and($attempt->question_set_meta['drawn'])->toBe(10)
        ->and($attempt->maxScore())->toBe((float) $attempt->questionSet()->sum('points'));
});

it('gives different attempts different random sets and orders', function () {
    $assessment = poolAssessment(20, 10);

    $sets = collect(range(1, 6))->map(fn ($seed) => frozenQuestionIds(freshAttempt($assessment, $seed * 7919)));

    expect($sets->map(fn ($ids) => implode(',', $ids))->unique()->count())->toBeGreaterThan(1)
        ->and($sets->map(fn ($ids) => implode(',', collect($ids)->sort()->all()))->unique()->count())->toBeGreaterThan(1);
});

it('keeps an attempt frozen when the pool changes afterwards', function () {
    $assessment = poolAssessment(12, 5);
    $attempt = freshAttempt($assessment, 42);
    $before = frozenQuestionIds($attempt);

    addQuestion($assessment, 'multiple_choice', [], [['A', true], ['B', false]]);
    $assessment->update(['questions_per_attempt' => 8]);

    $regenerated = app(AttemptQuestionSetGenerator::class)->generate($attempt->fresh(), 99);

    expect(frozenQuestionIds($regenerated))->toBe($before);
});

it('gives everyone every question when no pool size is set or it exceeds the pool', function (?int $perAttempt) {
    $assessment = poolAssessment(6, $perAttempt);
    $attempt = freshAttempt($assessment, 5);

    expect(frozenQuestionIds($attempt))->toBe($assessment->questions()->pluck('questions.id')->map(fn ($id) => (int) $id)->all())
        ->and($attempt->question_set_meta['mode'])->toBe('manual');
})->with([null, 6, 50]);

it('lets staff set the pool size from the editor and the create page', function () {
    $admin = userWithRole('admin');
    $course = Course::factory()->create(['instructor_id' => $admin->id]);
    $assessment = poolAssessment(8, null);
    $assessment->update(['course_id' => $course->id]);

    Livewire::actingAs($admin)->test(Edit::class, ['assessment' => $assessment])
        ->call('savePool', 4)
        ->assertHasNoErrors()
        ->assertSee('each student gets 4 of 8 questions');
    expect($assessment->fresh()->questions_per_attempt)->toBe(4);

    Livewire::actingAs($admin)->test(Edit::class, ['assessment' => $assessment->fresh()])
        ->call('savePool', null);
    expect($assessment->fresh()->questions_per_attempt)->toBeNull();

    Livewire::actingAs($admin)->test(Create::class)
        ->set('course_id', $course->id)
        ->set('title', 'Pooled quiz')
        ->set('questions_per_attempt', 10)
        ->call('save')
        ->assertHasNoErrors();
    expect(Assessment::where('title', 'Pooled quiz')->value('questions_per_attempt'))->toBe(10);
});
