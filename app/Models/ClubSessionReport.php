<?php

namespace App\Models;

use App\Contracts\Commentable;
use App\Models\Concerns\HasSubmissionComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubSessionReport extends Model implements Commentable
{
    use HasSubmissionComments;

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

    public function canBeViewedBy(User $user): bool
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return true;
        }

        if ((int) $this->facilitator_id === (int) $user->id) {
            return true;
        }

        return $user->hasCodeClubAccess()
            && in_array((int) $this->code_club_id, array_map('intval', $user->activeClubIds()), true);
    }

    public function commentOwner(): ?User
    {
        return $this->facilitator;
    }

    public function commentSubject(): string
    {
        return 'the Code Club report for '.($this->club?->name ?? 'a club').' on '.$this->session_date?->format('j M');
    }

    public function commentUrl(): string
    {
        return route('admin.club-session-reports.show', $this);
    }

    public function canComment(User $user): bool
    {
        return $this->canBeViewedBy($user);
    }
}
