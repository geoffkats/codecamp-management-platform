@props(['brand'])

@php
    $logo = $brand['logo'] ?: $brand['logoDark'];
    $phoneHref = $brand['phone'] ? preg_replace('/[^\d+]/', '', $brand['phone']) : null;
@endphp

<footer id="contact" class="scroll-mt-20 border-t border-white/10 bg-cau-deep text-blue-100/80" aria-labelledby="footer-title">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1.3fr]">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <span class="flex size-11 items-center justify-center overflow-hidden rounded-xl bg-white p-1">
                        @if ($logo)
                            <img src="{{ asset('storage/' . $logo) }}" alt="" class="h-full w-full object-contain" loading="lazy" width="44" height="44">
                        @else
                            <x-home.icon name="code" class="size-5 text-cau-navy" />
                        @endif
                    </span>
                    <span id="footer-title" class="text-lg font-bold text-white">{{ $brand['name'] }}</span>
                </a>
                <p class="mt-5 max-w-xs text-sm leading-relaxed">Practical technology education for the next generation of builders.</p>
            </div>

            <nav aria-label="Platform">
                <h3 class="text-sm font-semibold text-white">Platform</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li><a href="#courses" class="transition hover:text-white">Courses</a></li>
                    <li><a href="#programs" class="transition hover:text-white">Programs</a></li>
                    <li><a href="#projects" class="transition hover:text-white">Projects</a></li>
                    <li><a href="{{ config('homepage.projects_url') }}" target="_blank" rel="noopener" class="transition hover:text-white">Children's projects<span class="sr-only"> (opens in a new tab)</span></a></li>
                    <li><a href="#about" class="transition hover:text-white">About</a></li>
                    <li><a href="#contact" class="transition hover:text-white">Contact</a></li>
                </ul>
            </nav>

            <nav aria-label="Account">
                <h3 class="text-sm font-semibold text-white">Account</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="transition hover:text-white">Dashboard</a></li>
                        <li><a href="{{ route('courses.index') }}" class="transition hover:text-white">My courses</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="transition hover:text-white">Sign In</a></li>
                    @endauth
                </ul>
            </nav>

            @if ($brand['email'] || $brand['phone'] || $brand['address'])
                <div>
                    <h3 class="text-sm font-semibold text-white">Contact</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        @if ($brand['email'])
                            <li><a href="mailto:{{ $brand['email'] }}" class="inline-flex items-center gap-2.5 transition hover:text-white"><x-home.icon name="mail" class="size-4 text-orange-300" />{{ $brand['email'] }}</a></li>
                        @endif
                        @if ($brand['phone'])
                            <li><a href="tel:{{ $phoneHref }}" class="inline-flex items-center gap-2.5 transition hover:text-white"><x-home.icon name="phone-call" class="size-4 text-orange-300" />{{ $brand['phone'] }}</a></li>
                        @endif
                        @if ($brand['address'])
                            <li class="flex items-start gap-2.5"><x-home.icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-orange-300" /><span>{{ $brand['address'] }}</span></li>
                        @endif
                    </ul>
                </div>
            @endif
        </div>

        <div class="mt-14 flex flex-col gap-3 border-t border-white/10 pt-8 text-sm sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ $brand['name'] }}. All rights reserved.</p>
            <p class="text-blue-100/60">Learn. Build. Create.</p>
        </div>
    </div>
</footer>
