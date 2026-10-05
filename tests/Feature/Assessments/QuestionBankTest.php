<?php

use App\Livewire\Assessments\Edit;
use App\Livewire\Questions\Index;
use App\Livewire\Questions\QuestionEditor;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Question;
use App\Models\Tag;
use App\Services\Assessments\QuestionBankBackfill;
use App\Services\Assessments\QuestionWriter;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function teacherWithAssessment(): array
{
    $teacher = userWithRole('teacher');
    $course = Course::factory()->create(['instructor_id' => $teacher->id]);
    $assessment = Assessment::factory()->create(['course_id' => $course->id, 'assessment_type' => 'quiz']);

    return [$teacher, $course, $assessment];
}

it('backfills assessment links and course placements idempotently', function () {
    [$assessment] = assessmentForInstructor();
    $first = addQuestion($assessment, 'multiple_choice', ['order' => 7]);
    $second = addQuestion($assessment, 'essay', ['order' => 3]);

    DB::table('assessment_question')->delete();
    DB::table('question_placements')->delete();

    QuestionBankBackfill::run();
    QuestionBankBackfill::run();

    expect(DB::table('assessment_question')->count())->toBe(2)
        ->and(DB::table('question_placements')->where('course_id', $assessment->course_id)->count())->toBe(2)
        ->and($assessment->questions()->pluck('questions.id')->all())->toBe([$second->id, $first->id]);
});

it('pins a newly created question to its origin assessment after the existing ones', function () {
    [$assessment] = assessmentForInstructor();
    $a = addQuestion($assessment, 'essay', ['order' => 5]);
    $b = Question::factory()->create(['assessment_id' => $assessment->id, 'order' => 0]);

    $positions = DB::table('assessment_question')->where('assessment_id', $assessment->id)->pluck('position', 'question_id');

    expect((int) $positions[$a->id])->toBe(5)
        ->and((int) $positions[$b->id])->toBe(6);
});

it('shares one question between assessments and applies edits to both', function () {
    [$one] = assessmentForInstructor();
    [$two] = assessmentForInstructor();
    $question = addQuestion($one, 'essay', ['question_text' => 'Original?']);

    $two->pinQuestions([$question->id]);
    $question->update(['question_text' => 'Edited?']);

    expect($two->questions()->pluck('question_text')->all())->toBe(['Edited?'])
        ->and($one->questions()->pluck('question_text')->all())->toBe(['Edited?'])
        ->and($question->fresh()->version)->toBe(2);
});

it('keeps option ids when options are edited and bumps the version', function () {
    [$assessment] = assessmentForInstructor();
    $question = addQuestion($assessment, 'multiple_choice', [], [['Paris', true], ['Rome', false]]);
    $ids = $question->options->pluck('id')->all();

    $saved = app(QuestionWriter::class)->save($question, ['question_type' => 'multiple_choice'], [
        ['id' => $ids[0], 'option_text' => 'Paris', 'is_correct' => true],
        ['id' => $ids[1], 'option_text' => 'Madrid', 'is_correct' => false],
        ['option_text' => 'Berlin', 'is_correct' => false],
    ]);

    expect($saved->options->pluck('id')->take(2)->all())->toBe($ids)
        ->and($saved->options->pluck('option_text')->all())->toBe(['Paris', 'Madrid', 'Berlin'])
        ->and($saved->version)->toBe(2);
});

it('records the question version an attempt was given', function () {
    [$assessment, $user] = assessmentForInstructor();
    $question = addQuestion($assessment, 'essay');
    $question->update(['question_text' => 'Changed before anyone started?']);

    [, $attempt] = openTake($assessment, $user);
    $question->update(['question_text' => 'Changed again after the start?']);

    expect($attempt->questionSet()->first()->snapshot['version'])->toBe(2)
        ->and($question->fresh()->version)->toBe(3);
});

it('sorts ordering items by their correct position on save', function () {
    $saved = app(QuestionWriter::class)->save(null, [
        'question_text' => 'Order these',
        'question_type' => 'ordering',
        'points' => 5,
        'settings' => ['ordering_items' => [
            ['item_text' => 'Third', 'correct_order' => 3],
            ['item_text' => 'First', 'correct_order' => 1],
            ['item_text' => 'Second', 'correct_order' => 2],
        ]],
    ]);

    expect(array_column($saved->settings['ordering_items'], 'item_text'))->toBe(['First', 'Second', 'Third'])
        ->and($saved->options->pluck('option_text')->all())->toBe(['First', 'Second', 'Third']);
});

it('saves a short-answer key from the editor and auto-grades it', function () {
    [$teacher, , $assessment] = teacherWithAssessment();

    Livewire::actingAs($teacher)
        ->test(QuestionEditor::class, ['assessmentId' => $assessment->id])
        ->set('questionFormData.question_text', 'What does HTML stand for?')
        ->set('questionFormData.question_type', 'short_answer')
        ->set('questionFormData.points', 4)
        ->set('shortAnswer.correct_answer', 'HyperText Markup Language')
        ->set('shortAnswer.alternatives', "HTML\nhypertext markup language")
        ->set('tagInput', 'html, Basics')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('question-saved');

    $question = $assessment->questions()->sole();
    expect($question->settings['correct_answer'])->toBe('HyperText Markup Language')
        ->and($question->settings['alternative_answers'])->toBe(['HTML', 'hypertext markup language'])
        ->and($question->tags->pluck('name')->all())->toBe(['Basics', 'html'])
        ->and($question->placements->pluck('course_id')->all())->toBe([$assessment->course_id])
        ->and($question->created_by)->toBe($teacher->id);

    openTake($assessment, $teacher);
    $attempt = takeAndSubmit($assessment, $teacher, [$question->id => 'html']);

    expect((float) $attempt->score)->toBe(4.0);
});

it('lets a teacher add active bank questions from their courses and excludes archived ones', function () {
    [$teacher, $course, $assessment] = teacherWithAssessment();
    $other = Assessment::factory()->create(['course_id' => $course->id]);
    $active = addQuestion($other, 'essay', ['question_text' => 'Reusable essay']);
    $archived = addQuestion($other, 'essay', ['question_text' => 'Old essay', 'status' => 'archived']);

    $component = Livewire::actingAs($teacher)
        ->test(Edit::class, ['assessment' => $assessment])
        ->call('openBankPicker')
        ->assertSee('Reusable essay')
        ->assertDontSee('Old essay')
        ->set('bankSelected', [(string) $active->id, (string) $archived->id])
        ->call('addSelectedFromBank');

    expect($assessment->questions()->pluck('questions.id')->all())->toBe([$active->id]);

    $component->call('deleteQuestion', $active->id);

    expect($assessment->questions()->count())->toBe(0)
        ->and(Question::find($active->id))->not->toBeNull()
        ->and($other->questions()->pluck('questions.id')->all())->toContain($active->id);
});

it('reorders questions through the link table', function () {
    [$teacher, , $assessment] = teacherWithAssessment();
    $a = addQuestion($assessment, 'essay');
    $b = addQuestion($assessment, 'essay');

    Livewire::actingAs($teacher)
        ->test(Edit::class, ['assessment' => $assessment])
        ->call('moveQuestion', $b->id, -1);

    expect($assessment->questions()->pluck('questions.id')->all())->toBe([$b->id, $a->id]);
});

it('scopes the bank by role', function () {
    [$teacher, , $assessment] = teacherWithAssessment();
    $mine = addQuestion($assessment, 'essay');

    [$foreignAssessment] = assessmentForInstructor();
    $foreign = addQuestion($foreignAssessment, 'essay');

    $admin = userWithRole('admin');
    $student = userWithRole('student');

    expect(Question::visibleTo($teacher)->pluck('id')->all())->toContain($mine->id)->not->toContain($foreign->id)
        ->and(Question::visibleTo($admin)->pluck('id')->all())->toContain($mine->id, $foreign->id)
        ->and(Question::visibleTo($student)->count())->toBe(0);

    $this->actingAs($student)->get(route('questions.index'))->assertForbidden();
    $this->actingAs($teacher)->get(route('questions.edit', $foreign))->assertForbidden();
    $this->actingAs($teacher)->get(route('questions.edit', $mine))->assertOk();
    $this->actingAs($teacher)->get(route('questions.index'))->assertOk();
});

it('duplicates a question as a draft with options and tags, and archives in bulk', function () {
    [$teacher, , $assessment] = teacherWithAssessment();
    $source = addQuestion($assessment, 'multiple_choice', [], [['Yes', true], ['No', false]]);
    $source->tags()->sync(Tag::findOrCreateMany(['logic'])->pluck('id'));

    $copy = app(QuestionWriter::class)->duplicate($source);

    expect($copy->id)->not->toBe($source->id)
        ->and($copy->status)->toBe('draft')
        ->and($copy->options->pluck('option_text')->all())->toBe(['Yes', 'No'])
        ->and($copy->options->pluck('id')->intersect($source->options->pluck('id'))->all())->toBe([])
        ->and($copy->tags->pluck('name')->all())->toBe(['logic'])
        ->and($copy->assessments()->count())->toBe(0);

    Livewire::actingAs($teacher)
        ->test(Index::class)
        ->set('selected', [(string) $source->id])
        ->call('bulk', 'archive');

    expect($source->fresh()->status)->toBe('archived');
});

it('drops a deleted question from assessments while started attempts keep it', function () {
    [$assessment, $user] = assessmentForInstructor();
    $keep = addQuestion($assessment, 'essay');
    $gone = addQuestion($assessment, 'essay');

    [, $attempt] = openTake($assessment, $user);
    $gone->delete();

    expect($assessment->questions()->pluck('questions.id')->all())->toBe([$keep->id])
        ->and(frozenQuestionIds($attempt->fresh()))->toBe([$keep->id, $gone->id]);
});
