<?php

use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Services\Assessments\LegacyQuestionSetBackfiller as Backfiller;

/*
 * Amendment 1: preserve the exact observable order where it can be reconstructed deterministically,
 * otherwise flag for migration review — and never touch existing scores or answers.
 */

/**
 * Questions created in the past, so an attempt started an hour ago predates them unless changed.
 */
function legacySetup(array $assessmentAttributes = []): array
{
    test()->travelTo(now()->subDays(2));
    [$assessment, $user] = assessmentForInstructor($assessmentAttributes);
    test()->travelBack();

    return [$assessment, $user];
}

function pastQuestion($assessment, string $type, array $attributes = [], array $options = []): Question
{
    test()->travelTo(now()->subDays(2));
    $question = addQuestion($assessment, $type, $attributes, $options);
    test()->travelBack();

    return $question;
}

function legacyAttempt($assessment, $user, array $attributes = []): AssessmentAttempt
{
    return AssessmentAttempt::factory()->create(array_merge([
        'assessment_id' => $assessment->id,
        'user_id' => $user->id,
        'started_at' => now()->subHour(),
    ], $attributes));
}

it('reconstructs the exact legacy order for non-randomized attempts without flagging them', function () {
    [$assessment, $user] = legacySetup();
    $third = pastQuestion($assessment, 'multiple_choice', ['order' => 3], [['c1', true], ['c2', false]]);
    $first = pastQuestion($assessment, 'multiple_choice', ['order' => 1], [['a2', false], ['a1', true]]);
    $second = pastQuestion($assessment, 'short_answer', ['order' => 2, 'settings' => ['correct_answer' => 'b']]);

    $answers = [$first->id => optionId($first, 'a1')];
    $attempt = app(Backfiller::class)->backfill(legacyAttempt($assessment, $user, ['answers' => $answers]));

    expect(frozenQuestionIds($attempt))->toBe([$first->id, $second->id, $third->id])
        ->and($attempt->question_set_source)->toBe('backfill')
        ->and($attempt->question_set_review_required)->toBeFalse()
        ->and($attempt->question_set_meta['review_reasons'])->toBe([])
        ->and($attempt->answers)->toBe($answers)
        ->and($attempt->status)->toBe('in_progress');

    // Legacy Take sorted options by `order`; the snapshot keeps that exact sequence
    $firstRow = $attempt->questionSet()->first();
    expect($firstRow->presentation['option_order'])->toBe($first->options->sortBy('order')->pluck('id')->map(fn ($id) => (int) $id)->values()->all());
});

it('flags randomized in-progress attempts because legacy shuffles were never persisted', function () {
    [$assessment, $user] = legacySetup(['is_randomized' => true]);
    $a = pastQuestion($assessment, 'multiple_choice', ['order' => 1], [['x', true]]);
    $b = pastQuestion($assessment, 'multiple_choice', ['order' => 2], [['y', true]]);

    $attempt = app(Backfiller::class)->backfill(legacyAttempt($assessment, $user));

    expect($attempt->question_set_review_required)->toBeTrue()
        ->and($attempt->question_set_meta['review_reasons'])->toContain(Backfiller::RANDOM_ORDER_NOT_PERSISTED)
        // canonical order is recorded, never a fresh random one
        ->and(frozenQuestionIds($attempt))->toBe([$a->id, $b->id]);
});

it('flags in-progress attempts whose option order was shuffled', function () {
    [$assessment, $user] = legacySetup(['shuffle_options' => true]);
    pastQuestion($assessment, 'multiple_choice', [], [['x', true], ['y', false]]);

    $attempt = app(Backfiller::class)->backfill(legacyAttempt($assessment, $user));

    expect($attempt->question_set_meta['review_reasons'])->toBe([Backfiller::OPTION_ORDER_NOT_PERSISTED]);
});

it('does not flag shuffle settings for completed attempts whose order no longer matters', function () {
    [$assessment, $user] = legacySetup(['is_randomized' => true, 'shuffle_options' => true]);
    pastQuestion($assessment, 'multiple_choice', [], [['x', true], ['y', false]]);
    pastQuestion($assessment, 'multiple_choice', [], [['x', true], ['y', false]]);

    $attempt = app(Backfiller::class)->backfill(legacyAttempt($assessment, $user, ['status' => 'completed', 'completed_at' => now()->subMinutes(30)]));

    expect($attempt->question_set_review_required)->toBeFalse();
});

it('flags ambiguous legacy ordering', function () {
    [$assessment, $user] = legacySetup();
    pastQuestion($assessment, 'multiple_choice', ['order' => 1], [['x', true]]);
    pastQuestion($assessment, 'multiple_choice', ['order' => 1], [['y', true]]);

    $attempt = app(Backfiller::class)->backfill(legacyAttempt($assessment, $user));

    expect($attempt->question_set_meta['review_reasons'])->toContain(Backfiller::AMBIGUOUS_ORDER);
});

it('flags attempts whose questions changed after they started', function () {
    [$assessment, $user] = legacySetup();
    $q = pastQuestion($assessment, 'multiple_choice', [], [['x', true], ['y', false]]);
    $attempt = legacyAttempt($assessment, $user);

    $q->update(['question_text' => 'Edited later']);

    $attempt = app(Backfiller::class)->backfill($attempt);

    expect($attempt->question_set_meta['review_reasons'])->toBe([Backfiller::QUESTIONS_CHANGED]);
});

it('flags attempts whose answers reference questions or options that no longer exist', function () {
    [$assessment, $user] = legacySetup();
    $q = pastQuestion($assessment, 'multiple_choice', [], [['x', true], ['y', false]]);

    $attempt = app(Backfiller::class)->backfill(legacyAttempt($assessment, $user, [
        'answers' => [$q->id => 999999, 888888 => 'orphan', 'feedback' => 'kept'],
    ]));

    expect($attempt->question_set_meta['review_reasons'])
        ->toContain(Backfiller::MISSING_QUESTIONS)
        ->toContain(Backfiller::MISSING_OPTIONS)
        ->and($attempt->answers['feedback'])->toBe('kept');
});

it('never changes the score, answers or pass state of completed attempts', function () {
    [$assessment, $user] = legacySetup();
    $q = pastQuestion($assessment, 'multiple_choice', ['points' => 8], [['x', true], ['y', false]]);
    pastQuestion($assessment, 'essay', ['points' => 2]);

    $answers = [$q->id => optionId($q, 'y'), 'feedback' => 'Nice', 'question_scores' => [$q->id => 8]];
    $attempt = legacyAttempt($assessment, $user, [
        'status' => 'completed',
        'completed_at' => now()->subMinutes(30),
        'score' => 9.5,
        'is_passed' => true,
        'auto_scored' => false,
        'answers' => $answers,
    ]);
    $maxBefore = $attempt->maxScore();

    $after = app(Backfiller::class)->backfill($attempt);

    expect((float) $after->score)->toBe(9.5)
        ->and($after->is_passed)->toBeTrue()
        ->and($after->auto_scored)->toBeFalse()
        ->and($after->answers)->toBe($answers)
        ->and($after->score_unit)->toBeNull()
        ->and($after->maxScore())->toBe($maxBefore);
});

it('resumes a legacy in-progress attempt through Take without reshuffling or losing answers', function () {
    [$assessment, $user] = legacySetup();
    $a = pastQuestion($assessment, 'multiple_choice', ['order' => 1, 'question_text' => 'First legacy question'], [['x', true], ['y', false]]);
    $b = pastQuestion($assessment, 'multiple_choice', ['order' => 2], [['x', true], ['y', false]]);
    $legacy = legacyAttempt($assessment, $user, ['answers' => [$a->id => optionId($a, 'x')]]);

    [$component, $attempt] = openTake($assessment, $user);

    expect($attempt->id)->toBe($legacy->id)
        ->and($attempt->question_set_source)->toBe('backfill')
        ->and(frozenQuestionIds($attempt))->toBe([$a->id, $b->id]);

    $component->assertSet('answers.'.$a->id, optionId($a, 'x'))->assertSee('First legacy question');
});

it('reports without writing on a dry run, then snapshots and lists flagged attempts', function () {
    [$assessment, $user] = legacySetup(['is_randomized' => true]);
    pastQuestion($assessment, 'multiple_choice', [], [['x', true]]);
    pastQuestion($assessment, 'multiple_choice', [], [['y', true]]);
    $attempt = legacyAttempt($assessment, $user);

    $this->artisan('assessments:snapshot-attempts', ['--dry-run' => true])
        ->expectsOutputToContain('1 flagged for migration review')
        ->assertSuccessful();

    expect($attempt->fresh()->hasQuestionSet())->toBeFalse();

    $this->artisan('assessments:snapshot-attempts')
        ->expectsOutputToContain('Snapshotted 1 attempt(s); 1 flagged')
        ->assertSuccessful();

    expect($attempt->fresh()->hasQuestionSet())->toBeTrue()
        ->and($attempt->fresh()->question_set_review_required)->toBeTrue();
});
