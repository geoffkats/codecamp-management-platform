@php
    $hasFilters = $search || $filterStatus !== 'all' || $filterCategory !== 'all' || $filterDifficulty !== 'all';
    $canCreate = ! $isIctTeacher && auth()->user()->can('create', \App\Models\Course::class);
    $covers = [
        'from-orange-400 to-orange-600',
        'from-sky-400 to-indigo-600',
        'from-emerald-400 to-teal-600',
        'from-violet-400 to-fuchsia-600',
        'from-rose-400 to-pink-600',
        'from-amber-400 to-orange-500',
    ];
    $coverIcon = function ($course) {
        $text = \Illuminate\Support\Str::lower($course->title.' '.$course->category);
        return match (true) {
            str_contains($text, 'scratch') || str_contains($text, 'game') => 'puzzle-piece',
            str_contains($text, 'python') || str_contains($text, 'javascript') || str_contains($text, 'code') => 'code-bracket',
            str_contains($text, 'web') || str_contains($text, 'html') => 'globe-alt',
            str_contains($text, 'robot') || str_contains($text, 'arduino') => 'cpu-chip',
            str_contains($text, 'design') || str_contains($text, 'art') => 'paint-brush',
            str_contains($text, 'excel') || str_contains($text, 'data') || str_contains($text, 'spreadsheet') => 'table-cells',
            str_contains($text, 'word') || str_contains($text, 'document') => 'document-text',
            default => 'book-open',
        };
    };
    $levelCls = [
        'beginner' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20',
        'intermediate' => 'bg-sky-50 text-sky-700 ring-sky-100 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20',
        'advanced' => 'bg-violet-50 text-violet-700 ring-violet-100 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-500/20',
    ];
    $select = 'rounded-xl border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm font-medium text-gray-700 ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500/50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700';
    $statusChips = $isIctTeacher
        ? ['all' => ['All', $stats['total']]]
        : [
            'all' => ['All', $stats['total']],
            'published' => ['Live', $stats['live']],
            'draft' => ['Drafts', $stats['draft']],
            'pending' => ['Awaiting approval', $stats['pending']],
        ];
    $loadingTargets = 'search,filterStatus,filterCategory,filterDifficulty,sortBy,setStatus,clearFilters,setView';
@endphp

<div class="min-h-screen bg-gray-50/70 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6">

        {{-- Hero --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-500 to-orange-600 p-6 text-white shadow-lg shadow-orange-500/20 sm:p-7">
            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-24 right-40 h-56 w-56 rounded-full bg-white/5"></div>

            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-xl">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-orange-100">{{ $isIctTeacher ? 'ICT' : 'Programs' }}</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $pageTitle }}</h1>
                    <p class="mt-1 text-sm text-orange-50/90">{{ $isIctTeacher ? 'Modules available for your school.' : 'Build, publish and track every course in the catalogue.' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if(! $isIctTeacher && \Illuminate\Support\Facades\Route::has('curriculum.builder'))
                        <a href="{{ route('curriculum.builder') }}" wire:navigate
                           class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 px-3.5 py-2 text-sm font-semibold text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white/25">
                            <flux:icon name="squares-2x2" variant="mini" class="size-4" /> Curriculum builder
                        </a>
                    @endif
                    @if($canCreate)
                        <a href="{{ route('courses.create') }}" wire:navigate
                           class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-sm font-bold text-orange-700 shadow-sm transition hover:bg-orange-50">
                            <flux:icon name="plus" variant="mini" class="size-4" /> New course
                        </a>
                    @endif
                </div>
            </div>

            <div class="relative mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach([
                    [$isIctTeacher ? 'Modules' : 'Courses', $stats['total'], 'book-open'],
                    ['Live now', $stats['live'], 'signal'],
                    [$isIctTeacher ? 'Drafts' : 'Awaiting approval', $isIctTeacher ? $stats['draft'] : $stats['pending'], $isIctTeacher ? 'pencil-square' : 'clock'],
                    ['Enrolments', $stats['students'], 'user-group'],
                ] as [$label, $value, $icon])
                    <div class="flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/20">
                            <flux:icon :name="$icon" variant="outline" class="size-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xl font-extrabold leading-none">{{ number_format($value) }}</p>
                            <p class="mt-1 truncate text-[11px] font-semibold uppercase tracking-wide text-orange-50/90">{{ $label }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        @if(session()->has('message'))
            <div class="flex items-start gap-3 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20">
                <flux:icon name="check-circle" variant="solid" class="mt-0.5 size-5 shrink-0 text-emerald-500" />
                <p>{{ session('message') }}</p>
            </div>
        @endif

        {{-- Toolbar --}}
        <section class="rounded-2xl bg-white p-4 ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <flux:icon name="magnifying-glass" variant="mini" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by title, description or category…"
                        class="w-full rounded-xl border-0 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 ring-1 ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500/50 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700">
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center">
                    @unless($isIctTeacher)
                        <select wire:model.live="filterDifficulty" class="{{ $select }}" aria-label="Level">
                            @foreach($difficultyOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @if(count($categoryOptions) > 1)
                            <select wire:model.live="filterCategory" class="{{ $select }}" aria-label="Category">
                                @foreach($categoryOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        @endif
                    @endunless
                    <select wire:model.live="sortBy" class="{{ $select }}" aria-label="Sort">
                        <option value="latest">Newest first</option>
                        <option value="popular">Most enrolled</option>
                        <option value="title">Title A–Z</option>
                        <option value="duration">Duration</option>
                    </select>
                    <div class="col-span-2 flex rounded-xl bg-gray-100 p-1 dark:bg-zinc-800 sm:col-span-1" role="group" aria-label="Layout">
                        @foreach(['grid' => 'squares-2x2', 'list' => 'list-bullet'] as $mode => $icon)
                            <button type="button" wire:click="setView('{{ $mode }}')" title="{{ ucfirst($mode) }} view"
                                class="flex flex-1 items-center justify-center rounded-lg px-2.5 py-1.5 transition {{ $viewMode === $mode ? 'bg-white text-orange-600 shadow-sm dark:bg-zinc-900' : 'text-gray-500 hover:text-gray-800 dark:text-zinc-400' }}">
                                <flux:icon :name="$icon" variant="mini" class="size-4" />
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3 dark:border-zinc-800">
                @foreach($statusChips as $key => [$label, $count])
                    <button type="button" wire:click="setStatus('{{ $key }}')"
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 transition {{ $filterStatus === $key ? 'bg-gray-900 text-white ring-gray-900 dark:bg-white dark:text-gray-900 dark:ring-white' : 'bg-white text-gray-600 ring-gray-200 hover:text-gray-900 hover:ring-gray-300 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700' }}">
                        {{ $label }}
                        <span class="{{ $filterStatus === $key ? 'opacity-70' : 'text-gray-400' }}">{{ number_format($count) }}</span>
                    </button>
                @endforeach
                @if($hasFilters)
                    <button type="button" wire:click="clearFilters" class="ml-auto inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-500/10">
                        <flux:icon name="x-mark" variant="micro" class="size-3.5" /> Clear filters
                    </button>
                @endif
                <span wire:loading.flex wire:target="{{ $loadingTargets }}" class="{{ $hasFilters ? '' : 'ml-auto' }} items-center gap-1.5 text-xs font-medium text-orange-600">
                    <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Updating…
                </span>
            </div>
        </section>

        {{-- Results --}}
        <div wire:loading.class="opacity-60" wire:target="{{ $loadingTargets }}" class="transition-opacity">
            @if($courses->count() > 0)
                <p class="mb-3 px-1 text-sm text-gray-500">
                    Showing <span class="font-bold text-gray-900 dark:text-white">{{ $courses->firstItem() }}–{{ $courses->lastItem() }}</span> of {{ number_format($courses->total()) }}
                </p>

                @if($viewMode === 'grid')
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($courses as $course)
                            @php
                                $level = \Illuminate\Support\Str::lower((string) $course->difficulty_level);
                                $approval = $course->approval_status;
                            @endphp
                            <article wire:key="course-card-{{ $course->id }}" class="group flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-gray-200/60 hover:ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:shadow-black/30">
                                <a href="{{ route('courses.show', $course) }}" wire:navigate class="relative block h-36 overflow-hidden">
                                    @if($course->featured_image)
                                        <img src="{{ asset('storage/'.$course->featured_image) }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/0"></div>
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br {{ $covers[$course->id % count($covers)] }}">
                                            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                                            <div class="absolute -bottom-10 left-6 h-24 w-24 rounded-full bg-white/10"></div>
                                            <flux:icon :name="$coverIcon($course)" variant="outline" class="relative size-14 text-white/90 transition duration-500 group-hover:scale-110" />
                                        </div>
                                    @endif
                                    <div class="absolute left-3 top-3 flex gap-1.5">
                                        @if($course->category)
                                            <span class="rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-gray-700 backdrop-blur">{{ \Illuminate\Support\Str::limit($course->category, 18) }}</span>
                                        @endif
                                        @if($course->is_featured)
                                            <span class="inline-flex items-center gap-0.5 rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-950"><flux:icon name="star" variant="micro" class="size-3" />Featured</span>
                                        @endif
                                    </div>
                                    <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $course->is_published ? 'bg-emerald-500 text-white' : 'bg-gray-900/70 text-white backdrop-blur' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $course->is_published ? 'bg-white' : 'bg-amber-300' }}"></span>
                                        {{ $course->is_published ? 'Live' : 'Draft' }}
                                    </span>
                                </a>

                                <div class="flex flex-1 flex-col p-4">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if(isset($levelCls[$level]))
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 {{ $levelCls[$level] }}">{{ ucfirst($level) }}</span>
                                        @endif
                                        @if($approval === 'pending')
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 ring-1 ring-amber-100">Awaiting approval</span>
                                        @elseif($approval === 'rejected')
                                            <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold text-red-600 ring-1 ring-red-100">Changes requested</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('courses.show', $course) }}" wire:navigate class="mt-2 line-clamp-2 text-base font-bold leading-snug text-gray-900 group-hover:text-orange-600 dark:text-white">{{ $course->title }}</a>
                                    @if($course->short_description || $course->description)
                                        <p class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-zinc-400">{{ $course->short_description ?: strip_tags($course->description) }}</p>
                                    @endif

                                    <div class="mb-4 mt-4 flex items-center gap-4 text-xs font-semibold text-gray-500 dark:text-zinc-400">
                                        <span class="inline-flex items-center gap-1.5"><flux:icon name="play-circle" variant="mini" class="size-4 text-gray-400" />{{ number_format($course->lessons_count) }} {{ \Illuminate\Support\Str::plural('lesson', $course->lessons_count) }}</span>
                                        <span class="inline-flex items-center gap-1.5"><flux:icon name="user-group" variant="mini" class="size-4 text-gray-400" />{{ number_format($course->enrollments_count) }} {{ \Illuminate\Support\Str::plural('student', $course->enrollments_count) }}</span>
                                    </div>

                                    <div class="mt-auto flex items-center justify-between gap-2 border-t border-gray-100 pt-3 dark:border-zinc-800">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-[10px] font-bold text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $course->instructor ? $course->instructor->initials() : '—' }}</span>
                                            <span class="truncate text-xs font-medium text-gray-600 dark:text-zinc-400">{{ $course->instructor->name ?? 'No instructor' }}</span>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-0.5">
                                            @if(! $isIctTeacher && \Illuminate\Support\Facades\Route::has('curriculum.builder'))
                                                <a href="{{ route('curriculum.builder', ['course' => $course->id]) }}" wire:navigate title="Open in curriculum builder" class="rounded-lg p-2 text-gray-400 transition hover:bg-orange-50 hover:text-orange-600 dark:hover:bg-orange-500/10">
                                                    <flux:icon name="squares-2x2" variant="mini" class="size-4" />
                                                </a>
                                            @endif
                                            @can('update', $course)
                                                <a href="{{ route('courses.edit', $course) }}" wire:navigate title="Edit course" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
                                                    <flux:icon name="pencil-square" variant="mini" class="size-4" />
                                                </a>
                                            @endcan
                                            <a href="{{ route('courses.show', $course) }}" wire:navigate class="ml-1 inline-flex items-center gap-1 rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-orange-600 dark:bg-white dark:text-gray-900 dark:hover:bg-orange-500 dark:hover:text-white">
                                                Open <flux:icon name="arrow-right" variant="micro" class="size-3.5" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50/80 dark:bg-zinc-800/50">
                                    <tr>
                                        @foreach(['Course', 'Level', 'Status', 'Lessons', 'Students', ''] as $heading)
                                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400 {{ $loop->last ? 'text-right' : '' }}">{{ $heading }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                    @foreach($courses as $course)
                                        @php $level = \Illuminate\Support\Str::lower((string) $course->difficulty_level); @endphp
                                        <tr wire:key="course-row-{{ $course->id }}" class="group hover:bg-gray-50/80 dark:hover:bg-zinc-800/40">
                                            <td class="px-4 py-3">
                                                <a href="{{ route('courses.show', $course) }}" wire:navigate class="flex items-center gap-3">
                                                    @if($course->featured_image)
                                                        <img src="{{ asset('storage/'.$course->featured_image) }}" alt="" class="h-11 w-11 shrink-0 rounded-xl object-cover">
                                                    @else
                                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $covers[$course->id % count($covers)] }}">
                                                            <flux:icon :name="$coverIcon($course)" variant="outline" class="size-5 text-white" />
                                                        </span>
                                                    @endif
                                                    <span class="min-w-0">
                                                        <span class="block truncate text-sm font-bold text-gray-900 group-hover:text-orange-600 dark:text-white">{{ $course->title }}</span>
                                                        <span class="block truncate text-xs text-gray-400">{{ $course->instructor->name ?? 'No instructor' }}@if($course->category) · {{ $course->category }}@endif</span>
                                                    </span>
                                                </a>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if(isset($levelCls[$level]))
                                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold ring-1 {{ $levelCls[$level] }}">{{ ucfirst($level) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-1">
                                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-bold ring-1 {{ $course->is_published ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                                                        <span class="h-1.5 w-1.5 rounded-full {{ $course->is_published ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>{{ $course->is_published ? 'Live' : 'Draft' }}
                                                    </span>
                                                    @if($course->approval_status === 'pending')
                                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700 ring-1 ring-amber-100">Awaiting approval</span>
                                                    @elseif($course->approval_status === 'rejected')
                                                        <span class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-bold text-red-600 ring-1 ring-red-100">Changes requested</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-zinc-300">{{ number_format($course->lessons_count) }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-zinc-300">{{ number_format($course->enrollments_count) }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-0.5">
                                                    @if(! $isIctTeacher && \Illuminate\Support\Facades\Route::has('curriculum.builder'))
                                                        <a href="{{ route('curriculum.builder', ['course' => $course->id]) }}" wire:navigate title="Curriculum builder" class="rounded-lg p-2 text-gray-400 hover:bg-orange-50 hover:text-orange-600 dark:hover:bg-orange-500/10"><flux:icon name="squares-2x2" variant="mini" class="size-4" /></a>
                                                    @endif
                                                    @can('update', $course)
                                                        <a href="{{ route('courses.edit', $course) }}" wire:navigate title="Edit" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-zinc-800"><flux:icon name="pencil-square" variant="mini" class="size-4" /></a>
                                                    @endcan
                                                    <a href="{{ route('courses.show', $course) }}" wire:navigate title="Open" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-zinc-800"><flux:icon name="arrow-right" variant="mini" class="size-4" /></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($courses->hasPages())
                    <div class="mt-5">{{ $courses->links() }}</div>
                @endif
            @else
                <div class="rounded-2xl bg-white px-6 py-16 ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-500 dark:bg-orange-500/10">
                            <flux:icon name="book-open" variant="outline" class="size-7" />
                        </div>
                        <p class="mt-4 text-base font-bold text-gray-900 dark:text-white">{{ $hasFilters ? 'No courses match these filters' : 'No courses yet' }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $hasFilters ? 'Try a different search or clear the filters.' : 'Create your first course to start building lessons.' }}</p>
                        <div class="mt-4">
                            @if($hasFilters)
                                <button type="button" wire:click="clearFilters" class="rounded-xl px-4 py-2 text-sm font-semibold text-orange-600 ring-1 ring-orange-200 hover:bg-orange-50">Clear filters</button>
                            @elseif($canCreate)
                                <a href="{{ route('courses.create') }}" wire:navigate class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-bold text-white hover:bg-orange-600">New course</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
