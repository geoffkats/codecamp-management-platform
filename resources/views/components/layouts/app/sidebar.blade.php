<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @livewireStyles
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-gradient-to-b from-gray-50 via-blue-50/30 to-purple-50/30 dark:border-zinc-700 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 shadow-xl">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            {{-- Logo Section --}}
            @php
                $appName = cache()->remember('app_name', 86400, fn() => \App\Models\SystemSetting::get('app_name', config('app.name')));
                $appShortName = cache()->remember('app_short_name', 86400, fn() => \App\Models\SystemSetting::get('app_short_name', 'CAU'));
                $appTagline = cache()->remember('app_tagline', 86400, fn() => \App\Models\SystemSetting::get('app_tagline', 'E-Learning Platform'));
                $logo = cache()->remember('logo_path', 86400, fn() => \App\Models\SystemSetting::get('logo'));
                $logoDark = cache()->remember('logo_dark_path', 86400, fn() => \App\Models\SystemSetting::get('logo_dark'));
            @endphp
            <div class="me-5 mb-4">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 rtl:space-x-reverse group" wire:navigate>
                    @if($logo || $logoDark)
                        <div class="w-12 h-12 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform overflow-hidden">
                            <img src="{{ asset('storage/' . ($logo ?: $logoDark)) }}" alt="{{ $appName }}" class="w-10 h-10 object-contain dark:hidden">
                            <img src="{{ asset('storage/' . ($logoDark ?: $logo)) }}" alt="{{ $appName }}" class="w-10 h-10 object-contain hidden dark:block">
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                            <x-app-logo class="w-8 h-8" />
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ $appShortName }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $appTagline }}</p>
                    </div>
                </a>
            </div>

            <x-navigation.sidebar :user="auth()->user()" />

            @can('manage_users')
            <div class="px-3 mt-2">
                <flux:navlist variant="outline" class="rounded-lg">
                    <flux:navlist.item icon="shield-check" href="{{ route('admin.audit.logs') }}" wire:navigate>
                        {{ __('Audit Logs') }}
                    </flux:navlist.item>
                </flux:navlist>
            </div>
            @endcan

            <flux:spacer />

            {{-- Optional: External Links Section - Can be hidden if not needed --}}
            @if(false)
            <div class="px-3 mt-4">
                <flux:navlist variant="outline" class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-2">
                    <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                        {{ __('Repository') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                        {{ __('Documentation') }}
                    </flux:navlist.item>
                </flux:navlist>
            </div>
            @endif

            <!-- Desktop User Menu -->
            <div class="mt-4 px-3 pb-3">
                <div class="bg-gradient-to-r from-blue-500/10 via-purple-500/10 to-pink-500/10 dark:from-blue-500/20 dark:via-purple-500/20 dark:to-pink-500/20 rounded-xl p-3 border border-blue-200/50 dark:border-blue-700/50">
                    <flux:dropdown class="w-full" position="top" align="start">
                        <button type="button" class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-white/50 dark:hover:bg-gray-800/50 transition-colors" data-test="sidebar-menu-button">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-lg">
                                {{ auth()->user()->initials() }}
                            </div>
                            <div class="flex-1 min-w-0 text-left">
                                <div class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Str::limit(auth()->user()->email ?: auth()->user()->student_id, 20) }}</div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email ?: auth()->user()->student_id }}</span>
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
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email ?: auth()->user()->student_id }}</span>
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
        @fluxScripts
        @stack('scripts')
    </body>
</html>
