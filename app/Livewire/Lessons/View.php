<?php

namespace App\Livewire\Lessons;

use App\Models\CourseEnrollment;
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

    protected $listeners = [
        'assessment-completed' => 'checkCompletionStatus',
        'assignment-submitted' => 'checkCompletionStatus',
        'course-progress-updated' => 'checkCompletionStatus',
    ];

    public function mount(Lesson $lesson)
    {
        // Cache lesson data for 5 minutes to reduce database load
        $cacheKey = "lesson.{$lesson->id}.user." . Auth::id();
        
        $this->lesson = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($lesson) {
            return $lesson->load([
                'module.course.modules.lessons',
                'module.course.instructor',
                'quizzes.questions',
                'assignments' => function($q) {
                    $q->with(['submissions' => function($sq) {
                        $sq->where('user_id', Auth::id());
                    }]);
                },
                'assessments.questions.options',
                'assessments.attempts' => function($aq) {
                    $aq->where('user_id', Auth::id())->orderBy('completed_at', 'desc');
                },
            ]);
        });

        $this->course = $this->lesson->module->course;

        // Check enrollment
        $this->enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->where('course_id', $this->course->id)
            ->first();

        if (!$this->enrollment) {
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
        
        // Clear cache when status changes
        Cache::forget("lesson.{$this->lesson->id}.user." . Auth::id());
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
        $allLessons = collect($this->course->modules)
            ->flatMap(fn($module) => $module->lessons->map(fn($l) => (object)['module' => $module, 'lesson' => $l]))
            ->sortBy([['module.order_index', 'asc'], ['lesson.order_index', 'asc']])
            ->values();

        $currentIndex = $allLessons->search(fn($item) => $item->lesson->id === $this->lesson->id);

        if ($currentIndex !== false) {
            if ($currentIndex > 0) {
                $this->previousLesson = $allLessons[$currentIndex - 1]->lesson;
            }
            if ($currentIndex < $allLessons->count() - 1) {
                $this->nextLesson = $allLessons[$currentIndex + 1]->lesson;
            }
        }
    }

    private function markLessonAsStarted()
    {
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

    public function updateVideoProgress($watchedSeconds, $duration, $isCompleted = false)
    {
        if (!$this->lesson->video_url && $this->lesson->lesson_type !== 'video') {
            return;
        }

        $progressPercentage = $duration > 0 ? round(($watchedSeconds / $duration) * 100, 2) : 0;
        
        $videoProgress = VideoProgress::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'lesson_id' => $this->lesson->id,
            ],
            [
                'video_url' => $this->lesson->video_url,
                'duration_seconds' => $duration,
                'watched_seconds' => $watchedSeconds,
                'progress_percentage' => $progressPercentage,
                'last_position_seconds' => $watchedSeconds,
                'is_completed' => $isCompleted || $progressPercentage >= 90, // Mark as complete if 90%+ watched
                'last_watched_at' => now(),
            ]
        );
        
        // Increment watch count
        $videoProgress->increment('watch_count');
        
        // Refresh to get updated watch_count
        $videoProgress->refresh();

        $this->videoProgress = $videoProgress->progress_percentage;
        $this->videoWatchedSeconds = $videoProgress->watched_seconds;
        $this->isVideoCompleted = $videoProgress->is_completed;

        // Auto-complete lesson if video is completed (but only if all requirements are met)
        if ($videoProgress->is_completed && !$this->isLessonCompleted) {
            $this->checkCompletionStatus();
            if ($this->canComplete) {
                $this->completeLesson();
            }
        }

        $this->dispatch('video-progress-updated');
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
        if ($this->isLessonCompleted) {
            return;
        }
        
        // Check completion requirements using the service
        $this->checkCompletionStatus();
        
        if (!$this->canComplete) {
            $service = app(LessonCompletionService::class);
            $summary = $service->getCompletionSummary($this->lesson, Auth::user());
            
            // Provide detailed error messages
            $errors = [];
            foreach ($this->completionStatus['missing'] as $missing) {
                if ($missing['type'] === 'video') {
                    $remaining = 100 - ($missing['progress'] ?? 0);
                    $errors[] = "Complete watching the video ({$remaining}% remaining)";
                } elseif ($missing['type'] === 'assessment') {
                    $errors[] = "Complete assessment: {$missing['title']}";
                } elseif ($missing['type'] === 'assignment') {
                    $errors[] = "Complete assignment: {$missing['title']}";
                }
            }
            
            session()->flash('error', 'Cannot complete lesson. ' . implode(', ', $errors));
            return;
        }

        // If it's a video lesson, ensure video is watched (double-check)
        if (($this->lesson->video_url || $this->lesson->lesson_type === 'video') && !$this->isVideoCompleted) {
            $progress = VideoProgress::where('user_id', Auth::id())
                ->where('lesson_id', $this->lesson->id)
                ->first();
            
            $remaining = $progress ? (100 - ($progress->progress_percentage ?? 0)) : 100;
            $message = 'Please complete watching the video first.';
            
            if ($this->lesson->video_duration) {
                $remainingMinutes = round(($remaining / 100) * $this->lesson->video_duration);
                $message .= " Approximately {$remainingMinutes} minutes remaining.";
            } else {
                $message .= " {$remaining}% remaining.";
            }
            
            session()->flash('error', $message);
            return;
        }
        
        // Wrap everything in a transaction for data integrity
        DB::transaction(function () {
            $this->executeCompletion();
        });
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

        // Create user progress entry
        UserProgress::create([
            'user_id' => Auth::id(),
            'course_id' => $this->course->id,
            'lesson_id' => $this->lesson->id,
            'type' => 'lesson_completed',
            'points_earned' => $points,
            'completed_at' => now(),
            'time_spent' => $timeSpent,
        ]);

        // Ensure UserPoints exists
        $user = Auth::user();
        if (!$user->points) {
            \App\Models\UserPoint::create([
                'user_id' => $user->id,
                'total_points' => 0,
                'level' => 1,
                'points_to_next_level' => 100,
            ]);
            $user->refresh();
        }

        // Award points
        $user->points->increment('total_points', $points);

        // Update course enrollment progress
        $this->updateCourseProgress();

        // Check and award badges for lesson completion
        $badgeService = app(BadgeAwardingService::class);
        $badgeService->checkLessonCompletionBadges(Auth::user());
        $badgeService->checkLevelBadges(Auth::user());
        $badgeService->checkPointMilestoneBadges(Auth::user());

        // Clear cache after completion
        Cache::forget("lesson.{$this->lesson->id}.user." . Auth::id());

        // Refresh lesson completion status
        $this->checkLessonCompletion();
        $this->isLessonCompleted = true;
        $this->canComplete = true;
        
        session()->flash('message', 'Lesson completed! Great job! 🎉');
        
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
        // Optimize: Get all lesson IDs at once
        $allLessonIds = collect($this->course->modules)
            ->flatMap(fn($module) => $module->lessons)
            ->pluck('id')
            ->toArray();

        if (empty($allLessonIds)) {
            return;
        }

        $totalLessons = count($allLessonIds);

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

        // Update enrollment
        $this->enrollment->update([
            'progress_percentage' => $progressPercentage,
            'lessons_completed' => $completedLessons,
        ]);

        // Check if course is completed
        if ($completedLessons >= $totalLessons && !$this->enrollment->completed_at) {
            $this->enrollment->update(['completed_at' => now()]);
            
            // Ensure UserPoints exists
            $user = Auth::user();
            if (!$user->points) {
                \App\Models\UserPoint::create([
                    'user_id' => $user->id,
                    'total_points' => 0,
                    'level' => 1,
                    'points_to_next_level' => 100,
                ]);
                $user->refresh();
            }

            // Award completion points
            $user->points->increment('total_points', 100);

            // Create user progress entry
            UserProgress::create([
                'user_id' => Auth::id(),
                'course_id' => $this->course->id,
                'type' => 'course_completed',
                'points_earned' => 100,
                'completed_at' => now(),
            ]);

            // Check and award badges for course completion
            $badgeService = app(BadgeAwardingService::class);
            $badgeService->checkCourseCompletionBadges(Auth::user());
            $badgeService->checkLevelBadges(Auth::user());
            $badgeService->checkPointMilestoneBadges(Auth::user());

            // Auto-generate certificate
            $this->generateCertificate();
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

        // Generate certificate
        \App\Models\Certificate::create([
            'user_id' => Auth::id(),
            'course_id' => $this->course->id,
            'certificate_number' => 'CERT-' . strtoupper(substr(md5(time() . $this->course->id . Auth::id()), 0, 8)) . '-' . date('Y'),
            'title' => 'Certificate of Completion - ' . $this->course->title,
            'description' => 'This certifies that ' . Auth::user()->name . ' has successfully completed the course "' . $this->course->title . '".',
            'issued_at' => now(),
            'expires_at' => null,
            'is_verified' => true,
            'completion_data' => [
                'progress_percentage' => $this->enrollment->progress_percentage,
                'completion_date' => $this->enrollment->completed_at?->format('Y-m-d'),
                'lessons_completed' => $this->enrollment->lessons_completed ?? 0,
                'instructor' => $this->course->instructor->name ?? 'System',
            ],
        ]);

        // Check certificate badge
        $badge = \App\Models\Badge::where('slug', 'course-master')->first();
        if ($badge && !Auth::user()->badges()->where('badge_id', $badge->id)->exists()) {
            Auth::user()->badges()->attach($badge->id, ['earned_at' => now()]);
            
            if (Auth::user()->points) {
                Auth::user()->points->increment('total_points', $badge->points_reward ?? 200);
            }
        }
    }

    public function render()
    {
        return view('livewire.lessons.view');
    }
}

