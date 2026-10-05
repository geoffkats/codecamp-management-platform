<?php

use App\Livewire\Assessments\Take;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $roleModel = \App\Models\Role::firstOrCreate(['name' => $role], ['display_name' => ucfirst($role)]);
    $user->roles()->attach($roleModel->id);

    return $user;
}

/**
 * An assessment whose course is taught by the returned user, so Take::mount skips enrolment checks.
 *
 * @return array{0: Assessment, 1: User}
 */
function assessmentForInstructor(array $attributes = []): array
{
    $instructor = User::factory()->create();
    $course = Course::factory()->create(['instructor_id' => $instructor->id]);
    $assessment = Assessment::factory()->create(array_merge(['course_id' => $course->id], $attributes));

    return [$assessment, $instructor];
}

/**
 * @param  array<int, array{0: string, 1: bool}>  $options  [text, is_correct]
 */
function addQuestion(Assessment $assessment, string $type, array $attributes = [], array $options = []): Question
{
    $question = Question::factory()->create(array_merge([
        'assessment_id' => $assessment->id,
        'question_type' => $type,
        'order' => Question::where('assessment_id', $assessment->id)->count() + 1,
    ], $attributes));

    foreach (array_values($options) as $index => [$text, $correct]) {
        QuestionOption::factory()->create([
            'question_id' => $question->id,
            'option_text' => $text,
            'is_correct' => $correct,
            'order' => $index,
        ]);
    }

    return $question->load('options');
}

function optionId(Question $question, string $text): int
{
    return (int) $question->options->firstWhere('option_text', $text)->id;
}

/**
 * Open the Take screen (which starts or resumes the attempt) and return the component plus its attempt.
 *
 * @return array{0: \Livewire\Features\SupportTesting\Testable, 1: AssessmentAttempt}
 */
function openTake(Assessment $assessment, User $user): array
{
    $component = Livewire::actingAs($user)->test(Take::class, ['assessment' => $assessment]);

    $attempt = AssessmentAttempt::where('assessment_id', $assessment->id)
        ->where('user_id', $user->id)
        ->latest('id')
        ->firstOrFail();

    return [$component, $attempt];
}

/**
 * @return array<int, int> question ids in the attempt's frozen order
 */
function frozenQuestionIds(AssessmentAttempt $attempt): array
{
    return $attempt->questionSet()->get()->map(fn ($row) => (int) $row->snapshot['question_id'])->all();
}

/**
 * Take the assessment through the real Livewire component and submit the given answers.
 *
 * @param  array<int|string, mixed>  $answers
 */
function takeAndSubmit(Assessment $assessment, User $user, array $answers): AssessmentAttempt
{
    Livewire::actingAs($user)
        ->test(Take::class, ['assessment' => $assessment])
        ->set('answers', $answers)
        ->call('submitAssessment');

    return AssessmentAttempt::where('assessment_id', $assessment->id)
        ->where('user_id', $user->id)
        ->latest('id')
        ->firstOrFail();
}
