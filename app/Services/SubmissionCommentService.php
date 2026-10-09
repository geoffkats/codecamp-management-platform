<?php

namespace App\Services;

use App\Contracts\Commentable;
use App\Models\SubmissionComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SubmissionCommentService
{
    public function __construct(private NotificationService $notifications) {}

    public function post(Commentable&Model $item, User $author, string $body): SubmissionComment
    {
        abort_unless($item->canComment($author), 403);

        $comment = $item->comments()->create([
            'user_id' => $author->id,
            'body' => trim($body),
        ]);

        foreach ($this->recipients($item, $author) as $recipient) {
            $isOwner = (int) $recipient->id === (int) $item->commentOwner()?->id;

            $this->notifications->notify(
                $recipient,
                $isOwner ? 'New comment on your work' : 'New reply',
                $author->name.' commented on '.$item->commentSubject().': "'.Str::limit($comment->body, 120).'"',
                'comment',
                [
                    'comment_id' => $comment->id,
                    'action_url' => $item->commentUrl(),
                ]
            );
        }

        return $comment;
    }

    /**
     * The submitter plus everyone who already commented, minus the author.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function recipients(Commentable&Model $item, User $author)
    {
        $ids = $item->comments()->pluck('user_id')
            ->push($item->commentOwner()?->id)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn ($id) => $id === (int) $author->id);

        return User::whereIn('id', $ids)->get()
            ->filter(fn (User $user) => $item->canComment($user));
    }
}
