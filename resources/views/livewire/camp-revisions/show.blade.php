@php
    $badge = [
        'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'changes_requested' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    ];
    $canEditFiles = $isOwner && $revision->status !== 'approved';
@endphp

<div class="mx-auto max-w-4xl space-y-5 p-4 sm:p-6">
    <a href="{{ route('camp-revisions.index') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-white">
        <flux:icon.arrow-left class="size-4" /> Revised content
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
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">End-of-camp revised content</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $revision->title }}</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-zinc-400">
                    {{ $revision->camp?->name ?? 'Camp' }}@if($revision->course) · {{ $revision->course->title }}@endif
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <x-user-avatar :user="$revision->trainer" size="xs" rounded="full" />
                    <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">{{ $revision->trainer?->name }}</span>
                    @if($revision->submitted_at)
                        <span class="text-xs text-gray-400">· sent {{ $revision->submitted_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 flex-col items-start gap-1 sm:items-end">
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[$revision->status] ?? '' }}">{{ $revision->statusLabel() }}</span>
                @if($revision->reviewer && $revision->status !== 'pending')
                    <span class="text-xs text-gray-500 dark:text-zinc-400">by {{ $revision->reviewer->name }} · {{ $revision->reviewed_at?->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    </section>

    @if($isOwner && $revision->status === 'changes_requested')
        <div class="flex flex-col gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-900/20 sm:flex-row sm:items-center">
            <div class="flex-1 text-sm text-red-800 dark:text-red-200">
                <p class="font-semibold">Your supervisor asked for changes.</p>
                @if($revision->review_notes)<p class="mt-1 whitespace-pre-line">{{ $revision->review_notes }}</p>@endif
                <p class="mt-1 text-xs">Upload the updated files below, then send it back.</p>
            </div>
            <button type="button" wire:click="resubmit" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">
                <flux:icon.arrow-path class="size-4" /> Send back for review
            </button>
        </div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <h2 class="text-sm font-bold text-gray-900 dark:text-white">What changed and why</h2>
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-700 dark:text-zinc-300">{{ $revision->notes }}</p>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <header class="flex items-center justify-between gap-2 border-b border-gray-100 px-5 py-4 dark:border-zinc-800">
            <h2 class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white">
                <flux:icon.paper-clip class="size-5 text-orange-500" /> Files
                <span class="rounded-full bg-gray-100 px-2 text-xs font-medium text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $revision->files->count() }}</span>
            </h2>
            @if($canEditFiles)
                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                    <flux:icon.arrow-up-tray class="size-4" />
                    <span wire:loading.remove wire:target="picked">Add files</span>
                    <span wire:loading wire:target="picked">Uploading…</span>
                    <input type="file" wire:model="picked" multiple class="sr-only" />
                </label>
            @endif
        </header>
        @error('picked.*') <p class="px-5 pt-3 text-xs text-red-600">{{ $message }}</p> @enderror
        @if($revision->files->isNotEmpty())
            <ul class="divide-y divide-gray-100 dark:divide-zinc-800">
                @foreach($revision->files as $file)
                    <li wire:key="file-{{ $file->id }}" class="flex items-center gap-3 px-5 py-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-[10px] font-bold uppercase text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $file->extension() ?: 'file' }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $file->original_name }}</p>
                            <p class="text-xs text-gray-400">{{ $file->humanSize() }} · {{ $file->created_at->format('j M Y') }}</p>
                        </div>
                        <a href="{{ route('camp-revisions.files.download', $file) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-sm font-medium text-orange-600 hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-900/20">
                            <flux:icon.arrow-down-tray class="size-4" /> Download
                        </a>
                        @if($canEditFiles)
                            <button type="button" wire:click="deleteFile({{ $file->id }})" wire:confirm="Remove {{ $file->original_name }}?" class="text-gray-400 hover:text-red-500" aria-label="Remove file">
                                <flux:icon.trash class="size-4" />
                            </button>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="px-5 py-4 text-sm text-gray-500 dark:text-zinc-400">No files attached.</p>
        @endif
    </section>

    @if($isReviewer && $revision->status === 'pending')
        <section class="rounded-2xl border border-orange-200 bg-orange-50/60 p-5 dark:border-orange-900 dark:bg-orange-900/10">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">Your review</h2>
            <textarea wire:model="reviewNote" rows="3" placeholder="Note for the trainer (needed if you send it back)"
                      class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:border-orange-400 focus:ring-orange-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"></textarea>
            @error('reviewNote') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <div class="mt-3 flex flex-wrap justify-end gap-2">
                <button type="button" wire:click="requestChanges" class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-900 dark:bg-transparent dark:text-red-300">
                    <flux:icon.arrow-uturn-left class="size-4" /> Ask for changes
                </button>
                <button type="button" wire:click="approve" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    <flux:icon.check class="size-4" /> Approve
                </button>
            </div>
        </section>
    @endif

    <livewire:comments.thread :commentable="$revision" :key="'revision-comments-'.$revision->id"
        :placeholder="$isOwner ? 'Reply to your supervisor…' : 'Comment for the trainer…'" />
</div>
