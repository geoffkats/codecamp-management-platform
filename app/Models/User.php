<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'student_type',
        'student_id',
        'password',
        'initial_password',
        'profile_image',
        'bio',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'initial_password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function points()
    {
        return $this->hasOne(UserPoint::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function schoolTeachers()
    {
        return $this->hasMany(SchoolTeacher::class, 'teacher_id');
    }

    public function ictSchoolId(): ?int
    {
        $assignment = $this->schoolTeachers()
            ->where('role', 'ict_teacher')
            ->orderByDesc('created_at')
            ->first();

        if ($assignment) {
            return $assignment->status === 'active' ? (int) $assignment->school_id : null;
        }

        $schoolId = $this->teacherProfile?->school_id;
        return $schoolId ? (int) $schoolId : null;
    }

    public function invitations()
    {
        return $this->hasMany(CourseInvitation::class);
    }

    public function studentAttendances()
    {
        return $this->hasManyThrough(
            StudentAttendance::class,
            StudentProfile::class,
            'user_id', // Foreign key on student_profiles table
            'student_profile_id', // Foreign key on student_attendances table
            'id', // Local key on users table
            'id' // Local key on student_profiles table
        );
    }

    public function checkedInToday()
    {
        if (!$this->studentProfile) {
            return false;
        }
        
        return $this->studentAttendances()
            ->whereDate('attendance_date', today())
            ->whereIn('status', ['present', 'late'])
            ->exists();
    }

    public function todayCheckIn()
    {
        if (!$this->studentProfile) {
            return null;
        }
        
        return $this->studentAttendances()
            ->whereDate('attendance_date', today())
            ->whereIn('status', ['present', 'late'])
            ->first();
    }

    public function getTodayCheckInAttribute()
    {
        return $this->todayCheckIn();
    }

    public function isIctStudent(): bool
    {
        return $this->isStudent() && $this->student_type === 'ict';
    }

    public function isCodecampStudent(): bool
    {
        return $this->isStudent() && $this->student_type === 'codecamp';
    }

    public function discussionReplies()
    {
        return $this->hasMany(DiscussionReply::class);
    }

    public function getHelperLevelAttribute()
    {
        $count = $this->helpful_answers_count ?? 0;
        
        if ($count >= 100) return 'community-leader';
        if ($count >= 50) return 'discussion-master';
        if ($count >= 20) return 'code-mentor';
        if ($count >= 5) return 'helper';
        
        return null;
    }
}
