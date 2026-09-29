<section class="relative isolate overflow-hidden bg-cau-deep py-20 sm:py-28" aria-labelledby="cta-title">
    <div class="absolute inset-0 -z-10 opacity-[0.1] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:56px_56px] [mask-image:radial-gradient(ellipse_at_center,black_10%,transparent_70%)]" aria-hidden="true"></div>
    <div class="absolute left-1/2 top-0 -z-10 h-px w-2/3 -translate-x-1/2 bg-gradient-to-r from-transparent via-cau-orange/70 to-transparent" aria-hidden="true"></div>

    <div class="mx-auto max-w-3xl px-4 text-center sm:px-6" data-reveal>
        <h2 id="cta-title" class="text-4xl font-bold tracking-tight text-white text-balance sm:text-5xl">Your next project starts here.</h2>
        <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-blue-100/80">Choose a course, start learning and build something you're proud of.</p>

        <div class="mt-10 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="#courses" class="inline-flex items-center justify-center gap-2 rounded-xl bg-cau-orange px-7 py-3.5 text-base font-semibold text-cau-deep shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:shadow-orange-500/30 active:translate-y-0 active:bg-cau-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300">
                Explore Courses <x-home.icon name="arrow-right" class="size-4" />
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-white/30 px-7 py-3.5 text-base font-semibold text-white transition hover:border-white/60 hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300">Go to dashboard</a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-white/30 px-7 py-3.5 text-base font-semibold text-white transition hover:border-white/60 hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300">Sign In</a>
            @endauth
        </div>
    </div>
</section>
