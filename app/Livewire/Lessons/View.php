<?php

namespace App\Livewire\Lessons;

use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\VideoProgress;
use App\Services\LessonCompletionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class View extends Component
{
    public Lesson $lesson;
    public $course;
    public $enrollment;
    public $videoProgress = 0;
    public $videoWatchedSeconds = 0;
    public $isVideoCompleted = false;
    public $isLessonCompleted = false;
    public $previousLesson = null;
    public $nextLesson = null;
    public $completionStatus = [];
    public $canComplete = false;
    public $showCompletionModal = false;
    public $autoCompleting = false;

    protected $listeners = [
        'assessment-completed' => 'checkCompletionStatus',
        'assignment-submitted' => 'checkCompletionStatus',
        'course-progress-updated' => 'checkCompletionStatus',
        'lesson-video-progress' => 'handleVideoProgressUpdated',
        'lesson-video-completed' => 'handleVideoCompleted',
    ];

    public function mount(Lesson $lesson)
    {
        // Optimize: Load only necessary relationships with select to reduce payload
        $this->lesson = $lesson->load([
            'module:id,course_id,title,order_index',
            'module.course:id,title,instructor_id,enrollment_type',
            'module.course.instructor:id,name',
            'quizzes:id,lesson_id,title,time_limit',
            'assignments:id,lesson_id,title,due_date',
            'assessments:id,lesson_id,title,assessment_type,time_limit_minutes,passing_score',
        ]);

        $this->course = $this->lesson->module->course;

        $user = Auth::user();

        // Check enrollment
        $this->enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->where('course_id', $this->course->id)
            ->first();

        $hasLearnerEnrollment = (bool) $this->enrollment;

        $isInstructor = !$hasLearnerEnrollment && (
            $this->course->instructor_id === $user->id ||
            $user->hasRole('admin') ||
            $user->hasRole('supervisor') ||
            $user->hasRole('teacher') ||
            $user->hasRole('codecamp_trainer')
        );

        $isIctTeacherWithAccess = false;
        if ($user->isIctTeacher()) {
            $schoolId = $user->ictSchoolId();
            $isIctTeacherWithAccess = $schoolId
                && $this->course->schools
                    ->where('id', (int) $schoolId)
                    ->where('pivot.is_active', true)
                    ->isNotEmpty();
        }

        if (!$this->enrollment && !$isInstructor && !$isIctTeacherWithAccess) {
            abort(403, 'You must be enrolled in this course to view lessons.');
        }
        
        // Load video progress if it's a video lesson
        if ($this->lesson->video_url || $this->lesson->lesson_type === 'video') {
            $this->loadVideoProgress();
        }

        // Record start first (never wipes completed), then evaluate auto-complete.
        $this->markLessonAsStarted();
        $this->checkLessonCompletion();
        $this->checkCompletionStatus();
        $this->findAdjacentLessons();
    }
    
    public function checkCompletionStatus()
    {
        $service = app(LessonCompletionService::class);
        $this->checkLessonCompletion();
        $check = $service->canCompleteLesson($this->lesson, Auth::user());
        
        $this->completionStatus = $check;
        $this->canComplete = $check['can_complete'];

        if ($this->canComplete && !$this->isLessonCompleted && !$this->autoCompleting) {
            $this->autoCompleting = true;
            $this->completeLesson();
            $this->autoCompleting = false;
        }
        
        // Clear cache when status changes
        Cache::forget("lesson.{$this->lesson->id}.user." . Auth::id());
    }

    public function handleVideoProgressUpdated($progress, $watchedSeconds, $isCompleted)
    {
        $this->videoProgress = $progress;
        $this->videoWatchedSeconds = $watchedSeconds;
        $this->isVideoCompleted = $isCompleted;
    }

    public function handleVideoCompleted()
    {
        if ($this->isLessonCompleted) {
            return;
        }

        $this->checkCompletionStatus();

        if ($this->canComplete) {
            $this->completeLesson();
        }
    }

    private function loadVideoProgress()
    {
        $progress = VideoProgress::where('user_id', Auth::id())
            ->where('lesson_id', $this->lesson->id)
            ->first();

        if ($progress) {
            $this->videoWatchedSeconds = $progress->watched_seconds ?? 0;
            $this->videoProgress = $progress->progress_percentage ?? 0;
            $this->isVideoCompleted = $progress->is_completed ?? false;
        }
    }

    private function checkLessonCompletion()
    {
        $this->isLessonCompleted = app(LessonCompletionService::class)
            ->isLessonCompleted(Auth::user(), $this->lesson);
    }

    private function findAdjacentLessons()
    {
        // Optimize: Use database query instead of loading all lessons
        $currentModule = $this->lesson->module;
        
        // Try to find previous lesson in same module
        $this->previousLesson = Lesson::where('module_id', $currentModule->id)
            ->where('order_index', '<', $this->lesson->order_index)
            ->orderBy('order_index', 'desc')
            ->select('id', 'title', 'module_id', 'order_index')
            ->first();
        
        // If no previous in same module, check previous module
        if (!$this->previousLesson) {
            $previousModule = CourseModule::where('course_id', $this->course->id)
                ->where('order_index', '<', $currentModule->order_index)
                ->orderBy('order_index', 'desc')
                ->first();
            
            if ($previousModule) {
                $this->previousLesson = Lesson::where('module_id', $previousModule->id)
                    ->orderBy('order_index', 'desc')
                    ->select('id', 'title', 'module_id', 'order_index')
                    ->first();
            }
        }
        
        // Try to find next lesson in same module
        $this->nextLesson = Lesson::where('module_id', $currentModule->id)
            ->where('order_index', '>', $this->lesson->order_index)
            ->orderBy('order_index', 'asc')
            ->select('id', 'title', 'module_id', 'order_index')
            ->first();
        
        // If no next in same module, check next module
        if (!$this->nextLesson) {
            $nextModule = CourseModule::where('course_id', $this->course->id)
                ->where('order_index', '>', $currentModule->order_index)
                ->orderBy('order_index', 'asc')
                ->first();
            
            if ($nextModule) {
                $this->nextLesson = Lesson::where('module_id', $nextModule->id)
                    ->orderBy('order_index', 'asc')
                    ->select('id', 'title', 'module_id', 'order_index')
                    ->first();
            }
        }
    }

    private function markLessonAsStarted()
    {
        // Don't record progress for instructors/admins previewing the lesson
        $user = Auth::user();
        $isPreview = !$this->enrollment && (
            $user->hasAnyRole(['admin', 'supervisor', 'teacher', 'codecamp_trainer']) ||
            $this->course->instructor_id === $user->id
        );
        if ($isPreview) {
            return;
        }

        app(LessonCompletionService::class)->ensureStarted($user, $this->lesson);
    }


    public function confirmCompleteLesson()
    {
        $this->showCompletionModal = false;
        $this->completeLesson();
    }
    
    public function openCompletionModal()
    {
        $this->checkCompletionStatus();
        $this->showCompletionModal = true;
    }

    public function completeLesson()
    {
        // Block instructors from accidentally marking their own preview as "complete"
        $user = Auth::user();
        $isPreview = !$this->enrollment && (
            $user->hasAnyRole(['admin', 'supervisor', 'teacher', 'codecamp_trainer']) ||
            $this->course->instructor_id === $user->id
        );
        if ($isPreview) {
            return;
        }

        if ($this->isLessonCompleted) {
            return;
        }

        $check = app(LessonCompletionService::class)->canCompleteLesson($this->lesson, Auth::user());
        $this->completionStatus = $check;
        $this->canComplete = $check['can_complete'];

        if (! $this->canComplete) {
            $first = $check['missing'][0] ?? [];
            $detail = $first['message'] ?? $first['title'] ?? null;
            session()->flash(
                'error',
                $detail
                    ? $detail.' You cannot mark this lesson complete yet.'
                    : 'Finish the required assignment or quiz before marking this lesson complete.'
            );

            return;
        }

        $result = app(LessonCompletionService::class)->completeIfEligible(Auth::user(), $this->lesson);

        if (! ($result['completed'] ?? false)) {
            return;
        }

        if ($this->enrollment) {
            $this->enrollment->refresh();
        }

        $this->checkLessonCompletion();
        $this->isLessonCompleted = true;
        $this->canComplete = true;

        if (! ($result['already'] ?? false)) {
            session()->flash('message', 'Lesson completed! Great job!');
        }

        $points = (int) ($result['points'] ?? 0);
        $this->dispatch('lesson-completed', xp: $points, title: $this->lesson->title);
        $this->dispatch('course-progress-updated');

        if (
            $this->enrollment
            && $this->enrollment->completed_at
            && config('certificate.auto_generate_on_course_completion', false)
        ) {
            $this->generateCertificate();
        }
    }

    private function generateCertificate()
    {
        $dataService = app(\App\Services\CertificateDataService::class);

        if ($dataService->certificateCovering(Auth::user(), $this->course->id)) {
            return;
        }

        $dataService->createOrUpdateCertificate(
            Auth::user(),
            $this->course,
            $dataService->modulesForCourse(Auth::user(), $this->course),
            now(),
        );

        // Check certificate badge
        $badge = \App\Models\Badge::where('slug', 'course-master')->first();
        if ($badge && !Auth::user()->badges()->where('badge_id', $badge->id)->exists()) {
            Auth::user()->badges()->attach($badge->id, ['earned_at' => now()]);
            
            if (Auth::user()->points) {
                Auth::user()->points->addPoints((int) ($badge->points_reward ?? 200));
            }
        }
    }

    public function render()
    {
        $modules = CourseModule::where('course_id', $this->course->id)
            ->with(['lessons' => fn($q) => $q->orderBy('order_index')
                ->select('id', 'module_id', 'title', 'order_index', 'lesson_type', 'is_locked')])
            ->orderBy('order_index')
            ->get();

        $allLessonIds = $modules->pluck('lessons')->flatten()->pluck('id')->toArray();

        $completedLessonIds = app(LessonCompletionService::class)
            ->completedLessonIds(Auth::user(), $allLessonIds);

        $courseProgress = $this->enrollment
            ? (int) ($this->enrollment->progress_percentage ?? 0)
            : 0;

        return view('livewire.lessons.view', compact('modules', 'completedLessonIds', 'courseProgress'));
    }
}






