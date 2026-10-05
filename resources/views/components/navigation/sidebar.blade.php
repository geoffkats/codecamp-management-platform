@props(['user'])

@php
    $dashboardActive = request()->routeIs('dashboard');
    $searchIndex = collect($sections)->mapWithKeys(fn ($s) => [$s['key'] => collect($s['items'])->pluck('keywords')->all()])->all();
    $defaultCollapsed = collect(config('navigation.collapsed', []))->all();
    $activeKeys = collect($sections)->where('active', true)->pluck('key')->all();
    $itemClasses = 'group relative flex items-center gap-3 rounded-xl px-2.5 py-2 text-[13.5px] transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/60';
    $activeClasses = 'bg-orange-50 font-semibold text-orange-700 ring-1 ring-orange-100 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/20';
    $idleClasses = 'font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-white';
@endphp

<div
    class="flex min-h-0 flex-1 flex-col"
    x-data="{
        q: '',
        index: @js($searchIndex),
        collapsed: [],
        active: @js($activeKeys),
        init() {
            let saved = null;
            try { saved = JSON.parse(localStorage.getItem('cau.sidebar.collapsed')); } catch (e) {}
            this.collapsed = Array.isArray(saved) ? saved : @js($defaultCollapsed);
            this.$nextTick(() => this.$root.querySelector('nav [aria-current=page]')?.scrollIntoView({ block: 'nearest' }));
        },
        term() { return this.q.trim().toLowerCase(); },
        matches(keywords) { const t = this.term(); return !t || t.split(/\s+/).every(w => keywords.includes(w)); },
        sectionVisible(key) { return !this.term() || (this.index[key] || []).some(k => this.matches(k)); },
        anyMatch() { return Object.keys(this.index).some(k => this.sectionVisible(k)) || this.matches('dashboard home overview'); },
        isOpen(key) { return !!this.term() || this.active.includes(key) || !this.collapsed.includes(key); },
        toggle(key) {
            if (this.term()) return;
            if (this.active.includes(key)) { this.active = this.active.filter(k => k !== key); if (!this.collapsed.includes(key)) this.collapsed.push(key); }
            else if (this.collapsed.includes(key)) { this.collapsed = this.collapsed.filter(k => k !== key); }
            else { this.collapsed.push(key); }
            localStorage.setItem('cau.sidebar.collapsed', JSON.stringify(this.collapsed));
        },
        openFirst() {
            const link = [...this.$root.querySelectorAll('a[data-nav-item]')].find(a => a.offsetParent !== null);
            if (!link) return;
            this.q = '';
            window.Livewire?.navigate ? Livewire.navigate(link.href) : (window.location.href = link.href);
        },
        focusSearch(e) {
            const tag = (e.target.tagName || '').toLowerCase();
            if (['input', 'textarea', 'select'].includes(tag) || e.target.isContentEditable) return;
            e.preventDefault();
            this.$refs.search.focus();
        },
    }"
    @keydown.window.slash="focusSearch($event)"
    @keydown.window.ctrl.k.prevent="$refs.search.focus()"
    @keydown.window.meta.k.prevent="$refs.search.focus()"
>
    {{-- Quick find --}}
    <div class="relative mb-3 shrink-0 px-1">
        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/>
        </svg>
        <input
            x-ref="search"
            x-model="q"
            type="search"
            placeholder="{{ __('Search menu…') }}"
            aria-label="{{ __('Search menu') }}"
            autocomplete="off"
            @keydown.enter.prevent="openFirst()"
            @keydown.escape="q = ''; $el.blur()"
            class="w-full rounded-xl border-0 bg-zinc-100 py-2 pl-9 pr-12 text-sm text-zinc-800 placeholder-zinc-400 ring-1 ring-transparent transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 dark:bg-white/5 dark:text-zinc-100 dark:focus:bg-zinc-900 [&::-webkit-search-cancel-button]:hidden"
        >
        <kbd x-show="!q" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded-md border border-zinc-200 bg-white px-1.5 py-0.5 font-sans text-[10px] font-semibold text-zinc-400 dark:border-zinc-700 dark:bg-zinc-800">Ctrl K</kbd>
        <button x-show="q" x-cloak type="button" @click="q = ''; $refs.search.focus()" class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-md p-1 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-white/10" aria-label="{{ __('Clear search') }}">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="-mx-1 flex min-h-0 flex-1 flex-col gap-0.5 overflow-y-auto overscroll-contain px-2 pb-2 [scrollbar-width:thin]" aria-label="{{ __('Main') }}">
        <a
            href="{{ route('dashboard') }}"
            wire:navigate
            data-nav-item
            x-show="matches('dashboard home overview')"
            @if($dashboardActive) aria-current="page" @endif
            class="{{ $itemClasses }} {{ $dashboardActive ? $activeClasses : $idleClasses }}"
        >
            @if($dashboardActive)<span class="absolute -left-1 top-2 bottom-2 w-1 rounded-full bg-orange-500"></span>@endif
            <flux:icon name="home" variant="{{ $dashboardActive ? 'solid' : 'outline' }}" class="size-[18px] shrink-0 {{ $dashboardActive ? 'text-orange-600 dark:text-orange-400' : 'text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-200' }}" />
            <span class="truncate">{{ __('Dashboard') }}</span>
        </a>

        @foreach($sections as $section)
            <div x-show="sectionVisible(@js($section['key']))" class="mt-2">
                <button
                    type="button"
                    @click="toggle(@js($section['key']))"
                    class="group/section flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/50"
                    :aria-expanded="isOpen(@js($section['key'])).toString()"
                >
                    <span class="flex-1 truncate text-[11px] font-bold uppercase tracking-[0.08em] {{ $section['active'] ? 'text-orange-600 dark:text-orange-400' : 'text-zinc-400 group-hover/section:text-zinc-600 dark:text-zinc-500 dark:group-hover/section:text-zinc-300' }}">
                        {{ $section['label'] }}
                    </span>
                    @if($section['badge'] > 0)
                        <span x-show="!isOpen(@js($section['key']))" x-cloak class="h-2 w-2 rounded-full bg-rose-500" title="{{ $section['badge'] }} {{ __('waiting') }}"></span>
                    @endif
                    <span x-show="!isOpen(@js($section['key']))" x-cloak class="text-[10px] font-semibold text-zinc-400">{{ count($section['items']) }}</span>
                    <svg class="h-3.5 w-3.5 text-zinc-400 transition-transform duration-200" :class="isOpen(@js($section['key'])) ? '' : '-rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m19 9-7 7-7-7"/>
                    </svg>
                </button>

                <div
                    x-show="isOpen(@js($section['key']))"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="mt-0.5 flex flex-col gap-0.5"
                >
                    @foreach($section['items'] as $item)
                        <a
                            href="{{ $item['href'] }}"
                            wire:navigate
                            data-nav-item
                            x-show="matches(@js($item['keywords']))"
                            @if($item['active']) aria-current="page" @endif
                            class="{{ $itemClasses }} {{ $item['active'] ? $activeClasses : $idleClasses }}"
                        >
                            @if($item['active'])<span class="absolute -left-1 top-2 bottom-2 w-1 rounded-full bg-orange-500"></span>@endif
                            <flux:icon :name="$item['icon']" :variant="$item['active'] ? 'solid' : 'outline'" class="size-[18px] shrink-0 {{ $item['active'] ? 'text-orange-600 dark:text-orange-400' : 'text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-200' }}" />
                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                            @if($item['badge'] > 0)
                                <span class="min-w-5 rounded-full bg-rose-500 px-1.5 py-0.5 text-center text-[10px] font-bold leading-none text-white shadow-sm shadow-rose-500/30">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div x-show="term() && !anyMatch()" x-cloak class="mx-1 mt-3 rounded-xl border border-dashed border-zinc-200 px-3 py-4 text-center dark:border-zinc-700">
            <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Nothing found') }}</p>
            <p class="mt-0.5 text-xs text-zinc-500">{{ __('No menu item matches') }} “<span x-text="q"></span>”.</p>
        </div>
    </nav>

    {{-- Account shortcuts --}}
    <div class="grid shrink-0 grid-cols-2 gap-1.5 px-1 pt-3">
        @php
            $notificationsActive = request()->routeIs('notifications.*');
            $settingsActive = request()->routeIs('profile.edit');
        @endphp
        <a href="{{ route('notifications.index') }}" wire:navigate
           class="relative flex items-center justify-center gap-2 rounded-xl px-2 py-2 text-xs font-semibold ring-1 transition {{ $notificationsActive ? 'bg-orange-50 text-orange-700 ring-orange-100 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/20' : 'text-zinc-600 ring-zinc-200 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-300 dark:ring-zinc-700 dark:hover:bg-white/5' }}">
            <flux:icon name="bell" variant="outline" class="size-4" />
            {{ __('Alerts') }}
            @if(($unreadNotificationsCount ?? 0) > 0)
                <span class="absolute -right-1 -top-1 min-w-5 rounded-full bg-rose-500 px-1 py-0.5 text-center text-[10px] font-bold leading-none text-white ring-2 ring-white dark:ring-zinc-900">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
            @endif
        </a>
        <a href="{{ route('profile.edit') }}" wire:navigate
           class="flex items-center justify-center gap-2 rounded-xl px-2 py-2 text-xs font-semibold ring-1 transition {{ $settingsActive ? 'bg-orange-50 text-orange-700 ring-orange-100 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/20' : 'text-zinc-600 ring-zinc-200 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-300 dark:ring-zinc-700 dark:hover:bg-white/5' }}">
            <flux:icon name="cog-6-tooth" variant="outline" class="size-4" />
            {{ __('Settings') }}
        </a>
    </div>
</div>
