<?php

namespace App\Livewire\Curriculum\Concerns;

use Illuminate\Support\Facades\Auth;

trait ComputesBuilderStatus
{
    public function getCurrentUserIdProperty(): ?int
    {
        return Auth::id();
    }

    public function getIsApproverProperty(): bool
    {
        return $this->isAdmin || $this->isSupervisor;
    }

    public function getCanManageCollaboratorsProperty(): bool
    {
        if (!$this->course) {
            return false;
        }

        $user = Auth::user();

        return $this->isAdmin
            || $this->isSupervisor
            || $this->course->instructor_id === $user->id
            || (method_exists($user, 'hasPermission') && $user->hasPermission('edit_courses'));
    }

    public function getCourseRestoreByProperty()
    {
        return $this->course?->deleted_at?->copy()->addDays($this->restoreWindowDays);
    }

    public function getCourseStatsProperty(): array
    {
        if (!$this->course) {
            return [
                'modules' => 0,
                'lessons' => 0,
                'assessments' => 0,
            ];
        }

        $lessonCount = $this->course->modules->sum(fn($module) => $module->lessons->count());
        $assessmentCount = $this->course->modules
            ->flatMap(fn($module) => $module->lessons)
            ->flatMap(fn($lesson) => $lesson->assessments)
            ->count();

        return [
            'modules' => $this->course->modules->count(),
            'lessons' => $lessonCount,
            'assessments' => $assessmentCount,
        ];
    }

    public function getContentLockStatsProperty(): array
    {
        if (!$this->course) {
            return [
                'locked_lessons' => 0,
                'unlocked_lessons' => 0,
                'locked_assessments' => 0,
                'unlocked_assessments' => 0,
            ];
        }

        $allLessons = $this->course->modules->flatMap(fn($module) => $module->lessons);
        $lockedLessons = $allLessons->where('is_locked', true)->count();
        $unlockedLessons = $allLessons->where('is_locked', false)->count();

        $allAssessments = $allLessons->flatMap(fn($lesson) => $lesson->assessments);
        $lockedAssessments = $allAssessments->where('is_locked', true)->count();
        $unlockedAssessments = $allAssessments->where('is_locked', false)->count();

        return [
            'locked_lessons' => $lockedLessons,
            'unlocked_lessons' => $unlockedLessons,
            'locked_assessments' => $lockedAssessments,
            'unlocked_assessments' => $unlockedAssessments,
        ];
    }

    public function getLessonStatusColorsProperty(): array
    {
        return [
            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        ];
    }

    public function getLessonApprovalStatusProperty(): string
    {
        return $this->formData['approval_status'] ?? 'draft';
    }

    public function getLessonRequiresReapprovalProperty(): bool
    {
        return (bool) $this->selectedId
            && $this->lessonApprovalStatus === 'approved'
            && !$this->isApprover;
    }

    public function getSelectedModuleIdProperty(): ?int
    {
        if ($this->selectedType === 'module') {
            return $this->selectedId ? (int) $this->selectedId : null;
        }

        if ($this->selectedType === 'lesson' && $this->lesson) {
            return $this->lesson->module_id ? (int) $this->lesson->module_id : null;
        }

        return null;
    }

    public function getSelectedLessonAssessmentsProperty()
    {
        if (!$this->lesson) {
            return collect();
        }

        return $this->lesson->assessments ?? collect();
    }
}
