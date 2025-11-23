<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'student_id',
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
        'scratch_account',
        'scratch_password',
        'github_account',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'uniform_payment_date' => 'date',
        'uniform_paid' => 'boolean',
        'tshirt_collected' => 'boolean',
        'parent_data' => 'array',
    ];

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
