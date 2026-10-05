<?php

namespace App\Services\Assessments;

use App\Models\AssessmentAttempt;
use App\Models\Grade;
use App\Models\User;
use App\Support\LetterGrade;

/**
 * The single write path for a teacher's grade on an assessment attempt. Callers keep their own
 * side effects (XP, notifications); this records the score consistently in points.
 */
class AttemptGradeRecorder
{
    /**
     * @param  array<int|string, float|int|string>|null  $questionScores  per-question points keyed by question id
     * @param  array<string, mixed>  $feedbackData  extra data stored with the Grade feedback (e.g. rubric scores)
     * @return array{max: float, percentage: float, passed: bool, letter: string}
     */
    public function record(
        AssessmentAttempt $attempt,
        float $score,
        ?string $feedback,
        User $grader,
        ?array $questionScores = null,
        ?float $maxScore = null,
        array $feedbackData = [],
    ): array {
        $assessment = $attempt->assessment()->withTrashed()->first();
        $max = $maxScore ?? $attempt->maxScore();
        $percentage = $max > 0 ? min(round(($score / $max) * 100, 2), 100) : 0.0;
        $passed = $percentage >= (float) ($assessment?->passing_score ?? 70);
        $letter = LetterGrade::fromPercentage($percentage);

        $answers = $attempt->answers ?? [];
        $answers['feedback'] = $feedback;
        $answers['graded_at'] = now()->toIso8601String();
        $answers['graded_by'] = $grader->id;
        if ($questionScores) {
            $answers['question_scores'] = $questionScores;
        }

        $attempt->update([
            'score' => $score,
            'score_unit' => 'points',
            'is_passed' => $passed,
            'answers' => $answers,
            'auto_scored' => false,
            'is_locked' => true,
            'teacher_id' => $grader->id,
            'status' => 'completed',
            'completed_at' => $attempt->completed_at ?? now(),
        ]);

        if ($questionScores && $attempt->hasQuestionSet()) {
            foreach ($attempt->questionSet()->get() as $row) {
                $id = (int) ($row->snapshot['question_id'] ?? $row->question_id);
                if (array_key_exists($id, $questionScores) || array_key_exists((string) $id, $questionScores)) {
                    $row->update([
                        'earned_points' => (float) ($questionScores[$id] ?? $questionScores[(string) $id]),
                        'graded_at' => now(),
                    ]);
                }
            }
        }

        Grade::updateOrCreate(
            [
                'user_id' => $attempt->user_id,
                'course_id' => $assessment?->course_id,
                'gradeable_type' => AssessmentAttempt::class,
                'gradeable_id' => $attempt->id,
            ],
            [
                'score' => $score,
                'max_score' => $max,
                'percentage' => $percentage,
                'letter_grade' => $letter,
                'feedback' => json_encode(['feedback' => $feedback] + $feedbackData),
                'graded_by' => $grader->id,
                'graded_at' => now(),
                'is_final' => true,
            ]
        );

        return ['max' => $max, 'percentage' => $percentage, 'passed' => $passed, 'letter' => $letter];
    }
}
