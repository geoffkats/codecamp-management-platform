<?php

namespace App\Livewire\Curriculum;

use App\Livewire\Curriculum\Concerns\ComputesBuilderStructure;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BuilderSidebar extends Component
{
    use ComputesBuilderStructure;

    public $courseId;
    public $course;
    public $courses;
    public $canManageCourse = false;
    public $selectedType = null;
    public $selectedId = null;
    public $selectedModuleId = null;
    public $sidebarCollapsed = false;
    public $structureTab = 'active';

    protected $listeners = [
        'course-structure-updated' => 'refreshCourse',
        'lesson-saved' => 'refreshCourse',
    ];

    public function mount($courseId = null, $course = null, $courses = null, $canManageCourse = false, $selectedType = null, $selectedId = null, $selectedModuleId = null, $sidebarCollapsed = false)
    {
        $this->courseId = $courseId;
        $this->course = $course;
        $this->courses = $courses;
        $this->canManageCourse = $canManageCourse;
        $this->selectedType = $selectedType;
        $this->selectedId = $selectedId;
        $this->selectedModuleId = $selectedModuleId;
        $this->sidebarCollapsed = $sidebarCollapsed;

        if (!$this->course && $this->courseId) {
            $this->refreshCourse();
        }
    }

    public function refreshCourse(): void
    {
        if (!$this->courseId) {
            return;
        }

        $this->course = Course::withTrashed()->with([
            'modules' => function ($q) {
                $q->withTrashed()->orderBy('order_index');
            },
            'modules.lessons' => function ($q) {
                $q->withTrashed()->orderBy('order_index');
            },
        ])->find($this->courseId);
    }

    public function setStructureTab(string $tab): void
    {
        if (!in_array($tab, ['active', 'archived'], true)) {
            return;
        }

        $this->structureTab = $tab;
    }

    public function selectItem($type, $id = null, $parentId = null): void
    {
        $this->selectedType = $type;
        $this->selectedId = $id;
        if ($type === 'module') {
            $this->selectedModuleId = $id ? (int) $id : null;
        } elseif ($type === 'lesson') {
            $this->selectedModuleId = $parentId ? (int) $parentId : $this->selectedModuleId;
        }
        $this->dispatch('select-item', type: $type, id: $id, parentId: $parentId)->to(NewBuilder::class);
    }

    public function reorderLessons(int $moduleId, array $orderedIds): void
    {
        $this->assertCanManage();

        $module = CourseModule::where('course_id', $this->courseId)->findOrFail($moduleId);
        $validIds = Lesson::where('module_id', $module->id)
            ->where('course_id', $this->courseId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $orderedIds = array_values(array_unique(array_map('intval', $orderedIds)));
        $orderedIds = array_values(array_filter($orderedIds, fn ($id) => in_array($id, $validIds, true)));

        if ($orderedIds === [] || count($orderedIds) !== count($validIds)) {
            return;
        }

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $lessonId) {
                Lesson::where('id', $lessonId)->update(['order_index' => $index + 1]);
            }
        });

        $this->refreshCourse();
        $this->dispatch('structure-reordered')->to(NewBuilder::class);
    }

    public function reorderModules(array $orderedIds): void
    {
        $this->assertCanManage();

        $validIds = CourseModule::where('course_id', $this->courseId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $orderedIds = array_values(array_unique(array_map('intval', $orderedIds)));
        $orderedIds = array_values(array_filter($orderedIds, fn ($id) => in_array($id, $validIds, true)));

        if ($orderedIds === [] || count($orderedIds) !== count($validIds)) {
            return;
        }

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $moduleId) {
                CourseModule::where('id', $moduleId)->update(['order_index' => $index + 1]);
            }
        });

        $this->refreshCourse();
        $this->dispatch('structure-reordered')->to(NewBuilder::class);
    }

    public function deleteModule(int $moduleId): void
    {
        $this->dispatch('archive-module', moduleId: $moduleId)->to(NewBuilder::class);
    }

    public function deleteLesson(int $lessonId): void
    {
        $this->dispatch('archive-lesson', lessonId: $lessonId)->to(NewBuilder::class);
    }

    public function restoreModule(int $moduleId): void
    {
        $this->dispatch('restore-module', moduleId: $moduleId)->to(NewBuilder::class);
    }

    public function restoreLesson(int $lessonId): void
    {
        $this->dispatch('restore-lesson', lessonId: $lessonId)->to(NewBuilder::class);
    }

    public function getCurrentUserIdProperty(): ?int
    {
        return Auth::id();
    }

    protected function assertCanManage(): void
    {
        if (! $this->canManageCourse || ! $this->courseId) {
            abort(403, 'You cannot reorder this course.');
        }
    }

    public function render()
    {
        return view('livewire.curriculum.builder-sidebar');
    }
}
