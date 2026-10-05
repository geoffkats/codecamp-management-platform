<?php

use App\Models\Grade;
use App\Services\Assessments\AttemptGradeRecorder;

it('records a manual grade in points against the frozen maximum', function () {
    [$assessment, $user] = assessmentForInstructor(['passing_score' => 60]);
    $choice = addQuestion($assessment, 'multiple_choice', ['points' => 4], [['A', true], ['B', false]]);
    $essay = addQuestion($assessment, 'essay', ['points' => 6]);

    openTake($assessment, $user);
    $attempt = takeAndSubmit($assessment, $user, [$choice->id => optionId($choice, 'A'), $essay->id => 'My essay']);

    expect($attempt->score)->toBeNull()->and($attempt->auto_scored)->toBeFalse();

    // Points edited in the bank afterwards must not change this attempt's maximum
    $essay->update(['points' => 100]);

    $result = app(AttemptGradeRecorder::class)->record($attempt, 7, 'Good work', $user, [$choice->id => 4, $essay->id => 3]);
    $attempt->refresh();

    expect($result)->toMatchArray(['max' => 10.0, 'percentage' => 70.0, 'passed' => true, 'letter' => 'C-'])
        ->and((float) $attempt->score)->toBe(7.0)
        ->and($attempt->score_unit)->toBe('points')
        ->and($attempt->is_locked)->toBeTrue()
        ->and($attempt->answers['feedback'])->toBe('Good work')
        ->and($attempt->answers[$essay->id])->toBe('My essay')
        ->and((float) $attempt->questionSet()->get()->last()->earned_points)->toBe(3.0);

    $grade = Grade::where('gradeable_id', $attempt->id)->sole();
    expect((float) $grade->max_score)->toBe(10.0)
        ->and((float) $grade->percentage)->toBe(70.0);
});
