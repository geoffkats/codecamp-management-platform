@php
    $firstName = explode(' ', auth()->user()->name)[0];
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

    $series = $activitySeries ?: ['labels' => [], 'enrollments' => [], 'users' => [], 'attempts' => []];
    $sum = fn (array $values) => array_sum($values);

    $line = function (array $values, float $w, float $h, ?float $max = null): string {
        $count = count($values);
        if ($count < 2) {
            return '';
        }
        $max = max($max ?? max($values), 1);
        $points = [];
        foreach (array_values($values) as $i => $v) {
            $points[] = round($i / ($count - 1) * $w, 2).','.round($h - ($v / $max) * ($h - 4) - 2, 2);
        }

        return 'M'.implode(' L', $points);
    };
    $area = fn (array $values, float $w, float $h, ?float $max = null) => ($path = $line($values, $w, $h, $max)) ? $path." L{$w},{$h} L0,{$h} Z" : '';

    $chartMax = max(1, max(array_merge($series['enrollments'] ?: [0], $series['attempts'] ?: [0], $series['users'] ?: [0])));

    $avatarColors = ['bg-orange-500', 'bg-sky-500', 'bg-emerald-500', 'bg-violet-500', 'bg-rose-500', 'bg-amber-500', 'bg-teal-500', 'bg-indigo-500'];
    $avatar = fn (string $name) => $avatarColors[crc32($name) % count($avatarColors)];
    $initials = fn (string $name) => collect(explode(' ', trim($name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');

    $attention = array_values(array_filter([
        ($assessmentStats['pending_grading'] ?? 0) > 0 && Route::has('assessments.manage') ? [
            'count' => $assessmentStats['pending_grading'], 'label' => 'submissions to grade', 'hint' => 'Quizzes and assignments waiting for marks',
            'icon' => 'pencil-square', 'tone' => 'rose', 'url' => route('assessments.manage', ['view' => 'grading']),
        ] : null,
        ($stats['pending_approvals'] ?? 0) > 0 && Route::has('content-approvals.index') ? [
            'count' => $stats['pending_approvals'], 'label' => 'content approvals', 'hint' => 'Courses and lessons submitted for review',
            'icon' => 'check-badge', 'tone' => 'amber', 'url' => route('content-approvals.index'),
        ] : null,
        config('features.code_club', false) && ($codeClubStats['pending_reports'] ?? 0) > 0 && Route::has('admin.club-session-reports.index') ? [
            'count' => $codeClubStats['pending_reports'], 'label' => 'club session reports', 'hint' => ($codeClubStats['follow_up_reports'] ?? 0).' need follow-up',
            'icon' => 'document-text', 'tone' => 'violet', 'url' => route('admin.club-session-reports.index'),
        ] : null,
        (($systemHealth['recent_errors']['status'] ?? 'healthy') !== 'healthy') ? [
            'count' => (int) ($systemHealth['recent_errors']['message'] ?? 0), 'label' => 'system errors (24h)', 'hint' => 'Check the logs and failed jobs',
            'icon' => 'exclamation-triangle', 'tone' => 'red', 'url' => null,
        ] : null,
    ]));
    $tones = [
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100 dark:bg-rose-950/30 dark:ring-rose-900/40',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100 dark:bg-amber-950/30 dark:ring-amber-900/40',
        'violet' => 'bg-violet-50 text-violet-600 ring-violet-100 dark:bg-violet-950/30 dark:ring-violet-900/40',
        'red' => 'bg-red-50 text-red-600 ring-red-100 dark:bg-red-950/30 dark:ring-red-900/40',
    ];

    $kpis = [
        ['label' => 'Active users', 'value' => $stats['active_users'] ?? 0, 'sub' => number_format($stats['total_users'] ?? 0).' accounts in total', 'change' => $stats['users_change'] ?? 0,
         'icon' => 'users', 'tile' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/40 dark:text-orange-300', 'stroke' => '#f97316', 'series' => $series['users'], 'route' => 'admin.users.index'],
        ['label' => 'Enrollments', 'value' => $stats['total_enrollments'] ?? 0, 'sub' => number_format($quickStats['month_enrollments'] ?? 0).' new this month', 'change' => $stats['enrollments_change'] ?? 0,
         'icon' => 'academic-cap', 'tile' => 'bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300', 'stroke' => '#0ea5e9', 'series' => $series['enrollments'], 'route' => 'admin.enrollments'],
        ['label' => 'Assessment attempts', 'value' => $assessmentStats['attempts_7d'] ?? 0, 'sub' => 'completed in the last 7 days', 'change' => null,
         'icon' => 'clipboard-document-check', 'tile' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300', 'stroke' => '#10b981', 'series' => $series['attempts'], 'route' => 'assessments.manage'],
        ['label' => 'Published courses', 'value' => $stats['published_courses'] ?? 0, 'sub' => number_format($stats['total_lessons'] ?? 0).' lessons', 'change' => $stats['courses_change'] ?? 0,
         'icon' => 'book-open', 'tile' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-300', 'stroke' => '#8b5cf6', 'series' => null, 'route' => 'courses.index'],
    ];

    $health = [
        ['label' => 'Course completion', 'value' => (float) ($performanceMetrics['completion_rate'] ?? 0), 'hint' => 'of enrollments finished', 'color' => '#f97316'],
        ['label' => 'Engagement', 'value' => (float) ($performanceMetrics['engagement_rate'] ?? 0), 'hint' => 'have started learning', 'color' => '#0ea5e9'],
        ['label' => '30-day retention', 'value' => (float) ($performanceMetrics['retention_rate'] ?? 0), 'hint' => 'still active this month', 'color' => '#10b981'],
    ];

    $card = 'rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800';
    $cardHead = 'flex items-center justify-between gap-3 px-5 pt-5 pb-3';
    $cardTitle = 'text-[15px] font-extrabold text-gray-900 dark:text-white';
    $link = 'text-xs font-bold text-orange-600 hover:text-orange-700 dark:text-orange-400';
@endphp

<div class="min-h-screen bg-gray-50/70 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6">

        {{-- Hero --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-500 via-orange-500 to-orange-600 px-6 py-7 text-white shadow-lg sm:px-8">
            <div class="pointer-events-none absolute -right-16 -top-24 size-72 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute right-40 -bottom-28 size-56 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute left-1/2 top-6 size-3 rounded-full bg-white/40"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-orange-100">{{ now()->format('l, j F Y') }}</p>
                    <h1 class="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $greeting }}, {{ $firstName }}</h1>
                    <p class="mt-1 max-w-xl text-sm text-orange-50/90">
                        Here's what's happening across Code Academy.
                        @if(count($attention) > 0)
                            You have <span class="font-bold text-white">{{ count($attention) }} {{ Str::plural('thing', count($attention)) }}</span> waiting for you.
                        @else
                            Everything is up to date.
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if(Route::has('students.create'))
                        <a href="{{ route('students.create') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-bold ring-1 ring-white/30 backdrop-blur transition hover:bg-white/25">
                            <flux:icon name="user-plus" variant="micro" class="size-4" /> Add student
                        </a>
                    @endif
                    @if(Route::has('assessments.create'))
                        <a href="{{ route('assessments.create') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-bold ring-1 ring-white/30 backdrop-blur transition hover:bg-white/25">
                            <flux:icon name="plus" variant="micro" class="size-4" /> New assessment
                        </a>
                    @endif
                    <button wire:click="refresh" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-orange-600 shadow-sm transition hover:shadow-md disabled:opacity-70">
                        <flux:icon name="arrow-path" variant="micro" class="size-4" wire:loading.class="animate-spin" wire:target="refresh" /> Refresh
                    </button>
                </div>
            </div>

            <div class="relative mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach([
                    ['label' => 'New users today', 'value' => $quickStats['today_new_users'] ?? 0, 'icon' => 'user-plus'],
                    ['label' => 'Enrollments today', 'value' => $quickStats['today_enrollments'] ?? 0, 'icon' => 'academic-cap'],
                    ['label' => 'Completions today', 'value' => $quickStats['today_completions'] ?? 0, 'icon' => 'trophy'],
                    ['label' => 'Active learners (7d)', 'value' => $performanceMetrics['active_learners_7d'] ?? 0, 'icon' => 'bolt'],
                ] as $pill)
                    <div class="flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white/20"><flux:icon :name="$pill['icon']" class="size-5" /></span>
                        <div class="min-w-0">
                            <p class="text-2xl font-extrabold leading-none">{{ number_format($pill['value']) }}</p>
                            <p class="mt-1 truncate text-[11px] font-semibold uppercase tracking-wide text-orange-100">{{ $pill['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Needs attention --}}
        @if(count($attention) > 0)
            <section>
                <h2 class="mb-3 flex items-center gap-2 text-xs font-extrabold uppercase tracking-widest text-gray-500 dark:text-zinc-400">
                    <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-rose-400 opacity-75"></span><span class="relative inline-flex size-2 rounded-full bg-rose-500"></span></span>
                    Needs your attention
                </h2>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($attention as $item)
                        <a @if($item['url']) href="{{ $item['url'] }}" wire:navigate @endif
                           class="group flex items-center gap-4 {{ $card }} p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $tones[$item['tone']] }}">
                                <flux:icon :name="$item['icon']" class="size-6" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-600 dark:text-zinc-300"><span class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ number_format($item['count']) }}</span> {{ $item['label'] }}</p>
                                <p class="truncate text-xs text-gray-400">{{ $item['hint'] }}</p>
                            </div>
                            @if($item['url'])
                                <flux:icon name="chevron-right" class="size-5 text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-orange-500" />
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- KPIs --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($kpis as $kpi)
                <a @if(Route::has($kpi['route'])) href="{{ route($kpi['route']) }}" wire:navigate @endif
                   class="group relative overflow-hidden {{ $card }} p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <span class="flex size-11 items-center justify-center rounded-2xl {{ $kpi['tile'] }}"><flux:icon :name="$kpi['icon']" class="size-6" /></span>
                        @if($kpi['change'] !== null && (float) $kpi['change'] != 0)
                            <span @class([
                                'inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-[11px] font-bold',
                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' => $kpi['change'] > 0,
                                'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300' => $kpi['change'] < 0,
                            ])>
                                <flux:icon :name="$kpi['change'] > 0 ? 'arrow-trending-up' : 'arrow-trending-down'" variant="micro" class="size-3.5" />
                                {{ $kpi['change'] > 0 ? '+' : '' }}{{ number_format($kpi['change'], 0) }}%
                            </span>
                        @endif
                    </div>
                    <p class="mt-4 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($kpi['value']) }}</p>
                    <p class="text-sm font-bold text-gray-700 dark:text-zinc-200">{{ $kpi['label'] }}</p>
                    <p class="text-xs text-gray-400">{{ $kpi['sub'] }}</p>
                    @if($kpi['series'] && $sum($kpi['series']) > 0)
                        <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="mt-3 h-10 w-full" aria-hidden="true">
                            <defs>
                                <linearGradient id="spark-{{ $loop->index }}" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="{{ $kpi['stroke'] }}" stop-opacity="0.25" />
                                    <stop offset="100%" stop-color="{{ $kpi['stroke'] }}" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <path d="{{ $area($kpi['series'], 100, 32) }}" fill="url(#spark-{{ $loop->index }})" />
                            <path d="{{ $line($kpi['series'], 100, 32) }}" fill="none" stroke="{{ $kpi['stroke'] }}" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round" />
                        </svg>
                    @else
                        <div class="mt-3 flex h-10 items-end gap-1">
                            @for($i = 0; $i < 14; $i++)
                                <span class="flex-1 rounded-sm bg-gray-100 dark:bg-zinc-800" style="height: {{ 20 + (($i * 37) % 60) }}%"></span>
                            @endfor
                        </div>
                    @endif
                </a>
            @endforeach
        </section>

        {{-- Activity + Learning health --}}
        <section class="grid gap-4 lg:grid-cols-3">
            <div class="{{ $card }} lg:col-span-2">
                <div class="{{ $cardHead }}">
                    <div>
                        <h3 class="{{ $cardTitle }}">Activity</h3>
                        <p class="text-xs text-gray-400">Last 14 days</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-gray-500 dark:text-zinc-400">
                        <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-orange-500"></span>Enrollments <b class="text-gray-900 dark:text-white">{{ $sum($series['enrollments']) }}</b></span>
                        <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-emerald-500"></span>Assessments <b class="text-gray-900 dark:text-white">{{ $sum($series['attempts']) }}</b></span>
                        <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-sky-500"></span>New users <b class="text-gray-900 dark:text-white">{{ $sum($series['users']) }}</b></span>
                    </div>
                </div>
                <div class="px-5 pb-5">
                    <div class="relative h-56">
                        <div class="absolute inset-0 flex flex-col justify-between pb-6">
                            @foreach([1, 0.75, 0.5, 0.25, 0] as $tick)
                                <div class="flex items-center gap-2">
                                    <span class="w-6 text-right text-[10px] font-semibold text-gray-300 dark:text-zinc-600">{{ round($chartMax * $tick) }}</span>
                                    <div class="h-px flex-1 border-t border-dashed border-gray-100 dark:border-zinc-800"></div>
                                </div>
                            @endforeach
                        </div>
                        <svg viewBox="0 0 600 200" preserveAspectRatio="none" class="absolute left-8 right-0 top-1.5 h-[calc(100%-1.9rem)] w-[calc(100%-2rem)]" aria-hidden="true">
                            <defs>
                                <linearGradient id="area-enrol" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#f97316" stop-opacity="0.22" /><stop offset="100%" stop-color="#f97316" stop-opacity="0" /></linearGradient>
                                <linearGradient id="area-attempts" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#10b981" stop-opacity="0.18" /><stop offset="100%" stop-color="#10b981" stop-opacity="0" /></linearGradient>
                            </defs>
                            <path d="{{ $area($series['enrollments'], 600, 200, $chartMax) }}" fill="url(#area-enrol)" />
                            <path d="{{ $area($series['attempts'], 600, 200, $chartMax) }}" fill="url(#area-attempts)" />
                            <path d="{{ $line($series['users'], 600, 200, $chartMax) }}" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-dasharray="4 4" vector-effect="non-scaling-stroke" />
                            <path d="{{ $line($series['attempts'], 600, 200, $chartMax) }}" fill="none" stroke="#10b981" stroke-width="2.5" vector-effect="non-scaling-stroke" stroke-linejoin="round" />
                            <path d="{{ $line($series['enrollments'], 600, 200, $chartMax) }}" fill="none" stroke="#f97316" stroke-width="2.5" vector-effect="non-scaling-stroke" stroke-linejoin="round" />
                        </svg>
                        <div class="absolute bottom-0 left-8 right-0 flex justify-between text-[10px] font-semibold text-gray-400">
                            @foreach($series['labels'] as $i => $label)
                                <span class="{{ $i % 2 === 1 ? 'hidden sm:inline' : '' }}">{{ $label }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <div>
                        <h3 class="{{ $cardTitle }}">Learning health</h3>
                        <p class="text-xs text-gray-400">Across all enrollments</p>
                    </div>
                    @if(Route::has('analytics.dashboard'))
                        <a href="{{ route('analytics.dashboard') }}" wire:navigate class="{{ $link }}">Analytics →</a>
                    @endif
                </div>
                <div class="space-y-4 px-5 pb-5">
                    @foreach($health as $metric)
                        <div class="flex items-center gap-4">
                            <div class="relative size-16 shrink-0">
                                <svg viewBox="0 0 36 36" class="size-16 -rotate-90">
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke-width="3.5" class="stroke-gray-100 dark:stroke-zinc-800" />
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="{{ $metric['color'] }}" stroke-width="3.5" stroke-linecap="round" stroke-dasharray="{{ min(100, max(0, $metric['value'])) }} 100" />
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-sm font-extrabold text-gray-900 dark:text-white">{{ round($metric['value']) }}%</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $metric['label'] }}</p>
                                <p class="text-xs text-gray-400">{{ $metric['hint'] }}</p>
                            </div>
                        </div>
                    @endforeach
                    <div class="grid grid-cols-2 gap-2 border-t border-gray-100 pt-4 dark:border-zinc-800">
                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-zinc-800/60">
                            <p class="text-lg font-extrabold text-gray-900 dark:text-white">{{ $quickStats['avg_completion_time'] ?? 0 }}<span class="text-xs font-bold text-gray-400"> days</span></p>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Avg. to complete</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-zinc-800/60">
                            <p class="text-lg font-extrabold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['active_learners_30d'] ?? 0) }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Active (30d)</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Assessments + Leaderboard --}}
        <section class="grid gap-4 lg:grid-cols-3">
            <div class="{{ $card }} lg:col-span-2">
                <div class="{{ $cardHead }}">
                    <div>
                        <h3 class="{{ $cardTitle }}">Assessments</h3>
                        <p class="text-xs text-gray-400">Latest marked results</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if(Route::has('questions.index'))
                            <a href="{{ route('questions.index') }}" wire:navigate class="text-xs font-bold text-gray-500 hover:text-gray-800 dark:text-zinc-400">Question Bank</a>
                        @endif
                        @if(Route::has('assessments.manage'))
                            <a href="{{ route('assessments.manage') }}" wire:navigate class="{{ $link }}">All assessments →</a>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 px-5 sm:grid-cols-4">
                    @foreach([
                        ['label' => 'To grade', 'value' => number_format($assessmentStats['pending_grading'] ?? 0), 'class' => ($assessmentStats['pending_grading'] ?? 0) > 0 ? 'text-rose-600' : 'text-gray-900 dark:text-white'],
                        ['label' => 'Pass rate (30d)', 'value' => ($assessmentStats['pass_rate_30d'] ?? null) === null ? '—' : $assessmentStats['pass_rate_30d'].'%', 'class' => 'text-gray-900 dark:text-white'],
                        ['label' => 'Assessments', 'value' => number_format($assessmentStats['assessments'] ?? 0), 'class' => 'text-gray-900 dark:text-white'],
                        ['label' => 'Bank questions', 'value' => number_format($assessmentStats['bank_questions'] ?? 0), 'class' => 'text-gray-900 dark:text-white'],
                    ] as $mini)
                        <div class="rounded-xl bg-gray-50 px-3 py-2.5 dark:bg-zinc-800/60">
                            <p class="text-xl font-extrabold {{ $mini['class'] }}">{{ $mini['value'] }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">{{ $mini['label'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 divide-y divide-gray-50 pb-2 dark:divide-zinc-800">
                    @forelse($recentResults as $result)
                        <a @if(Route::has('assessments.show')) href="{{ route('assessments.show', $result['assessment_id']) }}" wire:navigate @endif
                           class="flex items-center gap-3 px-5 py-2.5 transition hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {{ $avatar($result['student']) }}">{{ $initials($result['student']) }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $result['student'] }}</p>
                                <p class="truncate text-xs text-gray-400">{{ $result['assessment'] }}</p>
                            </div>
                            <div class="hidden w-28 sm:block">
                                <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-800">
                                    <div class="h-full rounded-full {{ $result['passed'] ? 'bg-emerald-500' : 'bg-rose-400' }}" style="width: {{ $result['percent'] }}%"></div>
                                </div>
                            </div>
                            <span @class([
                                'w-14 shrink-0 rounded-full px-2 py-0.5 text-center text-xs font-extrabold',
                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' => $result['passed'],
                                'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300' => ! $result['passed'],
                            ])>{{ $result['percent'] }}%</span>
                            <span class="hidden w-12 shrink-0 text-right text-[11px] text-gray-400 md:block">{{ $result['when'] }}</span>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <flux:icon name="clipboard-document-check" class="mx-auto size-8 text-gray-300" />
                            <p class="mt-2 text-sm font-semibold text-gray-500">No marked results yet</p>
                            <p class="text-xs text-gray-400">Results appear here as students finish quizzes.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <div>
                        <h3 class="{{ $cardTitle }}">Top learners</h3>
                        <p class="text-xs text-gray-400">Ranked by XP</p>
                    </div>
                    @if(Route::has('leaderboards.index'))
                        <a href="{{ route('leaderboards.index') }}" wire:navigate class="{{ $link }}">Leaderboard →</a>
                    @endif
                </div>
                @php $top = array_slice($topPerformers ?? [], 0, 7); @endphp
                @if(count($top) >= 3)
                    <div class="mx-5 mb-3 grid grid-cols-3 items-end gap-2 rounded-2xl bg-gradient-to-b from-orange-50 to-white px-3 pt-4 dark:from-orange-950/20 dark:to-zinc-900">
                        @foreach([1, 0, 2] as $rank)
                            @php $p = $top[$rank]; @endphp
                            <div class="flex flex-col items-center text-center">
                                <span class="relative flex {{ $rank === 0 ? 'size-14' : 'size-11' }} items-center justify-center rounded-full text-sm font-bold text-white ring-4 {{ $rank === 0 ? 'ring-amber-300' : ($rank === 1 ? 'ring-gray-200' : 'ring-orange-200') }} {{ $avatar($p['name']) }}">
                                    {{ $initials($p['name']) }}
                                    <span class="absolute -bottom-1.5 flex size-5 items-center justify-center rounded-full bg-white text-[10px] font-extrabold text-gray-700 shadow">{{ $rank + 1 }}</span>
                                </span>
                                <p class="mt-2 w-full truncate text-xs font-bold text-gray-900 dark:text-white">{{ explode(' ', $p['name'])[0] }}</p>
                                <p class="text-[11px] font-semibold text-orange-600">{{ $p['points'] }} XP</p>
                                <div class="mt-2 w-full rounded-t-lg {{ $rank === 0 ? 'h-10 bg-amber-300/70' : ($rank === 1 ? 'h-7 bg-gray-200 dark:bg-zinc-700' : 'h-5 bg-orange-200/80') }}"></div>
                            </div>
                        @endforeach
                    </div>
                    @php $rest = array_slice($top, 3, null, true); @endphp
                @else
                    @php $rest = $top; @endphp
                @endif
                <div class="divide-y divide-gray-50 pb-2 dark:divide-zinc-800">
                    @forelse($rest as $i => $p)
                        <div class="flex items-center gap-3 px-5 py-2">
                            <span class="w-5 text-center text-xs font-extrabold text-gray-300">{{ $i + 1 }}</span>
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full text-[11px] font-bold text-white {{ $avatar($p['name']) }}">{{ $initials($p['name']) }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $p['name'] }}</p>
                                <p class="text-[11px] text-gray-400">Level {{ $p['level'] ?? 1 }} · {{ $p['badges_count'] }} badges</p>
                            </div>
                            <span class="text-xs font-extrabold text-gray-700 dark:text-zinc-200">{{ $p['points'] }}</span>
                        </div>
                    @empty
                        @if(count($top) === 0)
                            <p class="px-5 py-10 text-center text-xs text-gray-400">No XP earned yet.</p>
                        @endif
                    @endforelse
                </div>
            </div>
        </section>

        {{-- People, courses, schools --}}
        <section class="grid gap-4 lg:grid-cols-3">
            <div class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <h3 class="{{ $cardTitle }}">New people</h3>
                    @if(Route::has('admin.users.index'))
                        <a href="{{ route('admin.users.index') }}" wire:navigate class="{{ $link }}">All users →</a>
                    @endif
                </div>
                <div class="divide-y divide-gray-50 pb-2 dark:divide-zinc-800">
                    @forelse(array_slice($recentUsers ?? [], 0, 6) as $u)
                        <a href="{{ route('admin.users.show', $u['id']) }}" wire:navigate class="flex items-center gap-3 px-5 py-2.5 transition hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                            <span class="relative flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {{ $avatar($u['name']) }}">
                                {{ $initials($u['name']) }}
                                <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full ring-2 ring-white dark:ring-zinc-900 {{ $u['is_active'] ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $u['name'] }}</p>
                                <p class="truncate text-xs text-gray-400">{{ $u['roles'] ?: 'No role' }}</p>
                            </div>
                            <span class="shrink-0 text-[11px] text-gray-400">{{ $u['created_at'] }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-10 text-center text-xs text-gray-400">No users yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <h3 class="{{ $cardTitle }}">Latest courses</h3>
                    @if(Route::has('courses.index'))
                        <a href="{{ route('courses.index') }}" wire:navigate class="{{ $link }}">All courses →</a>
                    @endif
                </div>
                <div class="divide-y divide-gray-50 pb-2 dark:divide-zinc-800">
                    @forelse(array_slice($recentCourses ?? [], 0, 6) as $course)
                        <a href="{{ route('courses.show', $course['id']) }}" wire:navigate class="flex items-center gap-3 px-5 py-2.5 transition hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-300"><flux:icon name="book-open" variant="mini" class="size-5" /></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $course['title'] }}</p>
                                <p class="truncate text-xs text-gray-400">{{ $course['instructor'] ?? 'No instructor' }} · {{ $course['enrollments'] }} enrolled</p>
                            </div>
                            <span @class([
                                'shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold',
                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' => $course['is_published'],
                                'bg-gray-100 text-gray-500 dark:bg-zinc-800 dark:text-zinc-400' => ! $course['is_published'],
                            ])>{{ $course['is_published'] ? 'Live' : ucfirst($course['status'] ?? 'Draft') }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-10 text-center text-xs text-gray-400">No courses yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <h3 class="{{ $cardTitle }}">ICT schools</h3>
                    @if(Route::has('admin.schools'))
                        <a href="{{ route('admin.schools') }}" wire:navigate class="{{ $link }}">All schools →</a>
                    @endif
                </div>
                <div class="space-y-3 px-5 pb-5">
                    @forelse(array_slice($ictSchoolPerformance ?? [], 0, 6) as $school)
                        @php $rate = $school['pass_rate']; @endphp
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-zinc-200">{{ $school['school_name'] }}</p>
                                <span class="shrink-0 text-xs font-extrabold {{ $rate >= 70 ? 'text-emerald-600' : ($rate >= 50 ? 'text-amber-600' : 'text-rose-500') }}">{{ $rate }}%</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full {{ $rate >= 70 ? 'bg-emerald-500' : ($rate >= 50 ? 'bg-amber-400' : 'bg-rose-400') }}" style="width: {{ min($rate, 100) }}%"></div>
                            </div>
                            <p class="mt-1 text-[11px] text-gray-400">{{ number_format($school['passed_attempts']) }} of {{ number_format($school['total_attempts']) }} attempts passed</p>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <flux:icon name="building-library" class="mx-auto size-8 text-gray-300" />
                            <p class="mt-2 text-xs text-gray-400">No ICT assessment results yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Code Club --}}
        @if(config('features.code_club', false))
            <section class="overflow-hidden {{ $card }}">
                <div class="flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-4 text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-white/15"><flux:icon name="code-bracket" class="size-5" /></span>
                        <div>
                            <h3 class="text-[15px] font-extrabold">Code Club</h3>
                            <p class="text-xs text-violet-100">School clubs, members and session reports</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @if(Route::has('admin.code-clubs.index'))
                            <a href="{{ route('admin.code-clubs.index') }}" wire:navigate class="rounded-xl bg-white/15 px-3 py-1.5 text-xs font-bold ring-1 ring-white/25 hover:bg-white/25">All clubs</a>
                        @endif
                        @if(Route::has('admin.club-session-reports.index'))
                            <a href="{{ route('admin.club-session-reports.index') }}" wire:navigate class="rounded-xl bg-white px-3 py-1.5 text-xs font-bold text-violet-700">Session reports</a>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach([
                        ['label' => 'Active clubs', 'value' => $codeClubStats['active_clubs'] ?? 0],
                        ['label' => 'Club members', 'value' => $codeClubStats['total_members'] ?? 0],
                        ['label' => 'Students', 'value' => $codeClubStats['students'] ?? 0],
                        ['label' => 'New this month', 'value' => $codeClubStats['new_students_month'] ?? 0],
                        ['label' => 'Reports pending', 'value' => $codeClubStats['pending_reports'] ?? 0, 'alert' => ($codeClubStats['pending_reports'] ?? 0) > 0],
                        ['label' => 'Need follow-up', 'value' => $codeClubStats['follow_up_reports'] ?? 0, 'alert' => ($codeClubStats['follow_up_reports'] ?? 0) > 0],
                    ] as $cc)
                        <div class="rounded-xl bg-gray-50 px-3 py-2.5 dark:bg-zinc-800/60">
                            <p class="text-xl font-extrabold {{ ! empty($cc['alert']) ? 'text-amber-600' : 'text-gray-900 dark:text-white' }}">{{ number_format($cc['value']) }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">{{ $cc['label'] }}</p>
                        </div>
                    @endforeach
                </div>
                @if(count($codeClubHighlights ?? []) > 0)
                    <div class="grid gap-3 border-t border-gray-100 p-5 sm:grid-cols-2 lg:grid-cols-5 dark:border-zinc-800">
                        @foreach($codeClubHighlights as $club)
                            <a href="{{ route('admin.code-clubs.show', $club['id']) }}" wire:navigate class="rounded-xl p-3 ring-1 ring-gray-100 transition hover:ring-violet-300 dark:ring-zinc-800">
                                <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $club['name'] }}</p>
                                <p class="truncate text-xs text-gray-400">{{ $club['school'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-violet-600 dark:text-violet-400">{{ $club['members'] }} members</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        {{-- Shortcuts + this week + system --}}
        <section class="grid gap-4 lg:grid-cols-3">
            <div class="{{ $card }} lg:col-span-2">
                <div class="{{ $cardHead }}"><h3 class="{{ $cardTitle }}">Shortcuts</h3></div>
                <div class="grid grid-cols-2 gap-3 px-5 pb-5 sm:grid-cols-4">
                    @foreach(array_filter([
                        ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users', 'tile' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/40 dark:text-orange-300'],
                        ['label' => 'Students', 'route' => 'students.index', 'icon' => 'academic-cap', 'tile' => 'bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300'],
                        ['label' => 'Assessments', 'route' => 'assessments.manage', 'icon' => 'clipboard-document-check', 'tile' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300'],
                        ['label' => 'Question Bank', 'route' => 'questions.index', 'icon' => 'archive-box', 'tile' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
                        ['label' => 'Camps', 'route' => 'admin.camps.index', 'icon' => 'sun', 'tile' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'],
                        ['label' => 'Attendance', 'route' => 'attendance.dashboard', 'icon' => 'calendar-days', 'tile' => 'bg-teal-100 text-teal-600 dark:bg-teal-900/40 dark:text-teal-300'],
                        ['label' => 'Certificates', 'route' => 'certificates.index', 'icon' => 'trophy', 'tile' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-300'],
                        ['label' => 'Settings', 'route' => 'admin.settings', 'icon' => 'cog-6-tooth', 'tile' => 'bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-zinc-300'],
                    ], fn ($s) => Route::has($s['route'])) as $shortcut)
                        <a href="{{ route($shortcut['route']) }}" wire:navigate class="group flex items-center gap-3 rounded-xl p-3 ring-1 ring-gray-100 transition hover:bg-orange-50/50 hover:ring-orange-200 dark:ring-zinc-800 dark:hover:bg-orange-950/10">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $shortcut['tile'] }}"><flux:icon :name="$shortcut['icon']" variant="mini" class="size-5" /></span>
                            <span class="text-sm font-bold text-gray-800 group-hover:text-orange-700 dark:text-zinc-200">{{ $shortcut['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <h3 class="{{ $cardTitle }}">This week</h3>
                    <span class="text-xs text-gray-400">Last 7 days</span>
                </div>
                <div class="grid grid-cols-2 gap-2 px-5">
                    @foreach([
                        ['label' => 'Badges earned', 'value' => $recentActivity['new_badges_earned'] ?? 0, 'icon' => 'star'],
                        ['label' => 'Challenges done', 'value' => $recentActivity['challenges_completed'] ?? 0, 'icon' => 'fire'],
                        ['label' => 'Discussions', 'value' => $recentActivity['discussions_created'] ?? 0, 'icon' => 'chat-bubble-left-right'],
                        ['label' => 'New courses', 'value' => $recentActivity['course_creations'] ?? 0, 'icon' => 'book-open'],
                    ] as $act)
                        <div class="rounded-xl bg-gray-50 px-3 py-2.5 dark:bg-zinc-800/60">
                            <flux:icon :name="$act['icon']" variant="micro" class="size-4 text-orange-500" />
                            <p class="mt-1 text-lg font-extrabold text-gray-900 dark:text-white">{{ number_format($act['value']) }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">{{ $act['label'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1.5 border-t border-gray-100 px-5 py-3.5 text-[11px] font-semibold text-gray-500 dark:border-zinc-800 dark:text-zinc-400">
                    @foreach([
                        'Database' => ($systemHealth['database']['status'] ?? '') === 'healthy',
                        'Storage '.($systemHealth['storage']['percent'] ?? '?').'% free' => ($systemHealth['storage']['status'] ?? '') === 'healthy',
                        ($systemHealth['active_sessions']['count'] ?? 0).' online now' => ($systemHealth['active_sessions']['count'] ?? 0) > 0,
                    ] as $label => $ok)
                        <span class="flex items-center gap-1.5"><span class="size-2 rounded-full {{ $ok ? 'bg-emerald-500' : 'bg-amber-400' }}"></span>{{ $label }}</span>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>
