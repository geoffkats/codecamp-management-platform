<div class="flex flex-col items-center justify-center px-6 py-16 text-center">
    <div class="flex size-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 dark:bg-orange-950/40">
        <flux:icon.inbox class="size-7" />
    </div>
    <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
    <p class="mt-1 max-w-sm text-sm text-zinc-500">{{ $text }}</p>
    @if (! empty($action))
        <button type="button" wire:click="openEnrollPanel" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-700">
            <flux:icon.user-plus class="size-4" /> Enroll students
        </button>
    @endif
</div>
