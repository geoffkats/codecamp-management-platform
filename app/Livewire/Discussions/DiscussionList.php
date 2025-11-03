<?php

namespace App\Livewire\Discussions;

use App\Models\Discussion;
use Livewire\Component;
use Livewire\WithPagination;

class DiscussionList extends Component
{
    use WithPagination;

    public $courseId;
    public $lessonId;
    public $search = '';
    public $filterStatus = 'active';

    public function mount($courseId = null, $lessonId = null)
    {
        $this->courseId = $courseId;
        $this->lessonId = $lessonId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Discussion::with(['user', 'lastReplyBy'])
            ->when($this->courseId, function ($q) {
                $q->where('course_id', $this->courseId);
            })
            ->when($this->lessonId, function ($q) {
                $q->where('lesson_id', $this->lessonId);
            })
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_reply_at')
            ->orderByDesc('created_at');

        return view('livewire.discussions.discussion-list', [
            'discussions' => $query->paginate(15)
        ]);
    }
}

