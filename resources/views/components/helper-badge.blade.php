@props([
    'level' => 'helper', // helper, mentor, master, leader
    'count' => 0,
    'size' => 'md',
])

@php
$badges = [
    'helper' => ['emoji' => '🥉', 'title' => 'Helper', 'color' => 'bronze', 'min' => 5],
    'mentor' => ['emoji' => '🥈', 'title' => 'Code Mentor', 'color' => 'silver', 'min' => 20],
    'master' => ['emoji' => '🥇', 'title' => 'Discussion Master', 'color' => 'gold', 'min' => 50],
    'leader' => ['emoji' => '🏆', 'title' => 'Community Leader', 'color' => 'platinum', 'min' => 100],
];

$badge = $badges[$level] ?? $badges['helper'];

$colors = [
    'bronze' => 'from-orange-400 to-orange-600',
    'silver' => 'from-gray-300 to-gray-500',
    'gold' => 'from-yellow-400 to-yellow-600',
    'platinum' => 'from-purple-400 to-purple-600',
];

$sizes = [
    'sm' => 'px-2 py-1 text-xs',
    'md' => 'px-3 py-1.5 text-sm',
    'lg' => 'px-4 py-2 text-base',
];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 bg-gradient-to-r ' . $colors[$badge['color']] . ' text-white rounded-full font-bold shadow-md ' . $sizes[$size]]) }}>
    <span class="text-base">{{ $badge['emoji'] }}</span>
    <span>{{ $badge['title'] }}</span>
    @if($count > 0)
        <span class="ml-1 px-1.5 py-0.5 bg-white/20 rounded-full text-xs">{{ $count }}</span>
    @endif
</div>
