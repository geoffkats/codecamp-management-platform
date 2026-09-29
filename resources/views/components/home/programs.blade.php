@props(['programs' => []])

@if (count($programs))
    <section id="programs" class="scroll-mt-20 bg-zinc-50 py-20 sm:py-24 dark:bg-zinc-900/40" aria-labelledby="programs-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-home.section-heading id="programs-title" eyebrow="Programs" title="Ways to learn with us" subtitle="Learn online on the platform, join a hands-on camp, or bring Code Academy to your school." />

            <ul class="mt-12 grid gap-4 sm:grid-cols-2 {{ count($programs) >= 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
                @foreach ($programs as $program)
                    <li data-reveal style="--reveal-delay: {{ $loop->index * 60 }}ms">
                        @php($external = ! str_starts_with($program['url'], url('/')))
                        <a href="{{ $program['url'] }}" @if ($external) target="_blank" rel="noopener" @endif class="group flex h-full flex-col rounded-2xl border border-zinc-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-cau-navy/25 hover:shadow-lg hover:shadow-cau-navy/5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cau-blue dark:border-zinc-800 dark:bg-zinc-900">
                            <span class="text-xs font-semibold uppercase tracking-wider text-cau-blue-dark dark:text-blue-300">{{ $program['audience'] }}</span>
                            <h3 class="mt-2 text-lg font-bold text-cau-navy dark:text-white">{{ $program['title'] }}</h3>
                            <p class="mt-2 flex-1 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $program['description'] }}</p>
                            <span class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-cau-navy transition group-hover:gap-2 group-hover:text-cau-orange-darker dark:text-white dark:group-hover:text-orange-300">
                                {{ $program['cta'] }} <x-home.icon name="arrow-right" class="size-4" />
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
