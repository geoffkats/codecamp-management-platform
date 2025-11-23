<x-layouts.auth>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create account</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Get started with Code Academy Uganda</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
            @csrf
            
            <!-- Name -->
            <div>
                <flux:input
                    name="name"
                    :label="__('Full name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="__('John Doe')"
                    class="w-full"
                />
            </div>

            <!-- Email Address -->
            <div>
                <flux:input
                    name="email"
                    :label="__('Email address')"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="your.email@example.com"
                    class="w-full"
                />
            </div>

            <!-- Password -->
            <div>
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Create a strong password')"
                    viewable
                    class="w-full"
                />
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Must be at least 8 characters</p>
            </div>

            <!-- Confirm Password -->
            <div>
                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Re-enter your password')"
                    viewable
                    class="w-full"
                />
            </div>

            <!-- Submit Button -->
            <div class="pt-1">
                <flux:button type="submit" variant="primary" class="w-full h-12 text-base font-semibold" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <!-- Sign In Link -->
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-gray-800"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white dark:bg-neutral-950 text-gray-500 dark:text-gray-400">Already have an account?</span>
            </div>
        </div>

        <div class="text-center">
            <flux:link :href="route('login')" wire:navigate class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                {{ __('Sign in instead →') }}
            </flux:link>
        </div>

        <!-- Terms -->
        <p class="text-xs text-center text-gray-500 dark:text-gray-500 pt-2">
            By creating an account, you agree to our <a href="#" class="underline hover:text-gray-700 dark:hover:text-gray-300">Terms</a> and <a href="#" class="underline hover:text-gray-700 dark:hover:text-gray-300">Privacy Policy</a>
        </p>
    </div>
</x-layouts.auth>
