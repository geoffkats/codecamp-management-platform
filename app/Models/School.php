<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $school) {
            if (!$school->code) {
                $school->code = self::generateUniqueCode();
            } else {
                $school->code = strtoupper($school->code);
            }
        });
    }

    public function students(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(TeacherProfile::class);
    }

    public function schoolTeachers(): HasMany
    {
        return $this->hasMany(SchoolTeacher::class);
    }

    public function schoolCourses(): HasMany
    {
        return $this->hasMany(SchoolCourse::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'school_courses')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public static function generateUniqueCode(int $length = 6): string
    {
        do {
            $code = 'SCH-' . Str::upper(Str::random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
