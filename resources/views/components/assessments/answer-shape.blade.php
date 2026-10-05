@props(['shape' => 'triangle'])

<svg {{ $attributes->merge(['class' => 'fill-white drop-shadow']) }} viewBox="0 0 32 32" aria-hidden="true">
    @switch($shape)
        @case('diamond')
            <path d="M16 2 30 16 16 30 2 16Z" />
            @break
        @case('circle')
            <circle cx="16" cy="16" r="13" />
            @break
        @case('square')
            <rect x="4" y="4" width="24" height="24" rx="2" />
            @break
        @default
            <path d="M16 3 30 28H2Z" />
    @endswitch
</svg>
