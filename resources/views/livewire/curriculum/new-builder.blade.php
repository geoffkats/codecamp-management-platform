<div class="flex h-screen bg-gray-50 dark:bg-gray-950"
     x-data="{ collapsed: @entangle('sidebarCollapsed') }"
     x-init="
         document.addEventListener('keydown', (e) => {
             if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                 e.preventDefault();
                 $wire.toggleSidebar();
             }
         }, { passive: false });
     ">
    <div class="shrink-0 overflow-hidden transition-all duration-300"
         :class="collapsed ? 'w-0' : 'w-72'">
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

    @include('livewire.curriculum.new-builder.partials.sidebar-toggle')

    <div class="flex-1 overflow-y-auto relative" wire:key="content-{{ $viewState }}-{{ $selectedId }}">
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
