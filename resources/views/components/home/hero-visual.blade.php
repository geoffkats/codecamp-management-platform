{{-- Illustrative project preview built in HTML/CSS; decorative, so hidden from assistive tech. --}}
<div class="hero-in hero-in-delay relative mx-auto w-full max-w-md lg:max-w-none" aria-hidden="true">
    <div class="relative mx-auto max-w-lg">
        <div class="overflow-hidden rounded-2xl border border-white/15 bg-white shadow-2xl shadow-cau-deep/50 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-cau-orange-darker dark:text-orange-300">Project</p>
                    <p class="text-sm font-bold text-cau-navy dark:text-white">My First Web App</p>
                </div>
                <div class="flex gap-1.5">
                    <span class="size-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                    <span class="size-2.5 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                    <span class="size-2.5 rounded-full bg-cau-orange"></span>
                </div>
            </div>

            <div class="flex gap-1 border-b border-zinc-200 px-3 pt-2 text-xs font-semibold dark:border-zinc-800">
                <span class="rounded-t-md border-b-2 border-cau-orange px-3 py-1.5 text-cau-navy dark:text-white">index.html</span>
                <span class="px-3 py-1.5 text-zinc-400">style.css</span>
                <span class="px-3 py-1.5 text-zinc-400">app.js</span>
            </div>

            <div class="grid sm:grid-cols-[1.15fr_1fr]">
                <pre class="overflow-hidden bg-cau-deep px-4 py-4 font-mono text-[11.5px] leading-6 text-blue-100/90"><span class="text-blue-300/50">1</span>  <span class="text-sky-300">&lt;h1&gt;</span>Hello, Kampala!<span class="text-sky-300">&lt;/h1&gt;</span>
<span class="text-blue-300/50">2</span>  <span class="text-sky-300">&lt;button</span> <span class="text-orange-300">id</span>=<span class="text-emerald-300">"go"</span><span class="text-sky-300">&gt;</span>
<span class="text-blue-300/50">3</span>    Start
<span class="text-blue-300/50">4</span>  <span class="text-sky-300">&lt;/button&gt;</span>
<span class="text-blue-300/50">5</span>
<span class="text-blue-300/50">6</span>  <span class="text-sky-300">&lt;script&gt;</span>
<span class="text-blue-300/50">7</span>   go.<span class="text-orange-300">onclick</span> = () =&gt;
<span class="text-blue-300/50">8</span>     <span class="text-orange-300">celebrate</span>()
<span class="text-blue-300/50">9</span>  <span class="text-sky-300">&lt;/script&gt;</span></pre>

                <div class="hidden flex-col justify-between bg-white p-4 sm:flex dark:bg-zinc-900">
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Preview</p>
                        <p class="mt-2 text-base font-bold text-cau-navy dark:text-white">Hello, Kampala!</p>
                        <span class="mt-3 inline-flex rounded-md bg-cau-navy px-3 py-1.5 text-xs font-semibold text-white">Start</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        <span class="rounded-md bg-orange-50 px-2 py-1 text-[10px] font-bold text-cau-orange-darker dark:bg-orange-500/10 dark:text-orange-300">HTML</span>
                        <span class="rounded-md bg-blue-50 px-2 py-1 text-[10px] font-bold text-cau-blue-dark dark:bg-blue-500/10 dark:text-blue-300">CSS</span>
                        <span class="rounded-md bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">JavaScript</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 border-t border-zinc-200 px-4 py-3 dark:border-zinc-800">
                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Project progress</span>
                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                    <div class="h-full w-2/3 rounded-full bg-cau-orange"></div>
                </div>
                <span class="text-xs font-bold text-cau-navy dark:text-white">Step 4 of 6</span>
            </div>
        </div>

        <div class="absolute -left-10 top-10 hidden rounded-xl border border-white/15 bg-white/95 px-3.5 py-2.5 shadow-xl shadow-cau-deep/30 lg:flex lg:items-center lg:gap-2.5 dark:bg-zinc-900/95">
            <span class="flex size-8 items-center justify-center rounded-lg bg-blue-50 text-cau-blue-dark dark:bg-blue-500/10 dark:text-blue-300"><x-home.icon name="python" class="size-4.5" /></span>
            <span class="text-sm font-semibold text-cau-navy dark:text-white">Python</span>
        </div>
        <div class="absolute -right-8 top-24 hidden rounded-xl border border-white/15 bg-white/95 px-3.5 py-2.5 shadow-xl shadow-cau-deep/30 lg:flex lg:items-center lg:gap-2.5 dark:bg-zinc-900/95">
            <span class="flex size-8 items-center justify-center rounded-lg bg-orange-50 text-cau-orange-darker dark:bg-orange-500/10 dark:text-orange-300"><x-home.icon name="robot" class="size-4.5" /></span>
            <span class="text-sm font-semibold text-cau-navy dark:text-white">Robotics</span>
        </div>
        <div class="absolute -left-6 -bottom-6 hidden rounded-xl border border-white/15 bg-white/95 px-3.5 py-2.5 shadow-xl shadow-cau-deep/30 lg:flex lg:items-center lg:gap-2.5 dark:bg-zinc-900/95">
            <span class="flex size-8 items-center justify-center rounded-lg bg-blue-50 text-cau-blue-dark dark:bg-blue-500/10 dark:text-blue-300"><x-home.icon name="code" class="size-4.5" /></span>
            <span class="text-sm font-semibold text-cau-navy dark:text-white">Web Development</span>
        </div>
        <div class="absolute -right-4 -bottom-8 hidden rounded-xl border border-white/15 bg-white/95 px-3.5 py-2.5 shadow-xl shadow-cau-deep/30 lg:flex lg:items-center lg:gap-2.5 dark:bg-zinc-900/95">
            <span class="flex size-8 items-center justify-center rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300"><x-home.icon name="blocks" class="size-4.5" /></span>
            <span class="text-sm font-semibold text-cau-navy dark:text-white">Scratch</span>
        </div>

        <div class="mt-5 flex flex-wrap justify-center gap-2 lg:hidden">
            @foreach (['Python', 'Robotics', 'Web Development', 'Scratch'] as $chip)
                <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-blue-50">{{ $chip }}</span>
            @endforeach
        </div>
    </div>
</div>
