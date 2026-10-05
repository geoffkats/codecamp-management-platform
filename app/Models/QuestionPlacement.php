<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Where a bank question belongs in the curriculum: a course, optionally narrowed to a module.
 */
class QuestionPlacement extends Model
{
    protected $fillable = ['question_id', 'course_id', 'course_module_id'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }
}
