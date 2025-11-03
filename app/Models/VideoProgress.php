<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'video_url',
        'duration_seconds',
        'watched_seconds',
        'progress_percentage',
        'last_position_seconds',
        'is_completed',
        'last_watched_at',
        'watch_count',
    ];

    protected function casts(): array
    {
        return [
            'progress_percentage' => 'decimal:2',
            'is_completed' => 'boolean',
            'last_watched_at' => 'datetime',
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

