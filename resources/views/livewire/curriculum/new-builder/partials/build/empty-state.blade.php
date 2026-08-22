{{-- Course Outline View (shown when a course is selected but no item is open for editing) --}}
<div class="p-6 md:p-8">
    <div class="max-w-4xl mx-auto space-y-5">

        {{-- Action bar --}}
        @if($canManageCourse)
        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="selectItem('module')"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Add Module
            </button>
            @if($this->selectedModuleId)
                <button wire:click="selectItem('lesson', null, {{ $this->selectedModuleId }})"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Add Lesson
                </button>
            @endif
            <p class="text-xs text-gray-400 dark:text-gray-500 ml-1">
                {{ $this->activeModulesCount }} {{ $this->activeModulesCount === 1 ? 'module' : 'modules' }},
                {{ array_sum($this->activeLessonCounts) }} {{ array_sum($this->activeLessonCounts) === 1 ? 'lesson' : 'lessons' }}
            </p>
        </div>
        @endif

        @if($this->activeModules->isEmpty())
            {{-- No modules yet --}}
            <div class="text-center py-16 bg-white dark:bg-gray-900 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                <div class="w-16 h-16 bg-orange-50 dark:bg-orange-900/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-2">This course has no modules yet</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                    Modules are the chapters of your course. Add one to start building.
                </p>
                @if($canManageCourse)
                    <button wire:click="selectItem('module')" type="button"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add your first module
                    </button>
                @endif
            </div>
        @else
            {{-- Course outline --}}
            <div class="space-y-3">
                @foreach($this->activeModules as $moduleIndex => $module)
                    @php
                        $moduleLessons = $this->activeLessonsByModule[$module->id] ?? collect();
                        $isActiveModule = $selectedModuleId == $module->id;
                    @endphp

                    <div wire:key="outline-module-{{ $module->id }}"
                         x-data="{ open: true }"
                         class="bg-white dark:bg-gray-900 rounded-2xl border {{ $isActiveModule ? 'border-orange-200 dark:border-orange-800' : 'border-gray-200 dark:border-gray-800' }} overflow-hidden shadow-sm">

                        {{-- Module header --}}
                        <div class="flex items-center gap-3 px-5 py-4 {{ $isActiveModule ? 'bg-orange-50 dark:bg-orange-900/20' : 'bg-gray-50 dark:bg-gray-800/60' }}">
                            {{-- Expand/collapse --}}
                            <button @click="open = !open" type="button"
                                    class="flex-shrink-0 w-6 h-6 flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            {{-- Module number --}}
                            <div class="w-7 h-7 rounded-lg bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-orange-700 dark:text-orange-400">{{ $moduleIndex + 1 }}</span>
                            </div>

                            {{-- Module title --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 dark:text-white truncate">{{ $module->title }}</p>
                                @if($module->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ $module->description }}</p>
                                @endif
                            </div>

                            {{-- Lesson count --}}
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium flex-shrink-0">
                                {{ $moduleLessons->count() }} {{ $moduleLessons->count() === 1 ? 'lesson' : 'lessons' }}
                            </span>

                            {{-- Module actions --}}
                            @if($canManageCourse)
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button wire:click="selectItem('module', {{ $module->id }})" type="button"
                                        class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                        title="Edit module">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="selectItem('lesson', null, {{ $module->id }})" type="button"
                                        class="p-1.5 text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors"
                                        title="Add lesson to this module">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </div>

                        {{-- Lessons --}}
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100">
                            @if($moduleLessons->isEmpty())
                                <div class="px-5 py-4 flex items-center gap-3 text-sm text-gray-400 dark:text-gray-500 border-t border-gray-100 dark:border-gray-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>No lessons yet.</span>
                                    @if($canManageCourse)
                                        <button wire:click="selectItem('lesson', null, {{ $module->id }})" type="button"
                                                class="text-orange-600 dark:text-orange-400 font-semibold hover:underline ml-auto">
                                            + Add lesson
                                        </button>
                                    @endif
                                </div>
                            @else
                                @foreach($moduleLessons as $lessonIndex => $lesson)
                                    <button wire:key="outline-lesson-{{ $lesson->id }}"
                                            type="button"
                                            wire:click="selectItem('lesson', {{ $lesson->id }}, {{ $module->id }})"
                                            class="w-full flex items-center gap-3 px-5 py-3 border-t border-gray-100 dark:border-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-colors group text-left">
                                        {{-- Lesson number --}}
                                        <span class="w-5 text-xs font-semibold text-gray-400 dark:text-gray-500 flex-shrink-0 text-right">
                                            {{ $lessonIndex + 1 }}
                                        </span>

                                        {{-- Lesson type icon --}}
                                        @switch($lesson->lesson_type ?? 'text')
                                            @case('video')
                                                <svg class="w-4 h-4 flex-shrink-0 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                                </svg>
                                                @break
                                            @case('interactive')
                                                <svg class="w-4 h-4 flex-shrink-0 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                @break
                                            @case('quiz')
                                                <svg class="w-4 h-4 flex-shrink-0 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                                </svg>
                                                @break
                                            @default
                                                <svg class="w-4 h-4 flex-shrink-0 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                                </svg>
                                        @endswitch

                                        {{-- Lesson title --}}
                                        <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-200 group-hover:text-blue-700 dark:group-hover:text-blue-300 truncate transition-colors">
                                            {{ $lesson->title }}
                                        </span>

                                        {{-- Status --}}
                                        @if(($lesson->approval_status ?? '') === 'pending')
                                            <span class="text-xs text-amber-600 dark:text-amber-400 font-medium bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 rounded-full flex-shrink-0">Pending</span>
                                        @elseif(($lesson->approval_status ?? '') === 'rejected')
                                            <span class="text-xs text-red-600 dark:text-red-400 font-medium bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full flex-shrink-0">Rejected</span>
                                        @elseif(($lesson->approval_status ?? '') === 'approved')
                                            <span class="text-xs text-green-600 dark:text-green-400 font-medium flex-shrink-0">✓</span>
                                        @endif

                                        {{-- Edit arrow --}}
                                        <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-blue-400 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                @endforeach

                                {{-- Add lesson row --}}
                                @if($canManageCourse)
                                    <button wire:click="selectItem('lesson', null, {{ $module->id }})" type="button"
                                            class="w-full flex items-center gap-3 px-5 py-2.5 border-t border-dashed border-gray-200 dark:border-gray-700 text-sm text-gray-400 dark:text-gray-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/10 transition-colors group">
                                        <svg class="w-3.5 h-3.5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span class="font-medium">Add lesson</span>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Collaborators (for admins/supervisors) --}}
        @if($course && $this->canManageCollaborators)
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 mt-4">
                <div class="mb-4">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Course Collaborators</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Add trainers and co-editors without leaving the builder.</p>
                </div>
                <livewire:course.manage-collaborators :course="$course" :key="'collaborators-build-empty-'.$course->id" />
            </div>
        @endif

    </div>
</div>
