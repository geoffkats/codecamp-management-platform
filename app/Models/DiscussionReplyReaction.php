<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionReplyReaction extends Model
{
    protected $fillable = [
        'discussion_reply_id',
        'user_id',
        'reaction_type',
    ];

    public function discussionReply(): BelongsTo
    {
        return $this->belongsTo(DiscussionReply::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
