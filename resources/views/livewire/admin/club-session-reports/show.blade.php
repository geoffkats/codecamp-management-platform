@php
    $rate = $report->retentionRate();
    $stars = fn (?int $n) => $n ? str_repeat('★', $n).str_repeat('☆', 5 - $n) : '—';
    $sections = [
        ['Summary', $report->summary, 'document-text', 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300'],
        ['Topics taught', $report->topics_covered, 'academic-cap', 'bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-300'],
        ['New techniques', $report->new_techniques, 'light-bulb', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300'],
        ['Challenges', $report->challenges, 'exclamation-triangle', 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300'],
    ];
@endphp

<div class="mx-auto max-w-4xl space-y-5 p-4 sm:p-6">
    <a href="{{ route('admin.club-session-reports.index') }}" wire:navigate
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-white">
        <flux:icon.arrow-left class="size-4" /> Code Club reports
    </a>

    @if(session('message'))
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-200">
            <flux:icon.check-circle class="size-5 shrink-0" /> {{ session('message') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="h-1.5 bg-orange-500"></div>
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Club session report</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $report->club?->name ?? 'Club' }}</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-zinc-400">
                    {{ $report->session_date->format('l j F Y') }}@if($report->club?->school) · {{ $report->club->school->name }}@endif
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <x-user-avatar :user="$report->facilitator" size="xs" rounded="full" />
                    <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">{{ $report->facilitator?->name ?? 'Unknown facilitator' }}</span>
                    @if($report->submitted_at)
                        <span class="text-xs text-gray-400">· sent {{ $report->submitted_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 flex-col items-start gap-2 sm:items-end">
                @if($report->status === 'reviewed')
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                        <flux:icon.check-circle class="size-4" /> Reviewed
                    </span>
                    @if($report->reviewer)
                        <span class="text-xs text-gray-500 dark:text-zinc-400">by {{ $report->reviewer->name }}{{ $report->reviewed_at ? ' · '.$report->reviewed_at->diffForHumans() : '' }}</span>
                    @endif
                @elseif($isReviewer)
                    <button type="button" wire:click="markReviewed"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        <flux:icon.check class="size-4" /> Mark reviewed
                    </button>
                @else
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">Waiting review</span>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-2 border-t border-gray-100 dark:border-zinc-800 sm:grid-cols-4">
            <div class="p-4">
                <dt class="text-xs text-gray-500 dark:text-zinc-400">Attendance</dt>
                <dd class="mt-0.5 text-lg font-bold text-gray-900 dark:text-white">{{ $report->attendance_count }}/{{ $report->enrolled_count }}</dd>
            </div>
            <div class="p-4">
                <dt class="text-xs text-gray-500 dark:text-zinc-400">Retention</dt>
                <dd class="mt-0.5 text-lg font-bold text-gray-900 dark:text-white">{{ $rate !== null ? $rate.'%' : '—' }}</dd>
            </div>
            <div class="p-4">
                <dt class="text-xs text-gray-500 dark:text-zinc-400">Teamwork</dt>
                <dd class="mt-0.5 text-lg text-amber-500">{{ $stars($report->teamwork_rating) }}</dd>
            </div>
            <div class="p-4">
                <dt class="text-xs text-gray-500 dark:text-zinc-400">Collaboration</dt>
                <dd class="mt-0.5 text-lg text-amber-500">{{ $stars($report->collaboration_rating) }}</dd>
            </div>
        </dl>
    </section>

    @if($report->follow_up_required)
        <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
            <flux:icon.flag class="size-5 shrink-0" /> The facilitator flagged this session for follow-up.
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        @foreach($sections as [$label, $text, $icon, $tile])
            <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 {{ $label === 'Summary' ? 'md:col-span-2' : '' }}">
                <div class="mb-2 flex items-center gap-2">
                    <span class="flex size-7 items-center justify-center rounded-lg {{ $tile }}"><flux:icon :name="$icon" class="size-4" /></span>
                    <h2 class="text-sm font-bold text-gray-700 dark:text-zinc-300">{{ $label }}</h2>
                </div>
                <p class="whitespace-pre-line text-sm leading-relaxed {{ $text ? 'text-gray-700 dark:text-zinc-300' : 'text-gray-400' }}">{{ $text ?: 'Nothing added.' }}</p>
            </section>
        @endforeach
    </div>

    @if($report->admin_notes)
        <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-sm font-bold text-gray-700 dark:text-zinc-300">Earlier review note</h2>
            <p class="mt-2 whitespace-pre-line text-sm text-gray-700 dark:text-zinc-300">{{ $report->admin_notes }}</p>
        </section>
    @endif

    <livewire:comments.thread :commentable="$report" :key="'club-report-comments-'.$report->id"
        :placeholder="$isReviewer ? 'Leave feedback for the facilitator…' : 'Reply or add more detail…'" />
</div>
