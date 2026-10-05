<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One question exactly as a student received it in one attempt. Immutable once written,
 * apart from the grading columns (earned_points, is_correct, needs_manual, graded_at).
 */
class AssessmentAttemptQuestion extends Model
{
    protected $fillable = [
        'assessment_attempt_id',
        'question_id',
        'question_version',
        'position',
        'question_type',
        'points',
        'snapshot',
        'presentation',
        'source',
        'earned_points',
        'is_correct',
        'needs_manual',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'presentation' => 'array',
            'source' => 'array',
            'points' => 'decimal:2',
            'earned_points' => 'decimal:2',
            'is_correct' => 'boolean',
            'needs_manual' => 'boolean',
            'graded_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * The snapshot in the array shape the grading layer works with.
     *
     * @return array<string, mixed>
     */
    public function toQuestionData(): array
    {
        return array_merge($this->snapshot ?? [], [
            'type' => $this->question_type,
            'points' => (float) $this->points,
            'position' => (int) $this->position,
            'presentation' => $this->presentation ?? [],
        ]);
    }
}
