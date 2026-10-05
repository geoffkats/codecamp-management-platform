<div class="max-w-7xl mx-auto px-4 py-5 space-y-5">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-10 -top-16 size-48 rounded-full bg-white/10"></div>
        <a href="{{ route('questions.index') }}" wire:navigate class="relative inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-widest text-orange-100 hover:text-white">
            <flux:icon name="arrow-left" variant="micro" class="size-3.5" /> Question Bank
        </a>
        <h1 class="relative mt-1 text-2xl font-extrabold">New question</h1>
        <p class="relative text-sm text-orange-100">Write it once, then add it to any assessment from the curriculum builder.</p>
    </div>

    <livewire:questions.question-editor context="page" />
</div>
