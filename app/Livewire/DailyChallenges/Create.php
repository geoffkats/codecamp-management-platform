<?php

namespace App\Livewire\DailyChallenges;

use App\Models\DailyChallenge;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    // Step control
    public string $step = 'template'; // 'template' | 'form'

    // Template selection
    public string $selectedTemplate = '';

    // Form fields
    public string $title       = '';
    public string $description = '';
    public string $type        = '';
    public string $requirements= '';
    public int    $reward_points = 100;
    public string $date        = '';
    public bool   $is_active        = true;
    public string $difficulty_level = 'medium';
    public string $category         = '';
    public $course_id               = null;
    public bool   $is_competition   = false;
    public string $competition_ends_at = '';

    public $courses = [];
    public bool $isAdmin = false;

    /** Points automatically suggested per difficulty */
    public const POINTS_MAP = ['easy' => 50, 'medium' => 100, 'hard' => 200];

    /** Challenge templates — type + smart defaults */
    public const TEMPLATES = [
        'lesson_completion' => [
            'label'       => 'Complete a Lesson',
            'icon'        => 'book',
            'color'       => 'blue',
            'description' => 'Students earn XP by completing any lesson in the course.',
            'title_tpl'   => 'Complete a Lesson Today',
            'desc_tpl'    => 'Pick a lesson from your enrolled courses and finish it to claim your daily XP reward.',
            'reqs'        => ['Open a lesson', 'Watch/read the full content', 'Mark it as complete'],
            'category'    => 'learning',
        ],
        'quiz_score' => [
            'label'       => 'Pass a Quiz',
            'icon'        => 'academic-cap',
            'color'       => 'purple',
            'description' => 'Students must score above a threshold on any quiz or assessment.',
            'title_tpl'   => 'Score 80% on a Quiz',
            'desc_tpl'    => 'Take any quiz in your enrolled courses and score 80% or higher to earn bonus XP.',
            'reqs'        => ['Start a quiz or assessment', 'Answer all questions', 'Achieve 80% or above'],
            'category'    => 'assessment',
        ],
        'study_time' => [
            'label'       => 'Study Session',
            'icon'        => 'clock',
            'color'       => 'green',
            'description' => 'Students earn XP for spending time learning on the platform.',
            'title_tpl'   => 'Study for 30 Minutes',
            'desc_tpl'    => 'Spend at least 30 minutes going through lessons today. Consistent study builds lasting skills.',
            'reqs'        => ['Open any lesson', 'Stay active for 30 minutes', 'Complete or continue progress'],
            'category'    => 'consistency',
        ],
        'course_progress' => [
            'label'       => 'Advance in a Course',
            'icon'        => 'chart-bar',
            'color'       => 'orange',
            'description' => 'Students must move their course progress forward by a percentage.',
            'title_tpl'   => 'Make Progress in a Course',
            'desc_tpl'    => 'Increase your course completion percentage by at least 5% today. Every step counts!',
            'reqs'        => ['Open your enrolled course', 'Complete at least one lesson', 'Increase progress by 5%'],
            'category'    => 'progress',
        ],
        'forum_participation' => [
            'label'       => 'Join the Discussion',
            'icon'        => 'chat-bubble-left-right',
            'color'       => 'teal',
            'description' => 'Students earn XP by contributing to lesson discussions.',
            'title_tpl'   => 'Post in a Discussion',
            'desc_tpl'    => 'Share your thoughts, ask a question, or help a classmate in any lesson discussion today.',
            'reqs'        => ['Open any lesson', 'Go to the Discuss section', 'Write a meaningful contribution'],
            'category'    => 'community',
        ],
        'assignment_submission' => [
            'label'       => 'Submit an Assignment',
            'icon'        => 'document-text',
            'color'       => 'red',
            'description' => 'Students earn XP for submitting any assignment before the deadline.',
            'title_tpl'   => 'Submit an Assignment',
            'desc_tpl'    => 'Find an open assignment in your courses and submit your work today to earn bonus XP.',
            'reqs'        => ['Check your course for open assignments', 'Complete your work', 'Submit before deadline'],
            'category'    => 'practice',
        ],
    ];

    protected function rules(): array
    {
        return [
            'title'           => 'required|string|max:255',
            'description'     => 'required|string',
            'type'            => 'required|string|max:50',
            'requirements'    => 'nullable|string',
            'reward_points'   => 'required|integer|min:1|max:1000',
            'date'            => 'nullable|date',
            'is_active'       => 'boolean',
            'difficulty_level'=> 'required|in:easy,medium,hard',
            'category'        => 'nullable|string|max:50',
            'course_id'       => 'nullable|exists:courses,id',
        ];
    }

    public function mount()
    {
        Gate::authorize('manage_challenges');

        $user      = Auth::user();
        $this->isAdmin = $user->isAdmin() || $user->isSupervisor();
        $this->date = now()->toDateString();

        $query = Course::where('is_published', true)
            ->where('approval_status', 'approved')
            ->orderBy('title');

        if (! $this->isAdmin) {
            $query->where('instructor_id', $user->id);
        }

        $this->courses = $query->get(['id', 'title']);
    }

    /** Pick a template and jump to the form with pre-filled values */
    public function selectTemplate(string $template): void
    {
        if (! isset(self::TEMPLATES[$template])) {
            return;
        }

        $tpl = self::TEMPLATES[$template];

        $this->selectedTemplate  = $template;
        $this->type              = $template;
        $this->title             = $tpl['title_tpl'];
        $this->description       = $tpl['desc_tpl'];
        $this->requirements      = implode("\n", $tpl['reqs']);
        $this->category          = $tpl['category'];
        $this->difficulty_level  = 'medium';
        $this->reward_points     = self::POINTS_MAP['medium'];
        $this->step              = 'form';
    }

    /** Quick-set difficulty and auto-update points */
    public function setDifficulty(string $level): void
    {
        $this->difficulty_level = $level;
        $this->reward_points    = self::POINTS_MAP[$level] ?? 100;
    }

    /** Quick-set date from preset */
    public function setDate(string $preset): void
    {
        $this->date = match ($preset) {
            'today'    => now()->toDateString(),
            'tomorrow' => now()->addDay()->toDateString(),
            'week'     => now()->addWeek()->toDateString(),
            'evergreen'=> '',
            default    => $this->date,
        };
    }

    public function back(): void
    {
        $this->step = 'template';
        $this->reset(['selectedTemplate', 'title', 'description', 'type', 'requirements', 'category', 'course_id']);
        $this->difficulty_level = 'medium';
        $this->reward_points    = 100;
        $this->date             = now()->toDateString();
    }

    public function save()
    {
        Gate::authorize('manage_challenges');
        $this->validate();

        $requirementsArray = [];
        if ($this->requirements) {
            $requirementsArray = array_values(
                array_filter(array_map('trim', explode("\n", $this->requirements)))
            );
        }

        DailyChallenge::create([
            'title'                => $this->title,
            'description'          => $this->description,
            'type'                 => $this->type,
            'requirements'         => $requirementsArray,
            'reward_points'        => $this->reward_points,
            'date'                 => $this->date ?: null,
            'is_active'            => $this->is_active,
            'difficulty_level'     => $this->difficulty_level,
            'category'             => $this->category ?: null,
            'course_id'            => $this->course_id,
            'created_by'           => Auth::id(),
            'is_competition'       => $this->is_competition,
            'competition_ends_at'  => $this->is_competition && $this->competition_ends_at
                                        ? $this->competition_ends_at
                                        : null,
        ]);

        session()->flash('message', 'Challenge created!');

        return $this->redirect(route('daily-challenges.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.daily-challenges.create', [
            'templates'     => self::TEMPLATES,
            'pointsMap'     => self::POINTS_MAP,
        ]);
    }
}
