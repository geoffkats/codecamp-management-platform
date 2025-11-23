@props([
    'percent' => 0,
    'label' => null,
    'showPercent' => true,
    'size' => 'md', // sm, md, lg
    'color' => 'purple', // purple, blue, green, orange
])

@php
$heights = [
    'sm' => 'h-1.5',
    'md' => 'h-2.5',
    'lg' => 'h-4',
];

$colors = [
    'purple' => 'from-purple-500 to-pink-500',
    'blue' => 'from-blue-500 to-cyan-500',
    'green' => 'from-green-500 to-emerald-500',
    'orange' => 'from-orange-500 to-red-500',
];

$height = $heights[$size] ?? $heights['md'];
$gradient = $colors[$color] ?? $colors['purple'];
$safePercent = max(0, min(100, $percent));
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($label || $showPercent)
        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
            @if($label)
                <span class="font-medium">{{ $label }}</span>
            @endif
            @if($showPercent)
                <span class="font-bold text-gray-900 dark:text-white">{{ round($safePercent) }}%</span>
            @endif
        </div>
    @endif
    
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full {{ $height }} overflow-hidden shadow-inner">
        <div class="bg-gradient-to-r {{ $gradient }} {{ $height }} rounded-full transition-all duration-700 ease-out shadow-sm" 
             style="width: {{ $safePercent }}%"
             role="progressbar" 
             aria-valuenow="{{ $safePercent }}" 
             aria-valuemin="0" 
             aria-valuemax="100">
        </div>
    </div>
</div>
