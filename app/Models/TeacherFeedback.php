<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherFeedback extends Model
{
    protected $table = 'teacher_feedback';
    
    protected $fillable = [
        'student_id',
        'teacher_id',
        'course_id',
        'category',
        'rating',
        'feedback',
        'is_anonymous',
        'status',
        'admin_response',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
