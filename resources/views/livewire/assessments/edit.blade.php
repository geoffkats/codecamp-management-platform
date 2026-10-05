@php
    $isAssignment = $assessment_type === 'assignment';
    $inputClass = 'w-full rounded-xl border-0 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700';
    $labelClass = 'mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400';
    $cardClass = 'rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800 sm:p-6';
    $canReview = auth()->user()->isAdmin() || auth()->user()->isTeacher();
    $poolSize = $questions->count();
    $perAttempt = $poolSize ? $assessment->questionsPerAttemptFor($poolSize) : 0;
    $isPool = $poolSize > 1 && $assessment->drawsFromPool($poolSize);
    $typeLabels = app(\App\Services\Assessments\QuestionTypeRegistry::class);
    $tabs = array_filter([
        'questions' => $isAssignment ? null : ['Questions', 'queue-list', $poolSize],
        'settings' => ['Settings', 'adjustments-horizontal', null],
        'submissions' => $canReview ? ['Submissions', 'inbox-stack', $submissionStats['total']] : null,
    ]);
    $activeTab = array_key_exists($tab, $tabs) ? $tab : array_key_first($tabs);
    $facts = $isAssignment
        ? [
            ['star', ($assignment_max_points ?: 100).' pts', 'Max points'],
            ['calendar-days', $assignment_due_date ? \Illuminate\Support\Carbon::parse($assignment_due_date)->format('M j') : 'No due date', 'Due'],
            ['check-badge', ($passing_score ?? 0).'%', 'Pass mark'],
        ]
        : [
            ['queue-list', $isPool ? "{$perAttempt} of {$poolSize}" : $poolSize, $isPool ? 'Random draw' : 'Questions'],
            ['star', rtrim(rtrim(number_format((float) $totalPoints, 1), '0'), '.').' pts', 'Total'],
            ['check-badge', ($passing_score ?? 0).'%', 'Pass mark'],
            ['clock', $time_limit_minutes ? $time_limit_minutes.' min' : 'Untimed', 'Time'],
        ];
@endphp

<div class="{{ $this->embedded ? 'min-h-full bg-gray-50 dark:bg-gray-950' : 'mx-auto max-w-6xl p-4 sm:p-6' }}">

    @if($this->embedded)
        {{-- Embedded (curriculum builder) header --}}
        <div class="sticky top-0 z-20 border-b border-gray-200 bg-white/95 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
            <div class="flex items-center gap-3 px-5 py-2.5">
                <button wire:click="backToBuilder" type="button"
                        class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-bold text-gray-500 transition hover:bg-orange-50 hover:text-orange-600 dark:text-gray-400 dark:hover:bg-orange-900/20">
                    <flux:icon name="chevron-left" variant="micro" class="size-4" /> Lesson
                </button>
                <nav class="hidden min-w-0 items-center gap-1 text-xs text-gray-400 md:flex">
                    <span class="truncate">{{ $assessment->course->title }}</span>
                    @if($assessment->lesson)
                        <flux:icon name="chevron-right" variant="micro" class="size-3 shrink-0" />
                        <span class="truncate">{{ $assessment->lesson->title }}</span>
                    @endif
                </nav>
                <div class="ml-auto flex shrink-0 items-center gap-1.5">
                    <button type="button" wire:click="toggleLock"
                            @class([
                                'inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold ring-1 transition',
                                'bg-rose-50 text-rose-700 ring-rose-200 hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-300 dark:ring-rose-900' => $assessment->is_locked,
                                'bg-emerald-50 text-emerald-700 ring-emerald-200 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:ring-emerald-900' => ! $assessment->is_locked,
                            ])
                            title="{{ $assessment->is_locked ? 'Students cannot open it. Click to unlock.' : 'Students can open it. Click to lock.' }}">
                        <flux:icon :name="$assessment->is_locked ? 'lock-closed' : 'lock-open'" variant="micro" class="size-3.5" />
                        {{ $assessment->is_locked ? 'Locked' : 'Open to students' }}
                    </button>
                    <a href="{{ route('assessments.show', $assessment) }}" wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold text-gray-600 ring-1 ring-gray-200 transition hover:bg-gray-50 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-800">
                        <flux:icon name="chart-bar" variant="micro" class="size-3.5" /> Results
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="{{ $this->embedded ? 'space-y-5 p-5' : 'space-y-5' }}">

        {{-- Summary card --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-500 to-orange-600 p-5 text-white shadow-lg sm:p-6">
            <div class="pointer-events-none absolute -right-12 -top-16 size-52 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-20 right-32 size-40 rounded-full bg-white/10"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 ring-1 ring-white/30">
                        <flux:icon :name="$typeMeta['icon']" class="size-6" />
                    </span>
                    <div class="min-w-0">
                        @unless($this->embedded)
                            <a href="{{ route('assessments.manage') }}" wire:navigate class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-widest text-orange-100 hover:text-white">
                                <flux:icon name="arrow-left" variant="micro" class="size-3.5" /> Assessments
                            </a>
                        @endunless
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider ring-1 ring-white/25">{{ $typeMeta['label'] }}</span>
                            @if($is_required)
                                <span class="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider ring-1 ring-white/25">Required</span>
                            @endif
                            @if(! $this->embedded && $assessment->is_locked)
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-900/30 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider"><flux:icon name="lock-closed" variant="micro" class="size-3" /> Locked</span>
                            @endif
                        </div>
                        <h1 class="mt-1.5 truncate text-2xl font-extrabold leading-tight">{{ $title ?: 'Untitled assessment' }}</h1>
                        @unless($this->embedded)
                            <p class="truncate text-sm text-orange-100">{{ $assessment->course->title }}@if($assessment->lesson) · {{ $assessment->lesson->title }}@endif</p>
                        @endunless
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach($facts as [$icon, $value, $caption])
                        <div class="min-w-[92px] rounded-2xl bg-white/15 px-3.5 py-2 ring-1 ring-white/20">
                            <p class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-orange-100"><flux:icon :name="$icon" variant="micro" class="size-3" />{{ $caption }}</p>
                            <p class="text-base font-extrabold leading-tight">{{ $value }}</p>
                        </div>
                    @endforeach
                    @unless($this->embedded)
                        <a href="{{ route('curriculum.builder', ['course' => $assessment->course_id, 'assessment' => $assessment->id]) }}" wire:navigate
                           class="inline-flex items-center gap-1.5 self-center rounded-xl bg-white/15 px-3.5 py-2 text-xs font-bold ring-1 ring-white/30 transition hover:bg-white/25">
                            <flux:icon name="squares-2x2" variant="micro" class="size-4" /> In builder
                        </a>
                        <a href="{{ route('assessments.show', $assessment) }}" wire:navigate
                           class="inline-flex items-center gap-1.5 self-center rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-orange-600 shadow-sm transition hover:shadow-md">
                            <flux:icon name="chart-bar" variant="micro" class="size-4" /> Results
                        </a>
                    @endunless
                </div>
            </div>
        </div>

        @if(session()->has('message'))
            <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 px-4 py-3 ring-1 ring-emerald-200 dark:bg-emerald-900/20 dark:ring-emerald-900">
                <flux:icon name="check-circle" variant="mini" class="size-5 shrink-0 text-emerald-600" />
                <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">{{ session('message') }}</p>
            </div>
        @endif
        @if(session()->has('error'))
            <div class="flex items-center gap-3 rounded-2xl bg-rose-50 px-4 py-3 ring-1 ring-rose-200 dark:bg-rose-900/20 dark:ring-rose-900">
                <flux:icon name="exclamation-circle" variant="mini" class="size-5 shrink-0 text-rose-600" />
                <p class="text-sm font-semibold text-rose-800 dark:text-rose-200">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Tabs --}}
        <div class="flex flex-wrap items-center gap-1 rounded-2xl bg-white p-1.5 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800">
            @foreach($tabs as $key => [$label, $icon, $count])
                <button type="button" wire:click="setTab('{{ $key }}')" wire:key="tab-{{ $key }}"
                        @class([
                            'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition',
                            'bg-gray-900 text-white shadow-sm dark:bg-white dark:text-gray-900' => $activeTab === $key,
                            'text-gray-500 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' => $activeTab !== $key,
                        ])>
                    <flux:icon :name="$icon" variant="micro" class="size-4" />
                    {{ $label }}
                    @if($count !== null)
                        <span @class([
                            'rounded-full px-1.5 py-px text-[11px] font-extrabold',
                            'bg-white/20 text-white dark:bg-gray-900/10 dark:text-gray-900' => $activeTab === $key,
                            'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => $activeTab !== $key,
                        ])>{{ $count }}</span>
                    @endif
                    @if($key === 'submissions' && $submissionStats['pending'] > 0)
                        <span class="rounded-full bg-rose-500 px-1.5 py-px text-[11px] font-extrabold text-white" title="Waiting to be graded">{{ $submissionStats['pending'] }}</span>
                    @endif
                </button>
            @endforeach
            <div wire:loading.delay wire:target="setTab" class="ml-auto pr-3 text-xs font-semibold text-orange-600">Loading…</div>
        </div>

        {{-- ===================== QUESTIONS ===================== --}}
        @if($activeTab === 'questions')
            <section class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Questions</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $poolSize }} {{ \Illuminate\Support\Str::plural('question', $poolSize) }} · {{ rtrim(rtrim(number_format((float) $totalPoints, 1), '0'), '.') }} points in total
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="openBankPicker"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-gray-200 transition hover:ring-orange-300 hover:text-orange-600 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700">
                            <flux:icon name="archive-box" variant="micro" class="size-4" /> From Question Bank
                        </button>
                        <button type="button" wire:click="openQuestionModal"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-3.5 py-2 text-sm font-bold text-white shadow-sm transition hover:shadow-md">
                            <flux:icon name="plus" variant="micro" class="size-4" /> New question
                        </button>
                    </div>
                </div>

                @if($poolSize > 1)
                    <div class="rounded-2xl p-4 ring-1 {{ $isPool ? 'bg-orange-50 ring-orange-200 dark:bg-orange-900/10 dark:ring-orange-900/50' : 'bg-white ring-gray-100 dark:bg-gray-900 dark:ring-gray-800' }}"
                         x-data="{ count: {{ $assessment->questions_per_attempt ? min($assessment->questions_per_attempt, $poolSize) : max(1, (int) ceil($poolSize / 2)) }} }">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $isPool ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                    <flux:icon name="arrows-right-left" class="size-5" />
                                </span>
                                <div>
                                    <p class="text-sm font-extrabold text-gray-900 dark:text-white">
                                        {{ $isPool ? "Random pool: each student gets {$perAttempt} of {$poolSize} questions" : "Every student gets all {$poolSize} questions" }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $isPool ? 'Every attempt draws a fresh random set in a random order, so students can\'t copy each other or memorise the paper.' : 'Turn on a random pool so each attempt draws a different subset in random order.' }}
                                    </p>
                                    @error('questions_per_attempt') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="flex items-center gap-2 rounded-xl bg-white px-3 py-1.5 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700">
                                    <span class="text-xs font-semibold text-gray-500">Per attempt</span>
                                    <input type="number" min="1" max="{{ $poolSize }}" x-model.number="count"
                                           class="w-16 rounded-lg border-0 bg-gray-100 px-2 py-1 text-center text-sm font-extrabold text-gray-900 focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white">
                                    <span class="text-xs text-gray-400">of {{ $poolSize }}</span>
                                </div>
                                <button type="button" x-on:click="$wire.savePool(count)"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-orange-500 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-orange-600">
                                    <flux:icon name="sparkles" variant="micro" class="size-4" /> {{ $isPool ? 'Update pool' : 'Use random pool' }}
                                </button>
                                @if($isPool)
                                    <button type="button" wire:click="savePool(null)"
                                            class="rounded-xl px-3 py-2 text-xs font-bold text-gray-500 transition hover:bg-white hover:text-gray-800 dark:hover:bg-gray-800">
                                        Use all questions
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @forelse($questions as $question)
                    @php
                        $isChoice = in_array($question->question_type, ['multiple_choice', 'true_false', 'choice', 'multiple_select'], true);
                        $difficultyDot = ['easy' => 'bg-emerald-500', 'medium' => 'bg-amber-500', 'hard' => 'bg-rose-500'][$question->difficulty] ?? 'bg-gray-300';
                    @endphp
                    <article wire:key="assessment-question-{{ $question->id }}"
                             class="group flex gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 transition hover:ring-orange-200 dark:bg-gray-900 dark:ring-gray-800 dark:hover:ring-orange-900 sm:p-5">
                        <div class="flex shrink-0 flex-col items-center gap-1">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-gray-900 text-sm font-extrabold text-white dark:bg-white dark:text-gray-900">{{ $loop->iteration }}</span>
                            <button type="button" wire:click="moveQuestion({{ $question->id }}, -1)" @disabled($loop->first) title="Move up"
                                    class="rounded-md p-0.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 disabled:invisible dark:hover:bg-gray-800">
                                <flux:icon name="chevron-up" variant="micro" class="size-4" />
                            </button>
                            <button type="button" wire:click="moveQuestion({{ $question->id }}, 1)" @disabled($loop->last) title="Move down"
                                    class="rounded-md p-0.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 disabled:invisible dark:hover:bg-gray-800">
                                <flux:icon name="chevron-down" variant="micro" class="size-4" />
                            </button>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-bold">
                                <span class="rounded-md bg-orange-50 px-2 py-0.5 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">{{ $typeLabels->label($question->question_type) }}</span>
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ rtrim(rtrim(number_format((float) $question->points, 1), '0'), '.') }} {{ (float) $question->points === 1.0 ? 'pt' : 'pts' }}</span>
                                @if($question->difficulty)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-gray-50 px-2 py-0.5 capitalize text-gray-600 dark:bg-gray-800 dark:text-gray-300"><span class="size-1.5 rounded-full {{ $difficultyDot }}"></span>{{ $question->difficulty }}</span>
                                @endif
                                @if($question->status && $question->status !== 'active')
                                    <span class="rounded-md bg-gray-200 px-2 py-0.5 capitalize text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $question->status }}</span>
                                @endif
                                @if($question->assessments_count > 1)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:ring-amber-900" title="Editing this question changes it everywhere it is used">
                                        <flux:icon name="link" variant="micro" class="size-3" /> Shared · {{ $question->assessments_count }}
                                    </span>
                                @endif
                                @foreach($question->tags as $tag)
                                    <span class="font-semibold text-gray-400">#{{ $tag->name }}</span>
                                @endforeach
                            </div>

                            <x-question-text :text="$question->question_text" class="mt-2 text-[15px] font-semibold leading-snug text-gray-900 dark:text-white" />

                            @if($question->image_url)
                                <img src="{{ Storage::disk('public')->url($question->image_url) }}" alt="Question image"
                                     class="mt-3 max-h-48 rounded-xl ring-1 ring-gray-200 dark:ring-gray-700">
                            @endif

                            @if($question->options->count() > 0)
                                <div class="mt-3 grid gap-1.5 sm:grid-cols-2">
                                    @foreach($question->options as $option)
                                        <div @class([
                                            'flex items-start gap-2 rounded-xl px-3 py-2 text-sm',
                                            'bg-emerald-50 font-semibold text-emerald-800 ring-1 ring-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:ring-emerald-900' => $option->is_correct,
                                            'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300' => ! $option->is_correct,
                                        ])>
                                            @if($isChoice)
                                                <span @class([
                                                    'mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-full text-[10px] font-extrabold',
                                                    'bg-emerald-500 text-white' => $option->is_correct,
                                                    'bg-white text-gray-400 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700' => ! $option->is_correct,
                                                ])>
                                                    @if($option->is_correct)<flux:icon name="check" variant="micro" class="size-3" />@else{{ chr(65 + $loop->index) }}@endif
                                                </span>
                                            @else
                                                <span class="mt-0.5 shrink-0 text-xs font-extrabold text-gray-400">{{ $loop->iteration }}.</span>
                                            @endif
                                            <span class="min-w-0">
                                                {{ $option->option_text }}
                                                @if($option->image_url)
                                                    <img src="{{ Storage::disk('public')->url($option->image_url) }}" alt="{{ $option->image_alt_text ?? 'Option image' }}"
                                                         class="mt-1.5 max-h-28 rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($question->explanation)
                                <details class="group/exp mt-3">
                                    <summary class="inline-flex cursor-pointer list-none items-center gap-1 text-xs font-bold text-sky-600 hover:text-sky-700 dark:text-sky-400">
                                        <flux:icon name="light-bulb" variant="micro" class="size-3.5" /> Explanation
                                        <flux:icon name="chevron-down" variant="micro" class="size-3 transition group-open/exp:rotate-180" />
                                    </summary>
                                    <p class="mt-1.5 rounded-xl bg-sky-50 px-3 py-2 text-sm text-gray-700 dark:bg-sky-900/20 dark:text-gray-300">{{ $question->explanation }}</p>
                                </details>
                            @endif
                        </div>

                        <div class="flex shrink-0 flex-col gap-1 sm:flex-row sm:items-start">
                            <button type="button" wire:click="openQuestionModal({{ $question->id }})" title="Edit question"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold text-gray-600 ring-1 ring-gray-200 transition hover:bg-orange-50 hover:text-orange-600 hover:ring-orange-200 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-orange-900/20">
                                <flux:icon name="pencil-square" variant="micro" class="size-3.5" /> Edit
                            </button>
                            <button type="button" wire:click="deleteQuestion({{ $question->id }})" title="Remove from this assessment"
                                    wire:confirm="Remove this question from the assessment? It stays in the Question Bank."
                                    class="inline-flex items-center justify-center rounded-lg p-1.5 text-gray-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-900/20">
                                <flux:icon name="trash" variant="micro" class="size-4" />
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-white px-6 py-14 text-center dark:border-gray-800 dark:bg-gray-900">
                        <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-500 dark:bg-orange-900/20">
                            <flux:icon name="queue-list" class="size-7" />
                        </span>
                        <h3 class="mt-4 text-base font-extrabold text-gray-900 dark:text-white">No questions yet</h3>
                        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Write a new question, or reuse ones you already have in the Question Bank.</p>
                        <div class="mt-5 flex justify-center gap-2">
                            <button type="button" wire:click="openBankPicker"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-sm font-bold text-gray-700 ring-1 ring-gray-200 transition hover:ring-orange-300 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700">
                                <flux:icon name="archive-box" variant="micro" class="size-4" /> From Question Bank
                            </button>
                            <button type="button" wire:click="openQuestionModal"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-2 text-sm font-bold text-white shadow-sm">
                                <flux:icon name="plus" variant="micro" class="size-4" /> New question
                            </button>
                        </div>
                    </div>
                @endforelse
            </section>
        @endif

        {{-- ===================== SETTINGS ===================== --}}
        @if($activeTab === 'settings')
            <form wire:submit="updateAssessment" class="space-y-5">
                <section class="{{ $cardClass }} space-y-4">
                    <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Basics</h2>
                    <div>
                        <label class="{{ $labelClass }}">Title <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="title" class="{{ $inputClass }} text-base font-semibold" required>
                        @error('title') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">{{ $isAssignment ? 'Brief summary' : 'Description' }}</label>
                        <textarea wire:model="description" rows="3" class="{{ $inputClass }}" placeholder="Optional. Shown to students before they start."></textarea>
                        @error('description') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex size-7 items-center justify-center rounded-lg {{ $typeMeta['tile'] }}"><flux:icon :name="$typeMeta['icon']" variant="micro" class="size-4" /></span>
                        <span><span class="font-bold text-gray-700 dark:text-gray-200">{{ $typeMeta['label'] }}</span>. The type can't be changed after creation.</span>
                    </div>
                </section>

                @if($isAssignment)
                    <section class="{{ $cardClass }} space-y-4">
                        <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Assignment details</h2>
                        <div>
                            <label class="{{ $labelClass }}">Instructions for students</label>
                            <textarea wire:model="assignment_instructions" rows="5" class="{{ $inputClass }}" placeholder="What should students do? Include steps, rubric notes or links."></textarea>
                            @error('assignment_instructions') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="{{ $labelClass }}">Due date</label>
                                <input type="date" wire:model="assignment_due_date" class="{{ $inputClass }}">
                                @error('assignment_due_date') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Max points</label>
                                <input type="number" wire:model="assignment_max_points" min="1" max="1000" class="{{ $inputClass }}" required>
                                @error('assignment_max_points') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Students can hand in</label>
                            <div class="grid gap-2.5 sm:grid-cols-2">
                                @foreach(['assignment_allow_text' => ['Text response', 'Type their answer in the browser', 'pencil-square'], 'assignment_allow_files' => ['File upload', 'PDF, Word, images, ZIP (10MB max)', 'paper-clip']] as $field => [$label, $hint, $icon])
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-gray-200 transition has-[:checked]:bg-orange-50 has-[:checked]:ring-2 has-[:checked]:ring-orange-500 dark:ring-gray-700 dark:has-[:checked]:bg-orange-900/20">
                                        <input type="checkbox" wire:model="{{ $field }}" class="mt-0.5 size-4 rounded accent-orange-500">
                                        <span>
                                            <span class="flex items-center gap-1.5 text-sm font-bold text-gray-900 dark:text-white"><flux:icon :name="$icon" variant="micro" class="size-4 text-orange-500" />{{ $label }}</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('assignment_allow_files') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Brief files</label>
                            @if(!empty($assignment_existing_attachments))
                                <ul class="mb-3 space-y-2">
                                    @foreach($assignment_existing_attachments as $index => $file)
                                        <li class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 px-3 py-2 text-sm dark:bg-gray-800">
                                            <a href="{{ asset('storage/' . $file['path']) }}" target="_blank" class="flex min-w-0 items-center gap-2 font-semibold text-gray-700 hover:text-orange-600 dark:text-gray-200">
                                                <flux:icon name="document" variant="micro" class="size-4 shrink-0 text-gray-400" /><span class="truncate">{{ $file['name'] }}</span>
                                            </a>
                                            <button type="button" wire:click="removeExistingAssignmentAttachment({{ $index }})" class="text-xs font-bold text-rose-600 hover:text-rose-700">Remove</button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <label class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-gray-200 px-4 py-5 text-center transition hover:border-orange-300 hover:bg-orange-50/50 dark:border-gray-700 dark:hover:bg-orange-900/10">
                                <flux:icon name="cloud-arrow-up" class="size-6 text-orange-400" />
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Attach worksheets or reference images</span>
                                <span class="text-xs text-gray-400">Max 10MB each. Saved when you click Save changes.</span>
                                <input type="file" wire:model="assignmentBriefFiles" multiple accept=".pdf,.doc,.docx,.txt,.zip,.jpg,.jpeg,.png" class="hidden">
                            </label>
                            <div wire:loading wire:target="assignmentBriefFiles" class="mt-2 text-xs font-semibold text-orange-600">Uploading…</div>
                            @if(!empty($assignmentBriefFiles))
                                <p class="mt-2 text-xs font-semibold text-gray-600 dark:text-gray-300">{{ count($assignmentBriefFiles) }} new {{ \Illuminate\Support\Str::plural('file', count($assignmentBriefFiles)) }} ready to upload</p>
                            @endif
                            @error('assignmentBriefFiles.*') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </section>
                @endif

                <section class="{{ $cardClass }} space-y-5">
                    <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Rules</h2>
                    @php
                        $numbers = ['max_attempts' => ['Attempts', 'arrow-path', 1, null, ''], 'passing_score' => ['Pass mark %', 'check-badge', 0, 100, ''], 'xp_reward' => ['XP reward', 'star', 0, null, '']];
                        if (! $isAssignment) {
                            $numbers = ['max_attempts' => $numbers['max_attempts'], 'time_limit_minutes' => ['Time limit (min)', 'clock', 1, null, 'None']] + array_slice($numbers, 1, null, true);
                        }
                    @endphp
                    <div class="grid grid-cols-2 gap-3 {{ $isAssignment ? 'sm:grid-cols-3' : 'sm:grid-cols-4' }}">
                        @foreach($numbers as $field => [$label, $icon, $min, $max, $placeholder])
                            <div class="rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100 focus-within:ring-2 focus-within:ring-orange-500 dark:bg-gray-800 dark:ring-gray-700">
                                <span class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400"><flux:icon :name="$icon" variant="micro" class="size-3.5 text-orange-500" />{{ $label }}</span>
                                <input type="number" wire:model="{{ $field }}" min="{{ $min }}" @if($max) max="{{ $max }}" @endif placeholder="{{ $placeholder }}"
                                       class="mt-1 w-full border-0 bg-transparent p-0 text-2xl font-extrabold text-gray-900 placeholder:text-gray-300 focus:ring-0 dark:text-white">
                                @error($field) <p class="text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>

                    @unless($isAssignment)
                        <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach([
                                'is_randomized' => ['Shuffle question order', 'Each student sees questions in a different order', 'queue-list'],
                                'shuffle_options' => ['Shuffle answers', 'A, B, C, D appear in a different order', 'arrows-up-down'],
                                'show_results_immediately' => ['Show score right away', 'Students see their result on submit', 'bolt'],
                                'show_correct_answers' => ['Show correct answers', 'Reveal the answers after submitting', 'eye'],
                                'allow_review' => ['Allow review', 'Students can look back at their answers', 'document-magnifying-glass'],
                                'is_required' => ['Required', 'Must be completed to finish the lesson', 'flag'],
                            ] as $field => [$label, $hint, $icon])
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-gray-200 transition has-[:checked]:bg-orange-50 has-[:checked]:ring-orange-300 dark:ring-gray-700 dark:has-[:checked]:bg-orange-900/20">
                                    <input type="checkbox" wire:model="{{ $field }}" class="mt-0.5 size-4 rounded accent-orange-500">
                                    <span>
                                        <span class="flex items-center gap-1.5 text-sm font-bold text-gray-900 dark:text-white"><flux:icon :name="$icon" variant="micro" class="size-4 text-orange-500" />{{ $label }}</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <flux:icon name="arrows-right-left" variant="micro" class="inline size-3.5 text-orange-500" />
                            The random question pool is set on the <button type="button" wire:click="setTab('questions')" class="font-bold text-orange-600 hover:underline">Questions</button> tab.
                        </p>
                    @else
                        <label class="flex w-fit cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-gray-200 transition has-[:checked]:bg-orange-50 has-[:checked]:ring-orange-300 dark:ring-gray-700">
                            <input type="checkbox" wire:model="is_required" class="mt-0.5 size-4 rounded accent-orange-500">
                            <span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">Required</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Must be submitted to finish the lesson</span>
                            </span>
                        </label>
                    @endunless
                </section>

                <div class="sticky bottom-4 z-10 flex items-center justify-between gap-3 rounded-2xl bg-white/90 p-3 shadow-lg ring-1 ring-gray-100 backdrop-blur dark:bg-gray-900/90 dark:ring-gray-800">
                    <p class="hidden pl-2 text-xs text-gray-500 sm:block">Changes apply to new attempts. Attempts already started keep their questions.</p>
                    <button type="submit" wire:loading.attr="disabled" wire:target="updateAssessment"
                            class="ml-auto inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:shadow-md disabled:opacity-60">
                        <flux:icon name="check" variant="micro" class="size-4" />
                        <span wire:loading.remove wire:target="updateAssessment">Save changes</span>
                        <span wire:loading wire:target="updateAssessment">Saving…</span>
                    </button>
                </div>
            </form>
        @endif

        {{-- ===================== SUBMISSIONS ===================== --}}
        @if($activeTab === 'submissions')
            <section class="space-y-4">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach([
                        ['Submitted', $submissionStats['total'], 'inbox-stack', 'bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-300'],
                        ['Needs grading', $submissionStats['pending'], 'pencil-square', 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-300'],
                        ['Average score', $submissionStats['average'] !== null ? $submissionStats['average'].'%' : '–', 'chart-bar', 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-300'],
                        ['Pass rate', $submissionStats['passRate'] !== null ? $submissionStats['passRate'].'%' : '–', 'check-badge', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300'],
                    ] as [$label, $value, $icon, $tile])
                        <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $tile }}"><flux:icon :name="$icon" class="size-5" /></span>
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ $label }}</p>
                                <p class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $value }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800">
                    @forelse($attempts as $attempt)
                        @php
                            $percent = $attempt->scorePercentage();
                            $ungraded = $attempt->score === null;
                            $initials = collect(explode(' ', trim($attempt->user->name ?? '?')))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                        @endphp
                        <div wire:key="attempt-row-{{ $attempt->id }}" class="flex flex-col gap-3 border-b border-gray-100 px-4 py-3 last:border-0 dark:border-gray-800 sm:flex-row sm:items-center">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-gray-700 to-gray-900 text-xs font-extrabold text-white">{{ $initials ?: '?' }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $attempt->user->name ?? 'Unknown student' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $attempt->completed_at ? $attempt->completed_at->format('M j, Y · H:i') : 'Not finished' }}
                                        @if($attempt->time_spent) · {{ $attempt->time_spent }} min @endif                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($ungraded)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:ring-amber-900">
                                        <flux:icon name="clock" variant="micro" class="size-3.5" /> Awaiting grade
                                    </span>
                                @else
                                    <span class="w-14 text-right text-base font-extrabold {{ $attempt->is_passed ? 'text-emerald-600' : 'text-rose-600' }}">{{ $percent !== null ? round($percent).'%' : '–' }}</span>
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-xs font-bold',
                                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' => $attempt->is_passed,
                                        'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-300' => ! $attempt->is_passed,
                                    ])>{{ $attempt->is_passed ? 'Passed' : 'Not passed' }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 sm:justify-end">
                                <button type="button" wire:click="viewAttempt({{ $attempt->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold text-gray-600 ring-1 ring-gray-200 transition hover:bg-gray-50 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-800">
                                    <flux:icon name="eye" variant="micro" class="size-3.5" /> View
                                </button>
                                @if($isAssignment)
                                    <button type="button" wire:click="startGrading({{ $attempt->id }})"
                                            @class([
                                                'inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold transition',
                                                'bg-orange-500 text-white hover:bg-orange-600' => $ungraded,
                                                'text-orange-600 ring-1 ring-orange-200 hover:bg-orange-50 dark:ring-orange-900' => ! $ungraded,
                                            ])>
                                        <flux:icon name="pencil-square" variant="micro" class="size-3.5" /> {{ $ungraded ? 'Grade' : 'Edit grade' }}
                                    </button>
                                @elseif($ungraded)
                                    <a href="{{ route('grades.grade', $attempt->id) }}" wire:navigate
                                       class="inline-flex items-center gap-1.5 rounded-lg bg-orange-500 px-2.5 py-1.5 text-xs font-bold text-white transition hover:bg-orange-600">
                                        <flux:icon name="pencil-square" variant="micro" class="size-3.5" /> Grade
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-14 text-center">
                            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800">
                                <flux:icon name="inbox" class="size-7" />
                            </span>
                            <h3 class="mt-4 text-base font-extrabold text-gray-900 dark:text-white">No submissions yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Completed attempts will appear here.</p>
                        </div>
                    @endforelse
                </div>
                @if($attempts->hasPages())
                    <div>{{ $attempts->links() }}</div>
                @endif
            </section>
        @endif
    </div>

    {{-- Question Editor Modal --}}
    @if($showQuestionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeQuestionModal"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-2xl bg-gray-50 dark:bg-gray-950 shadow-2xl transition-all w-full max-w-6xl max-h-[92vh] flex flex-col">
                    <div class="relative overflow-hidden bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                        <div class="pointer-events-none absolute -right-8 -top-12 size-36 rounded-full bg-white/10"></div>
                        <div class="relative flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-widest text-orange-100">{{ $title }}</p>
                                <h3 class="text-xl font-extrabold text-white">{{ $editingQuestionId ? 'Edit question' : 'New question' }}</h3>
                            </div>
                            <button type="button" wire:click="closeQuestionModal" class="rounded-lg p-2 text-white/80 transition hover:bg-white/20 hover:text-white" aria-label="Close">
                                <flux:icon name="x-mark" class="size-6" />
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-5 flex-1 overflow-y-auto">
                        <livewire:questions.question-editor
                            :question-id="$editingQuestionId"
                            :assessment-id="$assessment->id"
                            :allowed-types="$availableQuestionTypes"
                            :default-type="$this->getDefaultQuestionType()"
                            context="modal"
                            :key="'question-editor-'.$questionEditorKey" />
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Add from Question Bank --}}
    @if($showBankPicker && $bank)
        @php
            $pickerStyles = \App\Livewire\Questions\QuestionEditor::TYPE_STYLES;
            $pickerSelected = array_map('strval', $bankSelected);
            $pickerDifficulty = ['easy' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'medium' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'hard' => 'bg-rose-50 text-rose-700 ring-rose-600/20'];
            $pickerSelect = 'w-full rounded-xl border-0 bg-gray-50 py-2 pl-3 pr-8 text-sm text-gray-700 ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700';
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeBankPicker"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative overflow-hidden rounded-2xl bg-gray-50 dark:bg-gray-950 shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col">
                    <div class="relative overflow-hidden bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 text-white">
                        <div class="pointer-events-none absolute -right-8 -top-12 size-36 rounded-full bg-white/10"></div>
                        <div class="relative flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-widest text-orange-100">Question Bank</p>
                                <h3 class="text-xl font-extrabold">Add questions to “{{ $title }}”</h3>
                            </div>
                            <button type="button" wire:click="closeBankPicker" class="rounded-lg p-2 text-white/80 transition hover:bg-white/20 hover:text-white" aria-label="Close">
                                <flux:icon name="x-mark" class="size-6" />
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3 border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900">
                        @php
                            $scopeBtn = fn (bool $on) => $on
                                ? 'bg-gray-900 text-white shadow-sm dark:bg-white dark:text-gray-900'
                                : 'text-gray-600 hover:bg-white hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700';
                            $otherCourse = is_numeric($bankCourse) ? $bank['courses']->firstWhere('id', (int) $bankCourse) : null;
                        @endphp
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Questions from</span>
                            <div class="flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
                                <button type="button" wire:click="$set('bankCourse', 'this')"
                                        class="inline-flex max-w-[22rem] items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $scopeBtn($bankCourse === 'this') }}">
                                    <flux:icon name="academic-cap" variant="micro" class="size-3.5 shrink-0" />
                                    <span class="truncate">This course: {{ $assessment->course->title }}</span>
                                    <span class="rounded-full bg-black/10 px-1.5 text-[10px] dark:bg-white/10">{{ $bank['thisCourseCount'] }}</span>
                                </button>
                                <button type="button" wire:click="$set('bankCourse', 'all')"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $scopeBtn($bankCourse === 'all') }}">
                                    <flux:icon name="globe-alt" variant="micro" class="size-3.5" /> All courses
                                    <span class="rounded-full bg-black/10 px-1.5 text-[10px] dark:bg-white/10">{{ $bank['allCount'] }}</span>
                                </button>
                                <select wire:model.live="bankCourse"
                                        class="rounded-lg border-0 bg-transparent py-1.5 pl-3 pr-8 text-xs font-bold focus:ring-2 focus:ring-orange-500 {{ $otherCourse ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 dark:text-gray-300' }}">
                                    <option value="this" @selected(! $otherCourse)>Another course…</option>
                                    @foreach($bank['courses'] as $course)
                                        @if($course->id !== $assessment->course_id)
                                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @if($bankCourse === 'this' && $bank['thisCourseCount'] === 0)
                            <p class="rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-800 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:ring-amber-900">
                                This course has no unused questions in the bank yet. Write a new one, or switch to <button type="button" wire:click="$set('bankCourse', 'all')" class="font-bold underline">All courses</button> to reuse questions from another course.
                            </p>
                        @endif
                        <div class="flex flex-col gap-2 md:flex-row">
                            <div class="relative flex-1">
                                <flux:icon name="magnifying-glass" variant="mini" class="absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-gray-400" />
                                <input type="search" wire:model.live.debounce.300ms="bankSearch" placeholder="Search questions..."
                                       class="w-full rounded-xl border-0 bg-gray-50 py-2 pl-11 pr-4 text-sm ring-1 ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                            </div>
                            <div class="grid grid-cols-2 gap-2 md:w-[22rem]">
                                <select wire:model.live="bankType" class="{{ $pickerSelect }}">
                                    <option value="">All types</option>
                                    @foreach($availableQuestionTypes as $value => $label)
                                        <option value="{{ $value }}">{{ \Illuminate\Support\Str::before($label, ' (') }}</option>
                                    @endforeach
                                </select>
                                <select wire:model.live="bankTag" class="{{ $pickerSelect }}">
                                    <option value="">All tags</option>
                                    @foreach($bank['tags'] as $tag)
                                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="mr-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">Difficulty</span>
                            @foreach(['' => 'Any'] + \App\Models\Question::DIFFICULTIES as $value => $label)
                                <button type="button" wire:click="$set('bankDifficulty', '{{ $value }}')" @class([
                                    'rounded-full px-3.5 py-1 text-xs font-semibold transition',
                                    'bg-orange-500 text-white shadow-sm' => $bankDifficulty === (string) $value,
                                    'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-700 dark:bg-gray-800 dark:text-gray-300' => $bankDifficulty !== (string) $value,
                                ])>{{ $label }}</button>
                            @endforeach
                            <span class="mx-1 h-4 w-px bg-gray-200 dark:bg-gray-700"></span>
                            <button type="button" wire:click="$toggle('bankUnusedOnly')" @class([
                                'inline-flex items-center gap-1 rounded-full px-3.5 py-1 text-xs font-semibold transition',
                                'bg-gray-900 text-white dark:bg-white dark:text-gray-900' => $bankUnusedOnly,
                                'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' => ! $bankUnusedOnly,
                            ])>
                                <flux:icon name="sparkles" variant="micro" class="size-3.5" /> Not used yet
                            </button>
                            @if($bankSearch !== '' || $bankType !== '' || $bankDifficulty !== '' || $bankTag !== '' || $bankUnusedOnly)
                                <button type="button" wire:click="clearBankFilters" class="text-xs font-semibold text-orange-600 hover:underline">Clear filters</button>
                            @endif
                            <div class="ml-auto flex items-center gap-3">
                                <span class="text-xs text-gray-500">{{ $bank['total'] }} available</span>
                                @if($bank['questions']->isNotEmpty())
                                    @php $allShownPicked = $bank['questions']->every(fn ($q) => in_array((string) $q->id, $pickerSelected, true)); @endphp
                                    <button type="button" wire:click="toggleAllBank"
                                            class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold text-orange-600 ring-1 ring-orange-200 transition hover:bg-orange-50 dark:ring-orange-900">
                                        <flux:icon :name="$allShownPicked ? 'minus' : 'check'" variant="micro" class="size-3.5" />
                                        {{ $allShownPicked ? 'Unselect shown' : 'Select all '.$bank['questions']->count().' shown' }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 space-y-2.5 overflow-y-auto px-6 py-4">
                        @forelse($bank['questions'] as $bankQuestion)
                            @php
                                $pStyle = $pickerStyles[$bankQuestion->question_type] ?? ['icon' => 'question-mark-circle', 'tile' => 'bg-gray-100 text-gray-600'];
                                $picked = in_array((string) $bankQuestion->id, $pickerSelected, true);
                            @endphp
                            <label wire:key="bank-q-{{ $bankQuestion->id }}" x-data="{ open: false }" @class([
                                'flex cursor-pointer items-start gap-3 rounded-2xl bg-white p-3.5 shadow-sm ring-1 transition hover:shadow-md dark:bg-gray-900',
                                'ring-2 ring-orange-500 bg-orange-50/40' => $picked,
                                'ring-gray-100 hover:ring-orange-200 dark:ring-gray-800' => ! $picked,
                            ])>
                                <input type="checkbox" value="{{ $bankQuestion->id }}" wire:model.live="bankSelected" class="mt-3 size-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $pStyle['tile'] }}">
                                    <flux:icon :name="$pStyle['icon']" variant="mini" class="size-5" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    @php $bankPreview = \App\Support\QuestionText::preview($bankQuestion->question_text); @endphp
                                    <span class="block text-sm font-semibold leading-snug text-gray-900 dark:text-white">{{ $bankPreview['text'] }}</span>
                                    @foreach($bankPreview['kinds'] as $kind)
                                        <span class="mt-1 mr-1 inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-bold {{ $kind === 'Scratch blocks' ? 'bg-amber-50 text-amber-700' : 'bg-slate-900 text-slate-100' }}">
                                            <flux:icon :name="$kind === 'Scratch blocks' ? 'puzzle-piece' : 'code-bracket'" variant="micro" class="size-3" /> {{ $kind }}
                                        </span>
                                    @endforeach
                                    <span class="mt-1.5 flex flex-wrap items-center gap-1.5 text-xs text-gray-500">
                                        <span class="font-semibold uppercase tracking-wide text-[10px] text-gray-400">{{ $typeLabels->label($bankQuestion->question_type) }}</span>
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold capitalize ring-1 ring-inset {{ $pickerDifficulty[$bankQuestion->difficulty] ?? $pickerDifficulty['medium'] }}">{{ $bankQuestion->difficulty }}</span>
                                        <span>{{ rtrim(rtrim(number_format((float) $bankQuestion->points, 1), '0'), '.') }} pts</span>
                                        @if($bankQuestion->assessments_count)
                                            <span>&middot; used in {{ $bankQuestion->assessments_count }}</span>
                                        @else
                                            <span class="rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-700 dark:bg-sky-900/20 dark:text-sky-300">Not used yet</span>
                                        @endif
                                        @foreach($bankQuestion->tags as $tag)
                                            <span class="rounded-lg bg-orange-50 px-1.5 py-0.5 font-medium text-orange-700 dark:bg-orange-900/20 dark:text-orange-300">#{{ $tag->name }}</span>
                                        @endforeach
                                    </span>
                                    @if($bankQuestion->placements->isNotEmpty() || $bankQuestion->creator)
                                        <span class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] text-gray-400">
                                            @foreach($bankQuestion->placements->take(2) as $placement)
                                                <span class="inline-flex items-center gap-1 rounded-md bg-gray-50 px-1.5 py-0.5 dark:bg-gray-800">
                                                    <flux:icon name="folder" variant="micro" class="size-3" />
                                                    {{ $placement->course?->title ?? 'Any course' }}@if($placement->module) › {{ $placement->module->title }}@endif
                                                </span>
                                            @endforeach
                                            @if($bankQuestion->placements->count() > 2)
                                                <span>+{{ $bankQuestion->placements->count() - 2 }} more</span>
                                            @endif
                                            @if($bankQuestion->creator)
                                                <span>by {{ $bankQuestion->creator->name }}</span>
                                            @endif
                                        </span>
                                    @endif

                                    @if($bankQuestion->options->isNotEmpty() || $bankQuestion->explanation)
                                        <button type="button" x-on:click.prevent.stop="open = !open"
                                                class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-gray-500 hover:text-orange-600">
                                            <flux:icon name="eye" variant="micro" class="size-3.5" />
                                            <span x-text="open ? 'Hide answers' : 'Preview answers'">Preview answers</span>
                                        </button>
                                        <span x-show="open" x-cloak class="mt-2 grid gap-1 sm:grid-cols-2">
                                            @foreach($bankQuestion->options as $option)
                                                <span @class([
                                                    'flex items-start gap-1.5 rounded-lg px-2.5 py-1.5 text-xs',
                                                    'bg-emerald-50 font-semibold text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200' => $option->is_correct,
                                                    'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => ! $option->is_correct,
                                                ])>
                                                    @if($option->is_correct)<flux:icon name="check" variant="micro" class="mt-px size-3.5 shrink-0" />@endif
                                                    {{ \Illuminate\Support\Str::limit(strip_tags((string) $option->option_text), 80) }}
                                                </span>
                                            @endforeach
                                            @if($bankQuestion->explanation)
                                                <span class="rounded-lg bg-sky-50 px-2.5 py-1.5 text-xs text-gray-600 sm:col-span-2 dark:bg-sky-900/20 dark:text-gray-300"><strong>Why:</strong> {{ \Illuminate\Support\Str::limit(strip_tags($bankQuestion->explanation), 160) }}</span>
                                            @endif
                                        </span>
                                    @endif
                                </span>
                            </label>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-14 text-center dark:border-gray-700 dark:bg-gray-900">
                                <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-2xl bg-orange-100 text-orange-500">
                                    <flux:icon name="magnifying-glass" class="size-7" />
                                </div>
                                <p class="font-bold text-gray-900 dark:text-white">No matching questions</p>
                                <p class="mt-1 text-sm text-gray-500">Clear the filters, switch to “All courses”, or write a new question.</p>
                            </div>
                        @endforelse

                        @if($bank['total'] > $bank['questions']->count())
                            <div class="pt-1 text-center">
                                <button type="button" wire:click="showMoreBank"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-xs font-bold text-gray-600 ring-1 ring-gray-200 transition hover:text-orange-600 hover:ring-orange-300 dark:bg-gray-900 dark:text-gray-300 dark:ring-gray-700">
                                    <flux:icon name="chevron-down" variant="micro" class="size-4" />
                                    Show more ({{ $bank['total'] - $bank['questions']->count() }} left)
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-bold text-gray-900 dark:text-white">{{ count($bankSelected) }}</span> selected
                            @if(count($bankSelected))
                                @php $poolAfter = $poolSize + count($bankSelected); @endphp
                                <span class="ml-2 text-xs text-gray-500">
                                    &middot; {{ $poolAfter }} in this assessment after adding{{ $assessment->questions_per_attempt && $assessment->questions_per_attempt < $poolAfter ? ', each student gets a random '.$assessment->questions_per_attempt : '' }}
                                </span>
                            @endif
                            @error('bankSelected') <span class="ml-2 font-medium text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="closeBankPicker" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50 dark:text-gray-300 dark:ring-gray-700">Cancel</button>
                            <button type="button" wire:click="addSelectedFromBank" @disabled(count($bankSelected) === 0)
                                    class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50">
                                <flux:icon name="plus" variant="mini" class="size-5" />
                                Add {{ count($bankSelected) ?: '' }} to assessment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Student Attempt Detail Modal --}}
    @if($selectedAttempt)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeAttemptView"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl transition-all w-full max-w-5xl max-h-[90vh] flex flex-col">
                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-8 py-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-white">
                                    Submission by {{ $selectedAttempt->user->name }}
                                </h3>
                                <p class="text-white/80 text-sm mt-1">
                                    @if($assessment->assessment_type === 'assignment' && $selectedAttempt->score === null)
                                        Score: Not Graded • Awaiting Grade
                                    @else
                                        Score: {{ number_format($selectedAttempt->scorePercentage() ?? 0, 1) }}% 
                                        @if($selectedAttempt->is_passed ?? false)
                                            • Passed ✓
                                        @else
                                            • Failed
                                        @endif
                                    @endif
                                </p>
                            </div>
                            <button type="button" wire:click="closeAttemptView" class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Modal Body --}}
                    <div class="px-8 py-6 flex-1 overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Score</p>
                                @if($assessment->assessment_type === 'assignment' && $selectedAttempt->score === null)
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">Not Graded</p>
                                @else
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($selectedAttempt->scorePercentage() ?? 0, 1) }}%</p>
                                @endif
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Time Spent</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $selectedAttempt->time_spent ?? 0 }} min</p>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Completed</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $selectedAttempt->completed_at?->format('M d, Y H:i') ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            @php
                                $answers = $selectedAttempt->answers ?? [];
                                $questions = $selectedAttemptQuestions ?? collect();
                            @endphp
                            @foreach($questions as $question)
                                <div class="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                    Question #{{ $question->order }}
                                                </span>
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 capitalize">
                                                    {{ str_replace('_', ' ', $question->question_type) }}
                                                </span>
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                                    {{ $question->points }} pts
                                                </span>
                                            </div>
                                            <x-question-text :text="$question->question_text" class="mb-2 font-semibold text-gray-900 dark:text-white" />
                                            @if($question->image_url)
                                                <img src="{{ Storage::disk('public')->url($question->image_url) }}" 
                                                     alt="Question image" 
                                                     class="max-w-md rounded-lg border border-gray-200 dark:border-gray-700 mt-3">
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Student Answer --}}
                                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Student Answer:</p>
                                        @if($question->question_type === 'file_upload')
                                            @php
                                                $answerData = $answers[$question->id] ?? null;
                                                $uploadedFiles = is_array($answerData) ? ($answerData['files'] ?? []) : [];
                                            @endphp
                                            @if(!empty($uploadedFiles))
                                                <div class="space-y-2">
                                                    @foreach($uploadedFiles as $file)
                                                        @php
                                                            $filePath = is_array($file) ? ($file['path'] ?? '') : (string) $file;
                                                            $fileName = is_array($file) ? ($file['name'] ?? basename($filePath)) : basename($filePath);
                                                        @endphp
                                                        @if($filePath === '') @continue @endif
                                                        <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $fileName }}</p>
                                                            </div>
                                                            <a href="{{ \App\Support\SubmissionFile::downloadUrl($filePath, $fileName) }}"
                                                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-gray-600 dark:text-gray-400 italic">No files uploaded</p>
                                            @endif
                                        @elseif(in_array($question->question_type, ['essay', 'short_answer', 'text', 'reflection']))
                                            <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ $answers[$question->id] ?? 'No answer provided' }}</p>
                                        @elseif(in_array($question->question_type, ['multiple_choice', 'multiple_select', 'choice']))
                                            @php
                                                $selectedOptionIds = is_array($answers[$question->id] ?? null) ? $answers[$question->id] : [$answers[$question->id] ?? null];
                                                $selectedOptions = $question->options->whereIn('id', $selectedOptionIds)->filter();
                                            @endphp
                                            @if($selectedOptions->count() > 0)
                                                @foreach($selectedOptions as $option)
                                                    <div class="flex items-start gap-2 p-3 bg-white dark:bg-gray-800 rounded-lg border {{ $option->is_correct ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                                        <input type="checkbox" checked disabled class="w-4 h-4 mt-1">
                                                        <div class="flex-1">
                                                            <span class="text-gray-900 dark:text-white">{{ $option->option_text }}</span>
                                                            @if($option->image_url)
                                                                <img src="{{ Storage::disk('public')->url($option->image_url) }}" 
                                                                     alt="Option image" 
                                                                     class="max-w-xs rounded border border-gray-200 dark:border-gray-700 mt-2">
                                                            @endif
                                                        </div>
                                                        @if($option->is_correct)
                                                            <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Correct</span>
                                                        @else
                                                            <span class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Incorrect</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-gray-600 dark:text-gray-400 italic">No answer selected</p>
                                            @endif
                                        @else
                                            @php $otherAnswer = $answers[$question->id] ?? null; @endphp
                                            <p class="text-gray-900 dark:text-white">{{ is_array($otherAnswer) ? implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $otherAnswer)) : ($otherAnswer ?? 'No answer provided') }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Grading Section for Assignment-Type Assessments --}}
                        @if($selectedAttempt->assessment->assessment_type === 'assignment')
                            <div class="mt-8 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl border-2 border-indigo-200 dark:border-indigo-800">
                                <div class="flex items-center justify-between mb-6">
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">Grade Submission</h4>
                                    @if(!$gradingAttempt && $selectedAttempt->score === null)
                                        <button wire:click="startGrading({{ $selectedAttempt->id }})" 
                                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                                            Start Grading
                                        </button>
                                    @elseif(!$gradingAttempt && $selectedAttempt->score !== null)
                                        <button wire:click="startGrading({{ $selectedAttempt->id }})" 
                                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors">
                                            Edit Grade
                                        </button>
                                    @endif
                                </div>

                                @if($gradingAttempt || $selectedAttempt->score !== null)
                                    <div class="space-y-6">
                                        @php
                                            $answers = $selectedAttempt->answers ?? [];
                                            $submissionText = $answers['text'] ?? '';
                                            $submissionFiles = $answers['files'] ?? [];
                                        @endphp

                                        {{-- Submission Text --}}
                                        @if($submissionText)
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
                                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Submission Text:</p>
                                                <div class="max-w-none prose prose-sm dark:prose-invert prose-headings:font-semibold prose-p:mb-4 prose-p:leading-7 prose-p:text-gray-800 dark:prose-p:text-gray-200 prose-strong:text-gray-900 dark:prose-strong:text-gray-100 prose-ul:my-4 prose-ol:my-4 prose-li:my-2 prose-pre:bg-gray-100 dark:prose-pre:bg-gray-800 prose-code:text-sm">
                                                    <div class="whitespace-pre-wrap break-words text-gray-900 dark:text-gray-100 leading-7">
                                                        @php
                                                            // Convert plain text to formatted HTML with proper line breaks and spacing
                                                            $formattedText = $submissionText;
                                                            // Split into paragraphs (double line breaks)
                                                            $paragraphs = preg_split('/\n\s*\n/', $formattedText);
                                                            $formattedText = '';
                                                            foreach ($paragraphs as $para) {
                                                                $para = trim($para);
                                                                if (!empty($para)) {
                                                                    // Check if it's a list item
                                                                    if (preg_match('/^[-*•]\s+/', $para) || preg_match('/^\d+[\.\)]\s+/', $para)) {
                                                                        $formattedText .= '<p class="mb-2 ml-4">' . nl2br(e($para)) . '</p>';
                                                                    } else {
                                                                        $formattedText .= '<p class="mb-4">' . nl2br(e($para)) . '</p>';
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        {!! $formattedText ?: '<p>' . nl2br(e($submissionText)) . '</p>' !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Submission Files --}}
                                        @if(!empty($submissionFiles))
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Uploaded Files:</p>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    @foreach($submissionFiles as $file)
                                                        @php
                                                            $filePath = is_array($file) ? ($file['path'] ?? '') : (string) $file;
                                                            $fileName = is_array($file) ? ($file['name'] ?? basename($filePath)) : basename($filePath);
                                                        @endphp
                                                        @if($filePath === '') @continue @endif
                                                        <a href="{{ \App\Support\SubmissionFile::downloadUrl($filePath, $fileName) }}"
                                                           class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                                                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $fileName }}</p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">Click to download</p>
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Grading Form --}}
                                        @if($gradingAttempt || $selectedAttempt->score !== null)
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 space-y-6">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                            Score (out of {{ $attemptMaxScore }})
                                                        </label>
                                                        <input type="number" 
                                                               wire:model.live="attemptScore" 
                                                               min="0" 
                                                               max="{{ $attemptMaxScore }}" 
                                                               step="0.5"
                                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                                               {{ $selectedAttempt->score !== null && !$gradingAttempt ? 'readonly' : '' }}>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                            Percentage
                                                        </label>
                                                        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                                                {{ $attemptMaxScore > 0 ? number_format(((float)$attemptScore / (float)$attemptMaxScore) * 100, 1) : 0 }}%
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        Feedback
                                                    </label>
                                                    <textarea wire:model="attemptFeedback" 
                                                              rows="6"
                                                              placeholder="Provide detailed feedback to the student..."
                                                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                                              {{ $selectedAttempt->score !== null && !$gradingAttempt ? 'readonly' : '' }}></textarea>
                                                </div>

                                                @if($gradingAttempt)
                                                    <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                                        <button wire:click="saveAttemptGrade" 
                                                                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                                                            Save Grade
                                                        </button>
                                                        <button wire:click="closeAttemptView" 
                                                                class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                                            Graded on: {{ $selectedAttempt->updated_at->format('M d, Y H:i') }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
