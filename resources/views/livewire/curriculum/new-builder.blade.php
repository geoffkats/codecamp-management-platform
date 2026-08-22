<div class="curriculum-builder-shell relative flex overflow-hidden bg-gray-50 dark:bg-gray-950"
     style="height: 100dvh; max-height: 100dvh;">
    {{-- Course tree stays mounted when a lesson opens. Width is Livewire-driven so Alpine cannot leave an empty w-72 gap. --}}
    <aside class="relative z-20 flex h-full shrink-0 flex-col overflow-hidden border-r border-gray-200 bg-white transition-[width] duration-200 ease-out dark:border-gray-800 dark:bg-gray-900 {{ $sidebarCollapsed ? 'w-0 border-r-0' : 'w-72' }}">
        <div class="flex h-full w-72 flex-col">
            @livewire('curriculum.builder-sidebar', [
                'courseId' => $courseId,
                'course' => $course,
                'courses' => $courses,
                'canManageCourse' => $canManageCourse,
                'selectedType' => $selectedType,
                'selectedId' => $selectedId,
                'selectedModuleId' => $this->selectedModuleId,
                'sidebarCollapsed' => $sidebarCollapsed,
            ], key('builder-sidebar-' . ($courseId ?? 'none')))
        </div>
    </aside>

    @include('livewire.curriculum.new-builder.partials.sidebar-toggle')

    <div class="relative min-h-0 min-w-0 flex-1 overflow-y-auto">
        @include('livewire.curriculum.new-builder.partials.loading-overlay')

        @include('livewire.curriculum.new-builder.partials.flash-messages')

        @if($courseId && $course)
            @include('livewire.curriculum.new-builder.partials.main-header')
        @endif

        {{-- State-driven view rendering - clean and scalable --}}
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
                {{-- Fallback: should never reach here --}}
                <div class="p-8 text-center text-red-500">
                    Invalid state: {{ $viewState }}
                </div>
        @endswitch
    </div>
</div>

@script
<script>
    const onKey = (e) => {
        if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B') && !e.repeat) {
            const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
            if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target?.isContentEditable) {
                return;
            }
            e.preventDefault();
            $wire.toggleSidebar();
        }
    };
    window.addEventListener('keydown', onKey);
    cleanup(() => window.removeEventListener('keydown', onKey));
</script>
@endscript
