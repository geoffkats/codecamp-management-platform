@php
    $hasFilters = $search || $filterStatus !== 'all' || $filterCategory !== 'all' || $filterDifficulty !== 'all';
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">

    {{-- Header --}}
    <div class="border-b-4 border-blue-600 bg-orange-600">
        <div class="mx-auto max-w-6xl px-4 py-5 sm:px-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-white">{{ $pageTitle }}</h1>
                    <p class="mt-0.5 text-sm text-orange-100">{{ $pageSubtitle }}</p>
                </div>
                @if(!$isIctTeacher)
                    @can('create', \App\Models\Course::class)
                        <flux:button href="{{ route('courses.create') }}" icon="plus" size="sm"
                            class="!bg-blue-600 !text-white hover:!bg-blue-700" wire:navigate>
                            New course
                        </flux:button>
                    @endcan
                @endif
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl space-y-4 px-4 py-5 sm:px-6">

        @if(session()->has('message'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
                {{ session('message') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-4">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search courses…" />
                </div>
                <div class="lg:col-span-2">
                    <flux:select wire:model.live="filterStatus" label="Status">
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </div>
                @if(!$isIctTeacher)
                    <div class="lg:col-span-2">
                        <flux:select wire:model.live="filterDifficulty" label="Level">
                            @foreach($difficultyOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    @if(count($categoryOptions) > 1)
                        <div class="lg:col-span-2">
                            <flux:select wire:model.live="filterCategory" label="Category">
                                @foreach($categoryOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif
                @endif
                <div class="lg:col-span-2">
                    <flux:select wire:model.live="sortBy" label="Sort">
                        <option value="latest">Newest</option>
                        <option value="popular">Most enrolled</option>
                        <option value="title">Title A–Z</option>
                        <option value="duration">Duration</option>
                    </flux:select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div wire:loading.class="opacity-60" wire:target="search,filterStatus,filterCategory,filterDifficulty,sortBy"
             class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Catalog</h2>
                <span class="text-xs text-slate-500">{{ $courses->total() }} courses</span>
            </div>

            @if($courses->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-100 bg-slate-50 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Course</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Instructor</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Students</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Lessons</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            @foreach($courses as $course)
                                <tr wire:key="course-{{ $course->id }}" class="hover:bg-slate-50 dark:hover:bg-zinc-800/40">
                                    <td class="px-4 py-2.5">
                                        <a href="{{ route('courses.show', $course) }}" wire:navigate
                                           class="font-medium text-slate-900 hover:text-orange-600 dark:text-white dark:hover:text-orange-400">
                                            {{ $course->title }}
                                        </a>
                                        @if($course->short_description)
                                            <p class="mt-0.5 line-clamp-1 text-xs text-slate-400">{{ $course->short_description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">
                                        {{ $course->instructor->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <div class="flex flex-wrap gap-1">
                                            <span class="rounded px-2 py-0.5 text-xs font-medium {{ $course->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                                {{ $course->is_published ? 'Live' : 'Draft' }}
                                            </span>
                                            @if($course->approval_status)
                                                <span class="rounded px-2 py-0.5 text-xs font-medium
                                                    {{ $course->approval_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($course->approval_status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600') }}">
                                                    {{ ucfirst($course->approval_status) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">{{ number_format($course->enrollments_count) }}</td>
                                    <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">{{ number_format($course->lessons_count) }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('courses.show', $course) }}" wire:navigate
                                               class="rounded-md px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30">
                                                Open
                                            </a>
                                            @can('update', $course)
                                                <a href="{{ route('courses.edit', $course) }}" wire:navigate
                                                   class="rounded-md px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-800">
                                                    Edit
                                                </a>
                                            @endcan
                                            @if(!$isIctTeacher)
                                                <a href="{{ route('curriculum.builder', ['course' => $course->id]) }}" wire:navigate
                                                   class="rounded-md px-2 py-1 text-xs font-medium text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-950/30">
                                                    Builder
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-4 py-3 dark:border-zinc-800">
                    {{ $courses->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-medium text-slate-900 dark:text-white">No courses found</p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $hasFilters ? 'Try adjusting your filters.' : 'Create your first course to get started.' }}
                    </p>
                    @if(!$isIctTeacher)
                        @can('create', \App\Models\Course::class)
                            <flux:button href="{{ route('courses.create') }}" variant="primary" size="sm"
                                class="mt-4 !bg-orange-600 hover:!bg-orange-700" wire:navigate>
                                New course
                            </flux:button>
                        @endcan
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
