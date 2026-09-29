@props(['paths' => []])

@if (count($paths))
    <section class="bg-zinc-50 py-20 sm:py-24 dark:bg-zinc-900/40" aria-labelledby="paths-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-home.section-heading id="paths-title" eyebrow="Learning paths" title="What do you want to build?" subtitle="Choose a path and start learning by doing." />

            <ul class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($paths as $path)
                    <li data-reveal style="--reveal-delay: {{ $loop->index * 60 }}ms">
                        <a href="#courses" data-path-link="{{ $path['key'] }}"
                           class="group relative flex h-full flex-col rounded-2xl border border-zinc-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-cau-navy/25 hover:shadow-xl hover:shadow-cau-navy/5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cau-blue dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-blue-400/30">
                            <span class="flex size-12 items-center justify-center rounded-xl bg-cau-navy text-white transition group-hover:bg-cau-deep dark:bg-blue-500/15 dark:text-blue-200">
                                <x-home.icon :name="$path['icon']" class="size-6" />
                            </span>
                            <h3 class="mt-5 text-lg font-bold text-cau-navy dark:text-white">{{ $path['title'] }}</h3>
                            <p class="mt-2 flex-1 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $path['description'] }}</p>
                            <span class="mt-6 flex items-center justify-between border-t border-zinc-100 pt-4 text-sm dark:border-zinc-800">
                                <span class="font-medium text-zinc-500 dark:text-zinc-400">{{ $path['count'] }} {{ \Illuminate\Support\Str::plural('course', $path['count']) }}</span>
                                <span class="inline-flex items-center gap-1 font-semibold text-cau-blue-dark transition group-hover:gap-2 group-hover:text-cau-orange-darker dark:text-blue-300 dark:group-hover:text-orange-300">
                                    View courses <x-home.icon name="arrow-right" class="size-4" />
                                </span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
