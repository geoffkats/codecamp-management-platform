<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubSessionReport extends Model
{
    protected $fillable = [
        'code_club_id',
        'facilitator_id',
        'session_date',
        'status',
        'summary',
        'challenges',
        'topics_covered',
        'new_techniques',
        'teamwork_rating',
        'collaboration_rating',
        'attendance_count',
        'enrolled_count',
        'follow_up_required',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'follow_up_required' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(CodeClub::class, 'code_club_id');
    }

    public function facilitator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'facilitator_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function retentionRate(): ?float
    {
        if ($this->enrolled_count <= 0) {
            return null;
        }

        return round(($this->attendance_count / $this->enrolled_count) * 100, 1);
    }
}
