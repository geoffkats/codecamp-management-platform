<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcdlExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'course_module_id',
        'exam_session',
        'score',
        'result',
        'status',
        'is_locked',
        'exam_date',
        'teacher_comment',
        'entered_by_teacher_id',
        'reviewed_by_admin_id',
        'reviewed_at',
        'unlock_reason',
        'unlocked_by_admin_id',
        'unlocked_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'is_locked' => 'boolean',
            'exam_date' => 'date',
            'reviewed_at' => 'datetime',
            'unlocked_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_teacher_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_admin_id');
    }
}
