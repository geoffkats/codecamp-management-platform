<?php

namespace App\Models;

use App\Contracts\Commentable;
use App\Models\Concerns\HasSubmissionComments;
use App\Support\SubmissionAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model implements Commentable
{
    use HasFactory, HasSubmissionComments;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'content',
        'attachments',
        'status',
        'submitted_at',
        'points_earned',
        'feedback',
        'graded_at',
        'graded_by',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function commentOwner(): ?User
    {
        return $this->user;
    }

    public function commentSubject(): string
    {
        return 'the submission for "'.($this->assignment?->title ?? 'an assignment').'"';
    }

    public function commentUrl(): string
    {
        return route('submissions.show', ['submissionId' => $this->id, 'type' => 'assignment']);
    }

    public function canComment(User $user): bool
    {
        return SubmissionAccess::canView($user, $this);
    }
}

