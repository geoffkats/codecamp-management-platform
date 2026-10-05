<div class="mx-auto flex w-full max-w-7xl flex-col gap-5 p-4 pb-28 sm:p-6 sm:pb-28">
    @php
        $selectedCourse = $courseFilter ? $courses->firstWhere('id', (int) $courseFilter) : null;
        $showRank = $sortBy === 'total_points' && $sortDirection === 'desc' && $search === '';
        $sortIcon = fn ($field) => $sortBy === $field ? ($sortDirection === 'asc' ? '↑' : '↓') : '';
    @endphp

    {{-- Header --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $canManageAllXp ? 'XP Manager' : 'Award XP' }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-zinc-400">Reward effort with XP. Pick a class to award everyone at once, or award one student from the list.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="openCourseBulkModal({{ $courseFilter ? (int) $courseFilter : 'null' }})"
                    class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                <flux:icon.sparkles class="size-4" />
                {{ $selectedCourse ? 'Award this class' : 'Award a whole class' }}
            </button>
            @if($canManageAllXp)
                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                    <button type="button" @click="open = ! open" aria-label="More XP tools"
                            class="inline-flex size-10 items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        <flux:icon.ellipsis-horizontal class="size-5" />
                    </button>
                    <div x-show="open" x-cloak x-transition.origin.top.right
                         class="absolute right-0 z-30 mt-2 w-60 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                        <button type="button" wire:click="syncAllLevels" wire:confirm="Recalculate every student's level and rank from their total XP?" @click="open = false"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            <flux:icon.arrow-path class="size-4 text-gray-400" /> Recalculate levels
                        </button>
                        <div class="my-1 border-t border-gray-100 dark:border-zinc-800"></div>
                        <p class="px-4 pt-1 pb-1 text-[11px] font-semibold uppercase tracking-wide text-red-500">Danger zone</p>
                        <button type="button" wire:click="openResetModal('week')" @click="open = false"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon.backward class="size-4" /> Remove this week's XP
                        </button>
                        <button type="button" wire:click="openResetModal('all')" @click="open = false"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon.trash class="size-4" /> Reset all XP to zero
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </header>

    @if (session('message'))
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-300">
            <flux:icon.check-circle class="size-5 shrink-0" /> {{ session('message') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300">
            <flux:icon.exclamation-circle class="size-5 shrink-0" /> {{ session('error') }}
        </div>
    @endif

    {{-- Summary --}}
    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach([
            ['Students', number_format($totalStudents), 'users', 'bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400'],
            ['XP earned in total', number_format($totalXp), 'star', 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'],
            ['Average per student', number_format($avgXp ?? 0, 0), 'chart-bar', 'bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400'],
        ] as [$label, $value, $icon, $tone])
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $tone }}"><flux:icon :name="$icon" class="size-5" /></span>
                <div>
                    <p class="text-sm text-gray-500 dark:text-zinc-400">{{ $label }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
                </div>
            </div>
        @endforeach
    </section>

    {{-- Students --}}
    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 lg:flex-row lg:items-center dark:border-zinc-800">
            <label class="relative flex-1">
                <span class="sr-only">Search students</span>
                <flux:icon.magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or email"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-sm focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
            </label>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3 lg:flex">
                <select wire:model.live="courseFilter" aria-label="Class"
                        class="rounded-xl border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-100 lg:w-56 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    <option value="">All classes</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </select>
                <select wire:model.live="timeFilter" aria-label="Period"
                        class="rounded-xl border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    <option value="all">All time</option>
                    <option value="today">Today</option>
                    <option value="week">This week</option>
                    <option value="month">This month</option>
                </select>
                <select wire:model.live="sortBy" aria-label="Sort by"
                        class="rounded-xl border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    <option value="total_points">Most XP</option>
                    <option value="level">Highest level</option>
                    <option value="name">Name</option>
                    <option value="period_xp">XP this period</option>
                    @if($courseFilter)
                        <option value="course_xp">XP in this class</option>
                    @endif
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs font-medium text-gray-500 dark:border-zinc-800 dark:text-zinc-400">
                        <th class="w-10 py-3 pl-4">
                            <input type="checkbox" wire:click="$toggle('selectAll')" @checked($selectAll) aria-label="Select all on this page"
                                   class="rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                        </th>
                        <th class="px-3 py-3">Student</th>
                        <th class="px-3 py-3"><button type="button" wire:click="sortBy('total_points')" class="hover:text-gray-900 dark:hover:text-white">XP {{ $sortIcon('total_points') }}</button></th>
                        <th class="px-3 py-3"><button type="button" wire:click="sortBy('level')" class="hover:text-gray-900 dark:hover:text-white">Level {{ $sortIcon('level') }}</button></th>
                        @if($timeFilter !== 'all')
                            <th class="px-3 py-3"><button type="button" wire:click="sortBy('period_xp')" class="hover:text-gray-900 dark:hover:text-white">{{ ['today' => 'Today', 'week' => 'This week', 'month' => 'This month'][$timeFilter] ?? 'Period' }} {{ $sortIcon('period_xp') }}</button></th>
                        @endif
                        @if($courseFilter)
                            <th class="px-3 py-3"><button type="button" wire:click="sortBy('course_xp')" class="hover:text-gray-900 dark:hover:text-white">In this class {{ $sortIcon('course_xp') }}</button></th>
                        @endif
                        <th class="py-3 pl-3 pr-4 text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                    @forelse($students as $student)
                        @php
                            $xp = (int) ($student->points->total_points ?? 0);
                            $lvl = \App\Support\LevelSystem::info($xp);
                            $position = ($students->currentPage() - 1) * $students->perPage() + $loop->iteration;
                            $isSelected = in_array((string) $student->id, array_map('strval', $selectedStudents), true);
                        @endphp
                        <tr wire:key="xp-student-{{ $student->id }}" @class(['transition', 'bg-orange-50/60 dark:bg-orange-900/10' => $isSelected, 'hover:bg-gray-50 dark:hover:bg-zinc-800/50' => ! $isSelected])>
                            <td class="py-3 pl-4">
                                <input type="checkbox" wire:model.live="selectedStudents" value="{{ $student->id }}" aria-label="Select {{ $student->name }}"
                                       class="rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-3">
                                    @if($showRank && $position <= 3)
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ [1 => 'bg-amber-100 text-amber-700', 2 => 'bg-gray-200 text-gray-700', 3 => 'bg-orange-100 text-orange-700'][$position] }}">{{ $position }}</span>
                                    @else
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $student->initials() }}</span>
                                    @endif
                                    <div class="min-w-0">
                                        <button type="button" wire:click="openDetailsModal({{ $student->id }})" class="block truncate text-left font-medium text-gray-900 hover:text-orange-600 dark:text-white">{{ $student->name }}</button>
                                        <p class="truncate text-xs text-gray-500 dark:text-zinc-400">{{ $student->email ?: $student->student_id }}</p>
                                    </div>
                                    @if($student->points?->xp_multiplier)
                                        <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300" title="XP boost">{{ $student->points->xp_multiplier }}× boost</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center gap-1 font-semibold text-gray-900 dark:text-white">
                                    <flux:icon.star variant="solid" class="size-4 text-amber-400" /> {{ number_format($xp) }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="w-36">
                                    <div class="flex items-baseline justify-between gap-2 text-xs">
                                        <span class="font-semibold text-gray-900 dark:text-white">Lv {{ $lvl['level'] }}</span>
                                        <span class="truncate text-gray-500 dark:text-zinc-400">{{ $lvl['name'] }}</span>
                                    </div>
                                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-800" title="{{ $lvl['xp_to_next_level'] }} XP to next level">
                                        <div class="h-full rounded-full" style="width: {{ $lvl['level_progress'] }}%; background: {{ $lvl['hex'] }}"></div>
                                    </div>
                                </div>
                            </td>
                            @if($timeFilter !== 'all')
                                <td class="px-3 py-3 font-medium {{ $student->period_xp > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">+{{ number_format($student->period_xp) }}</td>
                            @endif
                            @if($courseFilter)
                                <td class="px-3 py-3 font-medium text-gray-900 dark:text-white">{{ number_format($student->course_xp) }}</td>
                            @endif
                            <td class="py-3 pl-3 pr-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" wire:click="openDetailsModal({{ $student->id }})" class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-zinc-300 dark:hover:bg-zinc-800">History</button>
                                    <button type="button" wire:click="openEditModal({{ $student->id }})" class="rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 hover:bg-orange-100 dark:bg-orange-900/30 dark:text-orange-300">Award</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-14 text-center">
                                <flux:icon.users class="mx-auto size-8 text-gray-300" />
                                <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No students found</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">Try another class or clear the search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="border-t border-gray-100 px-4 py-3 dark:border-zinc-800">{{ $students->links() }}</div>
        @endif
    </section>

    {{-- Bulk bar --}}
    @if(! empty($selectedStudents))
        <div class="fixed inset-x-0 bottom-4 z-30 flex justify-center px-4">
            <div class="flex w-full max-w-4xl flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-2xl sm:flex-row sm:items-center dark:border-zinc-700 dark:bg-zinc-900">
                <p class="shrink-0 px-2 text-sm font-semibold text-gray-900 dark:text-white">{{ count($selectedStudents) }} selected</p>
                <div class="flex flex-1 flex-wrap items-center gap-2">
                    <select wire:model="bulkCourseId" aria-label="Class for XP" class="min-w-[11rem] flex-1 rounded-xl border border-gray-200 py-2 pl-3 pr-8 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <option value="">Choose class</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <select wire:model="bulkOperation" aria-label="Add or remove" class="rounded-xl border border-gray-200 py-2 pl-3 pr-8 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <option value="add">Add</option>
                        <option value="subtract">Remove</option>
                    </select>
                    <input type="number" wire:model="bulkPoints" min="1" placeholder="XP" aria-label="XP amount"
                           class="w-24 rounded-xl border border-gray-200 py-2 px-3 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    <button type="button" wire:click="bulkUpdateXp" class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">Apply</button>
                    <button type="button" wire:click="$set('selectedStudents', [])" class="rounded-xl px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-zinc-300 dark:hover:bg-zinc-800">Clear</button>
                </div>
                @error('bulkCourseId')<p class="w-full px-2 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    @if($showEditModal && $editingUser)
        <flux:modal name="edit-xp" :show="$showEditModal" wire:model="showEditModal" class="!max-w-3xl">
            <div class="p-6 space-y-6">
                    <!-- Header with User Info -->
                    <div class="flex items-center gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="w-16 h-16 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                            <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $editingUser->initials() }}</span>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $editingUser->name }}</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $editingUser->email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Current Stats</p>
                            @php $editInfo = \App\Support\LevelSystem::info($editingUser->points->total_points ?? 0); @endphp
                            <div class="flex items-center gap-2 mt-1 justify-end flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 rounded-full text-xs font-semibold text-yellow-700 dark:text-yellow-300">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ number_format($editingUser->points->total_points ?? 0) }} XP
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 dark:bg-purple-900/30 rounded-full text-xs font-semibold text-purple-700 dark:text-purple-300">
                                    Lv {{ $editInfo['level'] }} · {{ $editInfo['name'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Award XP to a course -->
                    @php
                        $selectedEnrollment = $editingEnrollments->firstWhere('course_id', (int) $awardCourseId);
                    @endphp
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Award XP to a course
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            XP is attached to the class you pick. This Week / This Month ranks only count their current class.
                        </p>
                        @if($editingEnrollments->isEmpty())
                            <div class="rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800 p-4 text-sm text-amber-800 dark:text-amber-200">
                                This student has no course enrollments. Enroll them in a class before awarding XP.
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Course</label>
                                    <select wire:model.live="awardCourseId" class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                        <option value="">Choose a course</option>
                                        @foreach($editingEnrollments as $enrollment)
                                            <option value="{{ $enrollment->course_id }}">
                                                {{ $enrollment->course?->title ?? 'Course #'.$enrollment->course_id }}
                                                @if($enrollment->completed_at)
                                                    (finished)
                                                @else
                                                    (current class)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('awardCourseId')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                                    @if($selectedEnrollment?->completed_at)
                                        <p class="text-xs text-amber-700 dark:text-amber-300 mt-2">
                                            This class is finished. XP still adds to All Time, not this week's class board.
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Action</label>
                                    <select wire:model="awardOperation" class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                        <option value="add">Add XP</option>
                                        <option value="subtract">Remove XP</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Points</label>
                                    <input type="number" min="1" max="10000" wire:model="awardPoints" placeholder="e.g. 50"
                                        class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                    @error('awardPoints')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason (optional)</label>
                                    <input type="text" wire:model="awardReason" maxlength="255" placeholder="Bonus, correction, event..."
                                        class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex justify-end mt-4">
                                <flux:button type="button" wire:click="awardStudentXp" variant="primary" :disabled="!$awardCourseId">
                                    Award to this course
                                </flux:button>
                            </div>
                            @if($awardMessage)
                                <div class="mt-3 rounded-lg border border-green-200 bg-green-50 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-200 px-4 py-2 text-sm">
                                    {{ $awardMessage }}
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Bonus Multiplier Section -->
                    @if($canManageAllXp)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            XP Multiplier Bonus
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">(Optional)</span>
                        </h3>
                        <div class="bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800 rounded-lg p-4 mb-4">
                            <p class="text-sm text-orange-800 dark:text-orange-200">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                Grant temporary XP boost (e.g., 1.5x = 50% bonus, 2x = double XP)
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Multiplier Value
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-normal block mt-0.5">Leave empty for none</span>
                                </label>
                                <div class="relative">
                                    <input type="number" min="0" step="0.1" wire:model.defer="editForm.xp_multiplier" 
                                        placeholder="e.g., 1.5" 
                                        class="w-full pl-10 pr-12 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <svg class="w-5 h-5 text-orange-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="absolute right-3 top-3 text-sm text-gray-500">×</span>
                                </div>
                                @error('editForm.xp_multiplier')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Expires At
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-normal block mt-0.5">When boost ends</span>
                                </label>
                                <div class="relative">
                                    <input type="datetime-local" wire:model.defer="editForm.multiplier_expires_at" 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <svg class="w-5 h-5 text-orange-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                @error('editForm.multiplier_expires_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Reason/Note
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-normal block mt-0.5">Why this bonus?</span>
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model.defer="editForm.multiplier_reason" 
                                        placeholder="e.g., Top performer" 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <svg class="w-5 h-5 text-orange-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                </div>
                                @error('editForm.multiplier_reason')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Changes apply immediately. Class ranks use the course you selected above.
                        </p>
                        <div class="flex items-center gap-3">
                            <flux:button type="button" wire:click="closeEditModal" variant="subtle">
                                Done
                            </flux:button>
                            @if($canManageAllXp)
                            <flux:button type="button" wire:click="saveEdit" variant="primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Save multiplier
                            </flux:button>
                            @endif
                        </div>
                    </div>
                </div>
        </flux:modal>
    @endif

    <!-- Student XP Details & Audit Modal -->
    @if($showDetailsModal && $detailsUser)
        <flux:modal name="xp-details" :show="$showDetailsModal" wire:model="showDetailsModal" class="!max-w-4xl">
            <div class="p-6 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                            <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $detailsUser->initials() }}</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $detailsUser->name }}</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $detailsUser->email }}</p>
                        </div>
                    </div>
                    <button wire:click="closeDetailsModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Current Stats Summary -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900 rounded-xl p-4">
                        <p class="text-xs font-medium text-yellow-700 dark:text-yellow-300 uppercase tracking-wide">Total XP</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100 mt-1">
                            {{ number_format($detailsUser->points->total_points ?? 0) }}
                        </p>
                    </div>
                    <div class="bg-violet-50 dark:bg-violet-900/20 border border-violet-100 dark:border-violet-900 rounded-xl p-4">
                        @php $detailsInfo = \App\Support\LevelSystem::info($detailsUser->points->total_points ?? 0); @endphp
                        <p class="text-xs font-medium text-purple-700 dark:text-purple-300 uppercase tracking-wide">Level · Rank</p>
                        <p class="text-2xl font-bold text-purple-900 dark:text-purple-100 mt-1">
                            {{ $detailsInfo['level'] }}
                        </p>
                        <p class="text-sm font-semibold text-purple-700 dark:text-purple-300 mt-0.5">{{ $detailsInfo['name'] }}</p>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-900 rounded-xl p-4">
                        <p class="text-xs font-medium text-green-700 dark:text-green-300 uppercase tracking-wide">XP Activities</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100 mt-1">
                            {{ count($xpHistory) }}
                        </p>
                    </div>
                    <div class="bg-sky-50 dark:bg-sky-900/20 border border-sky-100 dark:border-sky-900 rounded-xl p-4">
                        <p class="text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wide">Enrollments</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1">
                            {{ $detailsUser->enrollments->count() }}
                        </p>
                    </div>
                </div>

                <!-- XP Breakdown by Type -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        XP Breakdown
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @php
                            $breakdown = collect($xpHistory)->groupBy('type')->map(fn($items) => $items->sum('points'));
                        @endphp
                        @foreach(['course_enrolled' => 'Enrollments', 'lesson_completed' => 'Lessons', 'course_completed' => 'Completions', 'admin_award' => 'Staff awards'] as $type => $label)
                            <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $label }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ number_format($breakdown->get($type, 0)) }} XP
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    {{ collect($xpHistory)->where('type', $type)->count() }} activities
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Complete XP History/Audit Trail -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Complete XP Audit Trail
                    </h3>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="max-h-96 overflow-y-auto">
                            @forelse($xpHistory as $activity)
                                <div class="flex items-start gap-4 p-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition">
                                    <!-- Icon based on type -->
                                    <div class="flex-shrink-0 mt-1">
                                        @if($activity['type'] === 'course_enrolled')
                                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                        @elseif($activity['type'] === 'lesson_completed')
                                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        @elseif($activity['type'] === 'course_completed')
                                            <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                </svg>
                                            </div>
                                        @elseif($activity['type'] === 'admin_award')
                                            <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-900/30 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Activity Details -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ ucwords(str_replace('_', ' ', $activity['type'])) }}
                                                </p>
                                                @if($activity['course'])
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                        </svg>
                                                        {{ $activity['course'] }}
                                                    </p>
                                                @endif
                                                @if($activity['lesson'])
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                        </svg>
                                                        {{ $activity['lesson'] }}
                                                    </p>
                                                @endif
                                                @if(!empty($activity['metadata']['reason']))
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ $activity['metadata']['reason'] }}</p>
                                                @endif
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $activity['date']->format('M d, Y \a\t g:i A') }}
                                                    <span class="text-gray-400 mx-1">•</span>
                                                    {{ $activity['date']->diffForHumans() }}
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0 text-right">
                                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-bold {{ $activity['points'] >= 0 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' }}">
                                                    {{ $activity['points'] >= 0 ? '+' : '' }}{{ $activity['points'] }}
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-12 text-center">
                                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">No XP activity found for this student</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button wire:click="openEditModal({{ $detailsUser->id }})" variant="outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Award XP
                    </flux:button>
                    <flux:button wire:click="closeDetailsModal" variant="subtle">
                        Close
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    @if($showCourseBulkModal)
        <flux:modal wire:model.live="showCourseBulkModal" max-width="lg">
            <div class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Award XP to Course</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Give points to every student currently in this class.</p>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Course</label>
                        <select wire:model.live="courseBulkCourseId" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select a course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Points to award</label>
                            <input type="number" min="1" max="10000" wire:model.live="courseBulkPoints" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="e.g. 50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason (optional)</label>
                            <input type="text" wire:model.live="courseBulkReason" maxlength="255" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Bonus, event, reward...">
                        </div>
                    </div>

                    @if($courseBulkCourseId)
                        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 p-4 text-sm text-blue-800 dark:text-blue-200">
                            Students currently in this class will receive the XP. It is logged against this course so week/month ranks can count it.
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3">
                    <flux:button wire:click="closeCourseBulkModal" variant="subtle">Cancel</flux:button>
                    <flux:button wire:click="awardCourseXp" variant="primary" :disabled="!$courseBulkCourseId || $courseBulkPoints <= 0">
                        Award XP
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    <!-- Reset Confirmation Modal -->
    @if($showResetModal)
        <flux:modal name="reset-xp" :show="$showResetModal" wire:model="showResetModal">
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2 text-center">Confirm Reset</h2>
                
                @if($resetType === 'all')
                    <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                        This will <strong class="text-red-600">reset ALL student XP to 0</strong> and delete all progress records. This action cannot be undone!
                    </p>
                @elseif($resetType === 'week')
                    <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                        This will remove all XP earned <strong>this week</strong> and delete the associated progress records.
                    </p>
                @elseif($resetType === 'course')
                    <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                        This will remove all XP earned from <strong>the selected course</strong> and delete the associated progress records.
                    </p>
                @endif

                <div class="flex items-center justify-center gap-3">
                    <flux:button wire:click="closeResetModal" variant="subtle">Cancel</flux:button>
                    <flux:button wire:click="confirmReset" variant="danger">
                        Yes, Reset XP
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
