<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_type',
        'game_data',
        'user_answers',
        'score',
        'xp_earned',
        'play_time_seconds',
        'accuracy',
        'wpm',
        'words_typed',
        'completed',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'game_data' => 'array',
            'user_answers' => 'array',
            'accuracy' => 'decimal:2',
            'completed' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

