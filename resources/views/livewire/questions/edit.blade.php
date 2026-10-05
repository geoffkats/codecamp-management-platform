<div class="max-w-7xl mx-auto px-4 py-5 space-y-5">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-10 -top-16 size-48 rounded-full bg-white/10"></div>
        <div class="relative flex flex-wrap items-end justify-between gap-3">
            <div>
                <a href="{{ route('questions.index') }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-widest text-orange-100 hover:text-white">
                    <flux:icon name="arrow-left" variant="micro" class="size-3.5" /> Question Bank
                </a>
                <h1 class="mt-1 text-2xl font-extrabold">Edit question</h1>
                <p class="text-sm text-orange-100">Attempts already started keep the version they were given.</p>
            </div>
            <span class="rounded-xl bg-white/20 px-3 py-1.5 text-xs font-bold">Version {{ $question->version }}</span>
        </div>
    </div>

    <livewire:questions.question-editor :question-id="$question->id" context="page" />
</div>
