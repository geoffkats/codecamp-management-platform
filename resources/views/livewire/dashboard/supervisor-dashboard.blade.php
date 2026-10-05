@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = \Illuminate\Support\Str::of($user->name)->before(' ');
    $can = fn (string $ability) => \Illuminate\Support\Facades\Gate::allows($ability);
    $readable = fn (?string $text) => $text !== null && $text === mb_strtoupper($text) && preg_match('/\p{L}{4,}/u', $text)
        ? \Illuminate\Support\Str::title(mb_strtolower($text))
        : $text;
    $typeIcon = fn (string $label) => match ($label) {
        'Course' => 'book-open',
        'Module' => 'squares-2x2',
        'Lesson' => 'document-text',
        'Assessment' => 'clipboard-document-check',
        'Assignment' => 'pencil-square',
        default => 'document',
    };

    $shortcuts = array_values(array_filter([
        $can('review_content') ? ['Content approvals', 'check-badge', route('content-approvals.index')] : null,
        ['Submissions', 'inbox-stack', route('submissions.index')],
        $can('manage_enrollments') ? ['Enrollments', 'user-plus', route('admin.enrollments')] : null,
        $can('manage_enrollments') ? ['Student progress', 'chart-bar', route('admin.student-progress.index')] : null,
        $can('view_attendance') ? ['Attendance', 'calendar-days', route('attendance.dashboard')] : null,
        $can('review_daily_reports') ? ['Daily reports', 'document-text', route('admin.daily-reports.index')] : null,
        $can('view_analytics') ? ['Reports', 'presentation-chart-line', route('analytics.dashboard')] : null,
        ['Lesson locks', 'lock-closed', route('lessons.locks')],
    ]));

    $tabs = ['pending' => 'Waiting', 'approved' => 'Approved', 'rejected' => 'Sent back', 'all' => 'All'];
@endphp

<div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6">
    {{-- Greeting --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ now()->format('l, j F') }}</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $greeting }}, {{ $firstName }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-zinc-400">
                @if($pendingCount > 0)
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $pendingCount }} {{ \Illuminate\Support\Str::plural('item', $pendingCount) }}</span> waiting for your approval{{ $oldestPending ? ', the oldest from '.$oldestPending->diffForHumans() : '' }}.
                @else
                    Nothing is waiting for approval.
                @endif
            </p>
        </div>
        @if($can('review_content'))
            <a href="{{ route('content-approvals.index') }}" wire:navigate
               class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600 sm:self-auto">
                <flux:icon.check-badge class="size-4" /> Review content
                @if($pendingCount > 0)
                    <span class="rounded-full bg-white/25 px-2 py-0.5 text-xs font-bold">{{ $pendingCount }}</span>
                @endif
            </a>
        @endif
    </header>

    @if (session('message'))
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-300">
            <flux:icon.check-circle class="size-5 shrink-0" /> {{ session('message') }}
        </div>
    @endif

    {{-- Key numbers --}}
    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Summary">
        <a href="{{ $can('review_content') ? route('content-approvals.index') : '#' }}" wire:navigate
           class="rounded-2xl border border-gray-200 bg-white p-4 transition hover:border-orange-200 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-zinc-400">
                <span class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"><flux:icon.clock class="size-4" /></span>
                Waiting for approval
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $pendingCount }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">{{ $pendingByType->map(fn ($n, $t) => $n.' '.\Illuminate\Support\Str::plural(strtolower($t), $n))->take(2)->implode(' · ') ?: 'All clear' }}</p>
        </a>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-zinc-400">
                <span class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"><flux:icon.check class="size-4" /></span>
                Reviewed this week
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $approvedThisWeek + $rejectedThisWeek }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">{{ $approvedThisWeek }} approved · {{ $rejectedThisWeek }} sent back</p>
        </div>
        <a href="{{ route('submissions.index', ['filter' => 'pending']) }}" wire:navigate
           class="rounded-2xl border border-gray-200 bg-white p-4 transition hover:border-orange-200 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-zinc-400">
                <span class="flex size-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400"><flux:icon.inbox-stack class="size-4" /></span>
                Work not yet marked
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $waitingForMarks }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">across all trainers</p>
        </a>
        <a href="{{ $can('review_daily_reports') ? route('admin.daily-reports.index') : '#' }}" wire:navigate
           class="rounded-2xl border border-gray-200 bg-white p-4 transition hover:border-orange-200 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-zinc-400">
                <span class="flex size-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400"><flux:icon.document-text class="size-4" /></span>
                Daily reports
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $reportsToday }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">today · {{ $reportsThisWeek }} this week</p>
        </a>
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Approval queue --}}
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white lg:col-span-2 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
                <div>
                    <h2 class="font-semibold text-gray-900 dark:text-white">Content approvals</h2>
                    <p class="text-xs text-gray-500 dark:text-zinc-400">Courses, lessons and quizzes trainers have submitted</p>
                </div>
                <div class="inline-flex rounded-xl bg-gray-100 p-1 text-xs font-medium dark:bg-zinc-800" role="tablist">
                    @foreach($tabs as $key => $label)
                        @php $count = $key === 'all' ? $statusCounts->sum() : (int) ($statusCounts[$key] ?? 0); @endphp
                        <button type="button" wire:click="filterByStatus('{{ $key }}')" role="tab" aria-selected="{{ $filterStatus === $key ? 'true' : 'false' }}"
                                @class([
                                    'rounded-lg px-3 py-1.5 transition',
                                    'bg-white text-gray-900 shadow-sm dark:bg-zinc-700 dark:text-white' => $filterStatus === $key,
                                    'text-gray-500 hover:text-gray-800 dark:text-zinc-400 dark:hover:text-zinc-200' => $filterStatus !== $key,
                                ])>
                            {{ $label }} <span class="ml-0.5 text-gray-400">{{ $count }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            @if($approvals->isNotEmpty())
                <ul class="divide-y divide-gray-100 dark:divide-zinc-800">
                    @foreach($approvals as $approval)
                        @php $type = $this->typeLabel($approval->approvable_type); @endphp
                        <li wire:key="approval-{{ $approval->id }}" class="flex flex-col gap-3 px-5 py-3.5 sm:flex-row sm:items-center">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-500 dark:bg-zinc-800 dark:text-zinc-400" title="{{ $type }}">
                                    <flux:icon :name="$typeIcon($type)" class="size-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="flex items-center gap-2 truncate text-sm font-medium text-gray-900 dark:text-white">
                                        <span class="truncate">{{ $readable($approval->approvable?->title) ?? 'Removed item' }}</span>
                                        @if($approval->priority === 'high')
                                            <span class="shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-300">Urgent</span>
                                        @endif
                                    </p>
                                    <p class="truncate text-xs text-gray-500 dark:text-zinc-400">
                                        {{ $type }} · {{ $approval->submitter?->name ?? 'Unknown' }}@if($approval->submitted_at) · {{ $approval->submitted_at->diffForHumans() }}@endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2 pl-12 sm:pl-0">
                                @if($approval->status === 'pending')
                                    <a href="{{ route('content-approvals.review', $approval) }}" wire:navigate
                                       class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Review</a>
                                    <button type="button" wire:click="approveContent({{ $approval->id }})" wire:confirm="Approve this {{ strtolower($type) }} without opening it?"
                                            class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300">Approve</button>
                                @else
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-[11px] font-medium',
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' => $approval->status === 'approved',
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $approval->status === 'rejected',
                                    ])>{{ $approval->status === 'rejected' ? 'Sent back' : 'Approved' }}</span>
                                    <a href="{{ route('content-approvals.review', $approval) }}" wire:navigate class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-zinc-300 dark:hover:bg-zinc-800">Open</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if($approvals->hasPages())
                    <div class="border-t border-gray-100 px-5 py-3 dark:border-zinc-800">{{ $approvals->links() }}</div>
                @endif
            @else
                <div class="flex flex-col items-center px-5 py-12 text-center">
                    <span class="flex size-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"><flux:icon.check class="size-6" /></span>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">{{ $filterStatus === 'pending' ? 'All caught up' : 'Nothing here yet' }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">{{ $filterStatus === 'pending' ? 'New submissions from trainers will show up here.' : 'Try another tab.' }}</p>
                </div>
            @endif
        </section>

        <aside class="space-y-6">
            {{-- Shortcuts --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="px-1 font-semibold text-gray-900 dark:text-white">Oversight</h2>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach($shortcuts as [$label, $icon, $url])
                        <a href="{{ $url }}" wire:navigate
                           class="flex items-center gap-2 rounded-xl border border-gray-100 px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700 dark:border-zinc-800 dark:text-zinc-200 dark:hover:border-orange-900 dark:hover:bg-orange-900/20 dark:hover:text-orange-300">
                            <flux:icon :name="$icon" class="size-4 shrink-0 text-gray-400" />
                            <span class="truncate">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Waiting by type --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="px-1 font-semibold text-gray-900 dark:text-white">Waiting by type</h2>
                @if($pendingByType->isNotEmpty())
                    <ul class="mt-3 space-y-3 px-1">
                        @foreach($pendingByType as $type => $count)
                            <li>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 text-gray-700 dark:text-zinc-300"><flux:icon :name="$typeIcon($type)" class="size-4 text-gray-400" /> {{ \Illuminate\Support\Str::plural($type, $count) }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $count }}</span>
                                </div>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-800">
                                    <div class="h-full rounded-full bg-amber-400" style="width: {{ round($count / max(1, $pendingCount) * 100) }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 px-1 text-sm text-gray-500 dark:text-zinc-400">Nothing waiting.</p>
                @endif
            </section>
        </aside>
    </div>
</div>
