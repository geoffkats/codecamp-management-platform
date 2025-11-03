<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonPracticeResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'response',
        'additional_data',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'additional_data' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

