<?php

namespace App\Livewire\Discussions;

use App\Models\Discussion;
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
    public $subjectFilter = 'all'; // 'all', 'scratch', 'python', 'web', 'javascript'
    public $courseId = null;
    public $lessonId = null;

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
                'id', 'title', 'content', 'user_id', 'course_id', 'lesson_id',
                'subject_tag', 'is_pinned', 'has_best_answer', 'upvotes',
                'helpful_count', 'views_count', 'scratch_project_id',
                'code_snippets', 'created_at'
            ])
            ->latest('created_at');

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%');
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
        if (!$isStaff) {
            $userId = $user->id;
            
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId) // User's own discussions
                    // OR discussions from courses they're enrolled in
                    ->orWhereHas('course.enrollments', function ($enrollmentQuery) use ($userId) {
                        $enrollmentQuery->where('user_id', $userId);
                    })
                    // OR discussions from open courses (publicly accessible)
                    ->orWhereHas('course', function ($courseQuery) {
                        $courseQuery->where('enrollment_type', 'open');
                    })
                    // OR discussions without a course association (general discussions - visible to all)
                    ->orWhereNull('course_id');
            });
        }

        $discussions = $query->paginate(15);

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
        ]);
    }
}
