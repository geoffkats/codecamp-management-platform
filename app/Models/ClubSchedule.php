<?php

namespace App\Models;

use App\Support\TimeOfDay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubSchedule extends Model
{
    protected $fillable = [
        'code_club_id',
        'day_of_week',
        'session_start',
        'session_end',
        'instructor_id',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(CodeClub::class, 'code_club_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function effectiveSessionStart(?CodeClub $club = null): ?string
    {
        if ($this->session_start) {
            return TimeOfDay::toHi($this->session_start);
        }

        $club ??= $this->club;

        return $club?->session_start ? TimeOfDay::toHi($club->session_start) : null;
    }

    public function effectiveSessionEnd(?CodeClub $club = null): ?string
    {
        if ($this->session_end) {
            return TimeOfDay::toHi($this->session_end);
        }

        $club ??= $this->club;

        return $club?->session_end ? TimeOfDay::toHi($club->session_end) : null;
    }
}
