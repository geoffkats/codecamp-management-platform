<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'attempt_number',
        'score',
        'is_passed',
        'started_at',
        'completed_at',
        'answers',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'is_passed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the percentage score attribute (score is already stored as percentage 0-100)
     */
    public function getPercentageScoreAttribute(): ?float
    {
        return $this->score !== null ? (float) $this->score : null;
    }
}

