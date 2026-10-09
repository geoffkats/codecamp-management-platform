<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationPayment extends Model
{
    protected $fillable = [
        'registration_request_id',
        'gateway',
        'merchant_reference',
        'order_tracking_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'confirmation_code',
        'paid_at',
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'gateway_response' => 'array',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(RegistrationRequest::class, 'registration_request_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
