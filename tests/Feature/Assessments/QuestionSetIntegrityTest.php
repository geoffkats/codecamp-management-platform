<?php

use App\Livewire\Assessments\Results;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\Assessments\AttemptQuestionSetGenerator;
use Livewire\Livewire;

it('freezes the question set with generation metadata when an attempt starts', function () {
    [$assessment, $user] = assessmentForInstructor();
    $first = addQuestion($assessment, 'multiple_choice', ['points' => 4], [['A', true], ['B', false]]);
    $second = addQuestion($assessment, 'short_answer', ['points' => 6, 'settings' => ['correct_answer' => 'x']]);

    [, $attempt] = openTake($assessment, $user);

    expect($attempt->hasQuestionSet())->toBeTrue()
        ->and($attempt->question_set_version)->toBe(AttemptQuestionSetGenerator::VERSION)
        ->and($attempt->question_set_source)->toBe('generated')
        ->and($attempt->question_set_review_required)->toBeFalse()
        ->and($attempt->question_set_meta)->toHaveKeys(['seed', 'algorithm', 'mode', 'assessment', 'question_versions', 'review_reasons'])
        ->and(frozenQuestionIds($attempt))->toBe([$first->id, $second->id])
        ->and($attempt->maxScore())->toBe(10.0);
});

it('grades against the snapshot when a question is edited after the attempt started', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'multiple_choice', ['points' => 10, 'question_text' => 'Capital of France?'], [['Paris', true], ['Rome', false]]);
    $paris = optionId($q, 'Paris');

    openTake($assessment, $user);

    $q->update(['question_text' => 'Capital of Italy?', 'points' => 50]);
    $q->options()->where('id', $paris)->update(['is_correct' => false]);
    $q->options()->where('option_text', 'Rome')->update(['is_correct' => true]);

    $attempt = takeAndSubmit($assessment, $user, [$q->id => $paris]);

    expect((float) $attempt->score)->toBe(10.0)
        ->and($attempt->is_passed)->toBeTrue()
        ->and($attempt->score_unit)->toBe('points')
        ->and($attempt->maxScore())->toBe(10.0)
        ->and($attempt->scorePercentage())->toBe(100.0);

    $row = $attempt->questionSet()->first();
    expect($row->snapshot['text'])->toBe('Capital of France?')
        ->and((float) $row->earned_points)->toBe(10.0)
        ->and($row->is_correct)->toBeTrue();
});

it('keeps grading when options are deleted and recreated after the attempt started', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'multiple_choice', ['points' => 10], [['Paris', true], ['Rome', false]]);
    $paris = optionId($q, 'Paris');

    openTake($assessment, $user);

    // What Assessments\Edit::saveQuestion currently does on every save
    $q->options()->delete();
    QuestionOption::factory()->create(['question_id' => $q->id, 'option_text' => 'Paris', 'is_correct' => true, 'order' => 0]);
    QuestionOption::factory()->create(['question_id' => $q->id, 'option_text' => 'Rome', 'is_correct' => false, 'order' => 1]);

    expect((float) takeAndSubmit($assessment, $user, [$q->id => $paris])->score)->toBe(10.0);
});

it('keeps a deleted question in the attempt it was part of', function (bool $force) {
    [$assessment, $user] = assessmentForInstructor();
    $kept = addQuestion($assessment, 'multiple_choice', ['points' => 5], [['A', true], ['B', false]]);
    $deleted = addQuestion($assessment, 'multiple_choice', ['points' => 5, 'question_text' => 'Soon gone'], [['C', true], ['D', false]]);
    $answerC = optionId($deleted, 'C');

    openTake($assessment, $user);
    $force ? $deleted->forceDelete() : $deleted->delete();

    $attempt = takeAndSubmit($assessment, $user, [$kept->id => optionId($kept, 'A'), $deleted->id => $answerC]);
    $row = $attempt->questionSet()->get()->last();

    expect((float) $attempt->score)->toBe(10.0)
        ->and($attempt->maxScore())->toBe(10.0)
        ->and($attempt->questionSet()->count())->toBe(2)
        ->and($row->question_id)->toBe($force ? null : $deleted->id)
        ->and($row->snapshot['text'])->toBe('Soon gone');
})->with(['soft delete' => false, 'permanent delete' => true]);

it('does not add questions created after the attempt started', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'multiple_choice', ['points' => 10], [['A', true], ['B', false]]);

    openTake($assessment, $user);
    addQuestion($assessment, 'multiple_choice', ['points' => 90], [['C', true], ['D', false]]);

    $attempt = takeAndSubmit($assessment, $user, [$q->id => optionId($q, 'A')]);

    expect(frozenQuestionIds($attempt))->toBe([$q->id])
        ->and($attempt->maxScore())->toBe(10.0)
        ->and($attempt->scorePercentage())->toBe(100.0);
});

it('keeps the same randomized question and option order across refreshes and resumes', function () {
    [$assessment, $user] = assessmentForInstructor(['is_randomized' => true, 'shuffle_options' => true]);
    foreach (range(1, 8) as $i) {
        addQuestion($assessment, 'multiple_choice', ['question_text' => "Question number {$i}"], [["Opt {$i}a", true], ["Opt {$i}b", false], ["Opt {$i}c", false], ["Opt {$i}d", false]]);
    }

    [$component, $attempt] = openTake($assessment, $user);
    $order = frozenQuestionIds($attempt);
    $optionOrders = $attempt->questionSet()->get()->map(fn ($row) => $row->presentation['option_order'])->all();

    $first = Question::find($order[0]);
    $component->call('updateAnswer', $first->id, $first->options->first()->id);

    foreach (range(1, 3) as $refresh) {
        [$again, $resumed] = openTake($assessment, $user);

        expect($resumed->id)->toBe($attempt->id)
            ->and(frozenQuestionIds($resumed))->toBe($order)
            ->and($resumed->questionSet()->get()->map(fn ($row) => $row->presentation['option_order'])->all())->toBe($optionOrders)
            ->and($resumed->questionSet()->count())->toBe(8);

        $again->assertSet('answers.'.$first->id, $first->options->first()->id)
            ->assertSee($first->question_text);
    }

    expect(AssessmentAttempt::where('assessment_id', $assessment->id)->count())->toBe(1);
});

it('renders options in the frozen presentation order', function () {
    [$assessment, $user] = assessmentForInstructor(['shuffle_options' => true]);
    addQuestion($assessment, 'multiple_choice', [], [['Alpha', true], ['Bravo', false], ['Charlie', false], ['Delta', false], ['Echo', false]]);

    [$component, $attempt] = openTake($assessment, $user);
    $row = $attempt->questionSet()->first();
    $texts = collect($row->presentation['option_order'])
        ->map(fn ($id) => collect($row->snapshot['options'])->firstWhere('id', $id)['text'])
        ->all();

    $component->assertSeeInOrder($texts);
});

it('reproduces the same order from the same seed', function () {
    [$assessment, $user] = assessmentForInstructor(['is_randomized' => true, 'shuffle_options' => true]);
    foreach (range(1, 10) as $i) {
        addQuestion($assessment, 'multiple_choice', [], [['a', true], ['b', false], ['c', false]]);
    }

    $generator = app(AttemptQuestionSetGenerator::class);
    $one = $generator->generate(AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'user_id' => $user->id]), 4242);
    $two = $generator->generate(AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'user_id' => $user->id]), 4242);

    expect(frozenQuestionIds($one))->toBe(frozenQuestionIds($two))
        ->and($one->question_set_meta['seed'])->toBe(4242);
});

it('never generates a second question set for the same attempt', function () {
    [$assessment, $user] = assessmentForInstructor(['is_randomized' => true]);
    foreach (range(1, 5) as $i) {
        addQuestion($assessment, 'multiple_choice', [], [['a', true], ['b', false]]);
    }

    $attempt = AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'user_id' => $user->id]);
    $generator = app(AttemptQuestionSetGenerator::class);
    $first = frozenQuestionIds($generator->generate($attempt, 1));
    $second = frozenQuestionIds($generator->generate($attempt->fresh(), 999));

    expect($second)->toBe($first)->and($attempt->questionSet()->count())->toBe(5);
});

it('shows the results review from the snapshot, not the edited question', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'multiple_choice', ['points' => 10, 'question_text' => 'Original wording'], [['Yes', true], ['No', false]]);

    openTake($assessment, $user);
    $attempt = takeAndSubmit($assessment, $user, [$q->id => optionId($q, 'Yes')]);
    $q->update(['question_text' => 'Rewritten wording']);

    Livewire::actingAs($user)
        ->test(Results::class, ['assessment' => $assessment, 'attempt' => $attempt])
        ->assertSee('Original wording')
        ->assertDontSee('Rewritten wording');
});
