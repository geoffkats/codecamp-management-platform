@props(['project'])

@php
    $url = $project['url'] ?? null;
    $external = $url && ! str_starts_with($url, url('/')) && ! str_starts_with($url, '/') && ! str_starts_with($url, '#');
@endphp

<article class="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-cau-navy text-white ring-1 ring-cau-navy/10 transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-cau-navy/25 focus-within:ring-2 focus-within:ring-cau-orange dark:ring-white/10">
    <div class="relative aspect-[4/3] overflow-hidden border-b border-white/10">
        @if (! empty($project['image']))
            <img src="{{ $project['image'] }}" alt="{{ $project['title'] }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">
        @else
            <x-home.thumb :path="$project['path'] ?? null" />
        @endif
        @if (! empty($project['category']))
            <span class="absolute left-4 top-4 rounded-full bg-white px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-cau-navy">{{ $project['category'] }}</span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-lg font-bold">
            @if ($url)
                <a href="{{ $url }}" @if ($external) target="_blank" rel="noopener" @endif class="after:absolute after:inset-0 focus-visible:outline-none">{{ $project['title'] }}@if ($external)<span class="sr-only"> (opens in a new tab)</span>@endif</a>
            @else
                {{ $project['title'] }}
            @endif
        </h3>
        @if (! empty($project['description']))
            <p class="mt-2 flex-1 text-sm leading-relaxed text-blue-100/75">{{ $project['description'] }}</p>
        @endif
        @if ($url)
            <p class="mt-5 flex items-center justify-end border-t border-white/10 pt-4 text-sm">
                <span class="inline-flex items-center gap-1 font-semibold text-white transition group-hover:gap-2 group-hover:text-orange-300">
                    See learner projects <x-home.icon name="arrow-up-right" class="size-4" />
                </span>
            </p>
        @endif
    </div>
</article>
