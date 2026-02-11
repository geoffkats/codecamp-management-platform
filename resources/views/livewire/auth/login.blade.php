<x-layouts.auth>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Sign in to your account to continue</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf

            <!-- Email Address -->
            <div>
                <flux:input
                    name="email"
                    :label="__('Email or Student ID')"
                    type="text"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="email or student ID"
                    class="w-full"
                />
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <flux:label>Password</flux:label>
                    @if (Route::has('password.request'))
                        <flux:link class="text-sm hover:underline" :href="route('password.request')" wire:navigate>
                            {{ __('Forgot password?') }}
                        </flux:link>
                    @endif
                </div>
                <flux:input
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Enter your password')"
                    viewable
                    class="w-full"
                />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <flux:checkbox name="remember" :label="__('Keep me signed in')" :checked="old('remember')" />
            </div>

            <!-- Submit Button -->
            <div class="pt-1">
                <flux:button variant="primary" type="submit" class="w-full h-12 text-base font-semibold" data-test="login-button">
                    {{ __('Sign in') }}
                </flux:button>
            </div>
        </form>

        <!-- Sign Up Link -->
        @if (Route::has('register'))
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200 dark:border-gray-800"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white dark:bg-neutral-950 text-gray-500 dark:text-gray-400">New to Code Academy?</span>
                </div>
            </div>

            <div class="text-center">
                <flux:link :href="route('register')" wire:navigate class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('Create a free account →') }}
                </flux:link>
            </div>
        @endif
    </div>
</x-layouts.auth>
