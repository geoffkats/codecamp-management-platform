<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'instructions',
        'activity_type',
        'level_type',
        'level_status',
        'expected_duration_minutes',
        'order_index',
        'level_details',
        'is_required',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level_details' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(StudentLessonProgress::class, 'activity_id');
    }
}

