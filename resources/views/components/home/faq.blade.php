@props(['paths' => [], 'beginnerCourses' => 0])

@php
    $pathList = collect($paths)->pluck('title');
    $pathSentence = $pathList->count() > 1
        ? $pathList->slice(0, -1)->implode(', ') . ' and ' . $pathList->last()
        : $pathList->first();

    $faqs = [
        [
            'q' => 'How do I get access to a course?',
            'a' => 'Access is given by the Code Academy team. Join one of our programs, such as a code camp, a school code club or a partner school, and we will set up your account and send your sign-in details. Once signed in, you will see the courses you have been given access to.',
        ],
        [
            'q' => 'What courses are available?',
            'a' => $pathSentence
                ? "You can currently learn {$pathSentence}. Browse the Courses section above to see each course's level and duration."
                : 'Browse the Courses section above to see what is currently available.',
        ],
        [
            'q' => 'Can beginners join?',
            'a' => $beginnerCourses > 0
                ? "Yes. {$beginnerCourses} " . \Illuminate\Support\Str::plural('course', $beginnerCourses) . ' are marked Beginner and start from the basics, so no experience is needed.'
                : 'Yes. Each course shows its level so you can pick the right place to start.',
        ],
        [
            'q' => 'Do I learn through practical projects?',
            'a' => 'Yes. Lessons include hands-on activities, such as writing code in the built-in editor and building Scratch projects, plus assignments you submit for feedback from your instructor.',
        ],
        [
            'q' => 'Can I learn online?',
            'a' => 'Yes. The platform works in your web browser on phones, tablets and computers, and you can install it on your home screen like an app. Code camps and school programs also run in person.',
        ],
        [
            'q' => 'How do I track my progress?',
            'a' => 'Your dashboard shows your progress in each course, completed lessons, XP, streaks and badges. When you complete a course, you can receive a certificate.',
        ],
    ];
@endphp

<section class="bg-zinc-50 py-20 sm:py-24 dark:bg-zinc-900/40" aria-labelledby="faq-title">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16 lg:px-8">
        <x-home.section-heading id="faq-title" eyebrow="FAQ" title="Questions, answered" subtitle="Everything you need to know before you start." />

        <div class="divide-y divide-zinc-200 rounded-2xl border border-zinc-200 bg-white dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900" data-reveal>
            @foreach ($faqs as $faq)
                <details class="faq group" @if ($loop->first) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-5 text-left text-base font-semibold text-cau-navy transition hover:text-cau-blue-dark focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-cau-blue sm:px-6 dark:text-white dark:hover:text-blue-300 [&::-webkit-details-marker]:hidden">
                        {{ $faq['q'] }}
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 transition duration-300 group-open:rotate-180 group-open:bg-cau-navy group-open:text-white dark:bg-zinc-800 dark:text-zinc-400 dark:group-open:bg-blue-500">
                            <x-home.icon name="chevron-down" class="size-4" />
                        </span>
                    </summary>
                    <div class="faq-body px-5 pb-5 sm:px-6">
                        <p class="max-w-2xl text-sm leading-relaxed text-zinc-600 sm:text-base dark:text-zinc-400">{{ $faq['a'] }}</p>
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
