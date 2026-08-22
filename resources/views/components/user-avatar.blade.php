@props([
    'user' => null,
    'name' => null,
    'src' => null,
    'size' => 'md',
    'rounded' => 'xl',
])

@php
    $sizes = [
        'xs' => 'w-7 h-7 text-[10px]',
        'sm' => 'w-11 h-11 text-sm',
        'md' => 'w-12 h-12 text-base',
        'lg' => 'w-16 h-16 text-lg',
        'xl' => 'w-20 h-20 text-2xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $roundClass = $rounded === 'full' ? 'rounded-full' : 'rounded-xl';
    $url = $src ?? ($user instanceof \App\Models\User ? $user->profileImageUrl() : null);
    $initials = strtoupper($user instanceof \App\Models\User
        ? $user->initials()
        : substr(trim($name ?? 'S'), 0, 1));
    $alt = $user?->name ?? $name ?? 'User';
@endphp

@if($url)
    <img {{ $attributes->merge(['class' => "$sizeClass $roundClass object-cover"]) }} src="{{ $url }}" alt="{{ $alt }}">
@else
    <div {{ $attributes->merge(['class' => "$sizeClass $roundClass bg-gray-700 text-white font-bold flex items-center justify-center flex-shrink-0"]) }}>
        {{ $initials }}
    </div>
@endif
