<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'module_id',
        'title',
        'slug',
        'content',
        'summary',
        'order',
        'order_index',
        'duration_minutes',
        'video_url',
        'video_duration',
        'scratch_project_id',
        'lesson_steps',
        'scratch_blocks',
        'question_of_day',
        'objectives',
        'implementation_guidance',
        'lesson_type',
        'difficulty_level',
        'has_levels',
        'total_levels',
        'is_published',
        'is_free_preview',
        'is_locked',
        'is_active',
        'attachments',
        'approval_status',
        'submitted_for_approval_at',
        'approved_at',
        'approved_by',
        'approval_notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'has_levels' => 'boolean',
            'is_published' => 'boolean',
            'is_free_preview' => 'boolean',
            'is_locked' => 'boolean',
            'is_active' => 'boolean',
            'attachments' => 'array',
            'lesson_steps' => 'array',
            'scratch_blocks' => 'array',
            'submitted_for_approval_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($lesson) {
            if (empty($lesson->slug)) {
                $lesson->slug = Str::slug($lesson->title);
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class)->orderBy('order_index');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LessonActivity::class)->orderBy('order_index');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LessonAttachment::class);
    }

    public function practiceResponses(): HasMany
    {
        return $this->hasMany(LessonPracticeResponse::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}

