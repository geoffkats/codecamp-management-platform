<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'public_token',
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
        'payment_status',
        'meta',
    ];

    protected $casts = [
        'icdl_modules' => 'array',
        'meta' => 'array',
        'date_of_birth' => 'date',
        'preferred_exam_date' => 'date',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(RegistrationPayment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(RegistrationPayment::class)->latestOfMany();
    }

    public function isMembership(): bool
    {
        return $this->type === 'membership';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function paymentUrl(): ?string
    {
        return $this->public_token ? route('registration.membership.pay', $this->public_token) : null;
    }

    public static function membershipFee(): int
    {
        return (int) SystemSetting::get('membership_application_fee', config('membership.application_fee'));
    }
}
