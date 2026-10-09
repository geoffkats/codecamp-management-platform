<?php

namespace App\Models\Concerns;

use App\Models\SubmissionComment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSubmissionComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(SubmissionComment::class, 'commentable')->oldest();
    }
}
