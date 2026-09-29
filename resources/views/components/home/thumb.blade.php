@props(['path' => null, 'label' => null])

<div {{ $attributes->merge(['class' => 'absolute inset-0 overflow-hidden bg-cau-navy']) }} aria-hidden="true">
    <div class="absolute inset-0 opacity-[0.07] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:24px_24px]"></div>

    <div class="absolute inset-0 flex items-center justify-center p-[8%] transition duration-500 group-hover:scale-[1.04]">
        @switch($path)
            @case('coding')
                <div class="w-[62%] space-y-[6%]">
                    <div class="flex h-7 w-[70%] items-center rounded-md rounded-tl-2xl bg-amber-400 px-2.5 text-[10px] font-bold text-amber-950 shadow-lg">when clicked</div>
                    <div class="ml-[8%] flex h-7 w-[80%] items-center rounded-md bg-cau-orange px-2.5 text-[10px] font-bold text-white shadow-lg">move 10 steps</div>
                    <div class="ml-[8%] flex h-7 w-[90%] items-center gap-1.5 rounded-md bg-sky-400 px-2.5 text-[10px] font-bold text-sky-950 shadow-lg">repeat <span class="rounded bg-white/70 px-1.5">10</span></div>
                    <div class="ml-[16%] flex h-7 w-[64%] items-center rounded-md bg-fuchsia-400 px-2.5 text-[10px] font-bold text-fuchsia-950 shadow-lg">say Hello!</div>
                </div>
                @break

            @case('web')
                <div class="w-[78%] overflow-hidden rounded-lg bg-white shadow-2xl ring-1 ring-black/5">
                    <div class="flex items-center gap-1 border-b border-zinc-100 bg-zinc-50 px-2.5 py-1.5">
                        <span class="size-1.5 rounded-full bg-red-400"></span><span class="size-1.5 rounded-full bg-amber-400"></span><span class="size-1.5 rounded-full bg-emerald-400"></span>
                        <span class="ml-2 h-2 flex-1 rounded-full bg-zinc-200"></span>
                    </div>
                    <div class="space-y-2 p-3">
                        <div class="h-8 rounded-md bg-gradient-to-r from-cau-navy to-cau-blue"></div>
                        <div class="grid grid-cols-3 gap-1.5">
                            <div class="h-6 rounded bg-sky-100"></div><div class="h-6 rounded bg-orange-100"></div><div class="h-6 rounded bg-sky-100"></div>
                        </div>
                        <div class="h-1.5 w-3/4 rounded-full bg-zinc-200"></div>
                    </div>
                </div>
                @break

            @case('python')
                <div class="w-[78%] overflow-hidden rounded-lg bg-[#0b1220] font-mono text-[10px] leading-relaxed shadow-2xl ring-1 ring-white/10">
                    <div class="flex items-center gap-1 border-b border-white/10 px-2.5 py-1.5">
                        <span class="size-1.5 rounded-full bg-white/25"></span><span class="size-1.5 rounded-full bg-white/25"></span><span class="size-1.5 rounded-full bg-amber-400"></span>
                        <span class="ml-2 text-[9px] text-white/40">main.py</span>
                    </div>
                    <div class="px-3 py-2.5">
                        <p><span class="text-sky-400">def</span> <span class="text-amber-300">greet</span><span class="text-white/70">(name):</span></p>
                        <p class="pl-3"><span class="text-sky-400">return</span> <span class="text-emerald-300">f"Hi {name}!"</span></p>
                        <p class="mt-1 text-white/70"><span class="text-amber-300">print</span>(greet(<span class="text-emerald-300">"Uganda"</span>))</p>
                        <p class="mt-1 text-white/40">&gt;&gt;&gt; Hi Uganda!</p>
                    </div>
                </div>
                @break

            @case('robotics')
                <div class="flex flex-col items-center">
                    <span class="h-4 w-0.5 bg-white/70"></span>
                    <span class="-mt-5 size-2.5 rounded-full bg-cau-orange shadow-[0_0_12px] shadow-cau-orange"></span>
                    <div class="mt-3 flex h-16 w-24 items-center justify-center gap-4 rounded-2xl bg-white shadow-2xl">
                        <span class="size-4 rounded-full bg-cau-navy ring-4 ring-sky-200"></span>
                        <span class="size-4 rounded-full bg-cau-navy ring-4 ring-sky-200"></span>
                    </div>
                    <div class="mt-1.5 flex h-9 w-20 items-center justify-center gap-1 rounded-xl bg-white/90 shadow-xl">
                        <span class="h-1.5 w-2 rounded-sm bg-emerald-400"></span><span class="h-1.5 w-2 rounded-sm bg-amber-400"></span><span class="h-1.5 w-2 rounded-sm bg-red-400"></span>
                    </div>
                </div>
                @break

            @case('mobile')
                <div class="flex h-[88%] max-h-40 aspect-[9/17] flex-col rounded-[1.1rem] border-4 border-white bg-white shadow-2xl">
                    <div class="mx-auto mt-1 h-1 w-6 rounded-full bg-zinc-300"></div>
                    <div class="m-1.5 h-6 rounded-md bg-gradient-to-r from-indigo-500 to-cau-blue"></div>
                    <div class="grid flex-1 grid-cols-2 gap-1 px-1.5 pb-1.5">
                        <div class="rounded bg-orange-100"></div><div class="rounded bg-indigo-100"></div>
                        <div class="rounded bg-indigo-100"></div><div class="rounded bg-emerald-100"></div>
                    </div>
                </div>
                @break

            @case('ai')
                <svg viewBox="0 0 120 80" class="w-[70%] drop-shadow-xl" fill="none">
                    <g stroke="white" stroke-opacity=".45" stroke-width="1.2">
                        <path d="M20 20 60 12M20 20 60 40M20 20 60 68M20 60 60 12M20 60 60 40M20 60 60 68M60 12 100 40M60 40 100 40M60 68 100 40" />
                    </g>
                    <g fill="white"><circle cx="20" cy="20" r="6" /><circle cx="20" cy="60" r="6" /><circle cx="60" cy="12" r="6" /><circle cx="60" cy="40" r="6" /><circle cx="60" cy="68" r="6" /></g>
                    <circle cx="100" cy="40" r="8" fill="#f97316" />
                </svg>
                @break

            @case('digital')
                <div class="w-[74%] overflow-hidden rounded-lg bg-white shadow-2xl ring-1 ring-black/5">
                    <div class="flex items-center gap-1.5 bg-emerald-600 px-2.5 py-1.5">
                        <span class="h-1.5 w-8 rounded-full bg-white/70"></span><span class="h-1.5 w-5 rounded-full bg-white/40"></span>
                    </div>
                    <div class="grid grid-cols-4 gap-px bg-zinc-200 p-px">
                        @for ($i = 0; $i < 16; $i++)
                            <span class="h-3.5 {{ $i < 4 ? 'bg-emerald-50' : 'bg-white' }}"></span>
                        @endfor
                    </div>
                    <div class="flex items-end gap-1 px-2.5 py-2">
                        <span class="h-3 w-2.5 rounded-sm bg-emerald-300"></span><span class="h-5 w-2.5 rounded-sm bg-emerald-500"></span><span class="h-4 w-2.5 rounded-sm bg-cau-orange"></span><span class="h-6 w-2.5 rounded-sm bg-emerald-600"></span>
                    </div>
                </div>
                @break

            @default
                <div class="w-[74%] overflow-hidden rounded-lg bg-[#0b1220] font-mono text-[10px] leading-relaxed shadow-2xl ring-1 ring-white/10">
                    <div class="flex items-center gap-1 border-b border-white/10 px-2.5 py-1.5">
                        <span class="size-1.5 rounded-full bg-white/25"></span><span class="size-1.5 rounded-full bg-white/25"></span><span class="size-1.5 rounded-full bg-cau-orange"></span>
                    </div>
                    <div class="space-y-1.5 px-3 py-3">
                        <span class="block h-1.5 w-3/4 rounded-full bg-sky-400/70"></span>
                        <span class="ml-3 block h-1.5 w-1/2 rounded-full bg-emerald-300/70"></span>
                        <span class="ml-3 block h-1.5 w-2/3 rounded-full bg-amber-300/70"></span>
                        <span class="block h-1.5 w-1/3 rounded-full bg-white/30"></span>
                    </div>
                </div>
        @endswitch
    </div>

    @if ($label)
        <span class="absolute bottom-3 right-3 rounded-full bg-black/25 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-white/90 backdrop-blur-sm">{{ $label }}</span>
    @endif
</div>
