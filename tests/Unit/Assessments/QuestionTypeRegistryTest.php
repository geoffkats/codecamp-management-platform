<?php

use App\Services\Assessments\QuestionTypeRegistry;
use App\Services\Assessments\QuestionTypes\ManualType;

function q(string $type, array $extra = []): array
{
    return array_merge([
        'question_id' => 1,
        'type' => $type,
        'text' => 'Q',
        'points' => 10,
        'settings' => [],
        'options' => [],
    ], $extra);
}

function opts(array $rows): array
{
    return array_map(fn ($row, $i) => ['id' => $row[0], 'text' => $row[1], 'is_correct' => $row[2], 'order' => $i], $rows, array_keys($rows));
}

beforeEach(function () {
    $this->types = new QuestionTypeRegistry;
});

it('normalizes legacy spellings to canonical keys', function (string $raw, string $canonical) {
    expect($this->types->normalize($raw))->toBe($canonical);
})->with([
    ['multiple_choice', 'multiple_choice'],
    ['Multiple Choice', 'multiple_choice'],
    ['multiple-choice', 'multiple_choice'],
    ['MCQ', 'multiple_choice'],
    ['True/False', 'true_false'],
    ['true or false', 'true_false'],
    ['Multi Select', 'multiple_select'],
    ['checkbox', 'multiple_select'],
    ['Fill Blank', 'fill_blank'],
    ['long answer', 'essay'],
    ['code', 'code_submission'],
    ['rubric', 'rubric_criteria'],
    ['upload', 'file_upload'],
]);

it('treats unknown types as manually graded rather than scoring them zero', function () {
    $type = $this->types->for('mystery_widget');

    expect($type)->toBeInstanceOf(ManualType::class)
        ->and($type->grade(q('mystery_widget'), 'anything')->needsManual)->toBeTrue();
});

it('does not have a separate Scratch question type', function () {
    expect($this->types->has('scratch_multiple_choice'))->toBeFalse()
        ->and(array_keys($this->types->all()))->not->toContain('scratch');
});

it('grades single choice from option ids', function () {
    $question = q('multiple_choice', ['options' => opts([[11, 'A', false], [12, 'B', true]])]);
    $type = $this->types->for('multiple_choice');

    expect($type->grade($question, 12)->earned)->toBe(10.0)
        ->and($type->grade($question, '12')->earned)->toBe(10.0)
        ->and($type->grade($question, 11)->earned)->toBe(0.0)
        ->and($type->grade($question, ['value' => 12])->earned)->toBe(10.0)
        ->and($type->grade($question, null)->earned)->toBe(0.0);
});

it('grades multiple select as an exact set match', function () {
    $question = q('multiple_select', ['options' => opts([[1, 'A', true], [2, 'B', true], [3, 'C', false]])]);
    $type = $this->types->for('multiple_select');

    expect($type->grade($question, [2, 1])->earned)->toBe(10.0)
        ->and($type->grade($question, [1])->earned)->toBe(0.0)
        ->and($type->grade($question, [1, 2, 3])->earned)->toBe(0.0);
});

it('grades short answers with key, alternatives and case sensitivity', function () {
    $type = $this->types->for('short_answer');
    $question = q('short_answer', ['settings' => ['correct_answer' => 'Paris', 'alternative_answers' => ['paris city']]]);

    expect($type->grade($question, ' paris ')->earned)->toBe(10.0)
        ->and($type->grade($question, 'Paris City')->earned)->toBe(10.0)
        ->and($type->grade($question, 'Lyon')->earned)->toBe(0.0);

    $sensitive = q('short_answer', ['settings' => ['correct_answer' => 'Paris', 'case_sensitive' => true]]);
    expect($type->grade($sensitive, 'paris')->earned)->toBe(0.0);
});

it('reads the nested short answer key the editor stores', function () {
    $question = q('short_answer', ['settings' => ['short_answer' => ['correct_answer' => 'HTML', 'alternative_answers' => ['hypertext markup language']]]]);
    $type = $this->types->for('short_answer');

    expect($type->grade($question, 'html')->earned)->toBe(10.0)
        ->and($type->grade($question, 'HyperText Markup Language')->earned)->toBe(10.0);
});

it('leaves short answers without a key for manual grading', function () {
    $grade = $this->types->for('short_answer')->grade(q('short_answer'), 'whatever');

    expect($grade->needsManual)->toBeTrue()->and($grade->earned)->toBeNull();
});

it('gives proportional credit for fill in the blank and accepts zero as an answer', function () {
    $type = $this->types->for('fill_blank');
    $question = q('fill_blank', ['settings' => ['fill_blank' => ['blanks' => [['correct_answer' => '0'], ['correct_answer' => 'Two']]]]]);

    expect($type->grade($question, ['0', 'two'])->earned)->toBe(10.0)
        ->and($type->grade($question, ['0', 'three'])->earned)->toBe(5.0);
});

it('gives proportional credit for matching', function () {
    $question = q('matching', ['settings' => ['matching_pairs' => [
        ['left_item' => 'Cat', 'right_item' => 'Meow'],
        ['left_item' => 'Dog', 'right_item' => 'Woof'],
    ]]]);

    expect($this->types->for('matching')->grade($question, ['Meow', 'Meow'])->earned)->toBe(5.0);
});

it('grades ordering all or nothing', function () {
    $question = q('ordering', ['settings' => ['ordering_items' => [['item_text' => 'One'], ['item_text' => 'Two'], ['item_text' => 'Three']]]]);
    $type = $this->types->for('ordering');

    expect($type->grade($question, ['One', 'Two', 'Three'])->earned)->toBe(10.0)
        ->and($type->grade($question, ['Two', 'One', 'Three'])->earned)->toBe(0.0);
});

it('always sends essays, code, rubrics and uploads to manual grading', function (string $type) {
    expect($this->types->for($type)->grade(q($type), 'some answer')->needsManual)->toBeTrue();
})->with(['essay', 'code_submission', 'rubric_criteria', 'file_upload']);
