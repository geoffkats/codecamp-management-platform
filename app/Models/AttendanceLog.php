<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AttendanceLog extends Model
{
    protected $fillable = [
        'student_profile_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'code_used',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function getTotalHoursAttribute(): ?float
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $checkOut = Carbon::parse($this->check_out_time);

        return $checkIn->diffInHours($checkOut, true);
    }
}
