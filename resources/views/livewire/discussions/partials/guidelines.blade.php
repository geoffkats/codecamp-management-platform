@props(['compact' => false])

<div @class([
    'rounded-lg border border-blue-200 bg-blue-50/80 dark:border-blue-900 dark:bg-blue-950/40',
    'p-4' => $compact,
    'p-5' => ! $compact,
])>
    <div class="flex items-start gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#1a3a8f] text-white">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <h2 class="text-sm font-bold text-[#1a3a8f] dark:text-blue-200">Community guidelines</h2>
            @unless($compact)
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Keep discussions useful for everyone. Posts that break these rules may be closed by staff.</p>
            @endunless
            <ul class="mt-3 space-y-1.5 text-sm text-slate-700 dark:text-slate-300">
                <li><strong class="font-semibold">Stay on topic</strong> — post in the correct course. Use <em>Question</em> or <em>Help</em> when you need support.</li>
                <li><strong class="font-semibold">Clear titles</strong> — e.g. “HTML form not submitting” not “help me” or “urgent!!!”.</li>
                <li><strong class="font-semibold">One issue per post</strong> — start a new thread for a new problem.</li>
                <li><strong class="font-semibold">Show your work</strong> — say what you tried, what happened, and paste error messages or screenshots if relevant.</li>
                <li><strong class="font-semibold">Be respectful</strong> — no insults, spam, memes, or unrelated chat.</li>
                <li><strong class="font-semibold">Search first</strong> — someone may have already asked the same thing.</li>
            </ul>
        </div>
    </div>
</div>
