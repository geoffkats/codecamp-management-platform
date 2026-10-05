@php
    $guidesBySlug = $guides->keyBy('slug');
    $tagStyles = [
        'New' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'Improved' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'Fixed' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
        'Changed' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
    ];
@endphp

<div class="flex h-full min-h-0 flex-col"
     x-data="{
        tab: @js($initialGuide ? 'guides' : 'updates'),
        guide: @js($initialGuide),
        q: '',
        latestKey: @js($latestKey),
        seenKey: localStorage.getItem('cau-help-seen'),
        get hasUnseen() { return this.latestKey && this.seenKey !== this.latestKey; },
        init() {
            if (! this.guide && ! this.hasUnseen) this.tab = 'guides';
            this.$watch('tab', () => this.markSeen());
            this.markSeen();
        },
        markSeen() {
            if (this.tab !== 'updates' || ! this.latestKey) return;
            localStorage.setItem('cau-help-seen', this.latestKey);
            window.dispatchEvent(new CustomEvent('help-seen', { detail: this.latestKey }));
        },
        open(slug) {
            this.guide = slug;
            this.tab = 'guides';
            this.q = '';
            this.$nextTick(() => this.$refs.scroller.scrollTo({ top: 0 }));
        },
        back() {
            this.guide = null;
            this.$nextTick(() => this.$refs.scroller.scrollTo({ top: 0 }));
        },
        matches(el) {
            return ! this.q || el.dataset.search.includes(this.q.trim().toLowerCase());
        },
     }"
     x-on:help-open-guide.window="open($event.detail)">

    {{-- Header --}}
    <div class="relative shrink-0 overflow-hidden bg-gradient-to-br from-orange-500 to-orange-600 px-5 pb-4 pt-5 text-white">
        <div aria-hidden="true" class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full bg-white/10"></div>
        <div class="relative flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-white/80">Staff manual</p>
                <h2 class="text-xl font-black tracking-tight">Help &amp; What's new</h2>
                <p class="mt-0.5 text-sm text-white/85">How things work on your side of the system, and what changed recently.</p>
            </div>
            @if($mode === 'drawer')
                <div class="flex shrink-0 items-center gap-1">
                    <a href="{{ route('help.index') }}" wire:navigate x-on:click="$dispatch('help-close')"
                       class="rounded-lg p-2 text-white/85 transition hover:bg-white/15 hover:text-white" title="Open as a full page">
                        <flux:icon name="arrows-pointing-out" variant="mini" class="size-5" />
                    </a>
                    <button type="button" x-on:click="$dispatch('help-close')" class="rounded-lg p-2 text-white/85 transition hover:bg-white/15 hover:text-white" aria-label="Close help">
                        <flux:icon name="x-mark" variant="mini" class="size-5" />
                    </button>
                </div>
            @endif
        </div>

        <label class="relative mt-4 block">
            <span class="sr-only">Search the manual</span>
            <flux:icon name="magnifying-glass" variant="mini" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
            <input type="search" x-model="q" placeholder="Search guides and updates… e.g. grading, Scratch, attendance"
                   class="w-full rounded-xl border-0 bg-white py-2.5 pl-9 pr-3 text-sm text-zinc-900 shadow-sm placeholder:text-zinc-400 focus:ring-2 focus:ring-orange-300">
        </label>

        <div class="mt-3 flex gap-1.5" x-show="! q">
            <button type="button" x-on:click="tab = 'updates'; guide = null"
                    :class="tab === 'updates' ? 'bg-white text-orange-600 shadow' : 'bg-white/15 text-white hover:bg-white/25'"
                    class="relative inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-bold transition">
                <flux:icon name="sparkles" variant="mini" class="size-4" /> What's new
                <span x-show="hasUnseen" x-cloak class="absolute -right-0.5 -top-0.5 size-2.5 rounded-full bg-emerald-400 ring-2 ring-orange-500"></span>
            </button>
            <button type="button" x-on:click="tab = 'guides'; guide = null"
                    :class="tab === 'guides' ? 'bg-white text-orange-600 shadow' : 'bg-white/15 text-white hover:bg-white/25'"
                    class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-bold transition">
                <flux:icon name="book-open" variant="mini" class="size-4" /> Guides
                <span class="rounded-full bg-black/10 px-1.5 text-[11px]">{{ $guides->count() }}</span>
            </button>
        </div>
    </div>

    {{-- Body --}}
    <div x-ref="scroller" class="min-h-0 flex-1 overflow-y-auto bg-zinc-50 dark:bg-zinc-900">

        {{-- Search results --}}
        <div x-show="q" x-cloak class="space-y-2 p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Results</p>
            @foreach($guides as $g)
                <button type="button" x-show="matches($el)" data-search="{{ $g['search'] }}" x-on:click="open(@js($g['slug']))"
                        class="flex w-full items-start gap-3 rounded-xl bg-white p-3 text-left ring-1 ring-zinc-100 transition hover:ring-orange-300 dark:bg-zinc-800 dark:ring-zinc-700">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-500/10"><flux:icon :name="$g['icon']" variant="mini" class="size-5" /></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-bold text-zinc-900 dark:text-white">{{ $g['title'] }}</span>
                        <span class="block text-xs text-zinc-500">Guide · {{ $g['category'] }}</span>
                    </span>
                </button>
            @endforeach
            @foreach($updates as $u)
                <button type="button" x-show="matches($el)" data-search="{{ $u['search'] }}" x-on:click="q = ''; tab = 'updates'; guide = null; $nextTick(() => document.getElementById(@js('update-'.$u['slug']))?.scrollIntoView({ block: 'start' }))"
                        class="flex w-full items-start gap-3 rounded-xl bg-white p-3 text-left ring-1 ring-zinc-100 transition hover:ring-orange-300 dark:bg-zinc-800 dark:ring-zinc-700">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10"><flux:icon name="sparkles" variant="mini" class="size-5" /></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-bold text-zinc-900 dark:text-white">{{ $u['title'] }}</span>
                        <span class="block text-xs text-zinc-500">What's new · {{ \Illuminate\Support\Carbon::parse($u['date'])->format('j M Y') }}</span>
                    </span>
                </button>
            @endforeach
            <p class="py-8 text-center text-sm text-zinc-500"
               x-show="! [...$root.querySelectorAll('[data-search]')].some(el => matches(el))">
                Nothing matches “<span x-text="q"></span>”. Try a shorter word.
            </p>
        </div>

        {{-- What's new --}}
        <div x-show="! q && tab === 'updates'" class="p-4 sm:p-5">
            @forelse($updates as $u)
                <article id="update-{{ $u['slug'] }}" class="relative scroll-mt-4 pb-6 pl-6 last:pb-0">
                    <span class="absolute left-[5px] top-2 bottom-0 w-0.5 bg-zinc-200 dark:bg-zinc-700"></span>
                    <span class="absolute left-0 top-1.5 size-3 rounded-full bg-orange-500 ring-4 ring-orange-100 dark:ring-orange-500/20"></span>
                    <div class="rounded-2xl bg-white p-4 ring-1 ring-zinc-100 sm:p-5 dark:bg-zinc-800 dark:ring-zinc-700">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-xs font-bold text-zinc-500">{{ \Illuminate\Support\Carbon::parse($u['date'])->format('j M Y') }}</span>
                            @foreach($u['tags'] as $tag)
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $tagStyles[$tag] ?? 'bg-zinc-100 text-zinc-600' }}">{{ $tag }}</span>
                            @endforeach
                            <span class="ml-auto text-[11px] font-semibold text-zinc-400">For {{ implode(', ', $u['audienceLabels']) }}</span>
                        </div>
                        <h3 class="mt-1.5 text-base font-extrabold text-zinc-900 dark:text-white">{{ $u['title'] }}</h3>
                        @if($u['summary'])
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $u['summary'] }}</p>
                        @endif
                        @if($u['image'] ?? null)
                            <img src="{{ $u['image'] }}" alt="{{ $u['title'] }}" loading="lazy" class="mt-3 w-full rounded-xl ring-1 ring-zinc-200 dark:ring-zinc-700">
                        @endif
                        <div x-data="{ more: false }">
                            <div x-show="more" x-collapse x-cloak class="help-article mt-3">{!! $u['html'] !!}</div>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <button type="button" x-on:click="more = ! more" class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 px-3 py-1.5 text-xs font-bold text-zinc-700 transition hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-200">
                                    <span x-text="more ? 'Show less' : 'Read what changed'"></span>
                                    <flux:icon name="chevron-down" variant="micro" class="size-3.5 transition" x-bind:class="more && 'rotate-180'" />
                                </button>
                                @if($u['guide'] && $guidesBySlug->has($u['guide']))
                                    <button type="button" x-on:click="open(@js($u['guide']))" class="inline-flex items-center gap-1 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-700 transition hover:bg-orange-100 dark:bg-orange-500/10 dark:text-orange-300">
                                        <flux:icon name="book-open" variant="micro" class="size-3.5" /> Full guide: {{ $guidesBySlug[$u['guide']]['title'] }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <p class="py-10 text-center text-sm text-zinc-500">No updates yet.</p>
            @endforelse
        </div>

        {{-- Guide list --}}
        <div x-show="! q && tab === 'guides' && ! guide" class="space-y-6 p-4 sm:p-5">
            @foreach($guides->groupBy('category') as $category => $items)
                <section>
                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-zinc-400">{{ $category }}</h3>
                    <div class="grid gap-2 {{ $mode === 'page' ? 'sm:grid-cols-2' : '' }}">
                        @foreach($items as $g)
                            <button type="button" x-on:click="open(@js($g['slug']))"
                                    class="group flex w-full items-start gap-3 rounded-2xl bg-white p-3.5 text-left ring-1 ring-zinc-100 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-orange-200 dark:bg-zinc-800 dark:ring-zinc-700">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-600 transition group-hover:bg-orange-500 group-hover:text-white dark:bg-orange-500/10">
                                    <flux:icon :name="$g['icon']" variant="mini" class="size-5" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold text-zinc-900 dark:text-white">{{ $g['title'] }}</span>
                                    <span class="mt-0.5 block text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">{{ $g['summary'] }}</span>
                                    <span class="mt-1 block text-[11px] font-semibold text-zinc-400">{{ $g['minutes'] }} min read</span>
                                </span>
                                <flux:icon name="chevron-right" variant="mini" class="mt-2.5 size-4 shrink-0 text-zinc-300 group-hover:text-orange-500" />
                            </button>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        {{-- Guide article --}}
        @foreach($guides as $g)
            <article x-show="! q && tab === 'guides' && guide === @js($g['slug'])" x-cloak class="p-4 sm:p-6">
                <button type="button" x-on:click="back()" class="mb-4 inline-flex items-center gap-1 text-sm font-bold text-orange-600 hover:text-orange-700">
                    <flux:icon name="arrow-left" variant="mini" class="size-4" /> All guides
                </button>
                <div class="rounded-2xl bg-white p-5 ring-1 ring-zinc-100 sm:p-7 dark:bg-zinc-800 dark:ring-zinc-700">
                    <div class="flex items-start gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-orange-500 text-white shadow-md shadow-orange-500/30">
                            <flux:icon :name="$g['icon']" class="size-6" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-orange-600">{{ $g['category'] }}</p>
                            <h1 class="text-xl font-black leading-tight tracking-tight text-zinc-900 dark:text-white">{{ $g['title'] }}</h1>
                            <p class="mt-1 text-xs font-semibold text-zinc-400">{{ $g['minutes'] }} min read · For {{ implode(', ', $g['audienceLabels']) }}</p>
                        </div>
                    </div>
                    <div class="help-article mt-5">{!! $g['html'] !!}</div>
                    <div class="mt-8 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-700">
                        <button type="button" x-on:click="back()" class="rounded-lg bg-zinc-100 px-3 py-2 text-sm font-bold text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-200">← All guides</button>
                        <button type="button"
                                x-data="{ copied: false }"
                                x-on:click="navigator.clipboard?.writeText(@js(route('help.show', $g['slug']))); copied = true; setTimeout(() => copied = false, 1500)"
                                class="ml-auto inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-bold text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                            <flux:icon name="link" variant="mini" class="size-4" />
                            <span x-text="copied ? 'Link copied' : 'Copy link to this guide'"></span>
                        </button>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</div>
