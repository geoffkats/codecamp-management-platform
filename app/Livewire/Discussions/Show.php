<?php

namespace App\Livewire\Discussions;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Show extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    public Discussion $discussion;
    public $replyContent = '';
    public $parentReplyId = null;
    public $editingReplyId = null;
    public $editContent = '';
    public $replyImages = [];

    // Cache user role check to avoid duplicate queries
    protected $isStaff = null;
    
    protected function checkIsStaff()
    {
        if ($this->isStaff === null) {
            $user = Auth::user();
            $user->load('roles'); // Eager load roles once
            $this->isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);
        }
        return $this->isStaff;
    }
    
    public function mount(Discussion $discussion)
    {
        // Eager load all relationships at once
        $this->discussion = $discussion->load([
            'user:id,name,email',
            'course:id,title,enrollment_type',
            'lesson:id,title',
            'lastReplyBy:id,name',
            'reactions' // Eager load reactions to avoid N+1
        ]);
        
        $isStaff = $this->checkIsStaff();
        
        // Check access control - students can only view discussions from courses they're enrolled in
        // EXCEPT if the course enrollment_type is 'open' (open courses are publicly accessible)
        if ($this->discussion->course_id && !$isStaff) {
            $course = $this->discussion->course;
            
            // If course is not open, check enrollment
            if ($course->enrollment_type !== 'open') {
                $isEnrolled = $course->enrollments()
                    ->where('user_id', Auth::id())
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
        $isStaff = $this->checkIsStaff();
        
        // Re-check access control before allowing reply
        if ($this->discussion->course_id && !$isStaff) {
            $course = $this->discussion->course;
            if ($course->enrollment_type !== 'open') {
                $isEnrolled = $course->enrollments()
                    ->where('user_id', Auth::id())
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
            'replyImages' => 'nullable|array|max:5',
        ]);

        // Handle image uploads
        $attachments = [];
        if (!empty($this->replyImages)) {
            foreach ($this->replyImages as $image) {
                $path = $image->store('discussion-reply-images', 'public');
                $attachments[] = $path;
            }
        }

        DiscussionReply::create([
            'discussion_id' => $this->discussion->id,
            'user_id' => Auth::id(),
            'parent_id' => $this->parentReplyId,
            'content' => $this->replyContent,
            'attachments' => !empty($attachments) ? $attachments : null,
        ]);

        $this->discussion->increment('replies_count');
        $this->discussion->update([
            'last_reply_at' => now(),
            'last_reply_by' => Auth::id(),
        ]);

        $this->replyContent = '';
        $this->replyImages = [];
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
        $this->replyImages = [];
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

    public function toggleReaction($type, $discussionId, $replyId = null)
    {
        if (!auth()->check()) {
            session()->flash('error', 'Please login to react.');
            return;
        }
        
        if ($replyId) {
            // Reaction on reply
            $reply = DiscussionReply::find($replyId);
            if (!$reply) return;
            
            $existing = \App\Models\DiscussionReplyReaction::where('user_id', auth()->id())
                ->where('discussion_reply_id', $replyId)
                ->where('reaction_type', $type)
                ->first();
            
            if ($existing) {
                $existing->delete();
            } else {
                \App\Models\DiscussionReplyReaction::create([
                    'user_id' => auth()->id(),
                    'discussion_reply_id' => $replyId,
                    'reaction_type' => $type,
                ]);
                
                // Award XP
                if ($type === 'helpful' && $reply->user && $reply->user->points) {
                    $reply->user->points->increment('total_points', 2);
                }
            }
        } else {
            // Reaction on discussion
            $existing = \App\Models\DiscussionReaction::where('user_id', auth()->id())
                ->where('discussion_id', $discussionId)
                ->where('reaction_type', $type)
                ->first();
            
            if ($existing) {
                $existing->delete();
                if ($type === 'upvote') $this->discussion->decrement('upvotes');
                if ($type === 'helpful') $this->discussion->decrement('helpful_count');
            } else {
                \App\Models\DiscussionReaction::create([
                    'user_id' => auth()->id(),
                    'discussion_id' => $discussionId,
                    'reaction_type' => $type,
                ]);
                
                if ($type === 'upvote') $this->discussion->increment('upvotes');
                if ($type === 'helpful') {
                    $this->discussion->increment('helpful_count');
                    // Award XP to discussion author
                    if ($this->discussion->user && $this->discussion->user->points) {
                        $this->discussion->user->points->increment('total_points', 2);
                    }
                }
            }
        }
        
        // Refresh discussion
        $this->discussion = $this->discussion->fresh(['reactions']);
    }

    public function render()
    {
        // Optimize: Eager load users to prevent N+1 queries
        $topLevelReplies = DiscussionReply::where('discussion_id', $this->discussion->id)
            ->whereNull('parent_id')
            ->with([
                'user:id,name,email',
                'replies',
                'replies.user:id,name,email', 
                'replies.replies',
                'replies.replies.user:id,name,email',
                'replies.replies.replies',
                'replies.replies.replies.user:id,name,email' // Support 3 levels of nesting
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
