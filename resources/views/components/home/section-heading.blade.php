@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'left', 'invert' => false, 'id' => null])

<div {{ $attributes->class(['max-w-2xl', 'mx-auto text-center' => $align === 'center']) }} data-reveal>
    @if ($eyebrow)
        <p @class([
            'mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]',
            'text-orange-300' => $invert,
            'text-cau-orange-darker dark:text-orange-300' => ! $invert,
        ])>
            <span class="h-px w-6 bg-current opacity-60" aria-hidden="true"></span>{{ $eyebrow }}
        </p>
    @endif
    <h2 @if ($id) id="{{ $id }}" @endif @class([
        'text-3xl font-bold tracking-tight text-balance sm:text-4xl',
        'text-white' => $invert,
        'text-cau-navy dark:text-white' => ! $invert,
    ])>{{ $title }}</h2>
    @if ($subtitle)
        <p @class([
            'mt-4 text-base leading-relaxed text-pretty sm:text-lg',
            'text-blue-100/80' => $invert,
            'text-zinc-600 dark:text-zinc-400' => ! $invert,
        ])>{{ $subtitle }}</p>
    @endif
</div>
