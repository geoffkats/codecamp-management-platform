@props(['brand'])

@php
    $links = [
        ['label' => 'Courses', 'href' => '#courses'],
        ['label' => 'Programs', 'href' => '#programs'],
        ['label' => 'Projects', 'href' => '#projects'],
        ['label' => 'About', 'href' => '#about'],
    ];
    $logo = $brand['logo'] ?: $brand['logoDark'];
@endphp

<header id="site-header" class="group fixed inset-x-0 top-0 z-50 transition-[background-color,box-shadow,border-color] duration-300 border-b border-transparent data-scrolled:border-white/10 data-scrolled:bg-cau-navy/95 data-scrolled:shadow-lg data-scrolled:shadow-cau-deep/20 data-scrolled:backdrop-blur-md">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-3 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-cau-navy">Skip to content</a>

    <nav class="mx-auto flex h-18 max-w-7xl items-center justify-between gap-6 px-4 transition-[height] duration-300 group-data-scrolled:h-15 sm:px-6 lg:px-8" aria-label="Main">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-300">
            <span class="flex size-10 items-center justify-center overflow-hidden rounded-xl bg-white p-1 shadow-sm transition-all duration-300 group-data-scrolled:size-9">
                @if ($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="" class="h-full w-full object-contain" width="40" height="40">
                @else
                    <x-home.icon name="code" class="size-5 text-cau-navy" />
                @endif
            </span>
            <span class="leading-tight">
                <span class="block text-[15px] font-bold tracking-tight text-white">{{ $brand['name'] }}</span>
                <span class="block text-[11px] font-medium uppercase tracking-[0.16em] text-orange-300">Uganda</span>
            </span>
        </a>

        <ul class="hidden items-center gap-1 md:flex">
            @foreach ($links as $link)
                <li>
                    <a href="{{ $link['href'] }}" class="rounded-lg px-3 py-2 text-sm font-medium text-blue-100/90 transition hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-orange-300">{{ $link['label'] }}</a>
                </li>
            @endforeach
        </ul>

        <div class="hidden items-center gap-2 md:flex">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-cau-orange px-4 py-2 text-sm font-semibold text-cau-deep transition hover:shadow-lg hover:shadow-orange-500/25 active:bg-cau-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300">
                    Go to dashboard
                    <x-home.icon name="arrow-right" class="size-4" />
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg bg-cau-orange px-4 py-2 text-sm font-semibold text-cau-deep transition hover:shadow-lg hover:shadow-orange-500/25 active:bg-cau-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300">
                    Sign In
                    <x-home.icon name="arrow-right" class="size-4" />
                </a>
            @endauth
        </div>

        <button type="button" id="menu-toggle" class="inline-flex size-10 items-center justify-center rounded-lg text-white transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-orange-300 md:hidden" aria-controls="mobile-menu" aria-expanded="false">
            <span class="sr-only">Open menu</span>
            <x-home.icon name="menu" class="size-6" data-icon-open />
            <x-home.icon name="x" class="hidden size-6" data-icon-close />
        </button>
    </nav>

    <div id="mobile-menu" class="hidden border-t border-white/10 bg-cau-navy md:hidden">
        <ul class="space-y-1 px-4 py-4">
            @foreach ($links as $link)
                <li>
                    <a href="{{ $link['href'] }}" class="block rounded-lg px-3 py-3 text-base font-medium text-white hover:bg-white/10" data-menu-link>{{ $link['label'] }}</a>
                </li>
            @endforeach
        </ul>
        <div class="grid grid-cols-2 gap-3 border-t border-white/10 px-4 py-4">
            @auth
                <a href="{{ route('dashboard') }}" class="col-span-2 rounded-lg bg-cau-orange px-4 py-3 text-center text-sm font-semibold text-cau-deep">Go to dashboard</a>
            @else
                <a href="{{ route('login') }}" class="col-span-2 rounded-lg bg-cau-orange px-4 py-3 text-center text-sm font-semibold text-cau-deep">Sign In</a>
            @endauth
        </div>
    </div>
</header>
