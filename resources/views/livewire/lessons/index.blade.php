<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">All Lessons</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Browse lessons across all courses</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            {{-- Search --}}
            <div class="md:col-span-2">
                <flux:field>
                    <flux:label>Search Lessons</flux:label>
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by title or description..." />
                </flux:field>
            </div>

            {{-- Course Filter --}}
            <div>
                <flux:field>
                    <flux:label>Course</flux:label>
                    <flux:select wire:model.live="filterCourse">
                        <option value="all">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            {{-- Module Filter --}}
            @if($filterCourse !== 'all' && $modules->count() > 0)
                <div>
                    <flux:field>
                        <flux:label>Module</flux:label>
                        <flux:select wire:model.live="filterModule">
                            <option value="all">All Modules</option>
                            @foreach($modules as $module)
                                <option value="{{ $module->id }}">{{ $module->title }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
            @endif

            {{-- Status Filter --}}
            <div>
                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model.live="filterStatus">
                        <option value="all">All Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </flux:select>
                </flux:field>
            </div>
        </div>
    </div>

    {{-- Lessons Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($lessons as $lesson)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2">
                            {{ $lesson->title }}
                        </h3>
                        @if($lesson->is_published)
                            <flux:badge variant="success" size="sm">Published</flux:badge>
                        @else
                            <flux:badge variant="ghost" size="sm">Draft</flux:badge>
                        @endif
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                        {{ $lesson->description ?? 'No description' }}
                    </p>

                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 mb-4">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            {{ $lesson->course->title }}
                        </span>
                        @if($lesson->duration_minutes)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $lesson->duration_minutes }} min
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <flux:button href="{{ route('lessons.view', $lesson) }}" wire:navigate variant="ghost" size="sm">
                            View Lesson
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-gray-600 dark:text-gray-400 text-lg">No lessons found</p>
                <p class="text-gray-500 dark:text-gray-500 text-sm mt-2">Try adjusting your filters</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($lessons->hasPages())
        <div class="mt-6">
            {{ $lessons->links() }}
        </div>
    @endif
</div>
