<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyChallengeAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'challenge_id',
        'attempted_at',
        'completed_at',
        'is_completed',
        'points_earned',
        'details',
        'progress_data',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_completed' => 'boolean',
            'details' => 'array',
            'progress_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(DailyChallenge::class);
    }
}

