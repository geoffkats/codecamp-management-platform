<?php

namespace App\Livewire\Discussions;

use App\Models\Discussion;
use App\Services\DailyChallengeTrackerService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filter = 'all'; // 'all', 'course', 'lesson', 'my_discussions'
    public $categoryFilter = 'all';
    public $subjectFilter = 'all';
    public $courseId = null;
    public $lessonId = null;

    public function mount(): void
    {
        abort_unless(Auth::user()->canAccessDiscussions(), 403);

        $lessonParam = request()->query('lesson');
        $courseParam = request()->query('course');

        if ($lessonParam) {
            $lesson = \App\Models\Lesson::find((int) $lessonParam);

            if ($lesson) {
                $this->lessonId = $lesson->id;
                $this->courseId = $lesson->course_id;
                $this->filter = 'lesson';
            }
        } elseif ($courseParam) {
            $this->courseId = (int) $courseParam;
            $this->filter = 'course';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);
        
        $query = Discussion::with([
                'user.studentProfile',
                'course:id,title',
                'lesson:id,title'
            ])
            ->withCount('replies')
            ->select([
                'id', 'title', 'category', 'content', 'user_id', 'course_id', 'lesson_id',
                'subject_tag', 'is_pinned', 'has_best_answer', 'upvotes',
                'helpful_count', 'views_count', 'scratch_project_id',
                'code_snippets', 'created_at', 'last_reply_at'
            ])
            ->orderByDesc('is_pinned')
            ->latest('last_reply_at')
            ->latest('created_at');

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%');
        }

        // Apply category filter
        if ($this->categoryFilter && $this->categoryFilter !== 'all') {
            $query->where('category', $this->categoryFilter);
        }

        // Apply subject filter
        if ($this->subjectFilter && $this->subjectFilter !== 'all') {
            $query->where('subject_tag', $this->subjectFilter);
        }

        if ($this->filter === 'my_discussions') {
            $query->where('user_id', Auth::id());
        } elseif ($this->filter === 'course' && $this->courseId) {
            $query->where('course_id', $this->courseId)
                  ->whereNull('lesson_id');
        } elseif ($this->filter === 'lesson' && $this->lessonId) {
            $query->where('lesson_id', $this->lessonId);
        }

        if ($this->courseId) {
            $query->where('course_id', $this->courseId);
        }

        if ($this->lessonId) {
            $query->where('lesson_id', $this->lessonId);
        }

        // Access control: Students can only see discussions from:
        // 1. Discussions they created themselves (always visible)
        // 2. Courses they're enrolled in
        // 3. Open courses (enrollment_type = 'open')
        // 4. Discussions without a course association (general discussions - visible to all)
        // Staff can see all discussions
        if (! $isStaff) {
            $query->visibleToUser($user);
        }

        $discussions = $query->paginate(15);

        $tracker = app(DailyChallengeTrackerService::class);
        $forumChallenges = $tracker->activeForumChallengesForUser(Auth::id());
        $forumChallengeProgress = $forumChallenges->mapWithKeys(function ($challenge) use ($tracker) {
            return [$challenge->id => $tracker->evaluate($challenge, Auth::id())];
        });

        // Cache stats for 5 minutes to reduce database load
        $stats = cache()->remember('discussion_stats_' . Auth::id(), 300, function () {
            return [
                'total' => Discussion::count(),
                'my_discussions' => Discussion::where('user_id', Auth::id())->count(),
                'recent' => Discussion::where('created_at', '>=', now()->subDays(7))->count(),
            ];
        });

        return view('livewire.discussions.index', [
            'discussions' => $discussions,
            'stats' => $stats,
            'forumChallenges' => $forumChallenges,
            'forumChallengeProgress' => $forumChallengeProgress,
        ]);
    }
}
