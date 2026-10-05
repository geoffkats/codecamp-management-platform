@php
    $hasFilters = $search !== '' || $course !== '' || $type !== '';
    $tabs = [
        'all' => ['label' => 'All', 'count' => $stats['total']],
        'grading' => ['label' => 'Needs grading', 'count' => $stats['grading']],
        'empty' => ['label' => 'No questions yet', 'count' => $stats['empty']],
    ];
    $select = 'w-full rounded-xl border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-700 ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700';
@endphp

<div class="max-w-7xl mx-auto px-4 py-5 space-y-5">

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-10 -top-16 size-56 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute right-28 -bottom-20 size-40 rounded-full bg-white/10"></div>

        <div class="relative px-6 pt-6 pb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-orange-100 text-xs font-semibold uppercase tracking-widest mb-1">Teaching</p>
                <h1 class="text-3xl font-extrabold leading-tight">Assessments</h1>
                <p class="text-orange-100 text-sm mt-1 max-w-xl">Every quiz, test and assignment in your courses, in one place.</p>
            </div>
            <div class="flex flex-wrap gap-2 self-start">
                <a href="{{ route('questions.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/30 transition hover:bg-white/25">
                    <flux:icon name="archive-box" variant="mini" class="size-5" /> Question Bank
                </a>
                <a href="{{ route('assessments.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-orange-600 shadow-md transition hover:-translate-y-0.5 hover:shadow-lg">
                    <flux:icon name="plus" variant="mini" class="size-5" /> New assessment
                </a>
            </div>
        </div>

        <div class="relative grid grid-cols-2 sm:grid-cols-4 gap-3 px-6 pb-6">
            @foreach([
                ['label' => 'Quizzes & tests', 'value' => $stats['quizzes'], 'icon' => 'bolt', 'action' => null],
                ['label' => 'Assignments', 'value' => $stats['assignments'], 'icon' => 'clipboard-document-list', 'action' => "\$set('type', 'assignment')"],
                ['label' => 'Needs grading', 'value' => $stats['grading'], 'icon' => 'pencil-square', 'action' => "\$set('view', 'grading')"],
                ['label' => 'No questions yet', 'value' => $stats['empty'], 'icon' => 'exclamation-triangle', 'action' => "\$set('view', 'empty')"],
            ] as $card)
                <button type="button" @if($card['action']) wire:click="{{ $card['action'] }}" @endif
                        class="rounded-xl bg-white/15 px-4 py-3 text-left transition hover:bg-white/25">
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-orange-100">{{ $card['label'] }}</p>
                        <flux:icon :name="$card['icon']" variant="mini" class="size-4 text-orange-200" />
                    </div>
                    <p class="text-2xl font-extrabold leading-tight mt-0.5">{{ number_format($card['value']) }}</p>
                </button>
            @endforeach
        </div>
    </div>

    @if(session('message'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            <flux:icon name="check-circle" variant="mini" class="size-5 text-emerald-500" />
            {{ session('message') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm p-4 space-y-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <flux:icon name="magnifying-glass" variant="mini" class="absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-gray-400" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Find an assessment by name..."
                       class="w-full rounded-xl border-0 bg-gray-50 py-2.5 pl-11 pr-4 text-sm text-gray-900 ring-1 ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
            </div>
            <div class="grid grid-cols-2 gap-2 lg:w-[26rem]">
                <select wire:model.live="course" class="{{ $select }}">
                    <option value="">All courses</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->title }}</option>
                    @endforeach
                </select>
                <select wire:model.live="type" class="{{ $select }}">
                    <option value="">All types</option>
                    @foreach($types as $value => $meta)
                        <option value="{{ $value }}">{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @foreach($tabs as $value => $tab)
                <button type="button" wire:click="$set('view', '{{ $value }}')" @class([
                    'inline-flex items-center gap-1.5 rounded-full px-3.5 py-1 text-xs font-semibold transition',
                    'bg-orange-500 text-white shadow-sm' => $view === $value,
                    'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-700 dark:bg-gray-800 dark:text-gray-300' => $view !== $value,
                ])>
                    {{ $tab['label'] }}
                    <span @class(['rounded-full px-1.5 text-[10px]', 'bg-white/25' => $view === $value, 'bg-white text-gray-500 dark:bg-gray-700 dark:text-gray-300' => $view !== $value])>{{ $tab['count'] }}</span>
                </button>
            @endforeach
            @if($hasFilters || $view !== 'all')
                <button type="button" wire:click="clearFilters" class="ml-auto text-xs font-semibold text-orange-600 hover:text-orange-700">Clear filters</button>
            @endif
        </div>
    </div>

    {{-- List --}}
    @if($assessments->count() > 0)
        <div class="space-y-3">
            @foreach($assessments as $assessment)
                @php
                    $meta = $types[$assessment->assessment_type] ?? ['label' => ucwords(str_replace('_', ' ', $assessment->assessment_type)), 'icon' => 'document-text', 'tile' => 'bg-gray-100 text-gray-600'];
                    $isAssignment = $assessment->assessment_type === 'assignment';
                    $completed = (int) $assessment->completed_attempts_count;
                    $passRate = $completed > 0 ? round($assessment->passed_attempts_count / $completed * 100) : null;
                    $noQuestions = ! $isAssignment && (int) $assessment->questions_count === 0;
                    $builderUrl = route('curriculum.builder', ['course' => $assessment->course_id, 'assessment' => $assessment->id]);
                @endphp
                <div wire:key="assessment-{{ $assessment->id }}" class="group flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md hover:ring-orange-200 dark:bg-gray-900 dark:ring-gray-800 md:flex-row md:items-center">
                    <div class="flex min-w-0 flex-1 items-start gap-4">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl {{ $meta['tile'] }}">
                            <flux:icon :name="$meta['icon']" class="size-6" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                <span class="text-[11px] font-bold uppercase tracking-wide text-gray-400">{{ $meta['label'] }}</span>
                                @if($assessment->pending_attempts_count > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                        <span class="size-1.5 rounded-full bg-rose-500"></span>{{ $assessment->pending_attempts_count }} to grade
                                    </span>
                                @endif
                                @if($noQuestions)
                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">No questions yet</span>
                                @endif
                                @if($assessment->is_locked)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600"><flux:icon name="lock-closed" variant="micro" class="size-3" />Locked</span>
                                @endif
                            </div>
                            <a href="{{ route('assessments.edit', $assessment) }}" wire:navigate
                               class="block truncate text-[15px] font-semibold text-gray-900 hover:text-orange-600 dark:text-white dark:hover:text-orange-400">{{ $assessment->title }}</a>
                            <div class="mt-1 flex flex-wrap items-center gap-x-1.5 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1 font-medium text-gray-600 dark:text-gray-300"><flux:icon name="academic-cap" variant="micro" class="size-3.5 text-orange-400" />{{ $assessment->course?->title ?? 'No course' }}</span>
                                @if($assessment->lesson?->module)
                                    <span class="text-gray-300">/</span><span>{{ $assessment->lesson->module->title }}</span>
                                @endif
                                @if($assessment->lesson)
                                    <span class="text-gray-300">/</span><span class="truncate max-w-[16rem]">{{ $assessment->lesson->title }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center md:w-72">
                        <div class="rounded-xl bg-gray-50 px-2 py-2 dark:bg-gray-800">
                            @if(! $isAssignment && $assessment->drawsFromPool((int) $assessment->questions_count))
                                <p class="text-lg font-extrabold leading-none text-orange-600" title="Each attempt draws {{ $assessment->questions_per_attempt }} random questions from a pool of {{ $assessment->questions_count }}">
                                    {{ $assessment->questions_per_attempt }}<span class="text-xs font-bold text-gray-400"> / {{ $assessment->questions_count }}</span>
                                </p>
                                <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-orange-500">Random pool</p>
                            @else
                                <p class="text-lg font-extrabold leading-none text-gray-900 dark:text-white">{{ $isAssignment ? '—' : $assessment->questions_count }}</p>
                                <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400">Questions</p>
                            @endif
                        </div>
                        <div class="rounded-xl bg-gray-50 px-2 py-2 dark:bg-gray-800">
                            <p class="text-lg font-extrabold leading-none text-gray-900 dark:text-white">{{ $completed }}</p>
                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400">Attempts</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 px-2 py-2 dark:bg-gray-800">
                            <p @class(['text-lg font-extrabold leading-none', 'text-gray-300 dark:text-gray-600' => $passRate === null, 'text-emerald-600' => $passRate !== null && $passRate >= 70, 'text-amber-600' => $passRate !== null && $passRate >= 40 && $passRate < 70, 'text-rose-600' => $passRate !== null && $passRate < 40])>{{ $passRate === null ? '—' : $passRate.'%' }}</p>
                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400">Passed</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5 md:justify-end">
                        @if($assessment->pending_attempts_count > 0)
                            <a href="{{ route('assessments.show', $assessment) }}" wire:navigate
                               class="inline-flex items-center gap-1.5 rounded-xl bg-rose-500 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-rose-600">
                                <flux:icon name="pencil-square" variant="micro" class="size-4" /> Grade
                            </a>
                        @endif
                        <a href="{{ route('assessments.edit', $assessment) }}" wire:navigate
                           class="inline-flex items-center gap-1.5 rounded-xl bg-orange-500 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-orange-600">
                            <flux:icon name="pencil" variant="micro" class="size-4" /> {{ $noQuestions ? 'Add questions' : 'Edit' }}
                        </a>
                        <flux:dropdown position="bottom" align="end">
                            <button type="button" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800" aria-label="More actions">
                                <flux:icon name="ellipsis-vertical" variant="mini" class="size-5" />
                            </button>
                            <flux:menu>
                                <flux:menu.item href="{{ route('assessments.show', $assessment) }}" wire:navigate icon="chart-bar">Results &amp; submissions</flux:menu.item>
                                <flux:menu.item href="{{ $builderUrl }}" wire:navigate icon="squares-2x2">Open in curriculum builder</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $assessments->links() }}</div>
    @else
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-dashed border-gray-200 dark:border-gray-700 px-6 py-16 text-center">
            <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-2xl bg-orange-100 text-orange-500 dark:bg-orange-900/30">
                <flux:icon :name="$hasFilters || $view !== 'all' ? 'magnifying-glass' : 'clipboard-document-check'" class="size-8" />
            </div>
            @if($hasFilters || $view !== 'all')
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Nothing matches</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $view === 'grading' ? 'All caught up — no attempts are waiting to be graded.' : 'Try a different search or clear the filters.' }}
                </p>
                <button type="button" wire:click="clearFilters" class="mt-4 rounded-xl bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-700 hover:bg-orange-100">Show all assessments</button>
            @else
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">No assessments yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a quiz or assignment for one of your courses.</p>
                <a href="{{ route('assessments.create') }}" wire:navigate class="mt-4 inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-orange-600">
                    <flux:icon name="plus" variant="mini" class="size-5" /> New assessment
                </a>
            @endif
        </div>
    @endif
</div>
