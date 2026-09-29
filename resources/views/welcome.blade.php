@php
    $favicon = \App\Models\SystemSetting::get('favicon');
    $pageTitle = $brand['name'] . ' — Learn. Build. Create.';
    $pageDescription = 'Practical technology education in Uganda. Learn coding, web development, Python, robotics and digital skills through hands-on projects.';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" style="font-size: 100%">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url('/') }}">
        @if ($brand['logo'])
            <meta property="og:image" content="{{ asset('storage/' . $brand['logo']) }}">
        @endif
        @if ($favicon)
            <link rel="icon" href="{{ asset('storage/' . $favicon) }}">
        @elseif (file_exists(public_path('favicon.ico')))
            <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        @endif
        @include('partials.pwa')
        <script>document.documentElement.classList.add('js')</script>
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">
        @vite('resources/css/app.css')
        @fluxAppearance
        <style>
            .js [data-reveal]{opacity:0;transform:translateY(18px);transition:opacity .6s cubic-bezier(.2,.7,.2,1) var(--reveal-delay,0ms),transform .6s cubic-bezier(.2,.7,.2,1) var(--reveal-delay,0ms)}
            .js [data-reveal].is-visible{opacity:1;transform:none}
            .hero-in{animation:hero-in .7s cubic-bezier(.2,.7,.2,1) both}
            .hero-in-delay{animation-delay:.15s}
            @keyframes hero-in{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
            .faq[open] .faq-body{animation:faq-in .25s ease-out}
            @keyframes faq-in{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:none}}
            @media (prefers-reduced-motion: reduce){
                html{scroll-behavior:auto}
                .js [data-reveal]{opacity:1;transform:none;transition:none}
                .hero-in,.faq[open] .faq-body{animation:none}
                *,::before,::after{transition-duration:0s!important}
            }
        </style>
        {{-- Zoho PageSense analytics --}}
        <script src="https://cdn.pagesense.io/js/914121464/af0b8428118c471ea29b7f87bbd5c353.js" async></script>
        @include('partials.analytics.head')
    </head>
    <body class="bg-white font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        @include('partials.analytics.body')

        <x-home.navbar :brand="$brand" />

        <main id="main">
            <x-home.hero />
            <x-home.stats :stats="$stats" />
            <x-home.learning-paths :paths="$paths" />
            <x-home.featured-courses :courses="$courses" :paths="$paths" />
            <x-home.learning-process />
            <x-home.project-showcase :projects="$projects" :url="$projectsUrl" />
            <x-home.benefits />
            <x-home.student-journey :paths="$paths" />
            <x-home.programs :programs="$programs" />
            <x-home.testimonials :testimonials="$testimonials" />
            <x-home.faq :paths="$paths" :beginner-courses="$beginnerCourses" />
            <x-home.final-cta />
        </main>

        <x-home.footer :brand="$brand" />

        <script>
            (() => {
                const header = document.getElementById('site-header');
                const menu = document.getElementById('mobile-menu');
                const toggle = document.getElementById('menu-toggle');
                const menuOpen = () => !menu.classList.contains('hidden');

                const syncHeader = () => header.toggleAttribute('data-scrolled', window.scrollY > 12 || menuOpen());
                syncHeader();
                window.addEventListener('scroll', syncHeader, { passive: true });

                const setMenu = (open) => {
                    menu.classList.toggle('hidden', !open);
                    toggle.setAttribute('aria-expanded', String(open));
                    toggle.querySelector('[data-icon-open]').classList.toggle('hidden', open);
                    toggle.querySelector('[data-icon-close]').classList.toggle('hidden', !open);
                    toggle.querySelector('.sr-only').textContent = open ? 'Close menu' : 'Open menu';
                    syncHeader();
                };
                toggle.addEventListener('click', () => setMenu(!menuOpen()));
                menu.querySelectorAll('[data-menu-link]').forEach((link) => link.addEventListener('click', () => setMenu(false)));
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && menuOpen()) { setMenu(false); toggle.focus(); }
                });
                window.matchMedia('(min-width: 768px)').addEventListener('change', (e) => { if (e.matches) setMenu(false); });

                const revealables = document.querySelectorAll('[data-reveal]');
                if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('is-visible');
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
                    revealables.forEach((el) => observer.observe(el));
                } else {
                    revealables.forEach((el) => el.classList.add('is-visible'));
                }

                const filterButtons = document.querySelectorAll('[data-course-filter]');
                const courseItems = document.querySelectorAll('[data-course-path]');
                const emptyState = document.getElementById('course-empty');
                const applyFilter = (key) => {
                    let shown = 0;
                    filterButtons.forEach((btn) => btn.setAttribute('aria-pressed', String(btn.dataset.courseFilter === key)));
                    courseItems.forEach((item) => {
                        const visible = key === 'all' || item.dataset.coursePath === key;
                        item.hidden = !visible;
                        if (visible) { shown++; item.classList.add('is-visible'); }
                    });
                    if (emptyState) emptyState.hidden = shown > 0;
                };
                filterButtons.forEach((btn) => btn.addEventListener('click', () => applyFilter(btn.dataset.courseFilter)));
                document.querySelectorAll('[data-path-link]').forEach((link) => link.addEventListener('click', () => applyFilter(link.dataset.pathLink)));
            })();
        </script>
    </body>
</html>
