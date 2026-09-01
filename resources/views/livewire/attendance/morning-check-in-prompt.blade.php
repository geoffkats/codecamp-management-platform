<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    @if($show)
        <div class="mb-5 rounded-2xl border-2 border-orange-300 bg-orange-50 p-5 dark:border-orange-700 dark:bg-orange-900/20">
            <p class="text-xs font-bold uppercase tracking-widest text-orange-700 dark:text-orange-300">Good morning</p>
            <h2 class="mt-1 text-xl font-extrabold text-orange-950 dark:text-orange-100">Tap to check in</h2>
            <p class="mt-1 text-sm text-orange-800 dark:text-orange-200">Mark yourself present for today. One tap is enough — no code needed.</p>
            <button type="button" wire:click="checkInNow" wire:loading.attr="disabled"
                    class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-orange-600 px-6 py-4 text-lg font-bold text-white shadow-lg hover:bg-orange-700 disabled:opacity-60 sm:w-auto">
                <span wire:loading.remove wire:target="checkInNow">I'm here — check in</span>
                <span wire:loading wire:target="checkInNow">Checking in…</span>
            </button>
        </div>
    @elseif($checkedIn)
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200">
            Checked in today{{ $checkedInAt ? ' at '.$checkedInAt : '' }}.
        </div>
    @endif
</div>
