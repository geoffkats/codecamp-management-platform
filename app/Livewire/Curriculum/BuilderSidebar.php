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
    public $course = null;
    public $courses = null;
    public $canManageCourse = false;
    public $selectedType = null;
    public $selectedId = null;
    public $selectedModuleId = null;
    public $structureTab = 'active';

    protected $listeners = [
        'course-structure-updated' => 'refreshCourse',
        'lesson-saved' => 'refreshCourse',
    ];

    public function mount($courseId = null, $canManageCourse = false, $selectedType = null, $selectedId = null, $selectedModuleId = null)
    {
        $this->courseId = $courseId ? (int) $courseId : null;
        $this->canManageCourse = (bool) $canManageCourse;
        $this->selectedType = $selectedType;
        $this->selectedId = $selectedId;
        $this->selectedModuleId = $selectedModuleId;

        $this->reloadStructure();
    }

    public function hydrate(): void
    {
        $this->reloadStructure();
    }

    public function dehydrate(): void
    {
        $this->course = null;
        $this->courses = null;
    }

    public function refreshCourse(): void
    {
        $this->reloadStructure();
    }

    protected function reloadStructure(): void
    {
        if ($this->courseId) {
            $this->course = Course::withTrashed()->with([
                'modules' => function ($q) {
                    $q->withTrashed()
                        ->orderBy('order_index')
                        ->select('id', 'course_id', 'title', 'order_index', 'deleted_at');
                },
                'modules.lessons' => function ($q) {
                    $q->withTrashed()
                        ->orderBy('order_index')
                        ->select('id', 'module_id', 'title', 'order_index', 'lesson_type', 'approval_status', 'is_locked', 'deleted_at');
                },
            ])->find($this->courseId);
            $this->courses = collect();

            return;
        }

        $this->course = null;
        $this->loadCourseList();
    }

    protected function loadCourseList(): void
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $isSupervisor = $user->isSupervisor();

        $this->courses = Course::withCount('modules')
            ->where(function ($query) use ($isAdmin, $isSupervisor) {
                if (! $isAdmin && ! $isSupervisor) {
                    $query->where('instructor_id', Auth::id())
                        ->orWhereHas('collaborators', function ($q) {
                            $q->where('user_id', Auth::id());
                        });
                }
            })
            ->where('approval_status', '!=', 'deleted')
            ->orderBy('title')
            ->select('id', 'title', 'instructor_id')
            ->get();
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
        $this->reloadStructure();

        return view('livewire.curriculum.builder-sidebar', [
            'course' => $this->course,
            'courses' => $this->courses ?? collect(),
        ]);
    }
}
