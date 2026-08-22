@php
$colorMap = [
    'blue'   => ['bg' => 'bg-blue-100 dark:bg-blue-900/30',   'icon' => 'text-blue-600 dark:text-blue-400',   'ring' => 'ring-blue-500',   'badge' => 'bg-blue-500'],
    'purple' => ['bg' => 'bg-purple-100 dark:bg-purple-900/30','icon' => 'text-purple-600 dark:text-purple-400','ring' => 'ring-purple-500', 'badge' => 'bg-purple-500'],
    'green'  => ['bg' => 'bg-green-100 dark:bg-green-900/30',  'icon' => 'text-green-600 dark:text-green-400',  'ring' => 'ring-green-500',  'badge' => 'bg-green-500'],
    'orange' => ['bg' => 'bg-orange-100 dark:bg-orange-900/30','icon' => 'text-orange-600 dark:text-orange-400','ring' => 'ring-orange-500', 'badge' => 'bg-orange-500'],
    'teal'   => ['bg' => 'bg-teal-100 dark:bg-teal-900/30',   'icon' => 'text-teal-600 dark:text-teal-400',   'ring' => 'ring-teal-500',   'badge' => 'bg-teal-500'],
    'red'    => ['bg' => 'bg-red-100 dark:bg-red-900/30',     'icon' => 'text-red-600 dark:text-red-400',     'ring' => 'ring-red-500',    'badge' => 'bg-red-500'],
];
@endphp

<div class="max-w-4xl mx-auto px-4 py-6">

    {{-- ── Header ────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">New Challenge</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                @if($step === 'template')
                    Pick a type — everything else is pre-filled for you.
                @else
                    Review and tweak the details, then hit Create.
                @endif
            </p>
        </div>
        <a href="{{ route('daily-challenges.index') }}" wire:navigate
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
    </div>

    {{-- ── STEP 1: Template picker ─────────────────────────────────────── --}}
    @if($step === 'template')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($templates as $key => $tpl)
        @php $c = $colorMap[$tpl['color']]; @endphp
        <button
            wire:click="selectTemplate('{{ $key }}')"
            class="group text-left p-5 rounded-2xl border-2 border-transparent bg-white dark:bg-gray-800
                   hover:border-{{ $tpl['color'] }}-400 dark:hover:border-{{ $tpl['color'] }}-500
                   hover:shadow-lg transition-all duration-200 cursor-pointer"
        >
            <div class="flex items-start gap-4">
                <div class="{{ $c['bg'] }} rounded-xl p-3 flex-shrink-0 group-hover:scale-110 transition-transform">
                    @if($tpl['icon'] === 'book')
                        <svg class="w-6 h-6 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"/>
                        </svg>
                    @elseif($tpl['icon'] === 'academic-cap')
                        <svg class="w-6 h-6 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    @elseif($tpl['icon'] === 'clock')
                        <svg class="w-6 h-6 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($tpl['icon'] === 'chart-bar')
                        <svg class="w-6 h-6 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    @elseif($tpl['icon'] === 'chat-bubble-left-right')
                        <svg class="w-6 h-6 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    @elseif($tpl['icon'] === 'document-text')
                        <svg class="w-6 h-6 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 dark:text-white text-base leading-tight">{{ $tpl['label'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ $tpl['description'] }}</p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Auto-graded · XP awarded
                </span>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-{{ $tpl['color'] }}-500 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </button>
        @endforeach
    </div>
    @endif

    {{-- ── STEP 2: Smart form ──────────────────────────────────────────── --}}
    @if($step === 'form')
    @php
        $tplData = $templates[$selectedTemplate] ?? [];
        $c = $colorMap[$tplData['color'] ?? 'orange'];
    @endphp

    <form wire:submit="save" class="space-y-5">

        {{-- Selected template badge + change link --}}
        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="{{ $c['badge'] }} text-white text-xs font-bold px-3 py-1 rounded-full">
                {{ $tplData['label'] ?? $selectedTemplate }}
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Auto-graded · XP auto-awarded on completion</span>
            <button type="button" wire:click="back"
                    class="ml-auto text-xs text-blue-600 dark:text-blue-400 hover:underline">
                Change type
            </button>
        </div>

        {{-- Title --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Challenge Title</label>
            <input
                wire:model="title"
                type="text"
                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                placeholder="Give it a punchy title..."
                required
            />
            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">What students see</label>
            <textarea
                wire:model="description"
                rows="3"
                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition resize-none"
                placeholder="Describe what they need to do..."
                required
            ></textarea>
        </div>

        {{-- Difficulty + Points (linked) --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Difficulty</label>
                <span class="text-sm font-bold text-orange-600 dark:text-orange-400">
                    {{ $reward_points }} XP awarded
                </span>
            </div>
            <div class="flex gap-3">
                @foreach(['easy' => ['label'=>'Easy','pts'=>50,'color'=>'green'], 'medium' => ['label'=>'Medium','pts'=>100,'color'=>'yellow'], 'hard' => ['label'=>'Hard','pts'=>200,'color'=>'red']] as $lvl => $d)
                <button type="button"
                        wire:click="setDifficulty('{{ $lvl }}')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold border-2 transition-all
                               {{ $difficulty_level === $lvl
                                  ? 'border-orange-500 bg-orange-500 text-white shadow-md scale-105'
                                  : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:border-orange-300' }}">
                    {{ $d['label'] }}
                    <span class="block text-[11px] font-medium {{ $difficulty_level === $lvl ? 'text-orange-100' : 'text-gray-400' }}">{{ $d['pts'] }} XP</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Date presets --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">When</label>
                @if($date)
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($date)->format('D, M j') }}</span>
                @else
                    <span class="text-xs text-gray-400">Evergreen — visible always</span>
                @endif
            </div>
            <div class="flex flex-wrap gap-2 mb-2">
                @foreach(['today'=>'Today','tomorrow'=>'Tomorrow','week'=>'Next Week','evergreen'=>'Evergreen ∞'] as $preset => $label)
                @php
                    $activeDate = match($preset) {
                        'today'     => now()->toDateString(),
                        'tomorrow'  => now()->addDay()->toDateString(),
                        'week'      => now()->addWeek()->toDateString(),
                        'evergreen' => '',
                    };
                    $isActive = $date === $activeDate;
                @endphp
                <button type="button"
                        wire:click="setDate('{{ $preset }}')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all
                               {{ $isActive
                                  ? 'bg-blue-600 border-blue-600 text-white'
                                  : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-blue-400' }}">
                    {{ $label }}
                </button>
                @endforeach
                {{-- Custom date fallback --}}
                <input type="date" wire:model.live="date"
                       class="px-3 py-1.5 rounded-lg text-xs border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 focus:ring-1 focus:ring-blue-500"/>
            </div>
        </div>

        {{-- Course scope (optional) --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                Limit to a Course
                <span class="font-normal text-gray-400 ml-1">— optional</span>
            </label>
            <select wire:model="course_id"
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                <option value="">All students (no course restriction)</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
            </select>
            @if(!$isAdmin && $courses->isEmpty())
                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">You have no published courses — this challenge will be visible to all students.</p>
            @endif
        </div>

        {{-- Requirements (editable, pre-filled) --}}
        <details class="group">
            <summary class="cursor-pointer text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2 select-none">
                <svg class="w-4 h-4 text-gray-400 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                Completion checklist
                <span class="font-normal text-gray-400">(pre-filled, edit if needed)</span>
            </summary>
            <div class="mt-2">
                <textarea
                    wire:model="requirements"
                    rows="4"
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition resize-none"
                    placeholder="One requirement per line..."
                ></textarea>
                <p class="mt-1 text-xs text-gray-400">One item per line. Each line becomes a step students see.</p>
            </div>
        </details>

        {{-- Competition toggle --}}
        <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4">
            <label class="flex items-center gap-3 cursor-pointer select-none">
                <input type="checkbox" wire:model.live="is_competition"
                       class="h-4 w-4 rounded border-amber-400 text-amber-500 focus:ring-amber-500"/>
                <div>
                    <span class="text-sm font-bold text-amber-700 dark:text-amber-400">🏆 Weekly Competition</span>
                    <p class="text-xs text-amber-600 dark:text-amber-500">Everyone in the camp competes on this challenge. The fastest finisher wins the <strong>Week Winner</strong> badge.</p>
                </div>
            </label>
            @if($is_competition)
            <div class="mt-3">
                <label class="block text-xs font-semibold text-amber-700 dark:text-amber-400 mb-1">Competition ends at</label>
                <input type="datetime-local"
                       wire:model="competition_ends_at"
                       class="rounded-lg border border-amber-300 dark:border-amber-700 px-3 py-1.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:ring-1 focus:ring-amber-500 outline-none"
                       min="{{ now()->format('Y-m-d\TH:i') }}"
                />
            </div>
            @endif
        </div>

        {{-- Active toggle + Submit --}}
        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" wire:model="is_active"
                       class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500"/>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Publish immediately</span>
            </label>

            <div class="flex items-center gap-3">
                <button type="button" wire:click="back"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    ← Change type
                </button>
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white text-sm font-bold transition shadow-sm">
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Create Challenge
                </button>
            </div>
        </div>

    </form>
    @endif

</div>
