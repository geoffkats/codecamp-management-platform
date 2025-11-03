<?php

namespace App\Livewire\Discussions;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Show extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public Discussion $discussion;
    public $replyContent = '';
    public $parentReplyId = null;
    public $editingReplyId = null;
    public $editContent = '';

    public function mount(Discussion $discussion)
    {
        $this->discussion = $discussion->load(['user', 'course', 'lesson', 'lastReplyBy']);
        
        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);
        
        // Check access control - students can only view discussions from courses they're enrolled in
        // EXCEPT if the course enrollment_type is 'open' (open courses are publicly accessible)
        if ($this->discussion->course_id && !$isStaff) {
            $course = $this->discussion->course;
            
            // If course is not open, check enrollment
            if ($course->enrollment_type !== 'open') {
                $isEnrolled = $course->enrollments()
                    ->where('user_id', $user->id)
                    ->exists();
                
                if (!$isEnrolled) {
                    session()->flash('error', 'You must be enrolled in this course to view discussions.');
                    return redirect()->route('discussions.index');
                }
            }
        }
        
        // Check if discussion is locked
        if ($this->discussion->is_locked && !$isStaff) {
            session()->flash('error', 'This discussion is locked and cannot be replied to.');
            return;
        }
        
        // Increment views
        $this->discussion->increment('views_count');
    }

    public function reply()
    {
        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);
        
        // Re-check access control before allowing reply
        if ($this->discussion->course_id && !$isStaff) {
            $course = $this->discussion->course;
            if ($course->enrollment_type !== 'open') {
                $isEnrolled = $course->enrollments()
                    ->where('user_id', $user->id)
                    ->exists();
                
                if (!$isEnrolled) {
                    session()->flash('error', 'You must be enrolled in this course to reply.');
                    return;
                }
            }
        }
        
        if ($this->discussion->is_locked && !$isStaff) {
            session()->flash('error', 'This discussion is locked.');
            return;
        }

        $this->validate([
            'replyContent' => 'required|string|min:3',
        ]);

        DiscussionReply::create([
            'discussion_id' => $this->discussion->id,
            'user_id' => Auth::id(),
            'parent_id' => $this->parentReplyId,
            'content' => $this->replyContent,
        ]);

        $this->discussion->increment('replies_count');
        $this->discussion->update([
            'last_reply_at' => now(),
            'last_reply_by' => Auth::id(),
        ]);

        $this->replyContent = '';
        $this->parentReplyId = null;
        $this->resetPage();
        
        session()->flash('message', 'Reply posted successfully!');
        $this->dispatch('reply-added');
    }

    public function setReplyTo($replyId)
    {
        $this->parentReplyId = $replyId;
        $this->dispatch('scroll-to-reply-form');
    }

    public function cancelReply()
    {
        $this->parentReplyId = null;
        $this->replyContent = '';
    }

    public function startEdit($replyId)
    {
        $reply = DiscussionReply::findOrFail($replyId);
        
        // Only allow editing own replies or staff can edit any
        if ($reply->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor'])) {
            session()->flash('error', 'You can only edit your own replies.');
            return;
        }

        $this->editingReplyId = $replyId;
        $this->editContent = $reply->content;
    }

    public function updateReply()
    {
        $reply = DiscussionReply::findOrFail($this->editingReplyId);
        
        if ($reply->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor'])) {
            session()->flash('error', 'You can only edit your own replies.');
            return;
        }

        $this->validate([
            'editContent' => 'required|string|min:3',
        ]);

        $reply->update([
            'content' => $this->editContent,
        ]);

        $this->editingReplyId = null;
        $this->editContent = '';
        session()->flash('message', 'Reply updated successfully!');
    }

    public function cancelEdit()
    {
        $this->editingReplyId = null;
        $this->editContent = '';
    }

    public function deleteReply($replyId)
    {
        $reply = DiscussionReply::findOrFail($replyId);
        
        // Only allow deleting own replies or staff can delete any
        if ($reply->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor'])) {
            session()->flash('error', 'You can only delete your own replies.');
            return;
        }

        // If it's a parent reply with children, update children
        if ($reply->replies()->count() > 0) {
            $reply->replies()->update(['parent_id' => $reply->parent_id]);
        }

        $reply->delete();
        $this->discussion->decrement('replies_count');
        
        session()->flash('message', 'Reply deleted successfully!');
        $this->resetPage();
    }

    public function likeReply($replyId)
    {
        $reply = DiscussionReply::findOrFail($replyId);
        $reply->increment('likes_count');
        session()->flash('message', 'Reply liked!');
    }

    public function markAsSolution($replyId)
    {
        if ($this->discussion->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor'])) {
            session()->flash('error', 'Only the discussion creator or staff can mark solutions.');
            return;
        }

        // Unmark any existing solution
        DiscussionReply::where('discussion_id', $this->discussion->id)
            ->where('is_solution', true)
            ->update(['is_solution' => false]);

        // Mark this as solution
        $reply = DiscussionReply::findOrFail($replyId);
        $reply->update(['is_solution' => true]);

        session()->flash('message', 'Reply marked as solution!');
    }

    public function togglePin()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor'])) {
            session()->flash('error', 'Only staff can pin discussions.');
            return;
        }

        $this->discussion->update([
            'is_pinned' => !$this->discussion->is_pinned,
        ]);

        session()->flash('message', $this->discussion->is_pinned ? 'Discussion pinned!' : 'Discussion unpinned!');
    }

    public function toggleLock()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor'])) {
            session()->flash('error', 'Only staff can lock discussions.');
            return;
        }

        $this->discussion->update([
            'is_locked' => !$this->discussion->is_locked,
        ]);

        session()->flash('message', $this->discussion->is_locked ? 'Discussion locked!' : 'Discussion unlocked!');
    }

    public function closeDiscussion()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor'])) {
            session()->flash('error', 'Only staff can close discussions.');
            return;
        }

        $this->discussion->update([
            'status' => 'closed',
            'is_locked' => true, // Also lock when closing
        ]);

        session()->flash('message', 'Discussion closed successfully!');
    }

    public function reopenDiscussion()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor'])) {
            session()->flash('error', 'Only staff can reopen discussions.');
            return;
        }

        $this->discussion->update([
            'status' => 'active',
            'is_locked' => false, // Unlock when reopening
        ]);

        session()->flash('message', 'Discussion reopened successfully!');
    }

    public function archiveDiscussion()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'teacher', 'supervisor'])) {
            session()->flash('error', 'Only staff can archive discussions.');
            return;
        }

        $this->discussion->update([
            'status' => 'archived',
            'is_locked' => true,
        ]);

        session()->flash('message', 'Discussion archived successfully!');
    }

    public function render()
    {
        // Get top-level replies (replies without parent) with nested replies
        $topLevelReplies = DiscussionReply::where('discussion_id', $this->discussion->id)
            ->whereNull('parent_id')
            ->with([
                'user',
                'replies.user', 
                'replies.replies.user',
                'replies.replies.replies.user' // Support 3 levels of nesting
            ])
            ->orderBy('created_at')
            ->paginate(10);

        // Get all replies for count
        $totalReplies = DiscussionReply::where('discussion_id', $this->discussion->id)->count();

        return view('livewire.discussions.show', [
            'topLevelReplies' => $topLevelReplies,
            'totalReplies' => $totalReplies,
        ]);
    }
}
