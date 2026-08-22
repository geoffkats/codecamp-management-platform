<?php

namespace App\Livewire\Discussions;

use App\Models\Course;
use App\Models\Discussion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DiscussionList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $courseId;

    public $lessonId;

    public $compact = false;

    public function mount($courseId = null, $lessonId = null, $compact = false): void
    {
        $this->courseId = $courseId ? (int) $courseId : null;
        $this->lessonId = $lessonId ? (int) $lessonId : null;
        $this->compact = (bool) $compact;

        $user = Auth::user();

        abort_unless($user->canAccessDiscussions(), 403);

        if ($this->courseId && ! Discussion::userCanAccessCourse($user, $this->courseId)) {
            abort(403, 'You must be enrolled in this course to view discussions.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = Discussion::query()
            ->with([
                'user:id,name',
                'course:id,title',
                'lesson:id,title',
            ])
            ->withCount('replies')
            ->where('status', 'active')
            ->visibleToUser($user)
            ->when($this->courseId, fn ($q) => $q->where('course_id', $this->courseId))
            ->when($this->lessonId, fn ($q) => $q->where('lesson_id', $this->lessonId))
            ->orderByDesc('is_pinned')
            ->latest('last_reply_at')
            ->latest('created_at');

        $discussions = $query->paginate($this->compact ? 5 : 15);

        $createParams = array_filter([
            'course' => $this->courseId,
            'lesson' => $this->lessonId,
        ]);

        return view('livewire.discussions.discussion-list', [
            'discussions' => $discussions,
            'createParams' => $createParams,
        ]);
    }
}
