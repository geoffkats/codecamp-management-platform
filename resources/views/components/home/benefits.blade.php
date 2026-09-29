@php
    $benefits = [
        ['icon' => 'wrench', 'title' => 'Learn by doing', 'text' => 'Build practical projects instead of only watching lessons.'],
        ['icon' => 'map', 'title' => 'Structured learning', 'text' => 'Follow clear paths from fundamentals to more advanced skills.'],
        ['icon' => 'cube', 'title' => 'Real projects', 'text' => 'Turn concepts into websites, games, applications and physical projects.'],
        ['icon' => 'users', 'title' => 'Support when you need it', 'text' => 'Make learning easier with guidance, resources and instructor support.'],
    ];
@endphp

<section id="about" class="scroll-mt-20 bg-zinc-50 py-20 sm:py-24 dark:bg-zinc-900/40" aria-labelledby="about-title">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16 lg:px-8">
        <div class="lg:sticky lg:top-28 lg:self-start">
            <x-home.section-heading id="about-title" eyebrow="Why Code Academy" title="Learning that goes beyond the screen" subtitle="Code Academy Uganda helps young people and adults learn technology the practical way — by making things, with instructors guiding them at every step." />
        </div>

        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach ($benefits as $benefit)
                <li class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900" data-reveal style="--reveal-delay: {{ $loop->index * 70 }}ms">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-cau-navy dark:bg-blue-500/10 dark:text-blue-200">
                        <x-home.icon :name="$benefit['icon']" class="size-5.5" />
                    </span>
                    <h3 class="mt-5 text-lg font-bold text-cau-navy dark:text-white">{{ $benefit['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $benefit['text'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
