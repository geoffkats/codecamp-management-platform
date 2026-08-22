{{-- Curriculum Builder Sidebar --}}
<style>
    .curriculum-sortable-chosen {
        outline: 2px solid #fdba74;
        outline-offset: 1px;
        border-radius: 0.5rem;
    }
    .dark .curriculum-sortable-chosen {
        outline-color: #c2410c;
    }
</style>
<div class="h-screen bg-white dark:bg-gray-900 flex flex-col overflow-hidden select-none">

    @if(!$courseId)
        {{-- ═══════════════════════════════════════
             COURSE LIST VIEW (no course selected)
             ═══════════════════════════════════════ --}}

        {{-- Header --}}
        <div class="flex items-center gap-2 px-4 pt-5 pb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="w-7 h-7 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <span class="text-sm font-bold text-gray-800 dark:text-white">My Courses</span>
        </div>

        {{-- Course list --}}
        <div class="flex-1 overflow-y-auto p-3 space-y-1">
            @if($courses && $courses->count() > 0)
                <p class="px-3 pb-2 text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                    Select a course to build
                </p>
                @foreach($courses as $courseOption)
                    <a wire:key="course-{{ $courseOption->id }}"
                       href="{{ route('curriculum.builder', ['course' => $courseOption->id]) }}"
                       wire:navigate
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-orange-50 dark:hover:bg-gray-800 group transition-all duration-150 border border-transparent hover:border-orange-200 dark:hover:border-gray-700">
                        <div class="w-9 h-9 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-orange-200 dark:group-hover:bg-orange-900/50 transition-colors">
                            <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm text-gray-900 dark:text-white truncate leading-tight">{{ $courseOption->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $courseOption->modules_count }} {{ $courseOption->modules_count === 1 ? 'module' : 'modules' }}
                            </p>
                        </div>
                        @if($courseOption->instructor_id !== $this->currentUserId)
                            <span class="px-1.5 py-0.5 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-md font-medium flex-shrink-0">
                                Collab
                            </span>
                        @else
                            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-orange-400 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>
                @endforeach
            @else
                <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                    <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No courses yet</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Create a course first, then come back to build lessons.</p>
                    <a href="{{ route('courses.create') }}" wire:navigate
                       class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create a Course
                    </a>
                </div>
            @endif
        </div>

    @else
        {{-- ════════════════════════════════════════
             COURSE STRUCTURE VIEW (course selected)
             ════════════════════════════════════════ --}}

        {{-- Back nav --}}
        <div class="flex items-center px-3 py-3 border-b border-gray-100 dark:border-gray-800">
            <a href="{{ route('curriculum.builder') }}" wire:navigate
               class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors group">
                <svg class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
                All Courses
            </a>
        </div>

        @if($course)
            {{-- Course name + Add Module --}}
            <div class="px-3 py-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-900/60">
                <p class="text-sm font-bold text-gray-900 dark:text-white leading-snug line-clamp-2 mb-2.5">{{ $course->title }}</p>
                @if($canManageCourse)
                    <button wire:click="selectItem('module')"
                            wire:loading.attr="disabled"
                            type="button"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-700 dark:hover:bg-gray-100 active:scale-95 transition-all duration-150 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Module
                    </button>
                @endif

                <div class="mt-2.5 grid grid-cols-2 gap-1 p-0.5 bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <button type="button"
                            wire:click="setStructureTab('active')"
                            class="px-2 py-1.5 text-xs font-semibold rounded-md transition-colors {{ $structureTab === 'active' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                        Active
                    </button>
                    <button type="button"
                            wire:click="setStructureTab('archived')"
                            class="px-2 py-1.5 text-xs font-semibold rounded-md transition-colors {{ $structureTab === 'archived' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                        Archived
                        @if($this->archivedTotal > 0)
                            <span class="ml-0.5 inline-flex min-w-[1.1rem] justify-center px-1 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300">{{ $this->archivedTotal }}</span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Module tree --}}
            <div class="flex-1 overflow-y-auto py-2" style="scrollbar-width: thin;">

                @if($structureTab === 'archived')
                    @if($this->archivedStructureModules->isEmpty())
                        <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">No archived items</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Deleted modules and lessons appear here</p>
                        </div>
                    @else
                        @foreach($this->archivedStructureModules as $module)
                            @php
                                $moduleIsArchived = $this->archivedModuleFlags[$module->id] ?? false;
                                $archivedLessons = $this->archivedLessonsByModule[$module->id] ?? collect();
                            @endphp
                            <div wire:key="archived-module-{{ $module->id }}" class="mx-1.5 mb-2 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
                                <div class="flex items-center justify-between gap-2 px-2.5 py-2 bg-gray-50 dark:bg-gray-900/70">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-200 truncate">{{ $module->title }}</span>
                                    @if($canManageCourse && $moduleIsArchived)
                                        <button type="button"
                                                wire:click="restoreModule({{ $module->id }})"
                                                wire:confirm="Restore this module and its archived lessons?"
                                                class="flex-shrink-0 text-xs font-semibold text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300">
                                            Restore
                                        </button>
                                    @endif
                                </div>
                                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse($archivedLessons as $lesson)
                                        <div class="flex items-center justify-between gap-2 px-2.5 py-1.5">
                                            <span class="text-xs text-gray-600 dark:text-gray-300 truncate">{{ $lesson->title }}</span>
                                            @if($canManageCourse && !$moduleIsArchived)
                                                <button type="button"
                                                        wire:click="restoreLesson({{ $lesson->id }})"
                                                        wire:confirm="Restore this lesson?"
                                                        class="flex-shrink-0 text-xs font-semibold text-green-600 hover:text-green-700 dark:text-green-400">
                                                    Restore
                                                </button>
                                            @elseif($moduleIsArchived)
                                                <span class="flex-shrink-0 text-[10px] text-gray-400">via module</span>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="px-2.5 py-2 text-xs text-gray-400 dark:text-gray-500">No archived lessons</div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    @endif
                @elseif($this->activeModules->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                        <div class="w-10 h-10 bg-orange-50 dark:bg-orange-900/20 rounded-xl flex items-center justify-center mb-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">No modules yet</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Click "Add Module" above to start</p>
                    </div>
                @else
                    <div
                        @if($canManageCourse)
                            x-data="{
                                init() {
                                    const boot = () => this.$nextTick(() => this.initModulesSortable());
                                    if (typeof Sortable === 'undefined') {
                                        if (!window.__sortableLoading) {
                                            window.__sortableLoading = true;
                                            const script = document.createElement('script');
                                            script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
                                            script.onload = () => { window.__sortableReady = true; window.dispatchEvent(new Event('sortable-ready')); };
                                            document.head.appendChild(script);
                                        }
                                        window.addEventListener('sortable-ready', boot, { once: true });
                                    } else {
                                        boot();
                                    }
                                },
                                initModulesSortable() {
                                    const container = this.$refs.modulesList;
                                    if (!container || typeof Sortable === 'undefined') return;
                                    if (container._sortable) {
                                        container._sortable.destroy();
                                        container._sortable = null;
                                    }
                                    container._sortable = Sortable.create(container, {
                                        animation: 150,
                                        handle: '.module-drag-handle',
                                        draggable: '[data-module-id]',
                                        ghostClass: 'opacity-40',
                                        chosenClass: 'curriculum-sortable-chosen',
                                        onEnd: (evt) => {
                                            if (evt.oldIndex === evt.newIndex) return;
                                            const ids = Array.from(container.querySelectorAll('[data-module-id]'))
                                                .map(el => parseInt(el.dataset.moduleId, 10))
                                                .filter(Boolean);
                                            @this.call('reorderModules', ids);
                                        }
                                    });
                                }
                            }"
                        @endif
                    >
                    <div x-ref="modulesList" class="space-y-0.5">
                    @foreach($this->activeModules as $moduleIndex => $module)
                        @php
                            $moduleLessons = $this->activeLessonsByModule[$module->id] ?? collect();
                            $lessonCount   = $moduleLessons->count();
                            $isModuleActive = $selectedType === 'module' && $selectedId == $module->id;
                        @endphp

                        <div wire:key="module-{{ $module->id }}"
                             data-module-id="{{ $module->id }}"
                             x-data="{ open: {{ ($selectedModuleId == $module->id || $isModuleActive) ? 'true' : 'false' }} }"
                             class="mb-0.5">

                            {{-- Module row --}}
                            <div class="flex items-center mx-1.5 rounded-lg {{ $isModuleActive ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/70' }} transition-colors group">

                                @if($canManageCourse)
                                    <button type="button"
                                                    class="module-drag-handle flex-shrink-0 flex items-center justify-center w-6 h-9 text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 cursor-grab active:cursor-grabbing opacity-50 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity"
                                            title="Drag to reorder module"
                                            aria-label="Drag to reorder module">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M7 4a1 1 0 100 2 1 1 0 000-2zM7 9a1 1 0 100 2 1 1 0 000-2zM7 14a1 1 0 100 2 1 1 0 000-2zM13 4a1 1 0 100 2 1 1 0 000-2zM13 9a1 1 0 100 2 1 1 0 000-2zM13 14a1 1 0 100 2 1 1 0 000-2z"/>
                                        </svg>
                                    </button>
                                @endif

                                {{-- Chevron toggle --}}
                                <button @click="open = !open"
                                        type="button"
                                        class="flex-shrink-0 flex items-center justify-center w-7 h-9 text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                    <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                         :class="open ? 'rotate-90' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>

                                {{-- Module name (click to edit) --}}
                                <button wire:click="selectItem('module', {{ $module->id }})"
                                        type="button"
                                        class="flex-1 flex items-center gap-2 py-2 pr-1 text-left min-w-0">
                                    <span class="flex-1 text-xs font-bold leading-snug truncate {{ $isModuleActive ? 'text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200' }}">
                                        {{ $module->title }}
                                    </span>
                                    @if($lessonCount > 0)
                                        <span class="flex-shrink-0 min-w-[1.25rem] text-center text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded-full">
                                            {{ $lessonCount }}
                                        </span>
                                    @endif
                                </button>

                                @if($canManageCourse)
                                    <button type="button"
                                            wire:click="deleteModule({{ $module->id }})"
                                            wire:confirm="Archive this module and all of its lessons? You can restore them from the Archived tab."
                                            title="Archive module"
                                            class="flex-shrink-0 flex items-center justify-center w-7 h-7 mr-1 rounded-md text-gray-400 opacity-0 group-hover:opacity-100 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            {{-- Lessons list (collapsible) --}}
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="ml-5 border-l-2 border-gray-100 dark:border-gray-800 pl-1.5 mb-1">

                                @if($moduleLessons->isEmpty())
                                    <p class="px-3 py-2 text-xs text-gray-400 dark:text-gray-500 italic">No lessons yet.</p>
                                @endif

                                <div
                                    @if($canManageCourse && $moduleLessons->isNotEmpty())
                                        x-data="{
                                            init() {
                                                const boot = () => this.$nextTick(() => this.initLessonsSortable());
                                                if (typeof Sortable === 'undefined') {
                                                    if (!window.__sortableLoading) {
                                                        window.__sortableLoading = true;
                                                        const script = document.createElement('script');
                                                        script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
                                                        script.onload = () => { window.__sortableReady = true; window.dispatchEvent(new Event('sortable-ready')); };
                                                        document.head.appendChild(script);
                                                    }
                                                    window.addEventListener('sortable-ready', boot, { once: true });
                                                } else {
                                                    boot();
                                                }
                                            },
                                            initLessonsSortable() {
                                                const container = this.$refs.lessonsList;
                                                if (!container || typeof Sortable === 'undefined') return;
                                                if (container._sortable) {
                                                    container._sortable.destroy();
                                                    container._sortable = null;
                                                }
                                                container._sortable = Sortable.create(container, {
                                                    animation: 150,
                                                    handle: '.lesson-drag-handle',
                                                    draggable: '[data-lesson-id]',
                                                    ghostClass: 'opacity-40',
                                                    chosenClass: 'curriculum-sortable-chosen',
                                                    onEnd: (evt) => {
                                                        if (evt.oldIndex === evt.newIndex) return;
                                                        const ids = Array.from(container.querySelectorAll('[data-lesson-id]'))
                                                            .map(el => parseInt(el.dataset.lessonId, 10))
                                                            .filter(Boolean);
                                                        @this.call('reorderLessons', {{ $module->id }}, ids);
                                                    }
                                                });
                                            }
                                        }"
                                    @endif
                                >
                                <div x-ref="lessonsList">
                                @foreach($moduleLessons as $lessonIndex => $lesson)
                                    @php $isLessonActive = $selectedType === 'lesson' && $selectedId == $lesson->id; @endphp
                                    <div wire:key="lesson-{{ $lesson->id }}"
                                         data-lesson-id="{{ $lesson->id }}"
                                         class="flex items-center gap-0.5 rounded-lg transition-all duration-150 group/lesson {{ $isLessonActive ? 'bg-orange-50 dark:bg-orange-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800' }}">

                                    @if($canManageCourse)
                                        <button type="button"
                                                class="lesson-drag-handle flex-shrink-0 flex items-center justify-center w-5 h-7 text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 cursor-grab active:cursor-grabbing opacity-50 sm:opacity-0 sm:group-hover/lesson:opacity-100 transition-opacity"
                                                title="Drag to reorder lesson"
                                                aria-label="Drag to reorder lesson">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M7 4a1 1 0 100 2 1 1 0 000-2zM7 9a1 1 0 100 2 1 1 0 000-2zM7 14a1 1 0 100 2 1 1 0 000-2zM13 4a1 1 0 100 2 1 1 0 000-2zM13 9a1 1 0 100 2 1 1 0 000-2zM13 14a1 1 0 100 2 1 1 0 000-2z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- Edit lesson button (main click area) --}}
                                    <button type="button"
                                            wire:click="selectItem('lesson', {{ $lesson->id }}, {{ $module->id }})"
                                            class="flex-1 flex items-center gap-2 px-2 py-1.5 rounded-lg text-left min-w-0">

                                        <span class="flex-shrink-0 w-4 text-[10px] font-bold text-gray-400 dark:text-gray-500 text-right tabular-nums">{{ $lessonIndex + 1 }}</span>

                                        {{-- Lesson type icon --}}
                                        @switch($lesson->lesson_type ?? 'text')
                                            @case('video')
                                                <svg class="w-3.5 h-3.5 flex-shrink-0 text-purple-500 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                                </svg>
                                                @break
                                            @case('interactive')
                                                <svg class="w-3.5 h-3.5 flex-shrink-0 text-green-500 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                @break
                                            @case('quiz')
                                                <svg class="w-3.5 h-3.5 flex-shrink-0 text-orange-500 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                                </svg>
                                                @break
                                            @default
                                                <svg class="w-3.5 h-3.5 flex-shrink-0 text-blue-400 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                                </svg>
                                        @endswitch

                                        {{-- Lesson title --}}
                                        <span class="flex-1 text-xs truncate font-medium leading-snug {{ $isLessonActive ? 'text-orange-700 dark:text-orange-400' : 'text-gray-600 dark:text-gray-300' }}">
                                            {{ $lesson->title }}
                                        </span>

                                        {{-- Status indicators --}}
                                        @if(($lesson->approval_status ?? '') === 'pending')
                                            <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0" title="Pending approval"></span>
                                        @elseif(($lesson->approval_status ?? '') === 'rejected')
                                            <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0" title="Rejected — needs revision"></span>
                                        @elseif($lesson->is_locked ?? false)
                                            <svg class="w-3 h-3 flex-shrink-0 text-gray-400" fill="currentColor" viewBox="0 0 20 20" title="Locked">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </button>

                                    {{-- Preview lesson (student view) --}}
                                    <a href="{{ route('lessons.view', $lesson->id) }}"
                                       wire:navigate
                                       title="Preview student view"
                                       class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-md text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @if($canManageCourse)
                                        <button type="button"
                                                wire:click="deleteLesson({{ $lesson->id }})"
                                                wire:confirm="Archive this lesson? It will be hidden from students but can be restored from the Archived tab."
                                                title="Archive lesson"
                                                class="flex-shrink-0 flex items-center justify-center w-7 h-7 mr-0.5 rounded-md text-gray-400 opacity-0 group-hover/lesson:opacity-100 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif

                                    </div>
                                @endforeach
                                </div>
                                </div>

                                {{-- Add Lesson button --}}
                                @if($canManageCourse)
                                    <button wire:click="selectItem('lesson', null, {{ $module->id }})"
                                            type="button"
                                            class="w-full flex items-center gap-1.5 px-2.5 py-1.5 text-xs text-gray-400 dark:text-gray-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-all duration-150 group/add">
                                        <svg class="w-3 h-3 group-hover/add:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span class="font-medium">Add Lesson</span>
                                    </button>
                                @endif
                            </div>

                        </div>
                    @endforeach
                    </div>
                    </div>
                @endif

            </div>

            {{-- Bottom: legend + course settings --}}
            <div class="border-t border-gray-100 dark:border-gray-800 p-3 space-y-1">

                {{-- Status legend --}}
                <div class="flex items-center gap-3 px-2 py-1">
                    <span class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                        <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span> Pending
                    </span>
                    <span class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                        <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> Rejected
                    </span>
                </div>

                <a href="{{ route('courses.edit', $course->id) }}" wire:navigate
                   class="flex items-center gap-2 px-2.5 py-2 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Course Settings
                </a>
            </div>
        @endif

    @endif

</div>
