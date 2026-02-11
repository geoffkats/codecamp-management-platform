<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
class Assignment extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'course_id',
        'lesson_id',
        'created_by',
        'title',
        'description',
        'instructions',
        'due_date',
        'max_points',
        'status',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'is_locked' => 'boolean',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}

