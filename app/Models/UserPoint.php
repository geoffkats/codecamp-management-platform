<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_points',
        'level',
        'points_to_next_level',
        'xp_multiplier',
        'multiplier_expires_at',
        'multiplier_reason',
    ];

    protected function casts(): array
    {
        return [
            'xp_multiplier' => 'decimal:2',
            'multiplier_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

