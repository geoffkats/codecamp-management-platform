<?php

namespace App\Livewire\Discussions;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use Livewire\Component;
use Livewire\WithPagination;

class DiscussionThread extends Component
{
    use WithPagination;

    public $discussion;
    public $replyContent = '';

    public function mount(Discussion $discussion)
    {
        $this->discussion = $discussion;
        // Increment views
        $discussion->increment('views_count');
    }

    public function addReply()
    {
        $this->validate([
            'replyContent' => 'required|string|min:3',
        ]);

        DiscussionReply::create([
            'discussion_id' => $this->discussion->id,
            'user_id' => auth()->id(),
            'content' => $this->replyContent,
        ]);

        $this->discussion->increment('replies_count');
        $this->discussion->update([
            'last_reply_at' => now(),
            'last_reply_by' => auth()->id(),
        ]);

        $this->replyContent = '';
        session()->flash('message', 'Reply added successfully!');
        $this->dispatch('reply-added');
    }

    public function togglePin()
    {
        $this->discussion->update([
            'is_pinned' => !$this->discussion->is_pinned,
        ]);
    }

    public function toggleLock()
    {
        $this->discussion->update([
            'is_locked' => !$this->discussion->is_locked,
        ]);
    }

    public function render()
    {
        $replies = DiscussionReply::where('discussion_id', $this->discussion->id)
            ->with(['user', 'parent'])
            ->orderBy('created_at')
            ->paginate(20);

        return view('livewire.discussions.discussion-thread', [
            'replies' => $replies,
        ]);
    }
}

