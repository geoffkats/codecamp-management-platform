<div class="p-6">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">System Settings</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Manage your application branding and contact information</p>
        </div>

        {{-- Success Message --}}
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-green-800 dark:text-green-200">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        <form wire:submit="save">
            {{-- Branding Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Branding</h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- App Name --}}
                    <div>
                        <flux:input 
                            wire:model="settings.app_name" 
                            label="Application Name" 
                            placeholder="Code Academy Uganda"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This will appear in the browser title and throughout the application</p>
                    </div>

                    {{-- Short Name --}}
                    <div>
                        <flux:input 
                            wire:model="settings.app_short_name" 
                            label="Short Name / Abbreviation" 
                            placeholder="CAU"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used in compact spaces</p>
                    </div>

                    {{-- Tagline --}}
                    <div>
                        <flux:input 
                            wire:model="settings.app_tagline" 
                            label="Tagline" 
                            placeholder="E-Learning Platform"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Subtitle or description</p>
                    </div>

                    {{-- Favicon --}}
                    <div>
                        <flux:label>Favicon</flux:label>
                        <div class="mt-2 flex items-center gap-4">
                            @if($currentFavicon)
                                <img src="{{ asset('storage/' . $currentFavicon) }}" alt="Current Favicon" class="w-8 h-8 rounded">
                            @endif
                            <flux:input type="file" wire:model="favicon" accept="image/*" />
                        </div>
                        @if($favicon)
                            <p class="mt-1 text-xs text-green-600 dark:text-green-400">New favicon selected</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended: 32x32px or 64x64px ICO, PNG, or SVG</p>
                    </div>

                    {{-- Logo --}}
                    <div>
                        <flux:label>Logo (Light Mode)</flux:label>
                        <div class="mt-2 flex items-center gap-4">
                            @if($currentLogo)
                                <img src="{{ asset('storage/' . $currentLogo) }}" alt="Current Logo" class="h-12 rounded">
                            @endif
                            <flux:input type="file" wire:model="logo" accept="image/*" />
                        </div>
                        @if($logo)
                            <p class="mt-1 text-xs text-green-600 dark:text-green-400">New logo selected</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended: PNG or SVG with transparent background</p>
                    </div>

                    {{-- Logo Dark --}}
                    <div>
                        <flux:label>Logo (Dark Mode)</flux:label>
                        <div class="mt-2 flex items-center gap-4">
                            @if($currentLogoDark)
                                <div class="bg-gray-900 p-2 rounded">
                                    <img src="{{ asset('storage/' . $currentLogoDark) }}" alt="Current Dark Logo" class="h-12">
                                </div>
                            @endif
                            <flux:input type="file" wire:model="logo_dark" accept="image/*" />
                        </div>
                        @if($logo_dark)
                            <p class="mt-1 text-xs text-green-600 dark:text-green-400">New dark mode logo selected</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional: Logo optimized for dark backgrounds</p>
                    </div>
                </div>
            </div>

            {{-- Contact Information Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Contact Information</h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Email --}}
                    <div>
                        <flux:input 
                            wire:model="settings.contact_email" 
                            label="Contact Email" 
                            type="email"
                            placeholder="info@codeacademy.ug"
                        />
                    </div>

                    {{-- Phone --}}
                    <div>
                        <flux:input 
                            wire:model="settings.contact_phone" 
                            label="Contact Phone" 
                            placeholder="+256 784 781926"
                        />
                    </div>

                    {{-- Address --}}
                    <div>
                        <flux:textarea 
                            wire:model="settings.contact_address" 
                            label="Physical Address" 
                            placeholder="Mpererwe, Mugalu Zone, Kampala"
                            rows="3"
                        />
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" class="px-8">
                    Save Settings
                </flux:button>
            </div>
        </form>
    </div>
</div>
