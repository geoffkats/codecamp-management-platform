@props([
    'title' => 'Achievement',
    'description' => null,
    'icon' => '🏆',
    'earned' => false,
    'date' => null,
    'size' => 'md', // sm, md, lg
])

@php
$sizes = [
    'sm' => 'w-16 h-16',
    'md' => 'w-24 h-24',
    'lg' => 'w-32 h-32',
];

$iconSizes = [
    'sm' => 'text-2xl',
    'md' => 'text-4xl',
    'lg' => 'text-5xl',
];

$containerSize = $sizes[$size] ?? $sizes['md'];
$iconSize = $iconSizes[$size] ?? $iconSizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center text-center']) }}>
    {{-- Badge Circle --}}
    <div class="relative {{ $containerSize }} mb-3">
        @if($earned)
            {{-- Earned Badge --}}
            <div class="w-full h-full rounded-full bg-gradient-to-br from-yellow-400 via-orange-400 to-pink-500 flex items-center justify-center shadow-2xl transform hover:scale-110 transition-transform duration-300 animate-pulse-slow">
                <div class="w-[90%] h-[90%] rounded-full bg-gradient-to-br from-yellow-300 to-orange-300 flex items-center justify-center">
                    <span class="{{ $iconSize }}">{{ $icon }}</span>
                </div>
            </div>
            {{-- Sparkle Effect --}}
            <div class="absolute -top-1 -right-1 w-6 h-6 bg-yellow-300 rounded-full animate-ping"></div>
        @else
            {{-- Locked Badge --}}
            <div class="w-full h-full rounded-full bg-gray-300 dark:bg-gray-700 flex items-center justify-center shadow-lg opacity-50">
                <div class="w-[90%] h-[90%] rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        @endif
    </div>

    {{-- Badge Info --}}
    <div class="max-w-xs">
        <h3 class="font-bold {{ $earned ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }} mb-1">
            {{ $title }}
        </h3>
        @if($description)
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                {{ $description }}
            </p>
        @endif
        @if($earned && $date)
            <p class="text-xs text-gray-500 dark:text-gray-500">
                Earned {{ $date }}
            </p>
        @elseif(!$earned)
            <p class="text-xs text-gray-400 dark:text-gray-600 italic">
                Not yet earned
            </p>
        @endif
    </div>
</div>

<style>
@keyframes pulse-slow {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

.animate-pulse-slow {
    animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
