@php
    $help = app(\App\Support\Help\HelpCenter::class);
    $user = auth()->user();
    $helpReady = $user && $help->canSee($user) && ! request()->routeIs('help.*')
        && rescue(fn () => $help->latestUpdateKey($user) || true, false);
@endphp

@if($helpReady)
    @persist('help-drawer')
    <div x-data="{
            open: false,
            loaded: false,
            loading: false,
            failed: false,
            latestKey: @js($help->latestUpdateKey($user)),
            seenKey: localStorage.getItem('cau-help-seen'),
            get hasUnseen() { return this.latestKey && this.seenKey !== this.latestKey; },
            async show(guide = null) {
                this.open = true;
                if (! this.loaded && ! this.loading) {
                    this.loading = true;
                    this.failed = false;
                    try {
                        const res = await fetch(@js(route('help.panel')), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        if (! res.ok) throw new Error(res.status);
                        this.$refs.body.innerHTML = await res.text();
                        [...this.$refs.body.children].forEach((el) => window.Alpine.initTree(el));
                        this.loaded = true;
                    } catch (e) {
                        this.failed = true;
                    } finally {
                        this.loading = false;
                    }
                }
                if (guide) this.$nextTick(() => window.dispatchEvent(new CustomEvent('help-open-guide', { detail: guide })));
            },
         }"
         x-on:help-close.window="open = false"
         x-on:help-show.window="show($event.detail?.guide)"
         x-on:help-seen.window="seenKey = $event.detail"
         x-on:keydown.escape.window="open = false">

        <button type="button" x-on:click="show()" x-show="! open"
                class="group fixed right-0 top-1/2 z-40 flex -translate-y-1/2 flex-col items-center gap-2 rounded-l-2xl bg-gradient-to-b from-orange-500 to-orange-600 px-1.5 py-4 text-white shadow-lg shadow-orange-600/30 transition hover:px-2.5"
                aria-label="Open help and what's new">
            <flux:icon name="book-open" variant="mini" class="size-4" />
            <span class="text-[11px] font-black uppercase tracking-widest [writing-mode:vertical-rl]">Help &amp; What's new</span>
            <span x-show="hasUnseen" x-cloak class="relative mt-0.5 flex size-2.5">
                <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-300 opacity-75"></span>
                <span class="relative inline-flex size-2.5 rounded-full bg-emerald-300 ring-2 ring-orange-600"></span>
            </span>
        </button>

        {{-- Drawer --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Help and what's new">
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-zinc-900/40 backdrop-blur-[2px]" x-on:click="open = false"></div>
            <div x-show="open"
                 x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                 class="absolute inset-y-0 right-0 flex w-full max-w-2xl flex-col bg-white shadow-2xl dark:bg-zinc-900">
                <div x-ref="body" class="flex min-h-0 flex-1 flex-col"></div>
                <div x-show="loading" class="flex flex-1 items-center justify-center gap-2 text-sm font-semibold text-zinc-500">
                    <svg class="size-5 animate-spin text-orange-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Loading the manual…
                </div>
                <div x-show="failed" x-cloak class="flex flex-1 flex-col items-center justify-center gap-3 p-6 text-center text-sm text-zinc-600">
                    <p>The manual couldn't be loaded. Check your connection and try again.</p>
                    <div class="flex gap-2">
                        <button type="button" x-on:click="show()" class="rounded-lg bg-orange-500 px-4 py-2 font-bold text-white">Try again</button>
                        <button type="button" x-on:click="open = false" class="rounded-lg bg-zinc-100 px-4 py-2 font-bold text-zinc-700">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endpersist
@endif
