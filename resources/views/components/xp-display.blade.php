@props([
    'points' => 0,
    'label' => 'XP',
    'showLabel' => true,
    'size' => 'md', // sm, md, lg
    'animated' => false,
])

@php
$sizes = [
    'sm' => ['container' => 'px-2 py-1', 'icon' => 'w-3 h-3', 'text' => 'text-xs'],
    'md' => ['container' => 'px-3 py-1.5', 'icon' => 'w-4 h-4', 'text' => 'text-sm'],
    'lg' => ['container' => 'px-4 py-2', 'icon' => 'w-5 h-5', 'text' => 'text-base'],
];

$sizeClasses = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 bg-gradient-to-r from-yellow-400 to-orange-400 text-gray-900 rounded-full font-bold shadow-md ' . $sizeClasses['container'] . ($animated ? ' animate-pulse' : '')]) }}>
    <svg class="{{ $sizeClasses['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
    </svg>
    <span class="{{ $sizeClasses['text'] }}">
        {{ number_format($points) }}
        @if($showLabel)
            <span class="ml-0.5">{{ $label }}</span>
        @endif
    </span>
</div>
