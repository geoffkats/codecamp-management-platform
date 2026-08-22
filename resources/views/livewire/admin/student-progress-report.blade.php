@php
    $hasFilters = $search || $filterCourse !== 'all' || $filterProgram !== 'all' || $filterCamp !== 'all' || $filterCampStatus !== 'all';
    $showCampColumn = $filterCamp === 'all';
    $activeCamps = $campOptions->where('status', 'active');
    $emptyMetrics = [
        'courses_enrolled' => 0,
        'completion_rate' => 0,
        'lessons_completed' => 0,
        'assessments_attempted' => 0,
        'avg_assessment_score' => 0,
        'badges_earned' => 0,
        'last_activity_at' => null,
    ];
    $pageMetrics = collect($metrics);
    $pageAvgCompletion = $pageMetrics->isNotEmpty()
        ? round($pageMetrics->avg('completion_rate'), 1)
        : 0;
    $pageTotalAssessments = $pageMetrics->sum('assessments_attempted');
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">

    {{-- Header --}}
    <div class="border-b-4 border-blue-600 bg-orange-600">
        <div class="mx-auto max-w-6xl px-4 py-5 sm:px-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-white">Student Progress</h1>
                    <p class="mt-0.5 text-sm text-orange-100">Enrollment, lessons, assessments, and badges</p>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button icon="arrow-down-tray" size="sm"
                        class="!bg-blue-600 !text-white hover:!bg-blue-700"
                        wire:click="exportCsv"
                        wire:loading.attr="disabled"
                        wire:target="exportCsv">
                        <span wire:loading.remove wire:target="exportCsv">Export CSV</span>
                        <span wire:loading wire:target="exportCsv">Exporting…</span>
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl space-y-4 px-4 py-5 sm:px-6">

        @if(session()->has('message'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
                {{ session('message') }}
            </div>
        @endif

        {{-- Program tabs --}}
        <div class="flex flex-wrap gap-2">
            @foreach([
                'all' => 'All Programs',
                'codecamp' => 'Codecamp',
                'ict' => 'ICT',
            ] as $key => $label)
                <button type="button" wire:click="$set('filterProgram', '{{ $key }}')"
                    class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm font-medium transition
                        {{ $filterProgram === $key
                            ? 'border-orange-600 bg-orange-600 text-white'
                            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Active camp quick filters --}}
        @if($activeCamps->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-medium uppercase tracking-wide text-slate-500">Camps:</span>
                <button type="button" wire:click="selectCamp('all')"
                    class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-medium transition
                        {{ $filterCamp === 'all'
                            ? 'border-blue-600 bg-blue-600 text-white'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300' }}">
                    All camps
                </button>
                @foreach($activeCamps as $camp)
                    <button type="button" wire:click="selectCamp('{{ $camp->id }}')"
                        class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-medium transition
                            {{ (string) $filterCamp === (string) $camp->id
                                ? 'border-blue-600 bg-blue-600 text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300' }}">
                        {{ $camp->name }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Summary --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Matching Students</p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($summary['total']) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">This Page</p>
                <p class="mt-1 text-2xl font-bold text-blue-600">{{ number_format($students->count()) }}</p>
                <p class="text-xs text-slate-400">of {{ number_format($students->total()) }} total</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Avg Completion (page)</p>
                <p class="mt-1 text-2xl font-bold text-orange-600">{{ number_format($pageAvgCompletion, 1) }}%</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Assessments (page)</p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($pageTotalAssessments) }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 lg:items-end">
                <div class="sm:col-span-2">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search name, email, or student ID" />
                </div>
                <div>
                    <flux:select wire:model.live="filterCamp" label="Code camp">
                        <option value="all">All camps</option>
                        @foreach($campOptions as $campOption)
                            <option value="{{ $campOption->id }}">
                                {{ $campOption->name }} ({{ $campOption->status }})
                            </option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="filterCampStatus" label="Camp membership">
                        <option value="all">Any status</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="transferred">Transferred</option>
                        <option value="dropped">Dropped</option>
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="filterCourse" label="Course">
                        <option value="all">All courses</option>
                        @foreach($courseOptions as $courseOption)
                            <option value="{{ $courseOption->id }}">{{ $courseOption->title }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <flux:select wire:model.live="perPage" label="Per page">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </flux:select>
                    </div>
                    @if($hasFilters)
                        <flux:button variant="ghost" size="sm" wire:click="clearFilters">Clear</flux:button>
                    @endif
                </div>
            </div>
            @if($filterCamp !== 'all' || $filterCourse !== 'all')
                <p class="mt-2 text-xs text-blue-700 dark:text-blue-300">
                    @if($filterCamp !== 'all' && $filterCourse !== 'all')
                        Showing students in this camp with enrollments for the selected course. Metrics are camp- and course-scoped.
                    @elseif($filterCamp !== 'all')
                        Showing students enrolled in this camp. Metrics reflect progress tied to that camp's courses.
                    @else
                        Metrics scoped to the selected course only.
                    @endif
                </p>
            @endif
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto" wire:loading.class="opacity-60" wire:target="search,filterCourse,filterProgram,filterCamp,filterCampStatus,perPage,gotoPage,previousPage,nextPage">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-700">
                    <thead class="bg-slate-50 dark:bg-zinc-800/80">
                        <tr>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Student</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Program</th>
                            @if($showCampColumn)
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Camp</th>
                            @endif
                            <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Courses</th>
                            <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Lessons</th>
                            <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Progress</th>
                            <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Assessments</th>
                            <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Avg Score</th>
                            <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Badges</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Last Active</th>
                            <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        @forelse($students as $student)
                            @php
                                $studentMetrics = $metrics[$student->user_id] ?? $emptyMetrics;
                            @endphp
                            <tr class="hover:bg-orange-50/50 dark:hover:bg-orange-950/10">
                                <td class="px-3 py-2.5 align-top">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $student->full_name ?: $student->user?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $student->student_id ?: $student->user?->student_id ?: 'No ID' }} · {{ $student->class_grade ?: 'Unassigned' }}</p>
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    <span class="inline-flex rounded px-1.5 py-0.5 text-xs font-medium uppercase
                                        {{ $student->program_type === 'ict' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200' : 'bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-200' }}">
                                        {{ $student->program_type ?: '—' }}
                                    </span>
                                </td>
                                @if($showCampColumn)
                                    <td class="px-3 py-2.5 align-top text-xs text-slate-600 dark:text-slate-300">
                                        {{ $student->user?->currentCampEnrollment?->camp?->name ?: '—' }}
                                    </td>
                                @endif
                                <td class="px-3 py-2.5 text-right align-top text-sm font-medium text-slate-900 dark:text-white">{{ $studentMetrics['courses_enrolled'] }}</td>
                                <td class="px-3 py-2.5 text-right align-top text-sm text-slate-700 dark:text-slate-300">{{ $studentMetrics['lessons_completed'] }}</td>
                                <td class="px-3 py-2.5 text-right align-top">
                                    <span class="text-sm font-semibold {{ $studentMetrics['completion_rate'] >= 70 ? 'text-emerald-600' : ($studentMetrics['completion_rate'] >= 40 ? 'text-amber-600' : 'text-red-600') }}">
                                        {{ number_format($studentMetrics['completion_rate'], 1) }}%
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-right align-top text-sm text-slate-700 dark:text-slate-300">{{ $studentMetrics['assessments_attempted'] }}</td>
                                <td class="px-3 py-2.5 text-right align-top text-sm text-slate-700 dark:text-slate-300">
                                    {{ $studentMetrics['assessments_attempted'] > 0 ? number_format($studentMetrics['avg_assessment_score'], 1) . '%' : '—' }}
                                </td>
                                <td class="px-3 py-2.5 text-right align-top text-sm text-slate-700 dark:text-slate-300">{{ $studentMetrics['badges_earned'] }}</td>
                                <td class="px-3 py-2.5 align-top text-xs text-slate-500">
                                    @if($studentMetrics['last_activity_at'])
                                        {{ \Illuminate\Support\Carbon::parse($studentMetrics['last_activity_at'])->diffForHumans() }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-right align-top">
                                    <flux:button size="sm" variant="ghost"
                                        href="{{ route('admin.student-progress.show', $student->id) }}"
                                        wire:navigate>
                                        Details
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $showCampColumn ? 11 : 10 }}" class="px-4 py-10 text-center text-sm text-slate-500">
                                    No students match your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($students->hasPages())
            <div>{{ $students->links() }}</div>
        @endif
    </div>
</div>
