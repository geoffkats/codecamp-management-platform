@php
    $typeStyles = \App\Livewire\Questions\QuestionEditor::TYPE_STYLES;
    $difficultyPill = [
        'easy' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300',
        'medium' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-300',
        'hard' => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-900/30 dark:text-rose-300',
    ];
    $statCards = [
        ['label' => 'All questions', 'value' => (int) $stats->total, 'status' => 'all', 'icon' => 'archive-box'],
        ['label' => 'Active', 'value' => (int) $stats->active, 'status' => 'active', 'icon' => 'bolt'],
        ['label' => 'Drafts', 'value' => (int) $stats->draft, 'status' => 'draft', 'icon' => 'pencil-square'],
        ['label' => 'Archived', 'value' => (int) $stats->archived, 'status' => 'archived', 'icon' => 'archive-box-arrow-down'],
    ];
    $hasFilters = $search !== '' || $type !== '' || $difficulty !== '' || $tag !== '' || $course !== '';
    $allOnPage = count($pageIds) > 0 && count(array_diff($pageIds, array_map('strval', $selected))) === 0;
@endphp

<div class="max-w-7xl mx-auto px-4 py-5 space-y-5 pb-28">

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-10 -top-16 size-56 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute right-24 -bottom-20 size-40 rounded-full bg-white/10"></div>

        <div class="relative px-6 pt-6 pb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-orange-100 text-xs font-semibold uppercase tracking-widest mb-1">Assessments</p>
                <h1 class="text-3xl font-extrabold leading-tight">Question Bank</h1>
                <p class="text-orange-100 text-sm mt-1 max-w-xl">Write a question once and reuse it in any quiz or exam. Tag it so you can find it again.</p>
            </div>
            <a href="{{ route('questions.create') }}" wire:navigate
               class="inline-flex items-center gap-2 self-start rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-orange-600 shadow-md transition hover:-translate-y-0.5 hover:shadow-lg">
                <flux:icon name="plus" variant="mini" class="size-5" />
                New Question
            </a>
        </div>

        <div class="relative grid grid-cols-2 sm:grid-cols-5 gap-3 px-6 pb-6">
            @foreach($statCards as $card)
                <button type="button" wire:click="$set('status', '{{ $card['status'] }}')" @class([
                    'group text-left rounded-xl px-4 py-3 transition',
                    'bg-white text-gray-900 shadow-md' => $status === $card['status'],
                    'bg-white/15 hover:bg-white/25' => $status !== $card['status'],
                ])>
                    <div class="flex items-center justify-between">
                        <p @class(['text-[10px] font-semibold uppercase tracking-wide', 'text-orange-600' => $status === $card['status'], 'text-orange-100' => $status !== $card['status']])>{{ $card['label'] }}</p>
                        <flux:icon :name="$card['icon']" variant="mini" @class(['size-4', 'text-orange-500' => $status === $card['status'], 'text-orange-200' => $status !== $card['status']]) />
                    </div>
                    <p class="text-2xl font-extrabold leading-tight mt-0.5">{{ number_format($card['value']) }}</p>
                </button>
            @endforeach
            <div class="rounded-xl bg-white/15 px-4 py-3 col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-orange-100">Not used yet</p>
                    <flux:icon name="sparkles" variant="mini" class="size-4 text-orange-200" />
                </div>
                <p class="text-2xl font-extrabold leading-tight mt-0.5">{{ number_format($unused) }}</p>
            </div>
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
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search questions..."
                       class="w-full rounded-xl border-0 bg-gray-50 py-2.5 pl-11 pr-4 text-sm text-gray-900 ring-1 ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 lg:w-[34rem]">
                @foreach([
                    ['model' => 'type', 'placeholder' => 'All types', 'options' => $types],
                    ['model' => 'tag', 'placeholder' => 'All tags', 'options' => $tags->pluck('name', 'id')->all()],
                    ['model' => 'course', 'placeholder' => 'All courses', 'options' => $courses->pluck('title', 'id')->all()],
                ] as $filter)
                    <select wire:model.live="{{ $filter['model'] }}"
                            class="w-full rounded-xl border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm text-gray-700 ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                        <option value="">{{ $filter['placeholder'] }}</option>
                        @foreach($filter['options'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @endforeach
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mr-1">Difficulty</span>
            @foreach(['' => 'Any'] + $difficulties as $value => $label)
                <button type="button" wire:click="$set('difficulty', '{{ $value }}')" @class([
                    'rounded-full px-3.5 py-1 text-xs font-semibold transition',
                    'bg-orange-500 text-white shadow-sm' => $difficulty === (string) $value,
                    'bg-gray-100 text-gray-600 hover:bg-orange-50 hover:text-orange-700 dark:bg-gray-800 dark:text-gray-300' => $difficulty !== (string) $value,
                ])>{{ $label }}</button>
            @endforeach

            <div class="ml-auto flex items-center gap-3">
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($questions->total()) }} {{ \Illuminate\Support\Str::plural('question', $questions->total()) }}</span>
                @if($hasFilters || $status !== 'active')
                    <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-orange-600 hover:text-orange-700">Clear filters</button>
                @endif
            </div>
        </div>
    </div>

    {{-- List --}}
    @if($questions->count() > 0)
        <div class="flex items-center gap-3 px-1">
            <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 cursor-pointer">
                <input type="checkbox" class="size-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500"
                       @checked($allOnPage) wire:click="togglePage('{{ implode(',', $pageIds) }}')">
                Select page
            </label>
        </div>

        <div class="space-y-3">
            @foreach($questions as $question)
                @php
                    $style = $typeStyles[$question->question_type] ?? ['icon' => 'question-mark-circle', 'tile' => 'bg-gray-100 text-gray-600'];
                    $isSelected = in_array((string) $question->id, array_map('strval', $selected), true);
                @endphp
                <div wire:key="bank-row-{{ $question->id }}" @class([
                    'group relative flex gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 transition hover:shadow-md dark:bg-gray-900',
                    'ring-2 ring-orange-400' => $isSelected,
                    'ring-gray-100 hover:ring-orange-200 dark:ring-gray-800' => ! $isSelected,
                    'opacity-70' => $question->status === 'archived',
                ])>
                    <div class="flex flex-col items-center gap-3 pt-1">
                        <input type="checkbox" value="{{ $question->id }}" wire:model.live="selected"
                               class="size-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500" aria-label="Select question">
                    </div>

                    <div class="hidden sm:flex size-12 shrink-0 items-center justify-center rounded-xl {{ $style['tile'] }}">
                        <flux:icon :name="$style['icon']" class="size-6" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                            <span class="text-[11px] font-bold uppercase tracking-wide text-gray-400">{{ \Illuminate\Support\Str::before($types[$question->question_type] ?? str_replace('_', ' ', $question->question_type), ' (') }}</span>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold capitalize ring-1 ring-inset {{ $difficultyPill[$question->difficulty] ?? $difficultyPill['medium'] }}">{{ $question->difficulty }}</span>
                            @if($question->status === 'draft')
                                <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-700 ring-1 ring-inset ring-sky-600/20">Draft</span>
                            @elseif($question->status === 'archived')
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/20">Archived</span>
                            @endif
                        </div>

                        <a href="{{ route('questions.edit', $question) }}" wire:navigate
                           class="block text-[15px] font-semibold leading-snug text-gray-900 hover:text-orange-600 dark:text-white dark:hover:text-orange-400">
                            @php $preview = \App\Support\QuestionText::preview($question->question_text, 180); @endphp
                            {{ $preview['text'] }}
                        </a>
                        @if($preview['kinds'] !== [])
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                @foreach($preview['kinds'] as $kind)
                                    <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-bold {{ $kind === 'Scratch blocks' ? 'bg-amber-50 text-amber-700' : 'bg-slate-900 text-slate-100' }}">
                                        <flux:icon :name="$kind === 'Scratch blocks' ? 'puzzle-piece' : 'code-bracket'" variant="micro" class="size-3" /> {{ $kind }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if($question->tags->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach($question->tags as $t)
                                    <button type="button" wire:click="$set('tag', '{{ $t->id }}')"
                                            class="rounded-lg bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-700 transition hover:bg-orange-100 dark:bg-orange-900/20 dark:text-orange-300">#{{ $t->name }}</button>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1"><flux:icon name="star" variant="micro" class="size-3.5 text-orange-400" />{{ rtrim(rtrim(number_format((float) $question->points, 1), '0'), '.') }} pts</span>
                            <span class="inline-flex items-center gap-1">
                                <flux:icon name="rectangle-stack" variant="micro" class="size-3.5" />
                                @if($question->assessments_count === 0)
                                    Not used yet
                                @else
                                    Used in {{ $question->assessments_count }} {{ \Illuminate\Support\Str::plural('assessment', $question->assessments_count) }}
                                @endif
                            </span>
                            @if($question->placements->isNotEmpty())
                                @php $placement = $question->placements->first(); @endphp
                                <span class="inline-flex items-center gap-1 truncate max-w-xs">
                                    <flux:icon name="academic-cap" variant="micro" class="size-3.5" />
                                    {{ $placement->course?->title }}@if($placement->module) · {{ $placement->module->title }}@endif
                                    @if($question->placements->count() > 1)<span class="text-gray-400">+{{ $question->placements->count() - 1 }}</span>@endif
                                </span>
                            @endif
                            @if($question->creator)
                                <span class="inline-flex items-center gap-1"><flux:icon name="user" variant="micro" class="size-3.5" />{{ $question->creator->name }}</span>
                            @endif
                            <span class="text-gray-400">v{{ $question->version }}</span>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-start gap-1">
                        <a href="{{ route('questions.edit', $question) }}" wire:navigate
                           class="hidden md:inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-600 opacity-0 transition hover:bg-orange-50 hover:text-orange-700 group-hover:opacity-100 dark:text-gray-300">
                            <flux:icon name="pencil-square" variant="micro" class="size-4" /> Edit
                        </a>
                        <flux:dropdown position="bottom" align="end">
                            <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800" aria-label="More actions">
                                <flux:icon name="ellipsis-vertical" variant="mini" class="size-5" />
                            </button>
                            <flux:menu>
                                <flux:menu.item href="{{ route('questions.edit', $question) }}" wire:navigate icon="pencil-square">Edit</flux:menu.item>
                                <flux:menu.item wire:click="duplicate({{ $question->id }})" icon="document-duplicate">Duplicate</flux:menu.item>
                                @if($question->status === 'archived')
                                    <flux:menu.item wire:click="setStatus({{ $question->id }}, 'active')" icon="arrow-uturn-left">Restore</flux:menu.item>
                                @else
                                    <flux:menu.item wire:click="setStatus({{ $question->id }}, 'archived')" icon="archive-box">Archive</flux:menu.item>
                                @endif
                                <flux:menu.separator />
                                <flux:menu.item wire:click="delete({{ $question->id }})" wire:confirm="{{ $question->assessments_count > 0 ? 'This question is used in '.$question->assessments_count.' assessment(s). Deleting removes it from all of them. Past attempts keep their copy. Continue?' : 'Delete this question?' }}" variant="danger" icon="trash">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $questions->links() }}</div>
    @else
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-dashed border-gray-200 dark:border-gray-700 px-6 py-16 text-center">
            <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-2xl bg-orange-100 text-orange-500 dark:bg-orange-900/30">
                <flux:icon :name="$hasFilters ? 'magnifying-glass' : 'light-bulb'" class="size-8" />
            </div>
            @if($hasFilters)
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">No questions match</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try a different search or clear the filters.</p>
                <button type="button" wire:click="clearFilters" class="mt-4 rounded-xl bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-700 hover:bg-orange-100">Clear filters</button>
            @else
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Nothing here yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create your first question, then add it to any assessment from the curriculum builder.</p>
                <a href="{{ route('questions.create') }}" wire:navigate class="mt-4 inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-orange-600">
                    <flux:icon name="plus" variant="mini" class="size-5" /> New Question
                </a>
            @endif
        </div>
    @endif

    {{-- Floating bulk bar --}}
    @if(count($selected) > 0)
        <div class="fixed inset-x-0 bottom-5 z-40 flex justify-center px-4">
            <div class="flex flex-wrap items-center gap-2 rounded-2xl bg-cau-deep px-4 py-3 text-white shadow-2xl ring-1 ring-white/10">
                <span class="mr-1 inline-flex items-center gap-2 text-sm font-bold">
                    <span class="flex size-6 items-center justify-center rounded-full bg-orange-500 text-xs">{{ count($selected) }}</span>
                    selected
                </span>
                <span class="h-6 w-px bg-white/20"></span>
                <button type="button" wire:click="bulk('activate')" class="rounded-lg px-3 py-1.5 text-xs font-semibold hover:bg-white/10">Activate</button>
                <button type="button" wire:click="bulk('draft')" class="rounded-lg px-3 py-1.5 text-xs font-semibold hover:bg-white/10">Draft</button>
                <button type="button" wire:click="bulk('archive')" class="rounded-lg px-3 py-1.5 text-xs font-semibold hover:bg-white/10">Archive</button>
                <div class="flex items-center gap-1 rounded-lg bg-white/10 pl-2">
                    <flux:icon name="tag" variant="micro" class="size-3.5 text-orange-300" />
                    <input wire:model="bulkTags" placeholder="add tags…" class="w-28 border-0 bg-transparent py-1 text-xs text-white placeholder:text-white/50 focus:ring-0">
                    <button type="button" wire:click="bulk('tag')" class="rounded-lg bg-orange-500 px-2.5 py-1.5 text-xs font-bold hover:bg-orange-600">Add</button>
                </div>
                <button type="button" wire:click="bulk('delete')" wire:confirm="Delete the selected questions? They will be removed from every assessment that uses them. Past attempts keep their copy."
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/20">Delete</button>
                <button type="button" wire:click="$set('selected', [])" class="rounded-lg p-1.5 text-white/60 hover:bg-white/10 hover:text-white" aria-label="Clear selection">
                    <flux:icon name="x-mark" variant="mini" class="size-4" />
                </button>
            </div>
        </div>
    @endif
</div>
