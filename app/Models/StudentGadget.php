<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGadget extends Model
{
    protected $fillable = [
        'student_profile_id',
        'device_type',
        'brand',
        'serial_number',
        'ram',
        'storage',
        'condition',
        'accessories',
        'photo_path',
        'specifications',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }
}
