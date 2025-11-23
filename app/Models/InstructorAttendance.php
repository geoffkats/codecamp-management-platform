<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorAttendance extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_date',
        'status',
        'reason',
        'check_in_time',
        'check_out_time',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
