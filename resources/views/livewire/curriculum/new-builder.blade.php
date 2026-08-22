<div id="curriculum-builder-shell" class="curriculum-builder-shell relative flex overflow-hidden bg-gray-50 dark:bg-gray-950" style="height: 100dvh; max-height: 100dvh;">
    <aside id="curriculum-outline" class="relative z-20 flex h-full w-72 shrink-0 flex-col overflow-hidden border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        @livewire('curriculum.builder-sidebar', [
            'courseId' => $courseId,
            'canManageCourse' => $canManageCourse,
            'selectedType' => $selectedType,
            'selectedId' => $selectedId,
            'selectedModuleId' => $this->selectedModuleId,
        ], key('builder-sidebar-' . ($courseId ?? 'none')))
    </aside>

    @include('livewire.curriculum.new-builder.partials.sidebar-toggle')

    <div class="relative min-h-0 min-w-0 flex-1 overflow-y-auto">
        @include('livewire.curriculum.new-builder.partials.flash-messages')

        @if($courseId && $course)
            @include('livewire.curriculum.new-builder.partials.main-header')
        @endif

        @switch($viewState)
            @case('welcome')
                @include('livewire.curriculum.new-builder.partials.welcome')
                @break

            @case('lesson-form')
                @livewire('curriculum.forms.lesson-form', ['courseId' => $courseId, 'lessonId' => $selectedId, 'moduleId' => $formData['module_id'] ?? null], key('lesson-form-' . ($selectedId ?? 'new')))
                @break

            @case('module-form')
                @include('livewire.curriculum.new-builder.partials.forms.module')
                @break

            @case('assessment-form')
                @if($selectedAssessment)
                    @livewire('assessments.edit', ['assessment' => $selectedAssessment, 'embedded' => true], key('assessment-edit-' . $selectedAssessment->id))
                @else
                    @livewire('assessments.create', ['courseId' => $courseId, 'lessonId' => $lessonId, 'embedded' => true], key('assessment-create-' . ($courseId ?? 'none') . '-' . ($lessonId ?? 'none')))
                @endif
                @break

            @case('other-form')
                @include('livewire.curriculum.new-builder.partials.forms.other')
                @break

            @case('debug-form')
                @include('livewire.curriculum.new-builder.partials.forms.debug')
                @break

            @case('build-empty')
                @include('livewire.curriculum.new-builder.partials.build.empty-state')
                @break

            @case('not-found')
                @include('livewire.curriculum.new-builder.partials.course-not-found')
                @break

            @default
                <div class="p-8 text-center text-red-500">
                    Invalid state: {{ $viewState }}
                </div>
        @endswitch
    </div>
</div>

<style>
    #curriculum-builder-shell.cb-collapsed #curriculum-outline {
        width: 0;
        border-right-width: 0;
    }
    #curriculum-builder-shell.cb-collapsed #curriculum-outline-toggle {
        left: 0;
    }
    #curriculum-outline {
        transition: width 180ms ease-out, border-width 180ms ease-out;
    }
    #curriculum-outline-toggle {
        transition: left 180ms ease-out;
    }
</style>
