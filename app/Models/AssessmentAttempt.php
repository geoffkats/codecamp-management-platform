<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'user_id',
        'school_id',
        'teacher_id',
        'student_type',
        'auto_scored',
        'is_locked',
        'started_at',
        'completed_at',
        'score',
        'time_spent',
        'is_passed',
        'answers',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'decimal:2',
            'is_passed' => 'boolean',
            'auto_scored' => 'boolean',
            'is_locked' => 'boolean',
            'answers' => 'array',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasAnyRole(['admin', 'supervisor'])) {
            return $query;
        }

        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();

            return $query
                ->where('student_type', 'ict')
                ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        }

        if ($user->isTeacher()) {
            return $query
                ->where('student_type', 'codecamp')
                ->whereHas('assessment.course', fn ($q) => $q->where('instructor_id', $user->id));
        }

        if ($user->isStudent()) {
            return $query->where('user_id', $user->id);
        }

        return $query->whereRaw('1=0');
    }
}

