{{-- Course picker: the only course list on this page (the outline panel is hidden until a course is open). --}}
@php
    $seesAll = auth()->user()->isAdmin() || auth()->user()->isSupervisor();
    $statusStyles = [
        'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    ];
    $filters = ['all' => 'All', 'mine' => 'Mine', 'shared' => $seesAll ? 'By others' : 'Shared with me'];
    $pickerTargets = 'courseSearch,courseFilter,courseSort';
    $banners = ['bg-orange-500', 'bg-blue-600', 'bg-violet-600', 'bg-emerald-600', 'bg-rose-500', 'bg-indigo-600'];
@endphp

<div class="min-h-full bg-gray-50 dark:bg-gray-950">
    {{-- Header --}}
    <div class="border-b border-gray-200 bg-white px-4 py-6 sm:px-8 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Curriculum Builder</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Choose a course to add modules, write lessons and attach quizzes.
                    @if($courseTotal > 0)
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $courseTotal }} {{ Str::plural('course', $courseTotal) }}</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('courses.create') }}" wire:navigate
               class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-orange-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New course
            </a>
        </div>

        @if($courseTotal > 0)
            {{-- Toolbar --}}
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <div class="relative min-w-[16rem] flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <input type="search"
                           wire:model.live.debounce.300ms="courseSearch"
                           placeholder="Search courses by name or category…"
                           aria-label="Search courses"
                           class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-10 text-sm text-gray-900 placeholder-gray-400 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <svg wire:loading wire:target="courseSearch" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-orange-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                </div>

                <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-gray-800" role="tablist">
                    @foreach($filters as $key => $label)
                        <button type="button" wire:click="$set('courseFilter', '{{ $key }}')" role="tab"
                                aria-selected="{{ $courseFilter === $key ? 'true' : 'false' }}"
                                class="rounded-lg px-3 py-1.5 text-sm font-semibold transition-colors {{ $courseFilter === $key ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <select wire:model.live="courseSort" aria-label="Sort courses"
                        class="rounded-xl border border-gray-300 bg-white py-2.5 pl-3 pr-8 text-sm text-gray-700 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <option value="recent">Recently updated</option>
                    <option value="title">Name A–Z</option>
                </select>
            </div>
        @endif
    </div>

    <div class="px-4 py-6 sm:px-8">
        @if($courseTotal === 0)
            <div class="mx-auto max-w-sm py-16 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="mb-2 text-lg font-bold text-gray-700 dark:text-gray-300">No courses yet</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Create your first course with <strong>New course</strong>, then come back here to build it.</p>
            </div>
        @else
            {{-- Skeleton while search, filter or sort reloads --}}
            <div wire:loading.delay.grid wire:target="{{ $pickerTargets }}" class="hidden grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4" aria-hidden="true">
                @for($i = 0; $i < min(8, max(4, $courses->count())); $i++)
                    <div class="animate-pulse overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                        <div class="h-24 bg-gray-200 dark:bg-gray-800"></div>
                        <div class="space-y-3 p-4">
                            <div class="h-4 w-3/4 rounded bg-gray-200 dark:bg-gray-800"></div>
                            <div class="h-3 w-1/2 rounded bg-gray-200 dark:bg-gray-800"></div>
                            <div class="flex gap-2 pt-2">
                                <div class="h-6 w-20 rounded-full bg-gray-200 dark:bg-gray-800"></div>
                                <div class="h-6 w-20 rounded-full bg-gray-200 dark:bg-gray-800"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            <div wire:loading.delay.remove wire:target="{{ $pickerTargets }}">
                @if($courses->isEmpty())
                    <div class="mx-auto max-w-sm py-16 text-center">
                        <h3 class="mb-2 text-base font-bold text-gray-700 dark:text-gray-300">No courses match</h3>
                        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">Try a different name, or show all courses.</p>
                        <button type="button" wire:click="$set('courseSearch', ''); $set('courseFilter', 'all')"
                                class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            Clear search
                        </button>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                        @foreach($courses as $courseOption)
                            @php
                                $status = $courseOption->approval_status ?: 'draft';
                                $isMine = (int) $courseOption->instructor_id === (int) auth()->id();
                            @endphp
                            <a wire:key="picker-course-{{ $courseOption->id }}"
                               href="{{ route('curriculum.builder', ['course' => $courseOption->id]) }}" wire:navigate
                               x-data="{ opening: false }" x-on:click="opening = true"
                               class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white transition-all duration-200 hover:-translate-y-0.5 hover:border-orange-300 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900 dark:hover:border-orange-600">
                                <div class="relative h-24 overflow-hidden {{ $banners[$courseOption->id % count($banners)] }}">
                                    @if($courseOption->featured_image)
                                        <img src="{{ asset('storage/'.$courseOption->featured_image) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                    @else
                                        <span class="absolute bottom-2 left-4 text-4xl font-black text-white/80">{{ Str::upper(Str::substr($courseOption->title, 0, 1)) }}</span>
                                    @endif
                                    <span class="absolute right-3 top-3 rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusStyles[$status] ?? 'bg-white/90 text-gray-700' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </div>

                                <div class="flex flex-1 flex-col p-4">
                                    <p class="line-clamp-2 font-bold leading-snug text-gray-900 group-hover:text-orange-700 dark:text-white dark:group-hover:text-orange-400">
                                        {{ $courseOption->title }}
                                    </p>
                                    @if($courseOption->category || $courseOption->difficulty_level)
                                        <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                            {{ collect([$courseOption->category, $courseOption->difficulty_level ? ucfirst($courseOption->difficulty_level) : null])->filter()->join(' · ') }}
                                        </p>
                                    @endif

                                    <div class="mb-4 mt-3 flex flex-wrap gap-2 text-xs font-medium">
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ $courseOption->modules_count }} {{ Str::plural('module', $courseOption->modules_count) }}
                                        </span>
                                        <span class="rounded-full bg-purple-50 px-2.5 py-1 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                            {{ $courseOption->lessons_count }} {{ Str::plural('lesson', $courseOption->lessons_count) }}
                                        </span>
                                    </div>

                                    <div class="mt-auto flex items-center justify-between gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                        <span class="truncate">
                                            {{ $isMine ? 'Your course' : ($courseOption->instructor->name ?? 'Unassigned') }}
                                            · {{ $courseOption->updated_at?->diffForHumans(short: true) }}
                                        </span>
                                        <span class="inline-flex flex-shrink-0 items-center gap-1 font-semibold text-orange-600 dark:text-orange-400">
                                            <span x-show="!opening">Open</span>
                                            <span x-show="opening" x-cloak>Opening…</span>
                                            <svg x-show="!opening" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                            <svg x-show="opening" x-cloak class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
