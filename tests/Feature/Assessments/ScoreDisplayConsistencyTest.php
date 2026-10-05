<?php

use App\Livewire\Quizzes\Show as QuizShow;
use App\Livewire\Quizzes\Take as QuizTake;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Services\Assessments\AttemptQuestionSetGenerator;
use Livewire\Livewire;

it('computes the same percentage in SQL as in PHP, with and without a frozen question set', function () {
    [$assessment] = assessmentForInstructor(['questions_per_attempt' => 2, 'is_randomized' => false]);
    foreach ([1, 2, 3] as $points) {
        addQuestion($assessment, 'multiple_choice', ['points' => $points], [['Yes', true], ['No', false]]);
    }

    $legacy = AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'status' => 'completed', 'completed_at' => now(), 'score' => 3]);
    $frozen = AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'status' => 'in_progress']);
    app(AttemptQuestionSetGenerator::class)->generate($frozen);
    $frozen->update(['status' => 'completed', 'completed_at' => now(), 'score' => 1]);

    $fromSql = AssessmentAttempt::query()
        ->selectRaw('id, '.AssessmentAttempt::percentageSql().' AS pct')
        ->whereIn('id', [$legacy->id, $frozen->id])
        ->pluck('pct', 'id');

    expect(round((float) $fromSql[$legacy->id], 2))->toBe(50.0)
        ->and(round((float) $fromSql[$frozen->id], 2))->toBe(round($frozen->fresh()->scorePercentage(), 2))
        ->and($legacy->fresh()->percentage_score)->toBe(50.0);
});

it('stores points, not a percentage, when a quiz is taken on the quizzes page', function () {
    [$assessment, $instructor] = assessmentForInstructor(['assessment_type' => 'quiz', 'approval_status' => 'approved', 'passing_score' => 50, 'is_randomized' => false]);
    $admin = userWithRole('admin');
    $q1 = addQuestion($assessment, 'multiple_choice', ['points' => 2], [['Right', true], ['Wrong', false]]);
    $q2 = addQuestion($assessment, 'multiple_choice', ['points' => 2], [['Right', true], ['Wrong', false]]);

    Livewire::actingAs($admin)->test(QuizTake::class, ['assessment' => $assessment])
        ->set("answers.{$q1->id}", (string) $q1->options()->where('is_correct', true)->value('id'))
        ->set("answers.{$q2->id}", (string) $q2->options()->where('is_correct', false)->value('id'))
        ->call('submitQuiz')        ->assertSet('score', 50.0);

    $attempt = AssessmentAttempt::where('assessment_id', $assessment->id)->latest('id')->first();
    expect((float) $attempt->score)->toBe(2.0)
        ->and($attempt->scorePercentage())->toBe(50.0)
        ->and($attempt->is_passed)->toBeTrue();

    Livewire::actingAs($admin)->test(QuizShow::class, ['assessment' => $assessment])
        ->assertViewHas('stats', fn ($stats) => (float) $stats['average_score'] === 50.0);
});
