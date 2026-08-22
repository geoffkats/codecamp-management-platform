<?php

namespace App\Livewire\Curriculum\Concerns;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait HandlesBuilderArchiving
{
    public function deleteLesson($lessonId = null)
    {
        $lessonId = $this->normalizeBuilderId($lessonId);

        $lesson = $lessonId
            ? Lesson::where('course_id', $this->courseId)->find($lessonId)
            : null;

        if (!$lesson) {
            session()->flash('error', 'Lesson not found.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to remove this lesson.');
            return;
        }

        DB::transaction(function () use ($lesson) {
            $this->archiveLessonWithChildren($lesson);
        });

        session()->flash('message', 'Lesson removed. You can restore it within the restore window.');
        $this->closeForm();
        $this->loadCourse();
        $this->dispatch('course-structure-updated');
    }

    public function restoreLesson($lessonId = null)
    {
        $lessonId = $this->normalizeBuilderId($lessonId);
        $lesson = $lessonId
            ? Lesson::withTrashed()->where('course_id', $this->courseId)->find($lessonId)
            : null;

        if (!$lesson || !$lesson->trashed()) {
            session()->flash('error', 'This lesson is not in the recycle window.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to restore this lesson.');
            return;
        }

        $module = CourseModule::withTrashed()->find($lesson->module_id);
        if ($module && $module->trashed()) {
            session()->flash('error', 'Restore the module first, then restore this lesson.');
            return;
        }

        try {
            DB::transaction(function () use ($lesson) {
                $this->restoreLessonWithChildren($lesson);
            });

            session()->flash('message', 'Lesson restored.');
            $this->loadCourse();
            $this->dispatch('course-structure-updated');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteAssessment($assessmentId)
    {
        $assessment = Assessment::with('lesson')->find($assessmentId);

        if (!$assessment || !$assessment->lesson || (int) $assessment->lesson->course_id !== (int) $this->courseId) {
            session()->flash('error', 'Quiz not found for this course.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to remove this quiz.');
            return;
        }

        $assessment->delete();

        session()->flash('message', 'Quiz archived. You can restore it within the restore window.');
        $this->loadCourse();
    }

    public function deleteCourse()
    {
        if (!$this->course) {
            session()->flash('error', 'Course not found.');
            return;
        }

        if (!$this->userCanManageCourse(true)) {
            session()->flash('error', 'You do not have permission to delete this course.');
            return;
        }

        DB::transaction(function () {
            $this->archiveCourseWithChildren($this->course);
        });

        session()->flash('message', 'Course archived. You can restore it within the restore window.');
        $this->loadCourse();
    }

    public function restoreCourse()
    {
        $course = Course::withTrashed()->find($this->courseId);

        if (!$course || !$course->trashed()) {
            session()->flash('error', 'Course is not archived.');
            return;
        }

        if (!$this->userCanManageCourse(true)) {
            session()->flash('error', 'You do not have permission to restore this course.');
            return;
        }

        try {
            DB::transaction(function () use ($course) {
                $this->restoreCourseWithChildren($course);
            });

            $this->course = $course;
            session()->flash('message', 'Course restored.');
            $this->loadCourse();
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteModule($moduleId = null)
    {
        $moduleId = $this->normalizeBuilderId($moduleId);

        $module = $moduleId
            ? CourseModule::withTrashed()
                ->where('course_id', $this->courseId)
                ->with(['lessons' => fn ($q) => $q->withTrashed()])
                ->find($moduleId)
            : null;

        if (!$module) {
            session()->flash('error', 'Module not found for this course.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to remove this module.');
            return;
        }

        DB::transaction(function () use ($module) {
            $this->archiveModuleWithChildren($module);
        });

        session()->flash('message', 'Module archived. You can restore it within the restore window.');
        $this->closeForm();
        $this->loadCourse();
        $this->dispatch('course-structure-updated');
    }

    public function restoreModule($moduleId = null)
    {
        $moduleId = $this->normalizeBuilderId($moduleId);

        $module = $moduleId
            ? CourseModule::withTrashed()
                ->where('course_id', $this->courseId)
                ->find($moduleId)
            : null;

        if (!$module || !$module->trashed()) {
            session()->flash('error', 'Module is not archived.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to restore this module.');
            return;
        }

        try {
            DB::transaction(function () use ($module) {
                $this->restoreModuleWithChildren($module);
            });

            session()->flash('message', 'Module restored.');
            $this->loadCourse();
            $this->dispatch('course-structure-updated');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function forceDeleteModule($moduleId = null)
    {
        $moduleId = $this->normalizeBuilderId($moduleId);

        $module = $moduleId
            ? CourseModule::withTrashed()
                ->where('course_id', $this->courseId)
                ->find($moduleId)
            : null;

        if (!$module) {
            session()->flash('error', 'Module not found for this course.');
            return;
        }

        if (!$this->userCanManageCourse()) {
            session()->flash('error', 'You do not have permission to remove this module.');
            return;
        }

        $lessonCount = Lesson::withTrashed()->where('module_id', $module->id)->count();

        if ($lessonCount > 0) {
            session()->flash('error', 'Module has lessons. Archive it instead.');
            return;
        }

        DB::transaction(function () use ($module) {
            $module->forceDelete();
        });

        session()->flash('message', 'Module permanently deleted.');
        $this->loadCourse();
    }

    private function normalizeBuilderId(mixed $id): ?int
    {
        if (is_array($id)) {
            $id = $id['moduleId'] ?? $id['lessonId'] ?? $id['id'] ?? reset($id);
        }

        if ($id === null || $id === '' || !is_numeric($id)) {
            return null;
        }

        return (int) $id;
    }

    private function userCanManageCourse(bool $requireOwner = false): bool
    {
        $user = Auth::user();

        $this->isAdmin = method_exists($user, 'isAdmin') ? $user->isAdmin() : ($this->isAdmin ?? false);
        $this->isSupervisor = method_exists($user, 'isSupervisor') ? $user->isSupervisor() : ($this->isSupervisor ?? false);

        $hasEditPermission = method_exists($user, 'hasPermission')
            ? ($user->hasPermission('edit_courses') || $user->hasPermission('review_content'))
            : false;

        if ($this->isAdmin || $this->isSupervisor || $hasEditPermission) {
            return true;
        }

        if (!$this->course) {
            return false;
        }

        if ($requireOwner) {
            return $this->course->instructor_id === $user->id;
        }

        return $this->course->canUserEdit($user);
    }

    private function archiveCourseWithChildren(Course $course): void
    {
        $course->modules()->withTrashed()->each(function ($module) {
            $this->archiveModuleWithChildren($module);
        });

        $course->delete();
    }

    private function restoreCourseWithChildren(Course $course): void
    {
        if ($course->deleted_at && $course->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
            throw new \RuntimeException('Restore period has expired for this course.');
        }

        $course->modules()->withTrashed()->get()->each(function ($module) {
            $this->restoreModuleWithChildren($module, false);
        });

        $course->restore();
    }

    private function archiveModuleWithChildren(CourseModule $module): void
    {
        $module->lessons()->withTrashed()->each(function ($lesson) {
            $this->archiveLessonWithChildren($lesson);
        });

        $module->delete();
    }

    private function restoreModuleWithChildren(CourseModule $module, bool $enforceWindow = true): void
    {
        if ($enforceWindow && $module->deleted_at && $module->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
            throw new \RuntimeException('Restore period has expired for this module.');
        }

        $module->lessons()->withTrashed()->get()->each(function ($lesson) use ($enforceWindow) {
            $this->restoreLessonWithChildren($lesson, $enforceWindow);
        });

        $module->restore();
    }

    private function archiveLessonWithChildren(Lesson $lesson): void
    {
        Assessment::where('lesson_id', $lesson->id)->withTrashed()->get()->each(function ($assessment) {
            $assessment->delete();
        });

        Assignment::where('lesson_id', $lesson->id)->withTrashed()->get()->each(function ($assignment) {
            $assignment->delete();
        });

        Quiz::where('lesson_id', $lesson->id)->withTrashed()->get()->each(function ($quiz) {
            $quiz->delete();
        });

        $lesson->delete();
    }

    private function restoreLessonWithChildren(Lesson $lesson, bool $enforceWindow = true): void
    {
        if ($enforceWindow && $lesson->deleted_at && $lesson->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
            throw new \RuntimeException('Restore period has expired for this lesson.');
        }

        Assessment::withTrashed()->where('lesson_id', $lesson->id)->get()->each(function ($assessment) use ($enforceWindow) {
            if (!$enforceWindow || !$assessment->deleted_at || !$assessment->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
                $assessment->restore();
            }
        });

        Assignment::withTrashed()->where('lesson_id', $lesson->id)->get()->each(function ($assignment) use ($enforceWindow) {
            if (!$enforceWindow || !$assignment->deleted_at || !$assignment->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
                $assignment->restore();
            }
        });

        Quiz::withTrashed()->where('lesson_id', $lesson->id)->get()->each(function ($quiz) use ($enforceWindow) {
            if (!$enforceWindow || !$quiz->deleted_at || !$quiz->deleted_at->addDays($this->restoreWindowDays)->isPast()) {
                $quiz->restore();
            }
        });

        $lesson->restore();
    }
}
