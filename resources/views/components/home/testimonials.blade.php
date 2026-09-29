@props(['testimonials' => []])

{{-- Renders only when real testimonials are configured in config/homepage.php. --}}
@if (count($testimonials))
    <section class="bg-white py-20 sm:py-24 dark:bg-zinc-950" aria-labelledby="testimonials-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-home.section-heading id="testimonials-title" eyebrow="In their words" title="What learners and families say" />

            <ul class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    <li data-reveal style="--reveal-delay: {{ ($loop->index % 3) * 70 }}ms">
                        <figure class="flex h-full flex-col rounded-2xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-800 dark:bg-zinc-900">
                            <span class="text-4xl font-bold leading-none text-cau-orange" aria-hidden="true">&ldquo;</span>
                            <blockquote class="mt-2 flex-1 text-base leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $testimonial['quote'] }}</blockquote>
                            <figcaption class="mt-6 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                                <p class="font-semibold text-cau-navy dark:text-white">{{ $testimonial['name'] }}</p>
                                @if (! empty($testimonial['role']))
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $testimonial['role'] }}</p>
                                @endif
                            </figcaption>
                        </figure>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
