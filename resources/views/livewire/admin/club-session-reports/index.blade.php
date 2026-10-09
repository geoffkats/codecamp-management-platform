@php
    $waiting = (int) ($counts['submitted'] ?? 0);
    $tabs = [
        'all' => ['All', (int) $counts->sum()],
        'submitted' => [$isReviewer ? 'Waiting review' : 'Submitted', $waiting],
        'reviewed' => ['Reviewed', (int) ($counts['reviewed'] ?? 0)],
    ];
    $input = 'rounded-xl border border-gray-200 bg-white py-2 px-3 text-sm focus:border-orange-400 focus:ring-orange-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white';
@endphp

<div class="mx-auto max-w-7xl space-y-5 p-4 sm:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Code Club reports</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                @if($isReviewer && $waiting > 0)
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $waiting }} {{ \Illuminate\Support\Str::plural('report', $waiting) }}</span> waiting for your review. Open one to read it and leave a comment.
                @elseif($isReviewer)
                    All session reports are reviewed.
                @else
                    Your club session reports. You'll be notified when someone comments.
                @endif
            </p>
        </div>
        @unless($isReviewer)
            <a href="{{ route('club-session-reports.submit') }}" wire:navigate
               class="inline-flex items-center gap-1.5 self-start rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600 sm:self-auto">
                <flux:icon.plus class="size-4" /> New report
            </a>
        @endunless
    </div>

    @if(session('message'))
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-200">
            <flux:icon.check-circle class="size-5 shrink-0" /> {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-3 gap-3">
        @foreach([
            ['Reports, last 30 days', $monthStats['reports'], 'document-text', 'text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-300'],
            ['Average attendance', $monthStats['attendance'] !== null ? $monthStats['attendance'].'%' : '—', 'user-group', 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-300'],
            ['Need follow-up', $monthStats['followUps'], 'flag', 'text-amber-600 bg-amber-50 dark:bg-amber-900/30 dark:text-amber-300'],
        ] as [$label, $value, $icon, $tile])
            <div class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <span class="hidden size-10 shrink-0 items-center justify-center rounded-xl sm:flex {{ $tile }}"><flux:icon :name="$icon" class="size-5" /></span>
                <div class="min-w-0">
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
                    <p class="truncate text-xs text-gray-500 dark:text-zinc-400">{{ $label }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 dark:border-zinc-800 xl:flex-row xl:items-center xl:justify-between">
            <div class="inline-flex overflow-x-auto rounded-xl bg-gray-100 p-1 dark:bg-zinc-800">
                @foreach($tabs as $key => [$label, $count])
                    <button type="button" wire:click="setStatus('{{ $key }}')"
                            class="flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $status === $key ? 'bg-white text-gray-900 shadow-sm dark:bg-zinc-900 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-white' }}">
                        {{ $label }}
                        <span class="rounded-full px-1.5 text-xs {{ $key === 'submitted' && $count > 0 && $isReviewer ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-600 dark:bg-zinc-700 dark:text-zinc-300' }}">{{ $count }}</span>
                    </button>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-2">
                @if($schools->count() > 1)
                    <select wire:model.live="schoolId" class="{{ $input }} pr-8">
                        <option value="">All schools</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                @endif
                <select wire:model.live="clubId" class="{{ $input }} pr-8">
                    <option value="">All clubs</option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}">{{ $club->name }}</option>
                    @endforeach
                </select>
                <input type="date" wire:model.live="dateFrom" class="{{ $input }}" aria-label="From date" />
                <input type="date" wire:model.live="dateTo" class="{{ $input }}" aria-label="To date" />
            </div>
        </div>

        @if($reports->isNotEmpty())
            <ul class="divide-y divide-gray-100 dark:divide-zinc-800">
                @foreach($reports as $report)
                    @php $rate = $report->retentionRate(); @endphp
                    <li wire:key="club-report-{{ $report->id }}" class="flex flex-col gap-3 px-4 py-4 transition hover:bg-gray-50/70 dark:hover:bg-zinc-800/40 sm:flex-row sm:items-center sm:px-5">
                        <a href="{{ route('admin.club-session-reports.show', $report) }}" wire:navigate class="flex min-w-0 flex-1 items-start gap-4">
                            <div class="w-12 shrink-0 text-center">
                                <p class="text-[11px] font-bold uppercase text-gray-400">{{ $report->session_date->format('M') }}</p>
                                <p class="text-2xl font-extrabold leading-none text-gray-900 dark:text-white">{{ $report->session_date->format('d') }}</p>
                                <p class="text-[11px] text-gray-400">{{ $report->session_date->format('D') }}</p>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span class="truncate font-semibold text-gray-900 dark:text-white">{{ $report->club?->name ?? 'Club' }}</span>
                                    @if($report->follow_up_required)
                                        <span class="rounded-md bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Follow-up</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 truncate text-sm text-gray-500 dark:text-zinc-400">
                                    {{ $report->facilitator?->name ?? 'Unknown facilitator' }} · {{ $report->topics_covered ? \Illuminate\Support\Str::limit($report->topics_covered, 70) : \Illuminate\Support\Str::limit($report->summary, 70) }}
                                </p>
                                <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-zinc-400">
                                    <span class="inline-flex items-center gap-1"><flux:icon.user-group class="size-3.5" /> {{ $report->attendance_count }}/{{ $report->enrolled_count }}@if($rate !== null) ({{ $rate }}%)@endif</span>
                                    @if($report->teamwork_rating)
                                        <span title="Teamwork">Teamwork <span class="text-amber-500">{{ str_repeat('★', $report->teamwork_rating) }}</span></span>
                                    @endif
                                    @if($report->comments_count)
                                        <span class="inline-flex items-center gap-1 font-medium text-orange-600 dark:text-orange-400"><flux:icon.chat-bubble-left class="size-3.5" /> {{ $report->comments_count }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        <div class="flex shrink-0 items-center gap-2 pl-16 sm:pl-0">
                            @if($report->status === 'reviewed')
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                                      title="{{ $report->reviewer?->name ? 'Reviewed by '.$report->reviewer->name : '' }}">Reviewed</span>
                            @elseif($isReviewer)
                                <button type="button" wire:click="markReviewed({{ $report->id }})"
                                        class="inline-flex items-center gap-1 rounded-xl border border-emerald-200 px-3 py-1.5 text-sm font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-900 dark:text-emerald-300 dark:hover:bg-emerald-900/20">
                                    <flux:icon.check class="size-4" /> Mark reviewed
                                </button>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">Waiting review</span>
                            @endif
                            <a href="{{ route('admin.club-session-reports.show', $report) }}" wire:navigate
                               class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Open</a>
                        </div>
                    </li>
                @endforeach
            </ul>
            @if($reports->hasPages())
                <div class="border-t border-gray-100 px-4 py-3 dark:border-zinc-800">{{ $reports->links() }}</div>
            @endif
        @else
            <div class="px-6 py-14 text-center">
                <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <flux:icon.document-text class="size-6" />
                </span>
                <p class="mt-3 font-semibold text-gray-900 dark:text-white">No reports here</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Try another club, date or tab.</p>
            </div>
        @endif
    </section>
</div>
