<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'discussion_id',
        'user_id',
        'parent_id',
        'content',
        'is_solution',
        'likes_count',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'is_solution' => 'boolean',
            'attachments' => 'array',
        ];
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionReply::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'parent_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(DiscussionReplyReaction::class);
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

