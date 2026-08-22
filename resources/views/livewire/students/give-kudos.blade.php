@php
    $isOwnProfile = auth()->id() === $toUserId;
    $isStudent    = auth()->check() && auth()->user()->isStudent();
@endphp

<div class="flex flex-col items-center gap-2">
    {{-- Kudos count badge --}}
    <div class="flex items-center gap-1.5 text-sm font-semibold text-pink-600 dark:text-pink-400">
        <span class="text-xl">👏</span>
        <span>{{ number_format($totalKudos) }}</span>
        <span class="text-xs font-normal text-gray-500 dark:text-gray-400">kudos</span>
    </div>

    @if(!$isOwnProfile && $isStudent)
        @if($justGiven)
            <div class="flex items-center gap-1.5 px-4 py-2 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-semibold animate-bounce">
                ✅ Kudos sent!
            </div>
        @elseif($alreadyGivenToday)
            <div class="px-4 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-full text-xs font-medium">
                👏 Kudos sent today
            </div>
        @else
            <div x-data="{ open: false }" class="flex flex-col items-center gap-2">
                <button
                    @click="open = !open"
                    class="flex items-center gap-1.5 px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-full text-xs font-semibold transition-all hover:scale-105 shadow-sm"
                >
                    👏 Give Kudos
                </button>
                <div x-show="open" x-transition class="w-full flex flex-col gap-2">
                    <input
                        wire:model="message"
                        type="text"
                        maxlength="100"
                        placeholder="Optional message (100 chars)"
                        class="w-full text-xs rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:ring-1 focus:ring-pink-400 outline-none"
                    />
                    <button
                        wire:click="give"
                        wire:loading.attr="disabled"
                        class="w-full px-4 py-1.5 bg-pink-500 hover:bg-pink-600 disabled:opacity-50 text-white rounded-lg text-xs font-semibold transition-all"
                    >
                        <span wire:loading.remove>Send 👏</span>
                        <span wire:loading>Sending…</span>
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
