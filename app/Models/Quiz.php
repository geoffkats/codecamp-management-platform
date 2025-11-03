<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'instructions',
        'time_limit',
        'max_attempts',
        'passing_score',
        'is_randomized',
        'is_published',
        'show_correct_answers',
        'allow_review',
    ];

    protected function casts(): array
    {
        return [
            'passing_score' => 'decimal:2',
            'is_randomized' => 'boolean',
            'is_published' => 'boolean',
            'show_correct_answers' => 'boolean',
            'allow_review' => 'boolean',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}

