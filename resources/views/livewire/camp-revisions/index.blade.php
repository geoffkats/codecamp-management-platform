@php
    $pending = (int) ($counts['pending'] ?? 0);
    $tabs = ['all' => ['All', (int) $counts->sum()]];
    foreach (\App\Models\CampContentRevision::STATUSES as $key => $label) {
        $tabs[$key] = [$label, (int) ($counts[$key] ?? 0)];
    }
    $badge = [
        'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'changes_requested' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    ];
@endphp

<div class="mx-auto max-w-6xl space-y-5 p-4 sm:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Revised content</h1>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-zinc-400">
                @if($isReviewer)
                    At the end of each camp, trainers send the content they improved while teaching.
                    @if($pending > 0)<span class="font-semibold text-gray-900 dark:text-white">{{ $pending }} waiting for you.</span>@endif
                @else
                    After a camp, share the slides, notes and projects you improved while teaching. Your supervisor reviews them and can comment.
                @endif
            </p>
        </div>
        @if($canSubmit)
            <a href="{{ route('camp-revisions.create') }}" wire:navigate
               class="inline-flex items-center gap-1.5 self-start rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600 sm:self-auto">
                <flux:icon.document-arrow-up class="size-4" /> Submit revised content
            </a>
        @endif
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 dark:border-zinc-800 lg:flex-row lg:items-center lg:justify-between">
            <div class="inline-flex overflow-x-auto rounded-xl bg-gray-100 p-1 dark:bg-zinc-800">
                @foreach($tabs as $key => [$label, $count])
                    <button type="button" wire:click="setStatus('{{ $key }}')"
                            class="flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $status === $key ? 'bg-white text-gray-900 shadow-sm dark:bg-zinc-900 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-white' }}">
                        {{ $label }}
                        <span class="rounded-full px-1.5 text-xs {{ $key === 'pending' && $count > 0 && $isReviewer ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-600 dark:bg-zinc-700 dark:text-zinc-300' }}">{{ $count }}</span>
                    </button>
                @endforeach
            </div>
            @if($camps->count() > 1)
                <select wire:model.live="campId" class="rounded-xl border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm focus:border-orange-400 focus:ring-orange-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                    <option value="">All camps</option>
                    @foreach($camps as $camp)
                        <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        @if($revisions->isNotEmpty())
            <ul class="divide-y divide-gray-100 dark:divide-zinc-800">
                @foreach($revisions as $revision)
                    <li wire:key="revision-{{ $revision->id }}">
                        <a href="{{ route('camp-revisions.show', $revision) }}" wire:navigate
                           class="flex items-start gap-3 px-4 py-4 transition hover:bg-gray-50/70 dark:hover:bg-zinc-800/40 sm:items-center sm:px-5">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-300">
                                <flux:icon.document-arrow-up class="size-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span class="truncate font-semibold text-gray-900 dark:text-white">{{ $revision->title }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $badge[$revision->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $revision->statusLabel() }}</span>
                                </div>
                                <p class="mt-0.5 truncate text-sm text-gray-500 dark:text-zinc-400">
                                    {{ $revision->camp?->name ?? 'Camp' }}@if($revision->course) · {{ $revision->course->title }}@endif
                                    @if($isReviewer) · {{ $revision->trainer?->name }}@endif
                                    @if($revision->submitted_at) · {{ $revision->submitted_at->diffForHumans() }}@endif
                                </p>
                            </div>
                            <div class="hidden shrink-0 items-center gap-4 text-xs text-gray-500 dark:text-zinc-400 sm:flex">
                                <span class="inline-flex items-center gap-1"><flux:icon.paper-clip class="size-4" /> {{ $revision->files_count }}</span>
                                <span class="inline-flex items-center gap-1 {{ $revision->comments_count ? 'font-semibold text-orange-600 dark:text-orange-400' : '' }}"><flux:icon.chat-bubble-left class="size-4" /> {{ $revision->comments_count }}</span>
                                <flux:icon.chevron-right class="size-4" />
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
            @if($revisions->hasPages())
                <div class="border-t border-gray-100 px-4 py-3 dark:border-zinc-800">{{ $revisions->links() }}</div>
            @endif
        @else
            <div class="px-6 py-14 text-center">
                <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-300">
                    <flux:icon.document-arrow-up class="size-6" />
                </span>
                <p class="mt-3 font-semibold text-gray-900 dark:text-white">{{ $isReviewer && $status === 'pending' ? 'Nothing waiting' : 'No revised content yet' }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                    {{ $isReviewer ? 'Trainers\' end-of-camp submissions will show up here.' : 'When a camp ends, send what you improved so the next camp starts from better material.' }}
                </p>
            </div>
        @endif
    </section>
</div>
