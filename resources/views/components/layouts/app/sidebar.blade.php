<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @livewireStyles
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @include('partials.analytics.body')
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            {{-- Brand --}}
            @php
                $appName = cache()->remember('app_name', 86400, fn() => \App\Models\SystemSetting::get('app_name', config('app.name')));
                $appShortName = cache()->remember('app_short_name', 86400, fn() => \App\Models\SystemSetting::get('app_short_name', 'CAU'));
                $logo = cache()->remember('logo_path', 86400, fn() => \App\Models\SystemSetting::get('logo'));
                $logoDark = cache()->remember('logo_dark_path', 86400, fn() => \App\Models\SystemSetting::get('logo_dark'));
            @endphp
            <div class="mb-4 px-1 pt-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-slate-50 dark:hover:bg-zinc-800" wire:navigate>
                    @if($logo || $logoDark)
                        <img src="{{ asset('storage/' . ($logo ?: $logoDark)) }}" alt="{{ $appName }}" class="h-9 w-9 object-contain dark:hidden rounded-lg">
                        <img src="{{ asset('storage/' . ($logoDark ?: $logo)) }}" alt="{{ $appName }}" class="hidden h-9 w-9 object-contain dark:block rounded-lg">
                    @else
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-orange-600 text-sm font-bold text-white">
                            {{ strtoupper(substr($appShortName, 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        @php
                            $roleLabel = match (true) {
                                auth()->user()->isAdmin() => 'Administrator',
                                auth()->user()->isSupervisor() => 'Supervisor',
                                auth()->user()->isIctTeacher() => 'ICT Teacher',
                                auth()->user()->isTeacher() => 'Teacher',
                                auth()->user()->isStudent() => 'Student',
                                default => 'Member',
                            };
                        @endphp
                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $appName }}</p>
                        <p class="truncate text-xs font-medium text-orange-600 dark:text-orange-400">{{ $roleLabel }}</p>
                    </div>
                </a>
            </div>

            <x-navigation.program-switcher />

            <x-navigation.sidebar :user="auth()->user()" />

            <flux:spacer />

            {{-- User --}}
            <div class="border-t border-zinc-200 px-2 py-3 dark:border-zinc-700">
                <flux:dropdown class="w-full" position="top" align="start">
                    <button type="button" class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-800" data-test="sidebar-menu-button">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white">
                            {{ auth()->user()->initials() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->loginIdentifier() }}</p>
                        </div>
                    </button>

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-blue-600 font-bold text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->loginIdentifier() }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
            </div>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-blue-600 font-bold text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->loginIdentifier() }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @livewireScripts
        <script>
            document.addEventListener('livewire:init', () => {
                if (!window.Livewire || window.__codecamp419Hook) return;
                window.__codecamp419Hook = true;
                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status !== 419) return;
                        preventDefault();
                        window.location.replace('/login');
                    });
                });
            });
        </script>
        @fluxScripts
        @stack('scripts')
    </body>
</html>
