<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseCollaborator extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'role',
        'invited_at',
        'invited_by',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function canEdit(): bool
    {
        return $this->role === 'editor';
    }
}
