@props(['item', 'badges' => []])

@php
    $route = is_array($item) ? ($item['route'] ?? null) : null;
    $icon = is_array($item) ? ($item['icon'] ?? 'link') : 'link';
    $label = is_array($item) ? ($item['label'] ?? '') : '';
    $match = is_array($item) ? ($item['match'] ?? $route) : null;
    $badgeKey = is_array($item) ? ($item['badge'] ?? null) : null;
    $badgeCount = $badgeKey ? (int) ($badges[$badgeKey] ?? 0) : 0;
@endphp

@if($route && $label && Route::has($route))
    <flux:navlist.item
        :icon="$icon"
        :href="route($route)"
        :current="request()->routeIs($match)"
        wire:navigate
    >
        {{ __($label) }}
        @if($badgeCount > 0)
            <flux:badge size="sm" variant="danger">{{ $badgeCount }}</flux:badge>
        @endif
    </flux:navlist.item>
@endif
