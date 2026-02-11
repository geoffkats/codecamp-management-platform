<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentProfile extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $profile) {
            if (empty($profile->student_id)) {
                $profile->student_id = self::generateStudentId();
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

    public static function generateStudentId(): string
    {
        $year = now()->format('Y');
        
        // Get the last student ID for this year
        $lastStudent = self::where('student_id', 'like', "STU-{$year}-%")
            ->orderBy('student_id', 'desc')
            ->first();
        
        if ($lastStudent) {
            // Extract the sequence number from the last student ID
            $lastSequence = (int)substr($lastStudent->student_id, -4);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('STU-%s-%04d', $year, $sequence);
    }
}
