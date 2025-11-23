@props([
    'days' => 0,
    'label' => 'Day Streak',
    'icon' => '🔥',
    'size' => 'md', // sm, md, lg
])

@php
$sizes = [
    'sm' => ['container' => 'p-3', 'icon' => 'text-2xl', 'number' => 'text-xl', 'label' => 'text-xs'],
    'md' => ['container' => 'p-4', 'icon' => 'text-3xl', 'number' => 'text-3xl', 'label' => 'text-sm'],
    'lg' => ['container' => 'p-6', 'icon' => 'text-4xl', 'number' => 'text-4xl', 'label' => 'text-base'],
];

$sizeClasses = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 bg-gradient-to-br from-orange-500 to-red-500 text-white rounded-2xl shadow-lg ' . $sizeClasses['container']]) }}>
    {{-- Flame Icon --}}
    <div class="flex-shrink-0">
        <span class="{{ $sizeClasses['icon'] }} {{ $days > 0 ? 'animate-bounce' : '' }}">{{ $icon }}</span>
    </div>
    
    {{-- Streak Info --}}
    <div class="flex flex-col">
        <div class="font-bold {{ $sizeClasses['number'] }} leading-none mb-1">
            {{ $days }}
        </div>
        <div class="font-medium {{ $sizeClasses['label'] }} opacity-90">
            {{ $label }}
        </div>
    </div>
</div>
