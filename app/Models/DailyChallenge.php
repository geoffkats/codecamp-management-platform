<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'requirements',
        'reward_points',
        'date',
        'is_active',
        'difficulty_level',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
            'date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(DailyChallengeAttempt::class, 'challenge_id');
    }
}

