<?php

namespace App\Livewire\Lessons;

use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\StudentLessonProgress;
use App\Models\VideoProgress;
use App\Models\UserProgress;
use App\Services\BadgeAwardingService;
use App\Services\LessonCompletionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            'assessments:id,lesson_id,title,time_limit_minutes,passing_score',
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

        // Check lesson completion
        $this->checkLessonCompletion();
        
        // Check completion requirements
        $this->checkCompletionStatus();

        // Find previous and next lessons
        $this->findAdjacentLessons();

        // Mark lesson as started if not already
        $this->markLessonAsStarted();
    }
    
    public function checkCompletionStatus()
    {
        $service = app(LessonCompletionService::class);
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
        // Check both student_lesson_progress and lesson_progress
        $studentProgress = StudentLessonProgress::where('user_id', Auth::id())
            ->where('lesson_id', $this->lesson->id)
            ->first();

        $lessonProgress = \App\Models\LessonProgress::where('user_id', Auth::id())
            ->where('lesson_id', $this->lesson->id)
            ->first();

        $this->isLessonCompleted = ($studentProgress && $studentProgress->status === 'completed') || 
                                   ($lessonProgress && $lessonProgress->is_completed);
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

        StudentLessonProgress::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'lesson_id' => $this->lesson->id,
            ],
            [
                'status' => 'in_progress',
                'started_at' => now(),
            ]
        );
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
        // Bypass completion requirements and video checks.
        $this->canComplete = true;
        
        // Wrap everything in a transaction for data integrity
        DB::transaction(function () {
            $this->executeCompletion();
        });

        // Fire confetti + XP toast on the frontend
        $points = 10;
        if ($this->lesson->difficulty_level) {
            $points = match(strtolower($this->lesson->difficulty_level)) {
                'beginner' => 5, 'intermediate' => 10, 'advanced' => 15, default => 10,
            };
        }
        $this->dispatch('lesson-completed', xp: $points, title: $this->lesson->title);
    }
    
    private function executeCompletion()
    {
        // Calculate points to award based on difficulty
        $points = 10; // Default points
        if ($this->lesson->difficulty_level) {
            $points = match(strtolower($this->lesson->difficulty_level)) {
                'beginner' => 5,
                'intermediate' => 10,
                'advanced' => 15,
                default => 10,
            };
        }

        // Mark lesson as completed
        $timeSpent = $this->calculateTimeSpent();
        $studentProgress = StudentLessonProgress::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'lesson_id' => $this->lesson->id,
            ],
            [
                'status' => 'completed',
                'progress_percentage' => 100,
                'completed_at' => now(),
                'time_spent_minutes' => round($timeSpent / 60, 2),
                'last_accessed_at' => now(),
                'completion_data' => [
                    'completed_at' => now()->toIso8601String(),
                    'video_completed' => $this->isVideoCompleted,
                    'points_earned' => $points,
                ],
            ]
        );
        
        // Also update lesson_progress table for compatibility
        \App\Models\LessonProgress::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'lesson_id' => $this->lesson->id,
            ],
            [
                'is_completed' => true,
                'completed_at' => now(),
                'time_spent' => $timeSpent,
            ]
        );

        // Award lesson completion points (safe from duplicates)
        $pointsService = app(\App\Services\PointsService::class);
        $pointsService->awardLessonPoints(Auth::id(), $this->course->id, $this->lesson->id, $points, $timeSpent);

        // Update course enrollment progress
        $this->updateCourseProgress();

        // Check and award badges for lesson completion
        $badgeService = app(BadgeAwardingService::class);
        $badgeService->onLessonComplete(Auth::user());
        $badgeService->checkLessonCompletionBadges(Auth::user());
        $badgeService->checkLevelBadges(Auth::user());
        $badgeService->checkPointMilestoneBadges(Auth::user());

        // Clear cache after completion
        Cache::forget("lesson.{$this->lesson->id}.user." . Auth::id());

        // Refresh lesson completion status
        $this->checkLessonCompletion();
        $this->isLessonCompleted = true;
        $this->canComplete = true;
        
        session()->flash('message', 'Lesson completed! Great job! ðŸŽ‰');
        
        // Dispatch events to update UI
        $this->dispatch('lesson-completed');
        $this->dispatch('course-progress-updated');
    }

    private function calculateTimeSpent()
    {
        $progress = StudentLessonProgress::where('user_id', Auth::id())
            ->where('lesson_id', $this->lesson->id)
            ->first();

        if ($progress && $progress->started_at) {
            // Use abs() to prevent negative values and ensure positive integer
            return abs(now()->diffInSeconds($progress->started_at));
        }

        return 0;
    }

    private function updateCourseProgress()
    {
        // Optimize: Use database query instead of loading all lessons
        $totalLessons = Lesson::whereHas('module', function($q) {
            $q->where('course_id', $this->course->id);
        })->count();

        if ($totalLessons === 0) {
            return;
        }

        $allLessonIds = Lesson::whereHas('module', function($q) {
            $q->where('course_id', $this->course->id);
        })->pluck('id')->toArray();

        // Single query for all completed lessons from student_lesson_progress
        $completedLessonIds1 = StudentLessonProgress::where('user_id', Auth::id())
            ->whereIn('lesson_id', $allLessonIds)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->toArray();

        // Single query for all completed lessons from lesson_progress
        $completedLessonIds2 = \App\Models\LessonProgress::where('user_id', Auth::id())
            ->whereIn('lesson_id', $allLessonIds)
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->toArray();

        // Merge and get unique count
        $completedLessonIds = array_unique(array_merge($completedLessonIds1, $completedLessonIds2));
        $completedLessons = count($completedLessonIds);

        $progressPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

        // Refresh enrollment to get fresh data
        $this->enrollment->refresh();

        $isCompleted = $this->enrollment->completed_at !== null || $completedLessons >= $totalLessons;

        // Update enrollment
        $this->enrollment->update([
            'progress_percentage' => $isCompleted ? 100 : $progressPercentage,
            'lessons_completed' => $completedLessons,
        ]);

        // Check if course is completed
        if ($completedLessons >= $totalLessons && !$this->enrollment->completed_at) {
            $this->enrollment->update([
                'completed_at' => now(),
                'progress_percentage' => 100,
            ]);
            
            // Award course completion points (safe from duplicates)
            $pointsService = app(\App\Services\PointsService::class);
            $pointsService->awardCourseCompletionPoints(Auth::id(), $this->course->id);

            // Check and award badges for course completion
            $badgeService = app(BadgeAwardingService::class);
            $badgeService->checkCourseCompletionBadges(Auth::user());
            $badgeService->checkLevelBadges(Auth::user());
            $badgeService->checkPointMilestoneBadges(Auth::user());

            // Auto-generate certificate only when explicitly enabled.
            if (config('certificate.auto_generate_on_course_completion', false)) {
                $this->generateCertificate();
            }
        }
    }

    private function generateCertificate()
    {
        // Check if certificate already exists
        $existing = \App\Models\Certificate::where('user_id', Auth::id())
            ->where('course_id', $this->course->id)
            ->first();

        if ($existing) {
            return; // Certificate already exists
        }

        // Generate certificate with module profile data
        $dataService = app(\App\Services\CertificateDataService::class);
        $modules = $dataService->buildModulesForUser(Auth::user(), $this->course->id);

        \App\Models\Certificate::create([
            'user_id' => Auth::id(),
            'course_id' => $this->course->id,
            'certificate_number' => Auth::user()->studentProfile?->student_id
                ?? ('CERT-' . strtoupper(substr(md5(time() . $this->course->id . Auth::id()), 0, 8)) . '-' . date('Y')),
            'title' => 'CODE Profile Certificate',
            'description' => 'This certifies that ' . Auth::user()->name . ' has successfully completed modules in "' . $this->course->title . '".',
            'issued_at' => now(),
            'expires_at' => null,
            'is_verified' => true,
            'completion_data' => [
                'progress_percentage' => $this->enrollment->progress_percentage,
                'completion_date' => $this->enrollment->completed_at?->format('Y-m-d'),
                'lessons_completed' => $this->enrollment->lessons_completed ?? 0,
                'instructor' => $this->course->instructor->name ?? 'System',
                'modules' => $modules,
            ],
        ]);

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
                ->select('id', 'module_id', 'title', 'order_index', 'lesson_type')])
            ->orderBy('order_index')
            ->get();

        $allLessonIds = $modules->pluck('lessons')->flatten()->pluck('id')->toArray();

        $completedLessonIds = empty($allLessonIds) ? [] : StudentLessonProgress::where('user_id', Auth::id())
            ->whereIn('lesson_id', $allLessonIds)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->toArray();

        $courseProgress = $this->enrollment
            ? (int) ($this->enrollment->progress_percentage ?? 0)
            : 0;

        return view('livewire.lessons.view', compact('modules', 'completedLessonIds', 'courseProgress'));
    }
}






