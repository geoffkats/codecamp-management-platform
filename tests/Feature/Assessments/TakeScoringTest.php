<?php

/*
 * Characterization tests: these pin the scoring behaviour of Assessments\Take as it existed before the
 * grading refactor, so the consolidated grader must reproduce the same stored scores.
 */

it('awards full points for a correct single-choice answer and none for a wrong one', function (string $type) {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, $type, ['points' => 10], [['Paris', true], ['Rome', false]]);

    expect((float) takeAndSubmit($assessment, $user, [$q->id => optionId($q, 'Paris')])->score)->toBe(10.0);

    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, $type, ['points' => 10], [['Paris', true], ['Rome', false]]);

    expect((float) takeAndSubmit($assessment, $user, [$q->id => optionId($q, 'Rome')])->score)->toBe(0.0);
})->with(['multiple_choice', 'choice']);

it('scores true/false questions by option id', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'true_false', ['points' => 5], [['True', false], ['False', true]]);

    $attempt = takeAndSubmit($assessment, $user, [$q->id => optionId($q, 'False')]);

    expect((float) $attempt->score)->toBe(5.0)
        ->and($attempt->auto_scored)->toBeTrue();
});

it('requires the exact set of correct options for multiple select', function (array $picked, float $expected) {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'multiple_select', ['points' => 10], [['A', true], ['B', true], ['C', false]]);

    $answer = array_map(fn ($text) => optionId($q, $text), $picked);

    expect((float) takeAndSubmit($assessment, $user, [$q->id => $answer])->score)->toBe($expected);
})->with([
    'exact set' => [['A', 'B'], 10.0],
    'exact set reversed' => [['B', 'A'], 10.0],
    'partial' => [['A'], 0.0],
    'superset' => [['A', 'B', 'C'], 0.0],
]);

it('auto-grades short answers that have a key, case-insensitively and with alternatives', function (string $answer, float $expected) {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'short_answer', [
        'points' => 4,
        'settings' => ['correct_answer' => 'Kampala', 'alternative_answers' => ['KLA']],
    ]);

    $attempt = takeAndSubmit($assessment, $user, [$q->id => $answer]);

    expect((float) $attempt->score)->toBe($expected)
        ->and($attempt->auto_scored)->toBeTrue();
})->with([
    'exact' => ['Kampala', 4.0],
    'different case and spaces' => ['  kampala ', 4.0],
    'alternative' => ['kla', 4.0],
    'wrong' => ['Entebbe', 0.0],
]);

it('respects case sensitivity on short answers', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'short_answer', [
        'points' => 4,
        'settings' => ['correct_answer' => 'print', 'case_sensitive' => true],
    ]);

    expect((float) takeAndSubmit($assessment, $user, [$q->id => 'Print'])->score)->toBe(0.0);
});

it('sends short answers without a key to manual grading', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'short_answer', ['points' => 4]);

    $attempt = takeAndSubmit($assessment, $user, [$q->id => 'anything']);

    expect($attempt->score)->toBeNull()
        ->and($attempt->auto_scored)->toBeFalse()
        ->and($attempt->status)->toBe('completed');
});

it('gives proportional credit for fill-in-the-blank', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'fill_blank', [
        'points' => 10,
        'settings' => ['fill_blank' => ['blanks' => [
            ['correct_answer' => 'print', 'case_sensitive' => false, 'alternative_answers' => []],
            ['correct_answer' => 'input', 'case_sensitive' => false, 'alternative_answers' => ['raw_input']],
        ]]],
    ]);

    expect((float) takeAndSubmit($assessment, $user, [$q->id => ['PRINT', 'wrong']])->score)->toBe(5.0);

    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'fill_blank', [
        'points' => 10,
        'settings' => ['fill_blank' => ['blanks' => [
            ['correct_answer' => 'print', 'case_sensitive' => false, 'alternative_answers' => []],
            ['correct_answer' => 'input', 'case_sensitive' => false, 'alternative_answers' => ['raw_input']],
        ]]],
    ]);

    expect((float) takeAndSubmit($assessment, $user, [$q->id => ['print', 'raw_input']])->score)->toBe(10.0);
});

it('gives proportional credit for matching', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'matching', [
        'points' => 8,
        'settings' => ['matching_pairs' => [
            ['left_item' => 'Cat', 'right_item' => 'Meow'],
            ['left_item' => 'Dog', 'right_item' => 'Woof'],
            ['left_item' => 'Cow', 'right_item' => 'Moo'],
            ['left_item' => 'Duck', 'right_item' => 'Quack'],
        ]],
    ]);

    $attempt = takeAndSubmit($assessment, $user, [$q->id => ['Meow', 'Woof', 'Quack', 'Moo']]);

    expect((float) $attempt->score)->toBe(4.0);
});

it('scores ordering all-or-nothing against the configured item order', function (array $answer, float $expected) {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'ordering', [
        'points' => 6,
        'settings' => ['ordering_items' => [
            ['item_text' => 'Plan', 'correct_order' => 1],
            ['item_text' => 'Code', 'correct_order' => 2],
            ['item_text' => 'Test', 'correct_order' => 3],
        ]],
    ]);

    expect((float) takeAndSubmit($assessment, $user, [$q->id => $answer])->score)->toBe($expected);
})->with([
    'correct' => [['Plan', 'Code', 'Test'], 6.0],
    'wrong' => [['Code', 'Plan', 'Test'], 0.0],
    'incomplete' => [['Plan', 'Code'], 0.0],
]);

it('awards full points for any rating answer', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'rating', ['points' => 3, 'settings' => ['rating_scale' => ['min' => 1, 'max' => 5]]]);

    expect((float) takeAndSubmit($assessment, $user, [$q->id => '2'])->score)->toBe(3.0);
});

it('sends essays to manual grading', function () {
    [$assessment, $user] = assessmentForInstructor();
    $q = addQuestion($assessment, 'essay', ['points' => 20]);

    $attempt = takeAndSubmit($assessment, $user, [$q->id => 'My essay text here.']);

    expect($attempt->score)->toBeNull()
        ->and($attempt->auto_scored)->toBeFalse();
});

it('stores points, not percentages, and decides passing from the percentage', function () {
    [$assessment, $user] = assessmentForInstructor(['passing_score' => 60]);
    $a = addQuestion($assessment, 'multiple_choice', ['points' => 6], [['yes', true], ['no', false]]);
    $b = addQuestion($assessment, 'multiple_choice', ['points' => 4], [['yes', true], ['no', false]]);

    $attempt = takeAndSubmit($assessment, $user, [
        $a->id => optionId($a, 'yes'),
        $b->id => optionId($b, 'no'),
    ]);

    expect((float) $attempt->score)->toBe(6.0)
        ->and($attempt->is_passed)->toBeTrue()
        ->and($attempt->scorePercentage())->toBe(60.0);
});

it('scores unanswered questions as zero', function () {
    [$assessment, $user] = assessmentForInstructor();
    addQuestion($assessment, 'multiple_choice', ['points' => 10], [['yes', true], ['no', false]]);

    $attempt = takeAndSubmit($assessment, $user, []);

    expect((float) $attempt->score)->toBe(0.0)
        ->and($attempt->is_passed)->toBeFalse();
});
