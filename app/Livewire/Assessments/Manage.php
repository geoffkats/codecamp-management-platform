<?php

namespace App\Livewire\Assessments;

use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Staff home for assessments: every quiz, test and assignment across the courses the user manages.
 */
#[Layout('components.layouts.app')]
#[Title('Assessments')]
class Manage extends Component
{
    use WithPagination;

    public const TYPES = [
        'quiz' => ['label' => 'Quiz', 'icon' => 'bolt', 'tile' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/40 dark:text-orange-300'],
        'assignment' => ['label' => 'Assignment', 'icon' => 'clipboard-document-list', 'tile' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300'],
        'pre_project_test' => ['label' => 'Pre-project test', 'icon' => 'flag', 'tile' => 'bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300'],
        'post_project_test' => ['label' => 'Post-project test', 'icon' => 'trophy', 'tile' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300'],
        'unit_survey' => ['label' => 'Survey', 'icon' => 'chat-bubble-left-right', 'tile' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'],
        'rubric_assessment' => ['label' => 'Rubric', 'icon' => 'clipboard-document-check', 'tile' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-300'],
        'peer_review' => ['label' => 'Peer review', 'icon' => 'users', 'tile' => 'bg-pink-100 text-pink-600 dark:bg-pink-900/40 dark:text-pink-300'],
        'self_assessment' => ['label' => 'Self-assessment', 'icon' => 'user', 'tile' => 'bg-teal-100 text-teal-600 dark:bg-teal-900/40 dark:text-teal-300'],
    ];

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $course = '';

    #[Url(except: '')]
    public string $type = '';

    /** all | grading | empty */
    #[Url(except: 'all')]
    public string $view = 'all';

    public function updating($property): void
    {
        if (in_array($property, ['search', 'course', 'type', 'view'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'course', 'type']);
        $this->view = 'all';
        $this->resetPage();
    }

    protected function scoped(): Builder
    {
        $user = Auth::user();

        return Assessment::query()
            ->whereHas('course', fn ($q) => $user->isAdmin() ? $q : $q->accessibleBy($user));
    }

    protected static function pendingAttempts($q)
    {
        return $q->where(fn ($w) => $w->where('status', 'completed')->orWhereNotNull('completed_at'))
            ->whereNull('score');
    }

    public function render()
    {
        $user = Auth::user();

        $assessments = $this->scoped()
            ->with(['course:id,title', 'lesson:id,title,module_id', 'lesson.module:id,title'])
            ->withCount([
                'questions',
                'attempts as completed_attempts_count' => fn ($q) => $q->where('status', 'completed'),
                'attempts as passed_attempts_count' => fn ($q) => $q->where('status', 'completed')->where('is_passed', true),
                'attempts as pending_attempts_count' => fn ($q) => static::pendingAttempts($q),
            ])
            ->withSum('questions', 'points')
            ->when($this->search !== '', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->course !== '', fn ($q) => $q->where('course_id', $this->course))
            ->when($this->type !== '', fn ($q) => $q->where('assessment_type', $this->type))
            ->when($this->view === 'grading', fn ($q) => $q->whereHas('attempts', fn ($a) => static::pendingAttempts($a)))
            ->when($this->view === 'empty', fn ($q) => $q->where('assessment_type', '!=', 'assignment')->whereDoesntHave('questions'))
            ->orderByDesc('updated_at')
            ->paginate(20);

        $base = $this->scoped();
        $stats = [
            'total' => (clone $base)->count(),
            'quizzes' => (clone $base)->where('assessment_type', '!=', 'assignment')->count(),
            'assignments' => (clone $base)->where('assessment_type', 'assignment')->count(),
            'grading' => (clone $base)->whereHas('attempts', fn ($a) => static::pendingAttempts($a))->count(),
            'empty' => (clone $base)->where('assessment_type', '!=', 'assignment')->whereDoesntHave('questions')->count(),
        ];

        return view('livewire.assessments.manage', [
            'assessments' => $assessments,
            'stats' => $stats,
            'types' => self::TYPES,
            'courses' => Course::query()
                ->when(! $user->isAdmin(), fn ($q) => $q->accessibleBy($user))
                ->orderBy('title')
                ->get(['id', 'title']),
        ]);
    }
}
