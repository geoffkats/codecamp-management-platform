<?php

namespace App\Livewire\Lessons;

use App\Models\Course;
use App\Models\Lesson;
use App\Services\LessonLockService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LessonLocks extends Component
{
    #[Url(as: 'course')]
    public ?int $courseId = null;

    public string $message = '';

    public function mount(): void
    {
        $user = Auth::user();

        if (! $this->canManageLocks($user)) {
            abort(403);
        }

        $courses = $this->coursesForUser();

        if (! $this->courseId || ! $courses->contains('id', $this->courseId)) {
            $this->courseId = $courses->first()?->id;
        }
    }

    public function updatedCourseId(): void
    {
        $this->message = '';
        $this->assertCourseAccess();
    }

    public function toggleLesson(int $lessonId): void
    {
        $lesson = $this->findAccessibleLesson($lessonId);
        $locked = ! $lesson->is_locked;

        app(LessonLockService::class)->setLessonLocked($lesson, $locked);

        $this->message = $locked
            ? "Locked “{$lesson->title}” — students cannot open this lesson or its quizzes until you unlock it."
            : "Unlocked “{$lesson->title}” — students can now do this lesson and its quizzes.";
    }

    public function unlockOnly(int $lessonId): void
    {
        $lesson = $this->findAccessibleLesson($lessonId);
        $course = $this->assertCourseAccess();

        app(LessonLockService::class)->unlockOnlyLesson($course, $lesson);

        $this->message = "Only “{$lesson->title}” is open. Everything else on this course is locked.";
    }

    public function lockAll(): void
    {
        $course = $this->assertCourseAccess();
        $count = app(LessonLockService::class)->setCourseLocked($course, true);
        $this->message = "Locked all {$count} lessons. Students cannot rush ahead.";
    }

    public function unlockAll(): void
    {
        $course = $this->assertCourseAccess();
        $count = app(LessonLockService::class)->setCourseLocked($course, false);
        $this->message = "Unlocked all {$count} lessons.";
    }

    public function render()
    {
        $courses = $this->coursesForUser();
        $course = $this->courseId
            ? $courses->firstWhere('id', $this->courseId)
            : null;

        $modules = collect();
        $lockedCount = 0;
        $unlockedCount = 0;

        if ($course) {
            $modules = $course->modules()
                ->with(['lessons' => function ($query) {
                    $query->orderBy('order_index')
                        ->orderBy('order')
                        ->withCount('assessments');
                }])
                ->orderBy('order_index')
                ->get();

            $lessons = $modules->pluck('lessons')->flatten();
            $lockedCount = $lessons->where('is_locked', true)->count();
            $unlockedCount = $lessons->where('is_locked', false)->count();
        }

        return view('livewire.lessons.lesson-locks', [
            'courses' => $courses,
            'course' => $course,
            'modules' => $modules,
            'lockedCount' => $lockedCount,
            'unlockedCount' => $unlockedCount,
        ]);
    }

    private function canManageLocks($user): bool
    {
        return $user->isAdmin()
            || $user->isSupervisor()
            || $user->isTeacher()
            || $user->hasCodeClubAccess();
    }

    private function coursesForUser()
    {
        $user = Auth::user();
        $query = Course::query()->orderBy('title');

        if (! $user->isAdmin() && ! $user->isSupervisor()) {
            $query->accessibleBy($user);
        }

        return $query->get(['id', 'title']);
    }

    private function assertCourseAccess(): Course
    {
        $course = Course::query()->find($this->courseId);

        if (! $course || ! $this->coursesForUser()->contains('id', $course->id)) {
            abort(403, 'You cannot manage locks for this course.');
        }

        return $course;
    }

    private function findAccessibleLesson(int $lessonId): Lesson
    {
        $course = $this->assertCourseAccess();

        $lesson = Lesson::query()
            ->where('id', $lessonId)
            ->where(function ($query) use ($course) {
                $query->where('course_id', $course->id)
                    ->orWhereHas('module', fn ($module) => $module->where('course_id', $course->id));
            })
            ->first();

        if (! $lesson) {
            abort(404, 'Lesson not found on this course.');
        }

        return $lesson;
    }
}
