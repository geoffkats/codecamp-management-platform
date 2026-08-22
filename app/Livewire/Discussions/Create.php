<?php

namespace App\Livewire\Discussions;

use App\Models\Course;
use App\Models\Discussion;
use App\Models\Lesson;
use App\Support\DiscussionSanitizer;
use App\Services\DailyChallengeTrackerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Create extends Component
{
    use WithFileUploads;
    public $title = '';
    public $content = '';
    public $courseId = null;
    public $lessonId = null;
    public $isPinned = false;
    public $tags = [];
    public $tagInput = '';
    public $category = 'question';
    public $attachments = [];
    public $allowReplies = true;
    public $notifySubscribers = true;
    
    // Rich content fields
    public $subjectTag = '';
    public $scratchProjectId = '';
    public $codeLanguage = '';
    public $codeTitle = '';
    public $codeContent = '';
    public $images = [];

    public function mount(): void
    {
        abort_unless(Auth::user()->canAccessDiscussions(), 403);

        $lessonParam = request()->query('lesson');
        $courseParam = request()->query('course');

        if ($lessonParam) {
            $lesson = Lesson::find((int) $lessonParam);

            if ($lesson) {
                $this->lessonId = $lesson->id;
                $this->courseId = $lesson->course_id;
            }
        } elseif ($courseParam) {
            $this->courseId = (int) $courseParam;
        }
    }

    protected function rules(): array
    {
        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);

        $rules = [
            'title' => ['required', 'string', 'min:' . ($isStaff ? 5 : 10), 'max:255'],
            'content' => ['required', 'string', 'min:' . ($isStaff ? 10 : 40), 'max:10000'],
            'lessonId' => ['nullable', 'exists:lessons,id'],
            'isPinned' => ['boolean'],
            'tags' => ['array', 'max:5'],
            'allowReplies' => ['boolean'],
            'notifySubscribers' => ['boolean'],
            'subjectTag' => ['nullable', 'string', 'in:scratch,python,web,javascript'],
            'scratchProjectId' => ['nullable', 'string', 'max:50'],
            'codeLanguage' => ['nullable', 'string', 'in:python,javascript,html,css,php,sql'],
            'codeTitle' => ['nullable', 'string', 'max:100'],
            'codeContent' => ['nullable', 'string', 'max:20000'],
            'images' => ['nullable', 'array', 'max:5'],
        ];

        if ($isStaff) {
            $rules['courseId'] = ['nullable', 'exists:courses,id'];
            $rules['category'] = ['required', 'string', 'in:general,question,help,announcement,project,feedback'];
        } else {
            $rules['courseId'] = ['required', 'exists:courses,id'];
            $rules['category'] = ['required', 'string', 'in:question,help,project,feedback,general'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'title.min' => 'Use a clear title (at least 10 characters). Example: "Python loop prints wrong number".',
            'content.min' => 'Please explain your question in more detail (at least 40 characters). Say what you tried and what happened.',
            'courseId.required' => 'Select the course this post belongs to.',
        ];
    }

    public function updatedCourseId()
    {
        $this->lessonId = null; // Reset lesson when course changes
        $this->resetValidation('lessonId');
    }

    public function addTag()
    {
        if (!empty($this->tagInput) && count($this->tags) < 5) {
            $tag = trim($this->tagInput);
            if (!in_array($tag, $this->tags)) {
                $this->tags[] = $tag;
                $this->tagInput = '';
            }
        }
    }

    public function removeTag($index)
    {
        unset($this->tags[$index]);
        $this->tags = array_values($this->tags);
    }

    public function save()
    {
        $validated = $this->validate();

        // Check access - staff (admin/teacher/supervisor) can create conversations without course
        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);
        
        if ($this->courseId) {
            $course = Course::find($this->courseId);
            if (!$course) {
                session()->flash('error', 'Selected course not found.');
                return;
            }
            
            // Students can create discussions in:
            // 1. Courses they're enrolled in
            // 2. Open courses (enrollment_type = 'open') - no enrollment required
            if (!$isStaff) {
                $isEnrolled = $course
                    ->enrollments()
                    ->where('user_id', $user->id)
                    ->exists();

                $isOpen = $course->enrollment_type === 'open';

                if (!$isEnrolled && !$isOpen) {
                    session()->flash('error', 'You must be enrolled in this course to create discussions.');
                    return;
                }
            }
        }
        // Note: Students can now create discussions without a course (general discussions)

        // Validate lesson belongs to course if both are selected
        if ($this->courseId && $this->lessonId) {
            $lesson = Lesson::where('id', $this->lessonId)
                ->where('course_id', $this->courseId)
                ->first();
            
            if (!$lesson) {
                session()->flash('error', 'Selected lesson does not belong to the selected course.');
                return;
            }
        }

        // Prepare code snippets array
        $codeSnippets = [];
        if (!empty($this->codeContent) && !empty($this->codeLanguage)) {
            $codeSnippets[] = [
                'language' => $this->codeLanguage,
                'code' => DiscussionSanitizer::codeSnippet($this->codeContent),
                'title' => $this->codeTitle ? DiscussionSanitizer::title($this->codeTitle) : null,
            ];
        }

        $discussion = Discussion::create([
            'user_id' => $user->id,
            'course_id' => $this->courseId,
            'lesson_id' => $this->lessonId,
            'title' => DiscussionSanitizer::title($validated['title']),
            'category' => $validated['category'],
            'content' => DiscussionSanitizer::body($validated['content']),
            'is_pinned' => $this->isPinned && $isStaff,
            'is_locked' => false,
            'status' => 'active',
            'subject_tag' => $this->subjectTag ?: null,
            'scratch_project_id' => $this->scratchProjectId
                ? DiscussionSanitizer::scratchProjectId($this->scratchProjectId) ?: null
                : null,
            'code_snippets' => !empty($codeSnippets) ? $codeSnippets : null,
            'attachments' => !empty($this->images) ? $this->images : null,
        ]);

        // Award XP for creating discussion
        try {
            $user = Auth::user();
            $points = $user->points;
            if (!$points) {
                $points = \App\Models\UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                ]);
            }
            $points->addPoints(10); // Small XP for participation
        } catch (\Exception $e) {
            // XP awarding failed, but discussion was created, so continue
        }

        session()->flash('message', 'Discussion created successfully!');

        app(DailyChallengeTrackerService::class)->syncForumProgressForUser(
            $user->id,
            $discussion->course_id
        );

        return $this->redirect(route('discussions.show', $discussion), navigate: true);
    }

    public function render()
    {
        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['admin', 'teacher', 'supervisor']);
        
        // Staff can see all courses
        // Students can see:
        // 1. Courses they're enrolled in
        // 2. Open courses (enrollment_type = 'open')
        $courses = $isStaff
            ? Course::all()
            : Course::where(function ($q) use ($user) {
                $q->whereHas('enrollments', fn($q) => $q->where('user_id', $user->id))
                  ->orWhere('enrollment_type', 'open');
            })->get();

        $lessons = $this->courseId
            ? Lesson::where('course_id', $this->courseId)->get()
            : collect();

        $tracker = app(DailyChallengeTrackerService::class);
        $forumChallenges = $tracker->activeForumChallengesForUser(Auth::id(), $this->courseId);
        $forumChallengeProgress = $forumChallenges->mapWithKeys(function ($challenge) use ($tracker) {
            return [$challenge->id => $tracker->evaluate($challenge, Auth::id())];
        });

        return view('livewire.discussions.create', [
            'courses' => $courses,
            'lessons' => $lessons,
            'isStaff' => $isStaff,
            'forumChallenges' => $forumChallenges,
            'forumChallengeProgress' => $forumChallengeProgress,
        ]);
    }
}
