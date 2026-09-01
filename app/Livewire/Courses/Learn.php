<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\StudentLessonProgress;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Learn extends Component
{
    public Course $course;
    public $enrollment;
    public $selectedLessonId = null;
    public $selectedModuleId = null;

    public function mount(Course $course)
    {
        // Check if user is enrolled
        $this->enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->firstOrFail();

        $this->course = $course->load([
            'instructor',
            'modules' => function ($q) {
                $q->with(['lessons' => function ($query) {
                    $query->orderBy('order_index');
                }])->orderBy('order_index');
            },
            'assessments'
        ]);

        // Load first incomplete lesson if none selected
        if (!$this->selectedLessonId) {
            $firstIncompleteLesson = $this->getFirstIncompleteLesson();
            if ($firstIncompleteLesson) {
                $this->selectedLessonId = $firstIncompleteLesson->id;
            }
        }
    }

    public function selectLesson($lessonId)
    {
        $this->selectedLessonId = $lessonId;
        $lesson = Lesson::find($lessonId);
        
        // Mark lesson as started if not already
        $this->markLessonAsStarted($lessonId);
        
        // Redirect to lesson view
        return $this->redirect(route('lessons.view', $lesson), navigate: true);
    }

    private function getFirstIncompleteLesson()
    {
        foreach ($this->course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                $progress = StudentLessonProgress::where('user_id', Auth::id())
                    ->where('lesson_id', $lesson->id)
                    ->first();
                
                if ($lesson->is_locked) {
                    continue;
                }

                if (!$progress || !$progress->is_completed) {
                    return $lesson;
                }
            }
        }
        return null;
    }

    private function markLessonAsStarted($lessonId)
    {
        StudentLessonProgress::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'lesson_id' => $lessonId,
            ],
            [
                'status' => 'in_progress',
                'started_at' => now(),
            ]
        );

        // Create user progress entry
        \App\Models\UserProgress::create([
            'user_id' => Auth::id(),
            'course_id' => $this->course->id,
            'lesson_id' => $lessonId,
            'type' => 'lesson_started',
        ]);
    }

    public function getLessonProgress($lessonId)
    {
        return StudentLessonProgress::where('user_id', Auth::id())
            ->where('lesson_id', $lessonId)
            ->first();
    }

    public function isLessonCompleted($lessonId)
    {
        // Check both student_lesson_progress and lesson_progress
        $studentProgress = StudentLessonProgress::where('user_id', Auth::id())
            ->where('lesson_id', $lessonId)
            ->first();

        $lessonProgress = \App\Models\LessonProgress::where('user_id', Auth::id())
            ->where('lesson_id', $lessonId)
            ->first();

        return ($studentProgress && $studentProgress->status === 'completed') || 
               ($lessonProgress && $lessonProgress->is_completed);
    }

    public function getCourseProgress()
    {
        $totalLessons = $this->course->modules->sum(fn($module) => $module->lessons->count());
        $completedLessons = collect($this->course->modules)
            ->flatMap(fn($module) => $module->lessons)
            ->filter(fn($lesson) => $this->isLessonCompleted($lesson->id))
            ->count();

        return $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 1) : 0;
    }

    public function render()
    {
        $modules = $this->course->modules;
        $totalLessons = $modules->sum(fn($m) => $m->lessons->count());
        // Calculate completed lessons across all modules
        $completedLessons = 0;
        foreach ($modules as $module) {
            foreach ($module->lessons as $lesson) {
                if ($this->isLessonCompleted($lesson->id)) {
                    $completedLessons++;
                }
            }
        }

        return view('livewire.courses.learn', [
            'modules' => $modules,
            'totalLessons' => $totalLessons,
            'completedLessons' => $completedLessons,
            'courseProgress' => $this->getCourseProgress(),
        ]);
    }
}

