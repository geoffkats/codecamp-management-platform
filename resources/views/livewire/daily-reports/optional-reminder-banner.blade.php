<div>
    @if($show && !$dismissed)
        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-amber-900 dark:text-amber-200">Optional: daily Code Camp report</p>
                    <p class="mt-0.5 text-sm text-amber-800 dark:text-amber-300">
                        If you ran a Code Camp session today, please submit the daily report. Skip this on normal / non-camp days.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('daily-reports.submit') }}" wire:navigate
                       class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                        Submit report
                    </a>
                    <button type="button" wire:click="dismiss"
                            class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100 dark:text-amber-100 dark:hover:bg-amber-800/40">
                        Not today
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
