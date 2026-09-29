@php
    $startUrl = auth()->check() ? route('dashboard') : route('login');
@endphp

<section class="relative isolate overflow-hidden bg-cau-navy pt-28 pb-20 sm:pt-32 lg:pt-36 lg:pb-28" aria-labelledby="hero-title">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-cau-navy via-cau-navy to-cau-deep" aria-hidden="true"></div>
    <div class="absolute inset-0 -z-10 opacity-[0.14] [background-image:linear-gradient(to_right,rgba(255,255,255,.35)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,.35)_1px,transparent_1px)] [background-size:56px_56px] [mask-image:radial-gradient(ellipse_at_70%_40%,black_20%,transparent_75%)]" aria-hidden="true"></div>
    <div class="absolute inset-x-0 bottom-0 -z-10 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent" aria-hidden="true"></div>

    <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:gap-10 lg:px-8">
        <div class="hero-in max-w-xl">
            <h1 id="hero-title" class="text-5xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl">
                Learn. Build. <span class="relative whitespace-nowrap">Create.<span class="absolute -bottom-1 left-0 h-1.5 w-full rounded-full bg-cau-orange/90" aria-hidden="true"></span></span>
            </h1>

            <p class="mt-7 text-xl font-semibold text-blue-50 text-pretty sm:text-2xl">
                Practical technology education that turns curiosity into real skills.
            </p>
            <p class="mt-4 max-w-lg text-base leading-relaxed text-blue-100/80 text-pretty sm:text-lg">
                Learn coding, web development, Python, robotics and digital skills through practical projects designed to help you build things you can actually show.
            </p>

            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                <a href="#courses" class="group/btn inline-flex items-center justify-center gap-2 rounded-xl bg-cau-orange px-6 py-3.5 text-base font-semibold text-cau-deep shadow-lg shadow-cau-deep/30 transition hover:-translate-y-0.5 hover:shadow-orange-500/30 active:translate-y-0 active:bg-cau-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300">
                    Explore Courses
                    <x-home.icon name="arrow-right" class="size-4 transition group-hover/btn:translate-x-0.5" />
                </a>
                <a href="{{ $startUrl }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 px-6 py-3.5 text-base font-semibold text-white transition hover:border-white/60 hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300">
                    {{ auth()->check() ? 'Continue Learning' : 'Start Learning' }}
                </a>
            </div>

            <p class="mt-8 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm font-medium text-blue-100/75">
                <span>Practical learning</span>
                <span class="size-1 rounded-full bg-cau-orange" aria-hidden="true"></span>
                <span>Project-based</span>
                <span class="size-1 rounded-full bg-cau-orange" aria-hidden="true"></span>
                <span>Built for learners</span>
            </p>
        </div>

        <x-home.hero-visual />
    </div>
</section>
