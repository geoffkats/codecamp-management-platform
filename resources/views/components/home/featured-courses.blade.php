@props(['courses' => [], 'paths' => []])

@php
    $filterPaths = collect($paths)->filter(fn ($p) => collect($courses)->contains('path', $p['key']))->values();
@endphp

<section id="courses" class="scroll-mt-20 bg-white py-20 sm:py-24 dark:bg-zinc-950" aria-labelledby="courses-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <x-home.section-heading id="courses-title" eyebrow="Courses" title="Start learning today" subtitle="Explore practical courses designed to help you build real skills." />

            @if ($filterPaths->count() > 1)
                <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" data-reveal>
                    <div class="flex w-max gap-2 lg:w-auto lg:flex-wrap lg:justify-end" role="group" aria-label="Filter courses by path">
                        <button type="button" data-course-filter="all" aria-pressed="true"
                                class="shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cau-blue border-zinc-200 text-zinc-600 hover:border-cau-navy/40 hover:text-cau-navy aria-pressed:border-cau-navy aria-pressed:bg-cau-navy aria-pressed:text-white dark:border-zinc-700 dark:text-zinc-300 dark:aria-pressed:border-blue-400 dark:aria-pressed:bg-blue-500/15 dark:aria-pressed:text-white">
                            All courses
                        </button>
                        @foreach ($filterPaths as $path)
                            <button type="button" data-course-filter="{{ $path['key'] }}" aria-pressed="false"
                                    class="shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cau-blue border-zinc-200 text-zinc-600 hover:border-cau-navy/40 hover:text-cau-navy aria-pressed:border-cau-navy aria-pressed:bg-cau-navy aria-pressed:text-white dark:border-zinc-700 dark:text-zinc-300 dark:aria-pressed:border-blue-400 dark:aria-pressed:bg-blue-500/15 dark:aria-pressed:text-white">
                                {{ $path['title'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @if (count($courses))
            <ul class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" id="course-grid">
                @foreach ($courses as $course)
                    <li data-course-path="{{ $course['path'] ?? 'other' }}" data-reveal style="--reveal-delay: {{ ($loop->index % 3) * 70 }}ms">
                        <x-home.course-card :course="$course" />
                    </li>
                @endforeach
            </ul>
            <p class="mt-10 text-center text-sm text-zinc-500 dark:text-zinc-400" id="course-empty" hidden>No courses in this path yet.</p>
        @else
            <div class="mt-12 rounded-2xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                <p class="font-semibold text-cau-navy dark:text-white">New courses are on the way.</p>
                <p class="mt-1 text-sm text-zinc-500">Check back soon.</p>
            </div>
        @endif

        <div class="mt-12 flex flex-col items-center justify-center gap-3 sm:flex-row" data-reveal>
            @auth
                <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-cau-navy px-6 py-3 text-sm font-semibold text-white transition hover:bg-cau-deep focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cau-blue">
                    Browse all courses <x-home.icon name="arrow-right" class="size-4" />
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-cau-navy px-6 py-3 text-sm font-semibold text-white transition hover:bg-cau-deep focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cau-blue">
                    Sign in to your courses <x-home.icon name="arrow-right" class="size-4" />
                </a>
            @endauth
        </div>
    </div>
</section>
