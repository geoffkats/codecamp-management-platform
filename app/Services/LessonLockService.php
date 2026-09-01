<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Lesson;

class LessonLockService
{
    public function setLessonLocked(Lesson $lesson, bool $locked): void
    {
        $lesson->is_locked = $locked;
        $lesson->save();

        Assessment::query()
            ->where('lesson_id', $lesson->id)
            ->update(['is_locked' => $locked]);

        Assignment::query()
            ->where('lesson_id', $lesson->id)
            ->update(['is_locked' => $locked]);
    }

    public function setCourseLocked(Course $course, bool $locked): int
    {
        $lessons = $this->lessonsForCourse($course);

        foreach ($lessons as $lesson) {
            $this->setLessonLocked($lesson, $locked);
        }

        return $lessons->count();
    }

    /**
     * Unlock one lesson (and its quizzes/assignments) and lock every other lesson on the course.
     */
    public function unlockOnlyLesson(Course $course, Lesson $lesson): void
    {
        foreach ($this->lessonsForCourse($course) as $item) {
            $this->setLessonLocked($item, $item->id !== $lesson->id);
        }
    }

    private function lessonsForCourse(Course $course)
    {
        return Lesson::query()
            ->where(function ($query) use ($course) {
                $query->where('course_id', $course->id)
                    ->orWhereHas('module', fn ($module) => $module->where('course_id', $course->id));
            })
            ->get();
    }
}
