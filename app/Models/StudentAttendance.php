<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    protected $fillable = [
        'student_profile_id',
        'course_id',
        'attendance_date',
        'status',
        'reason',
        'notes',
        'recorded_by',
        'clock_in',
        'clock_out',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
