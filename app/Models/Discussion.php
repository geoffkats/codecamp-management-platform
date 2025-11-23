<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lesson_id',
        'user_id',
        'title',
        'content',
        'is_pinned',
        'is_locked',
        'status',
        'views_count',
        'replies_count',
        'last_reply_at',
        'last_reply_by',
        'subject_tag',
        'upvotes',
        'helpful_count',
        'has_best_answer',
        'scratch_project_id',
        'code_snippets',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
            'has_best_answer' => 'boolean',
            'last_reply_at' => 'datetime',
            'code_snippets' => 'array',
            'attachments' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class);
    }

    public function lastReplyBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_reply_by');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(DiscussionReaction::class);
    }

    public function userReactions()
    {
        return $this->reactions()->where('user_id', auth()->id());
    }
    
    public function getUserReactionTypesAttribute()
    {
        if (!auth()->check()) {
            return [];
        }
        return $this->userReactions->pluck('reaction_type')->toArray();
    }
}

