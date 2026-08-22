<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'user_id',
        'invited_by',
        'status',
        'invited_at',
        'expires_at',
        'responded_at',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'expires_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopeActivePending($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeStalePending($query)
    {
        return $query->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    public static function expireStale(): int
    {
        return static::stalePending()->update([
            'status' => 'expired',
            'responded_at' => now(),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function effectiveStatus(): string
    {
        if ($this->status === 'pending' && $this->isExpired()) {
            return 'expired';
        }

        return $this->status;
    }

    public function isActionable(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    public function renew(int $days, ?int $invitedBy = null, ?string $message = null): void
    {
        $this->update([
            'status' => 'pending',
            'invited_by' => $invitedBy ?? $this->invited_by,
            'invited_at' => now(),
            'expires_at' => now()->addDays($days),
            'responded_at' => null,
            'message' => $message ?? $this->message,
        ]);
    }

    public function accept(): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        $this->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        return true;
    }

    public function decline()
    {
        $this->update([
            'status' => 'declined',
            'responded_at' => now(),
        ]);
    }
}

