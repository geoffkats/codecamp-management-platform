<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lesson_id',
        'title',
        'assessment_type',
        'description',
        'max_attempts',
        'time_limit_minutes',
        'passing_score',
        'xp_reward',
        'questions',
        'rubric_criteria',
        'assignment_data',
        'project_test_data',
        'survey_data',
        'peer_review_data',
        'self_assessment_data',
        'is_required',
        'show_results_immediately',
        'is_randomized',
        'shuffle_options',
        'show_correct_answers',
        'allow_review',
        'is_locked',
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
            'questions' => 'array',
            'rubric_criteria' => 'array',
            'assignment_data' => 'array',
            'project_test_data' => 'array',
            'survey_data' => 'array',
            'peer_review_data' => 'array',
            'self_assessment_data' => 'array',
            'is_required' => 'boolean',
            'show_results_immediately' => 'boolean',
            'is_randomized' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_correct_answers' => 'boolean',
            'allow_review' => 'boolean',
            'is_locked' => 'boolean',
            'submitted_for_approval_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}

