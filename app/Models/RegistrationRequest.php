<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'type',
        'full_name',
        'email',
        'phone',
        'organization_name',
        'role_title',
        'program_interest',
        'course_interest',
        'school_level',
        'students_count',
        'national_id',
        'date_of_birth',
        'gender',
        'preferred_schedule',
        'preferred_exam_date',
        'icdl_modules',
        'message',
        'status',
        'meta',
    ];

    protected $casts = [
        'icdl_modules' => 'array',
        'meta' => 'array',
        'date_of_birth' => 'date',
        'preferred_exam_date' => 'date',
    ];
}
