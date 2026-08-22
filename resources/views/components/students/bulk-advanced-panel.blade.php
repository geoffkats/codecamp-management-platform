@props([
    'canDeactivate' => false,
    'canDelete' => false,
    'exportAction' => null,
    'deactivateAction' => 'bulkDeactivateStudents',
    'deleteAction' => 'bulkDeleteStudents',
])

@if($canDeactivate || $canDelete)
    <div x-data="{ open: false }" class="mt-3 w-full rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900/50 overflow-hidden">
        <button type="button" @click="open = !open"
            class="flex w-full items-center justify-between px-3 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Advanced settings</span>
            <svg :class="open ? 'rotate-180' : ''" class="h-4 w-4 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-collapse class="border-t border-slate-100 px-3 py-3 space-y-3 dark:border-zinc-800">
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-100">
                <p class="font-semibold">Before removing students</p>
                <p class="mt-0.5 text-amber-800/90 dark:text-amber-200/80">
                    Download a CSV backup first if you may need the data later.
                    Permanent removals are recorded in
                    @if(Route::has('admin.audit.logs'))
                        <a href="{{ route('admin.audit.logs') }}" wire:navigate class="font-semibold underline">Audit Logs</a>.
                    @else
                        Audit Logs.
                    @endif
                </p>
            </div>

            @if($exportAction)
                <button type="button" wire:click="{{ $exportAction }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-200">
                    ↓ Download backup (CSV)
                </button>
            @endif

            <div class="flex flex-wrap gap-2">
                @if($canDeactivate)
                    <button type="button" wire:click="{{ $deactivateAction }}"
                        wire:confirm="Deactivate selected students? They will no longer be able to sign in."
                        class="rounded-lg bg-slate-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                        Deactivate accounts
                    </button>
                @endif

                @if($canDelete)
                    <button type="button" wire:click="{{ $deleteAction }}"
                        wire:confirm="Permanently remove selected students from the system? Profiles will be deleted, login accounts deactivated, and this action will appear in Audit Logs. Export a CSV backup first if needed."
                        class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                        Delete from system
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif
