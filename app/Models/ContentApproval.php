<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'status',
        'notes',
        'rejection_reason',
        'submitted_by',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
        'read_at',
        'priority',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function typeLabel(): string
    {
        return match (class_basename((string) $this->approvable_type)) {
            'CourseModule' => 'Module',
            '' => 'Content',
            default => class_basename($this->approvable_type),
        };
    }

    public function approve(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'notes' => $notes,
        ]);

        $approvable = $this->approvable;

        if ($approvable) {
            $approvable->fill([
                'approved_at' => now(),
                'approved_by' => $reviewer->id,
                'approval_notes' => $notes,
            ]);

            if ($approvable instanceof Assignment) {
                $approvable->status = 'active';
            } else {
                $approvable->approval_status = 'approved';
            }

            if ($approvable instanceof Lesson && ! $approvable->is_published) {
                $approvable->is_published = true;
            }

            $approvable->save();
        }

        $this->notifySubmitter('Content Approved', 'has been approved.', 'success');
    }

    public function reject(User $reviewer, string $reason, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
            'notes' => $notes,
        ]);

        $approvable = $this->approvable;

        if ($approvable) {
            $approvable->fill(['rejection_reason' => $reason]);

            if ($approvable instanceof Assignment) {
                $approvable->status = 'draft';
            } else {
                $approvable->approval_status = 'rejected';
            }

            $approvable->save();
        }

        $this->notifySubmitter('Content Needs Changes', 'needs changes: '.$reason, 'warning');
    }

    private function notifySubmitter(string $title, string $outcome, string $type): void
    {
        if (! $this->submitted_by) {
            return;
        }

        Notification::create([
            'user_id' => $this->submitted_by,
            'title' => $title,
            'message' => 'Your '.strtolower($this->typeLabel()).' "'.($this->approvable->title ?? 'content').'" '.$outcome,
            'type' => $type,
            'data' => [
                'approval_id' => $this->id,
                'content_type' => $this->category,
                'content_id' => $this->approvable_id,
            ],
            'is_read' => false,
        ]);
    }
}

