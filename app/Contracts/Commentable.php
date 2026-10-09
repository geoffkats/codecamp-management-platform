<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Commentable
{
    public function comments(): MorphMany;

    /** The person who submitted the item; they are told about every new comment. */
    public function commentOwner(): ?User;

    /** Short phrase used in notifications, e.g. "your daily report for 7 Oct". */
    public function commentSubject(): string;

    public function commentUrl(): string;

    public function canComment(User $user): bool;
}
