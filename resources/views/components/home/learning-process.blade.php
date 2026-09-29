@php
    $steps = [
        ['n' => '01', 'title' => 'Learn', 'icon' => 'book', 'text' => 'Short, clear lessons introduce one idea at a time, with examples you can follow.'],
        ['n' => '02', 'title' => 'Practice', 'icon' => 'wrench', 'text' => 'Try it straight away in the browser, experiment, make mistakes and fix them.'],
        ['n' => '03', 'title' => 'Build', 'icon' => 'cube', 'text' => 'Combine what you know into a real project: a game, a website, an app or a robot.'],
        ['n' => '04', 'title' => 'Showcase', 'icon' => 'star', 'text' => 'Submit your work, get feedback and earn badges and certificates for what you made.'],
    ];
@endphp

<section class="relative isolate overflow-hidden bg-cau-deep py-20 sm:py-28" aria-labelledby="process-title">
    <div class="absolute inset-0 -z-10 opacity-[0.08] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:64px_64px]" aria-hidden="true"></div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-home.section-heading id="process-title" :invert="true" eyebrow="How learning works" title="Don't just learn it. Build it." subtitle="Technology becomes easier to understand when you use it to create something real." />

        <ol class="relative mt-16 grid gap-10 md:grid-cols-2 lg:grid-cols-4 lg:gap-8">
            <li class="pointer-events-none absolute left-6 top-6 hidden h-px w-[calc(100%-3rem)] bg-gradient-to-r from-cau-orange/70 via-white/25 to-white/10 lg:block" aria-hidden="true"></li>
            <li class="pointer-events-none absolute bottom-6 left-6 top-6 w-px bg-gradient-to-b from-cau-orange/70 via-white/25 to-white/5 md:hidden" aria-hidden="true"></li>

            @foreach ($steps as $step)
                <li class="relative flex gap-5 lg:flex-col lg:gap-0" data-reveal style="--reveal-delay: {{ $loop->index * 90 }}ms">
                    <span @class([
                        'relative z-10 flex size-12 shrink-0 items-center justify-center rounded-full text-sm font-bold ring-8 ring-cau-deep',
                        'bg-cau-orange text-cau-deep' => $loop->last,
                        'bg-white text-cau-navy' => ! $loop->last,
                    ])>{{ $step['n'] }}</span>
                    <div class="lg:mt-6">
                        <h3 class="flex items-center gap-2 text-xl font-bold text-white">
                            <x-home.icon :name="$step['icon']" class="size-5 text-blue-200" />
                            {{ $step['title'] }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-blue-100/75 lg:pr-4">{{ $step['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
