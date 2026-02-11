<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalTestMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'course_module_id',
        'test_name',
        'score',
        'passed',
        'status',
        'is_locked',
        'test_date',
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
            'passed' => 'boolean',
            'is_locked' => 'boolean',
            'test_date' => 'date',
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

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
