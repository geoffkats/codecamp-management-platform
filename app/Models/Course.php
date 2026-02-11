<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\Auditable;

class Course extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'instructor_id',
        'featured_image',
        'difficulty_level',
        'estimated_duration',
        'is_published',
        'is_featured',
        'enrollment_type',
        'max_students',
        'price',
        'category',
        'tags',
        'requirements',
        'what_you_learn',
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
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'price' => 'decimal:2',
            'tags' => 'array',
            'requirements' => 'array',
            'what_you_learn' => 'array',
            'submitted_for_approval_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected $attributes = [
        'enrollment_type' => 'invite_only',
        'approval_status' => 'draft',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
            // Set default enrollment type if not specified
            if (empty($course->enrollment_type)) {
                $course->enrollment_type = 'invite_only';
            }
        });
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('order_index');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'school_courses')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order_index');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(CourseInvitation::class);
    }

    public function enrollmentRequests(): HasMany
    {
        return $this->hasMany(EnrollmentRequest::class);
    }

    public function collaborators(): HasMany
    {
        return $this->hasMany(CourseCollaborator::class);
    }

    public function collaboratorUsers()
    {
        return $this->belongsToMany(User::class, 'course_collaborators')
            ->withPivot('role', 'invited_at', 'invited_by')
            ->withTimestamps();
    }

    public function isCollaborator(User $user): bool
    {
        return $this->collaborators()->where('user_id', $user->id)->exists();
    }

    public function canUserEdit(User $user): bool
    {
        if ($this->instructor_id === $user->id) {
            return true;
        }

        $collaborator = $this->collaborators()->where('user_id', $user->id)->first();
        return $collaborator && $collaborator->canEdit();
    }
}

