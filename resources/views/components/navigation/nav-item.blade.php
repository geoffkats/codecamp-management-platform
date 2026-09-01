@props(['item', 'badges' => []])

@php
    $route = is_array($item) ? ($item['route'] ?? null) : null;
    $fallbackUrl = is_array($item) ? ($item['url'] ?? null) : null;
    $icon = is_array($item) ? ($item['icon'] ?? 'link') : 'link';
    $label = is_array($item) ? ($item['label'] ?? '') : '';
    $match = is_array($item) ? ($item['match'] ?? $route) : null;
    $badgeKey = is_array($item) ? ($item['badge'] ?? null) : null;
    $badgeCount = $badgeKey ? (int) ($badges[$badgeKey] ?? 0) : 0;
    $href = ($route && \Illuminate\Support\Facades\Route::has($route))
        ? route($route)
        : ($fallbackUrl ? url($fallbackUrl) : null);
@endphp

@if($href && $label)
    <flux:navlist.item
        :icon="$icon"
        :href="$href"
        :current="$match ? request()->routeIs($match) : false"
        wire:navigate
    >
        {{ __($label) }}
        @if($badgeCount > 0)
            <flux:badge size="sm" variant="danger">{{ $badgeCount }}</flux:badge>
        @endif
    </flux:navlist.item>
@endif
