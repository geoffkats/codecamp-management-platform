@props(['user' => null])

@php
    $user = $user ?? auth()->user();
    $showSwitcher = $user?->hasDualProgramAccess() ?? false;
    $activeContext = $user?->activeProgramContext() ?? 'codecamp';
@endphp

@if($showSwitcher)
    <div {{ $attributes->merge(['class' => 'mx-2 mb-3']) }}>
        <p class="mb-2 px-1 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
            {{ __('Program View') }}
        </p>

        <div
            class="flex rounded-xl bg-zinc-100 p-1 ring-1 ring-zinc-200/80 dark:bg-zinc-800/80 dark:ring-zinc-700/80"
            role="tablist"
            aria-label="{{ __('Program view') }}"
        >
            <form method="POST" action="{{ route('program-context.switch') }}" class="min-w-0 flex-1">
                @csrf
                <input type="hidden" name="context" value="codecamp" />
                <button
                    type="submit"
                    role="tab"
                    aria-selected="{{ $activeContext === 'codecamp' ? 'true' : 'false' }}"
                    @class([
                        'flex w-full items-center justify-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-semibold transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/60',
                        'bg-white text-orange-600 shadow-sm ring-1 ring-zinc-200/60 dark:bg-zinc-900 dark:text-orange-400 dark:ring-zinc-700/60' => $activeContext === 'codecamp',
                        'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' => $activeContext !== 'codecamp',
                    ])
                >
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    <span class="truncate">{{ __('CodeCamp') }}</span>
                </button>
            </form>

            <form method="POST" action="{{ route('program-context.switch') }}" class="min-w-0 flex-1">
                @csrf
                <input type="hidden" name="context" value="codeclub" />
                <button
                    type="submit"
                    role="tab"
                    aria-selected="{{ $activeContext === 'codeclub' ? 'true' : 'false' }}"
                    @class([
                        'flex w-full items-center justify-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-semibold transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/60',
                        'bg-white text-blue-600 shadow-sm ring-1 ring-zinc-200/60 dark:bg-zinc-900 dark:text-blue-400 dark:ring-zinc-700/60' => $activeContext === 'codeclub',
                        'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' => $activeContext !== 'codeclub',
                    ])
                >
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate">{{ __('Code Club') }}</span>
                </button>
            </form>
        </div>
    </div>
@endif
