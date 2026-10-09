<?php

namespace App\Livewire\Comments;

use App\Contracts\Commentable;
use App\Services\SubmissionCommentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Thread extends Component
{
    /** @var \App\Contracts\Commentable&\Illuminate\Database\Eloquent\Model */
    public $commentable;

    public string $title = 'Comments';

    public string $placeholder = 'Write a comment…';

    public string $body = '';

    public function mount($commentable, ?string $title = null, ?string $placeholder = null): void
    {
        abort_unless($commentable instanceof Commentable, 500);
        abort_unless($commentable->canComment(Auth::user()), 403);

        $this->commentable = $commentable;
        $this->title = $title ?? $this->title;
        $this->placeholder = $placeholder ?? $this->placeholder;
    }

    public function post(SubmissionCommentService $comments): void
    {
        $this->validate(['body' => 'required|string|min:2|max:2000']);

        $comments->post($this->commentable, Auth::user(), $this->body);

        $this->reset('body');
    }

    #[On('comments-updated')]
    public function refreshComments(): void {}

    public function delete(int $commentId): void
    {
        $comment = $this->commentable->comments()->whereKey($commentId)->firstOrFail();
        $user = Auth::user();

        abort_unless((int) $comment->user_id === (int) $user->id || $user->isAdmin(), 403);

        $comment->delete();
    }

    public function render()
    {
        return view('livewire.comments.thread', [
            'comments' => $this->commentable->comments()->with('user')->get(),
        ]);
    }
}
