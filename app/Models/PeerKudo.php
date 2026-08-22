<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeerKudo extends Model
{
    protected $fillable = ['from_user_id', 'to_user_id', 'message', 'given_on'];

    protected $casts = ['given_on' => 'date'];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public static function canGiveToday(int $fromUserId): bool
    {
        return ! static::where('from_user_id', $fromUserId)
            ->where('given_on', today())
            ->exists();
    }

    public static function hasGivenTodayTo(int $fromUserId, int $toUserId): bool
    {
        return static::where('from_user_id', $fromUserId)
            ->where('to_user_id', $toUserId)
            ->where('given_on', today())
            ->exists();
    }
}
