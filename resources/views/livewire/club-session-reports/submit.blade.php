@php
    $input = 'mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:border-orange-400 focus:ring-orange-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white';
    $label = 'text-sm font-medium text-gray-700 dark:text-zinc-300';
    $ratings = [
        'teamworkRating' => ['Teamwork', 'How well did the students work together?'],
        'collaborationRating' => ['Collaboration', 'Did they share ideas and help each other?'],
    ];
@endphp

<div class="mx-auto max-w-3xl space-y-5 p-4 sm:p-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Club session report</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">Tell your supervisor how today's Code Club session went. They can reply with comments.</p>
        </div>
        <a href="{{ route('admin.club-session-reports.index') }}" wire:navigate
           class="inline-flex items-center gap-1.5 self-start rounded-xl border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800 sm:self-auto">
            <flux:icon.document-text class="size-4" /> My reports
        </a>
    </div>

    @if(session('message'))
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-200">
            <flux:icon.check-circle class="size-5 shrink-0" /> {{ session('message') }}
        </div>
    @endif

    <form wire:submit="submit" class="space-y-5">
        <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">Session</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="{{ $label }}" for="sessionDate">Date</label>
                    <input id="sessionDate" type="date" wire:model.live="sessionDate" max="{{ now()->toDateString() }}" class="{{ $input }}" />
                    @error('sessionDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}" for="clubId">Club</label>
                    <select id="clubId" wire:model.live="clubId" class="{{ $input }}">
                        <option value="">Select club</option>
                        @foreach($clubs as $club)
                            <option value="{{ $club->id }}">{{ $club->name }}</option>
                        @endforeach
                    </select>
                    @error('clubId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-4 flex items-center gap-3 rounded-xl bg-gray-50 p-3 dark:bg-zinc-800/60">
                <flux:icon.user-group class="size-5 shrink-0 text-emerald-600" />
                <div class="flex-1 text-sm text-gray-600 dark:text-zinc-300">
                    Students present <span class="text-xs text-gray-400">(filled from today's attendance, change it if needed)</span>
                </div>
                <input type="number" wire:model="attendanceCount" min="0" aria-label="Students present"
                       class="w-20 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-center text-sm font-semibold dark:border-zinc-700 dark:bg-zinc-950 dark:text-white" />
            </div>
            @error('attendanceCount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </section>

        <section class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">What happened</h2>
            <div>
                <label class="{{ $label }}" for="summary">Summary <span class="text-red-500">*</span></label>
                <textarea id="summary" wire:model="summary" rows="4" placeholder="What did the club do today? What went well?" class="{{ $input }}"></textarea>
                @error('summary') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $label }}" for="topicsCovered">Topics taught</label>
                    <textarea id="topicsCovered" wire:model="topicsCovered" rows="3" placeholder="e.g. Scratch loops, sprites" class="{{ $input }}"></textarea>
                </div>
                <div>
                    <label class="{{ $label }}" for="newTechniques">New techniques</label>
                    <textarea id="newTechniques" wire:model="newTechniques" rows="3" placeholder="New tools or skills introduced" class="{{ $input }}"></textarea>
                </div>
            </div>
            <div>
                <label class="{{ $label }}" for="challenges">Challenges</label>
                <textarea id="challenges" wire:model="challenges" rows="2" placeholder="Anything that got in the way: devices, internet, behaviour…" class="{{ $input }}"></textarea>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">How the group worked</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                @foreach($ratings as $field => [$ratingLabel, $hint])
                    <div>
                        <p class="{{ $label }}">{{ $ratingLabel }} <span class="text-red-500">*</span></p>
                        <p class="text-xs text-gray-500 dark:text-zinc-400">{{ $hint }}</p>
                        <div class="mt-2 flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" wire:click="$set('{{ $field }}', {{ $i }})" aria-label="{{ $ratingLabel }} {{ $i }} of 5"
                                        class="text-3xl leading-none transition hover:scale-110 {{ ($this->{$field} ?? 0) >= $i ? 'text-amber-400' : 'text-gray-200 dark:text-zinc-700' }}">★</button>
                            @endfor
                        </div>
                        @error($field) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-zinc-300">
                <input type="checkbox" wire:model="followUpRequired" class="rounded border-gray-300 text-orange-500 focus:ring-orange-400" />
                This session needs follow-up from my supervisor
            </label>
            <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 disabled:opacity-60">
                <flux:icon.paper-airplane class="size-4" /> Submit report
            </button>
        </div>
    </form>
</div>
