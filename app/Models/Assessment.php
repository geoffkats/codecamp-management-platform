<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
class Assessment extends Model
{
    use HasFactory, SoftDeletes, Auditable;

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

    // The 'questions' DB column (JSON) and the questions() relationship share the same name.
    // Eloquent's $casts check always wins over relationship lookup, so ->questions returns null
    // (the cast column) even after eager loading. This accessor restores correct behaviour.
    public function getQuestionsAttribute(mixed $value): mixed
    {
        if ($this->relationLoaded('questions')) {
            return $this->relations['questions'];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return collect(is_array($decoded) ? $decoded : []);
        }

        if (is_array($value)) {
            return collect($value);
        }

        return collect($value ?? []);
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(ContentApproval::class, 'approvable');
    }

    public function getMaxPointsAttribute(): int
    {
        if ($this->assessment_type === 'assignment') {
            return (int) ($this->assignment_data['max_points'] ?? 100);
        }

        $fromQuestions = $this->relationLoaded('questions')
            ? ($this->questions?->sum('points') ?? 0)
            : $this->questions()->sum('points');

        return (int) ($fromQuestions > 0 ? $fromQuestions : 100);
    }

    public function getDueDateAttribute(): ?\Illuminate\Support\Carbon
    {
        if ($this->assessment_type !== 'assignment') {
            return null;
        }

        $due = $this->assignment_data['due_date'] ?? null;

        return $due ? \Illuminate\Support\Carbon::parse($due) : null;
    }

    /**
     * @return array<int, array{path: string, name: string}>
     */
    public function assignmentAttachments(): array
    {
        if ($this->assessment_type !== 'assignment') {
            return [];
        }

        $attachments = $this->assignment_data['attachments'] ?? [];

        return collect($attachments)
            ->map(function ($file) {
                if (is_string($file)) {
                    return ['path' => $file, 'name' => basename($file)];
                }

                return [
                    'path' => (string) ($file['path'] ?? ''),
                    'name' => (string) ($file['name'] ?? basename($file['path'] ?? '')),
                ];
            })
            ->filter(fn (array $file) => $file['path'] !== '')
            ->values()
            ->all();
    }
}

