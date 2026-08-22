<div class="p-6 space-y-6">
    <div class="flex items-center justify-between gap-3 rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">
        <div class="flex items-center gap-2">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span class="font-semibold">Instructor preview</span>
            <span class="hidden text-blue-200 sm:inline">— browse lessons as students see them. Progress is not saved.</span>
        </div>
        <a href="{{ route('curriculum.builder', $course) }}" wire:navigate
           class="shrink-0 rounded-lg bg-white/20 px-3 py-1 text-xs font-semibold hover:bg-white/30">
            Edit in builder
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div class="space-y-2">
                <a href="{{ route('courses.show', $course) }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Back to course</a>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $course->title }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">Instructor: {{ $course->instructor->name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $totalLessons }} lessons across {{ $modules->count() }} modules</p>
            </div>
            @if($firstLesson)
                <flux:button href="{{ route('lessons.view', $firstLesson) }}" variant="primary" size="sm" wire:navigate>
                    Open first lesson
                </flux:button>
            @endif
        </div>
    </div>

    @if($modules->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">This course has no modules yet.</p>
            <flux:button href="{{ route('curriculum.builder', $course) }}" variant="primary" size="sm" class="mt-4" wire:navigate>
                Open curriculum builder
            </flux:button>
        </div>
    @else
        <div class="space-y-6">
            @foreach($modules as $module)
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $module->title }}</h2>
                        @if($module->description)
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $module->description }}</p>
                        @endif
                    </div>

                    <div class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($module->lessons as $lesson)
                            <div class="flex flex-col gap-3 px-6 py-4 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <a href="{{ route('lessons.view', $lesson) }}" wire:navigate class="text-base font-semibold text-gray-900 hover:text-orange-600 dark:text-white dark:hover:text-orange-400">
                                        {{ $lesson->order_index }}. {{ $lesson->title }}
                                    </a>
                                    @if($lesson->duration_minutes)
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $lesson->duration_minutes }} min</p>
                                    @endif
                                </div>
                                <flux:button href="{{ route('lessons.view', $lesson) }}" variant="ghost" size="sm" wire:navigate>
                                    Preview
                                </flux:button>
                            </div>
                        @empty
                            <p class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">No lessons in this module yet.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
