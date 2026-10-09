@php
    $readable = fn (?string $text) => $text !== null && $text === mb_strtoupper($text) && preg_match('/\p{L}{4,}/u', $text)
        ? \Illuminate\Support\Str::title(mb_strtolower($text))
        : $text;

    $pending = (int) ($counts['pending'] ?? 0);
    $tabs = [
        'pending' => ['Waiting', $pending],
        'approved' => ['Approved', (int) ($counts['approved'] ?? 0)],
        'rejected' => ['Sent back', (int) ($counts['rejected'] ?? 0)],
        'all' => ['All', (int) $counts->sum()],
    ];
    $typeIcons = [
        'Course' => ['book-open', 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300'],
        'Module' => ['squares-2x2', 'bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-300'],
        'Lesson' => ['document-text', 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-300'],
        'Assessment' => ['clipboard-document-check', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300'],
        'Assignment' => ['pencil-square', 'bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-300'],
    ];
@endphp

<div class="mx-auto max-w-7xl space-y-5 p-4 sm:p-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Content approval</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                @if($pending > 0)
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $pending }} {{ \Illuminate\Support\Str::plural('item', $pending) }}</span> from trainers {{ $pending === 1 ? 'is' : 'are' }} waiting for you{{ $oldestPending ? ', the oldest from '.$oldestPending->diffForHumans() : '' }}.
                @else
                    Nothing is waiting. Trainers' new courses, lessons and assessments will show up here.
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-zinc-400">
            <flux:icon.check-badge class="size-5 text-emerald-500" />
            <span><span class="font-semibold text-gray-900 dark:text-white">{{ $reviewedThisWeek }}</span> reviewed this week</span>
        </div>
    </div>

    @if(session('message'))
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-200">
            <flux:icon.check-circle class="size-5 shrink-0" /> {{ session('message') }}
        </div>
    @endif

    @if($pendingRevisions > 0)
        <a href="{{ route('camp-revisions.index') }}" wire:navigate
           class="flex items-center gap-3 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800 hover:bg-orange-100 dark:border-orange-900 dark:bg-orange-900/20 dark:text-orange-200">
            <flux:icon.document-arrow-up class="size-5 shrink-0" />
            <span class="flex-1"><span class="font-semibold">{{ $pendingRevisions }} end-of-camp revised {{ $pendingRevisions === 1 ? 'submission' : 'submissions' }}</span> from trainers waiting for review.</span>
            <flux:icon.arrow-right class="size-4" />
        </a>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 dark:border-zinc-800 lg:flex-row lg:items-center lg:justify-between">
            <div class="inline-flex overflow-x-auto rounded-xl bg-gray-100 p-1 dark:bg-zinc-800">
                @foreach($tabs as $key => [$label, $count])
                    <button type="button" wire:click="filterByStatus('{{ $key }}')"
                            class="flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $filterStatus === $key ? 'bg-white text-gray-900 shadow-sm dark:bg-zinc-900 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-white' }}">
                        {{ $label }}
                        <span class="rounded-full px-1.5 text-xs {{ $key === 'pending' && $count > 0 ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-600 dark:bg-zinc-700 dark:text-zinc-300' }}">{{ $count }}</span>
                    </button>
                @endforeach
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative">
                    <flux:icon.magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search title or trainer"
                           class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm focus:border-orange-400 focus:ring-orange-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:w-60" />
                </div>
                <select wire:model.live="filterType"
                        class="rounded-xl border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm focus:border-orange-400 focus:ring-orange-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                    <option value="all">All types</option>
                    <option value="course">Courses</option>
                    <option value="module">Modules</option>
                    <option value="lesson">Lessons</option>
                    <option value="assessment">Assessments</option>
                    <option value="assignment">Assignments</option>
                </select>
                @if($filterStatus === 'pending' && $pending > 1)
                    <button type="button" wire:click="approveAll"
                            wire:confirm="Approve all {{ $pending }} waiting items without opening them? Trainers will be told each one is approved."
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        <flux:icon.check class="size-4" /> Approve all
                    </button>
                @endif
            </div>
        </div>

        {{-- List --}}
        @if($approvals->isNotEmpty())
            <ul class="divide-y divide-gray-100 dark:divide-zinc-800">
                @foreach($approvals as $approval)
                    @php
                        $type = $approval->typeLabel();
                        [$icon, $tile] = $typeIcons[$type] ?? ['document', 'bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-zinc-300'];
                        $item = $approval->approvable;
                        $course = $item instanceof \App\Models\Course ? null : $item?->course;
                        $waitingDays = $approval->status === 'pending' && $approval->submitted_at ? (int) $approval->submitted_at->diffInDays(now()) : 0;
                    @endphp
                    <li wire:key="approval-{{ $approval->id }}" class="flex flex-col gap-3 px-4 py-4 transition hover:bg-gray-50/70 dark:hover:bg-zinc-800/40 sm:flex-row sm:items-center sm:px-5">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $tile }}">
                                <flux:icon :name="$icon" class="size-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <a href="{{ route('content-approvals.review', $approval) }}" wire:navigate class="truncate font-semibold text-gray-900 hover:text-orange-600 dark:text-white dark:hover:text-orange-400">
                                        {{ $item ? $readable($item->title) : 'Deleted item' }}
                                    </a>
                                    <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $type }}</span>
                                    @if($approval->priority === 'high')
                                        <span class="rounded-md bg-red-50 px-1.5 py-0.5 text-[11px] font-semibold text-red-600 dark:bg-red-900/30 dark:text-red-300">Urgent</span>
                                    @endif
                                    @if($waitingDays >= 3)
                                        <span class="rounded-md bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Waiting {{ $waitingDays }} days</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 truncate text-sm text-gray-500 dark:text-zinc-400">
                                    @if($course){{ $readable($course->title) }} · @endif{{ $approval->submitter?->name ?? 'Unknown trainer' }}@if($approval->submitted_at) · sent {{ $approval->submitted_at->diffForHumans() }}@endif
                                </p>
                                @if($approval->status === 'rejected' && $approval->rejection_reason)
                                    <p class="mt-2 line-clamp-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700 dark:bg-red-900/20 dark:text-red-300">
                                        <span class="font-semibold">Sent back:</span> {{ $approval->rejection_reason }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2 pl-13 sm:pl-0">
                            @if($approval->status === 'pending')
                                @if($item)
                                    <button type="button" wire:click="approveContent({{ $approval->id }})"
                                            wire:confirm="Approve &quot;{{ $item->title }}&quot; without opening it?"
                                            class="inline-flex items-center gap-1 rounded-xl border border-emerald-200 px-3 py-1.5 text-sm font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-900 dark:text-emerald-300 dark:hover:bg-emerald-900/20">
                                        <flux:icon.check class="size-4" /> Approve
                                    </button>
                                @endif
                                <a href="{{ route('content-approvals.review', $approval) }}" wire:navigate
                                   class="inline-flex items-center gap-1 rounded-xl bg-orange-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-orange-600">
                                    Review <flux:icon.arrow-right class="size-4" />
                                </a>
                            @else
                                <span class="text-right text-xs text-gray-500 dark:text-zinc-400">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $approval->status === 'approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                        {{ $approval->status === 'approved' ? 'Approved' : 'Sent back' }}
                                    </span>
                                    @if($approval->reviewed_at)
                                        <span class="mt-0.5 block">{{ $approval->reviewer?->name ? 'by '.$approval->reviewer->name.' · ' : '' }}{{ $approval->reviewed_at->diffForHumans() }}</span>
                                    @endif
                                </span>
                                <a href="{{ route('content-approvals.review', $approval) }}" wire:navigate
                                   class="rounded-xl border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Open</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            @if($approvals->hasPages())
                <div class="border-t border-gray-100 px-4 py-3 dark:border-zinc-800">{{ $approvals->links() }}</div>
            @endif
        @else
            <div class="px-6 py-14 text-center">
                <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300">
                    <flux:icon.check-badge class="size-6" />
                </span>
                @if($search !== '' || $filterType !== 'all')
                    <p class="mt-3 font-semibold text-gray-900 dark:text-white">Nothing matches</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Try another search or type.</p>
                @elseif($filterStatus === 'pending')
                    <p class="mt-3 font-semibold text-gray-900 dark:text-white">You're all caught up</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">No content is waiting for approval.</p>
                @else
                    <p class="mt-3 font-semibold text-gray-900 dark:text-white">Nothing here yet</p>
                @endif
            </div>
        @endif
    </section>
</div>
