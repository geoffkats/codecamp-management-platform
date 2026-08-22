<div class="px-8 pt-6 pb-4 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">

    {{-- Breadcrumb / course switcher --}}
    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('curriculum.builder') }}" wire:navigate
           class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 px-2.5 py-1.5 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-all border border-transparent hover:border-orange-200 dark:hover:border-orange-800">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
            All Courses
        </a>
        <svg class="w-3 h-3 text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate max-w-xs">{{ $course->title }}</span>
    </div>

    {{-- Title + Actions --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">{{ $course->title }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Build your course — add modules, then lessons inside each one.</p>
        </div>

        @if($canManageCourse)
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="selectItem('module')"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Module
                </button>

                {{-- Add Lesson: disabled unless a module is selected --}}
                <div class="relative" x-data="{ showTip: false }">
                    <button wire:click="selectItem('lesson', null, {{ $this->selectedModuleId ?? 'null' }})"
                            type="button"
                            @if(!$this->selectedModuleId) disabled @endif
                            @mouseenter="!{{ $this->selectedModuleId ? 'true' : 'false' }} && (showTip = true)"
                            @mouseleave="showTip = false"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg border transition-colors
                                {{ $this->selectedModuleId
                                    ? 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800'
                                    : 'border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-600 cursor-not-allowed bg-gray-50 dark:bg-gray-800/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Add Lesson
                    </button>
                    <div x-show="showTip"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="pointer-events-none absolute left-1/2 -translate-x-1/2 top-full mt-2 px-2.5 py-1.5 text-xs font-medium text-white bg-gray-900 dark:bg-gray-700 rounded-lg whitespace-nowrap shadow-lg z-10">
                        Select a module first
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-gray-900 dark:border-b-gray-700"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
