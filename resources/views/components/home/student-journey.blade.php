@props(['paths' => []])

@php
    $pathNames = collect($paths)->pluck('title')->take(4)->all() ?: ['Coding', 'Web Development', 'Python', 'Robotics'];
@endphp

<section class="bg-white py-20 sm:py-24 dark:bg-zinc-950" aria-labelledby="journey-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-home.section-heading id="journey-title" align="center" eyebrow="Your learning journey" title="From first click to real skills" subtitle="A clear journey that keeps you moving — whether you're writing your first line of code or building your tenth project." />

        <div class="mt-16 space-y-16 lg:space-y-24">
            {{-- Discover --}}
            <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-16" data-reveal>
                <div>
                    <p class="text-sm font-bold text-cau-orange-darker dark:text-orange-300">Discover</p>
                    <h3 class="mt-2 text-2xl font-bold text-cau-navy sm:text-3xl dark:text-white">Choose what interests you.</h3>
                    <p class="mt-3 max-w-md leading-relaxed text-zinc-600 dark:text-zinc-400">Pick a path that excites you. Every course shows its level and how long it takes, so you know where to start.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900" aria-hidden="true">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Pick a path</p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        @foreach ($pathNames as $name)
                            <div @class([
                                'rounded-xl border p-4 text-sm font-semibold',
                                'border-cau-navy bg-cau-navy text-white' => $loop->first,
                                'border-zinc-200 bg-white text-cau-navy dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200' => ! $loop->first,
                            ])>
                                {{ $name }}
                                @if ($loop->first)
                                    <span class="mt-1 block text-xs font-medium text-blue-200">Selected</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Learn --}}
            <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-16" data-reveal>
                <div class="lg:order-2">
                    <p class="text-sm font-bold text-cau-orange-darker dark:text-orange-300">Learn</p>
                    <h3 class="mt-2 text-2xl font-bold text-cau-navy sm:text-3xl dark:text-white">Follow structured lessons.</h3>
                    <p class="mt-3 max-w-md leading-relaxed text-zinc-600 dark:text-zinc-400">Courses are organised into modules and bite-sized lessons, so you always know what comes next.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 lg:order-1 dark:border-zinc-800 dark:bg-zinc-900" aria-hidden="true">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Module 2 · Building your first page</p>
                    <ul class="mt-4 space-y-2.5">
                        @foreach (['What is HTML?', 'Headings & paragraphs', 'Adding images', 'Styling with CSS'] as $i => $lesson)
                            <li class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ $i === 2 ? 'bg-blue-50 dark:bg-blue-500/10' : '' }}">
                                @if ($i < 2)
                                    <span class="flex size-6 items-center justify-center rounded-full bg-cau-navy text-white dark:bg-blue-500"><x-home.icon name="check" class="size-3.5" /></span>
                                @elseif ($i === 2)
                                    <span class="size-6 rounded-full border-2 border-cau-orange"></span>
                                @else
                                    <span class="size-6 rounded-full border-2 border-zinc-200 dark:border-zinc-700"></span>
                                @endif
                                <span class="text-sm font-medium {{ $i === 2 ? 'text-cau-navy dark:text-white' : 'text-zinc-600 dark:text-zinc-400' }}">{{ $lesson }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Build --}}
            <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-16" data-reveal>
                <div>
                    <p class="text-sm font-bold text-cau-orange-darker dark:text-orange-300">Build</p>
                    <h3 class="mt-2 text-2xl font-bold text-cau-navy sm:text-3xl dark:text-white">Create real projects.</h3>
                    <p class="mt-3 max-w-md leading-relaxed text-zinc-600 dark:text-zinc-400">Write code in the browser, build Scratch projects and submit assignments for your instructor to review.</p>
                </div>
                <div class="overflow-hidden rounded-2xl border border-cau-navy/20 bg-cau-deep shadow-sm" aria-hidden="true">
                    <div class="flex items-center gap-2 border-b border-white/10 px-4 py-3">
                        <span class="size-2.5 rounded-full bg-white/25"></span>
                        <span class="size-2.5 rounded-full bg-white/25"></span>
                        <span class="size-2.5 rounded-full bg-cau-orange"></span>
                        <span class="ml-2 font-mono text-xs text-blue-200/70">game.py</span>
                    </div>
                    <pre class="overflow-hidden px-5 py-5 font-mono text-[12.5px] leading-6 text-blue-100/90"><span class="text-orange-300">score</span> = 0
<span class="text-sky-300">for</span> question <span class="text-sky-300">in</span> quiz:
    answer = <span class="text-orange-300">input</span>(question.text)
    <span class="text-sky-300">if</span> answer == question.correct:
        score += 1
<span class="text-orange-300">print</span>(<span class="text-emerald-300">f"You scored {score}!"</span>)</pre>
                </div>
            </div>

            {{-- Grow --}}
            <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-16" data-reveal>
                <div class="lg:order-2">
                    <p class="text-sm font-bold text-cau-orange-darker dark:text-orange-300">Grow</p>
                    <h3 class="mt-2 text-2xl font-bold text-cau-navy sm:text-3xl dark:text-white">Develop skills you keep using.</h3>
                    <p class="mt-3 max-w-md leading-relaxed text-zinc-600 dark:text-zinc-400">Track your progress, earn XP and badges as you go, and receive a certificate when you complete a course.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 lg:order-1 dark:border-zinc-800 dark:bg-zinc-900" aria-hidden="true">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-bold text-cau-navy dark:text-white">Course progress</p>
                        <p class="text-sm font-bold text-cau-orange-darker dark:text-orange-300">72%</p>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full w-[72%] rounded-full bg-gradient-to-r from-cau-navy to-cau-blue"></div>
                    </div>
                    <div class="mt-6 grid grid-cols-3 gap-3">
                        @foreach ([['badge', 'First lesson'], ['star', 'First project'], ['chart', '7-day streak']] as [$icon, $label])
                            <div class="rounded-xl border border-zinc-200 p-3 text-center dark:border-zinc-800">
                                <span class="mx-auto flex size-9 items-center justify-center rounded-full bg-blue-50 text-cau-navy dark:bg-blue-500/10 dark:text-blue-200"><x-home.icon :name="$icon" class="size-4.5" /></span>
                                <p class="mt-2 text-[11px] font-semibold leading-tight text-zinc-600 dark:text-zinc-400">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
