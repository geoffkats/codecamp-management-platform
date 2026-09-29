@props(['projects' => [], 'url' => null])

@if (count($projects) || $url)
    <section id="projects" class="scroll-mt-20 bg-white py-20 sm:py-24 dark:bg-zinc-950" aria-labelledby="projects-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <x-home.section-heading id="projects-title" eyebrow="Projects" title="See what learners can build" subtitle="Games, websites, programs and robots made by Code Academy learners. Explore the full gallery of children's projects." />

                @if ($url)
                    <a href="{{ $url }}" target="_blank" rel="noopener" data-reveal
                       class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-cau-orange px-6 py-3 text-sm font-semibold text-cau-deep shadow-lg shadow-cau-orange/20 transition hover:bg-cau-orange-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cau-blue lg:self-auto">
                        View children's projects <x-home.icon name="arrow-up-right" class="size-4" />
                        <span class="sr-only">(opens in a new tab)</span>
                    </a>
                @endif
            </div>

            @if (count($projects))
                <ul class="-mx-4 mt-12 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:snap-none sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <li class="w-[82%] shrink-0 snap-start sm:w-auto" data-reveal style="--reveal-delay: {{ ($loop->index % 3) * 70 }}ms">
                            <x-home.project-card :project="$project" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endif
