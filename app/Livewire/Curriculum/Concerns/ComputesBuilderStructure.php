<?php

namespace App\Livewire\Curriculum\Concerns;

trait ComputesBuilderStructure
{
    protected function isTrashedModel($model): bool
    {
        return method_exists($model, 'trashed') ? $model->trashed() : false;
    }

    public function getActiveModulesProperty()
    {
        if (!$this->course) {
            return collect();
        }

        return $this->course->modules->filter(fn($module) => !$this->isTrashedModel($module));
    }

    public function getActiveModulesCountProperty(): int
    {
        return $this->activeModules->count();
    }

    public function getActiveLessonsByModuleProperty(): array
    {
        if (!$this->course) {
            return [];
        }

        $map = [];
        foreach ($this->course->modules as $module) {
            $map[$module->id] = $module->lessons->filter(fn($lesson) => !$this->isTrashedModel($lesson));
        }

        return $map;
    }

    public function getActiveLessonCountsProperty(): array
    {
        $counts = [];
        foreach ($this->activeLessonsByModule as $moduleId => $lessons) {
            $counts[$moduleId] = $lessons->count();
        }

        return $counts;
    }

    public function getArchivedModulesProperty()
    {
        if (!$this->course) {
            return collect();
        }

        return $this->course->modules->filter(fn($module) => $this->isTrashedModel($module));
    }

    public function getArchivedLessonCountProperty(): int
    {
        if (!$this->course) {
            return 0;
        }

        return $this->course->modules
            ->flatMap(fn($module) => $module->lessons)
            ->filter(fn($lesson) => $this->isTrashedModel($lesson))
            ->count();
    }

    public function getArchivedTotalProperty(): int
    {
        return $this->archivedModules->count() + $this->archivedLessonCount;
    }

    public function getArchivedLessonsByModuleProperty(): array
    {
        if (!$this->course) {
            return [];
        }

        $map = [];
        foreach ($this->course->modules as $module) {
            $map[$module->id] = $module->lessons->filter(fn($lesson) => $this->isTrashedModel($lesson));
        }

        return $map;
    }

    public function getArchivedStructureModulesProperty()
    {
        if (!$this->course) {
            return collect();
        }

        return $this->course->modules->filter(function ($module) {
            if ($this->isTrashedModel($module)) {
                return true;
            }

            $archivedLessons = $this->archivedLessonsByModule[$module->id] ?? collect();
            return $archivedLessons->isNotEmpty();
        });
    }

    public function getArchivedModuleFlagsProperty(): array
    {
        $flags = [];
        if (!$this->course) {
            return $flags;
        }

        foreach ($this->course->modules as $module) {
            $flags[$module->id] = $this->isTrashedModel($module);
        }

        return $flags;
    }
}
