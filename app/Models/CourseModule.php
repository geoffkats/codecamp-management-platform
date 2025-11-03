<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'overview',
        'order_index',
        'estimated_duration_hours',
        'is_active',
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
            'is_active' => 'boolean',
            'submitted_for_approval_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'module_id')->orderBy('order_index');
    }
}

