@props(['course'])

@php
    $enrollment = match ($course['enrollment']) {
        'open' => 'Open enrollment',
        'approval_required' => 'Request to join',
        'invite_only' => 'By invitation',
        default => null,
    };
    $cta = auth()->check() ? 'View course' : 'Sign in to view';
@endphp

<article class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm shadow-zinc-900/[0.03] transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-cau-navy/8 focus-within:ring-2 focus-within:ring-cau-blue focus-within:ring-offset-2 dark:border-zinc-800 dark:bg-zinc-900 dark:focus-within:ring-offset-zinc-950">
    <div class="relative aspect-[16/9] overflow-hidden bg-cau-navy">
        @if ($course['image'])
            <img src="{{ $course['image'] }}" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        @else
            <x-home.thumb :path="$course['path']" />
        @endif
        @if ($course['level'])
            <span class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-bold text-cau-navy shadow-sm">{{ $course['level'] }}</span>
        @endif
        <span class="absolute inset-x-0 bottom-0 h-1 origin-left scale-x-0 bg-cau-orange transition duration-300 group-hover:scale-x-100" aria-hidden="true"></span>
    </div>

    <div class="flex flex-1 flex-col p-5">
        @if ($course['category'])
            <p class="text-xs font-semibold uppercase tracking-wider text-cau-blue-dark dark:text-blue-300">{{ $course['category'] }}</p>
        @endif
        <h3 class="mt-1.5 text-lg font-bold leading-snug text-cau-navy dark:text-white">
            <a href="{{ $course['url'] }}" class="after:absolute after:inset-0 focus-visible:outline-none">{{ $course['title'] }}</a>
        </h3>
        @if ($course['summary'])
            <p class="mt-2 flex-1 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $course['summary'] }}</p>
        @else
            <div class="flex-1"></div>
        @endif

        <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">
            @if ($course['hours'])
                <li class="inline-flex items-center gap-1.5"><x-home.icon name="clock" class="size-4" /> {{ $course['hours'] }} {{ \Illuminate\Support\Str::plural('hour', $course['hours']) }}</li>
            @endif
            <li class="inline-flex items-center gap-1.5"><x-home.icon name="badge" class="size-4" /> Code Academy</li>
        </ul>

        <div class="mt-5 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
            @if ($enrollment)
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $enrollment }}</span>
            @else
                <span></span>
            @endif
            <span class="inline-flex items-center gap-1 text-sm font-semibold text-cau-blue-dark transition group-hover:gap-2 group-hover:text-cau-orange-darker dark:text-blue-300 dark:group-hover:text-orange-300">
                {{ $cta }} <x-home.icon name="arrow-right" class="size-4" />
            </span>
        </div>
    </div>
</article>
