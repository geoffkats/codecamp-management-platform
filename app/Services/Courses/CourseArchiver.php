<?php

namespace App\Services\Courses;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Archives (soft-deletes) courses, modules and lessons together with their quizzes and assignments,
 * and restores them within the restore window.
 */
class CourseArchiver
{
    public function restoreWindowDays(): int
    {
        return (int) config('app.restore_window_days', 30);
    }

    public function restoreDeadline(Course|CourseModule|Lesson $model): ?Carbon
    {
        return $model->deleted_at?->copy()->addDays($this->restoreWindowDays());
    }

    public function archiveCourse(Course $course): void
    {
        DB::transaction(function () use ($course) {
            $course->modules()->withTrashed()->each(fn (CourseModule $module) => $this->archiveModule($module));
            Lesson::where('course_id', $course->id)->whereNull('module_id')->each(fn (Lesson $lesson) => $this->archiveLesson($lesson));
            $course->delete();
        });
    }

    public function restoreCourse(Course $course): void
    {
        if ($this->isExpired($course)) {
            throw new RuntimeException('Restore period has expired for this course.');
        }

        DB::transaction(function () use ($course) {
            $course->modules()->withTrashed()->get()->each(fn (CourseModule $module) => $this->restoreModule($module, false));
            Lesson::withTrashed()->where('course_id', $course->id)->whereNull('module_id')->get()
                ->each(fn (Lesson $lesson) => $this->restoreLesson($lesson, false));
            $course->restore();
        });
    }

    public function archiveModule(CourseModule $module): void
    {
        $module->lessons()->withTrashed()->each(fn (Lesson $lesson) => $this->archiveLesson($lesson));
        $module->delete();
    }

    public function restoreModule(CourseModule $module, bool $enforceWindow = true): void
    {
        if ($enforceWindow && $this->isExpired($module)) {
            throw new RuntimeException('Restore period has expired for this module.');
        }

        $module->lessons()->withTrashed()->get()->each(fn (Lesson $lesson) => $this->restoreLesson($lesson, $enforceWindow));
        $module->restore();
    }

    public function archiveLesson(Lesson $lesson): void
    {
        foreach ([Assessment::class, Assignment::class, Quiz::class] as $model) {
            $model::where('lesson_id', $lesson->id)->get()->each->delete();
        }

        $lesson->delete();
    }

    public function restoreLesson(Lesson $lesson, bool $enforceWindow = true): void
    {
        if ($enforceWindow && $this->isExpired($lesson)) {
            throw new RuntimeException('Restore period has expired for this lesson.');
        }

        foreach ([Assessment::class, Assignment::class, Quiz::class] as $model) {
            $model::onlyTrashed()->where('lesson_id', $lesson->id)->get()->each(function ($item) use ($enforceWindow) {
                if (! $enforceWindow || ! $item->deleted_at->copy()->addDays($this->restoreWindowDays())->isPast()) {
                    $item->restore();
                }
            });
        }

        $lesson->restore();
    }

    private function isExpired(Course|CourseModule|Lesson $model): bool
    {
        return (bool) $this->restoreDeadline($model)?->isPast();
    }
}
