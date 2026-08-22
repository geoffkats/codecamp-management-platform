<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Traits\Auditable;

class StudentProfile extends Model
{
    use Auditable, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $profile) {
            if (empty($profile->student_id)) {
                $profile->student_id = self::generateStudentId($profile->program_type);
            }
        });

        static::updating(function (self $profile) {
            if ($profile->isDirty('student_id')) {
                $profile->student_id = $profile->getOriginal('student_id');
            }
        });
    }

    protected $fillable = [
        'user_id',
        'school_id',
        'program_type',
        'student_id',
        'icdl_number',
        'exam_readiness_status',
        'is_active',
        'full_name',
        'date_of_birth',
        'age',
        'gender',
        'nationality',
        'parent_guardian_name',
        'parent_guardian_contact',
        'parent_data',
        'class_grade',
        'photo_path',
        'address',
        'uniform_paid',
        'uniform_payment_date',
        'uniform_size',
        'tshirt_collected',
        'payment_receipt_path',
        'payment_amount',
        'payment_reference',
        'payment_status',
        'payment_submitted_at',
        'payment_verified_at',
        'icdl_test_score',
        'icdl_test_status',
        'icdl_test_submitted_at',
        'icdl_test_reviewed_at',
        'exam_request_status',
        'exam_requested_at',
        'exam_approved_at',
        'exam_payment_status',
        'exam_payment_submitted_at',
        'exam_payment_verified_at',
        'exam_scheduled_for',
        'scratch_account',
        'scratch_password',
        'github_account',
        'student_category',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'uniform_payment_date' => 'date',
        'uniform_paid' => 'boolean',
        'tshirt_collected' => 'boolean',
        'parent_data' => 'array',
        'is_active' => 'boolean',
        'payment_amount' => 'decimal:2',
        'payment_submitted_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'icdl_test_score' => 'decimal:2',
        'icdl_test_submitted_at' => 'datetime',
        'icdl_test_reviewed_at' => 'datetime',
        'exam_requested_at' => 'datetime',
        'exam_approved_at' => 'datetime',
        'exam_payment_submitted_at' => 'datetime',
        'exam_payment_verified_at' => 'datetime',
        'exam_scheduled_for' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gadgets(): HasMany
    {
        return $this->hasMany(StudentGadget::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function codeClubMemberships(): HasMany
    {
        return $this->hasMany(CodeClubMembership::class, 'student_id', 'user_id');
    }

    public function activeCodeClubMembership(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CodeClubMembership::class, 'student_id', 'user_id')
            ->where('status', 'active')
            ->latest();
    }

    public static function generateStudentId(?string $programType = null): string
    {
        $prefix = $programType === 'codeclub' ? 'CC' : 'STU';
        $year = now()->format('Y');
        $idPrefix = "{$prefix}-{$year}-";

        $lastProfileId = self::where('student_id', 'like', "{$idPrefix}%")
            ->orderBy('student_id', 'desc')
            ->value('student_id');

        $lastUserId = User::where('student_id', 'like', "{$idPrefix}%")
            ->orderBy('student_id', 'desc')
            ->value('student_id');

        $lastProfileSequence = $lastProfileId ? (int) substr($lastProfileId, -4) : 0;
        $lastUserSequence = $lastUserId ? (int) substr($lastUserId, -4) : 0;
        $sequence = max($lastProfileSequence, $lastUserSequence) + 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    public function getDisplayName(): ?string
    {
        return $this->full_name ?: $this->student_id;
    }

    /**
     * Remove student profile from the system: drop club links and deactivate login.
     */
    public function removeFromSystem(): void
    {
        if ($this->user_id) {
            CodeClubMembership::where('student_id', $this->user_id)->delete();
            $this->user?->update(['is_active' => false]);
        }

        $this->delete();
    }

    protected static function filterSensitiveData(?array $data): ?array
    {
        if (! $data) {
            return null;
        }

        foreach (['password', 'password_confirmation', 'token', 'api_token', 'secret', 'remember_token', 'scratch_password', 'initial_password'] as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}
