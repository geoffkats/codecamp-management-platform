<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">

    {{-- Header --}}
    <div class="border-b-4 border-blue-600 bg-orange-600">
        <div class="mx-auto max-w-3xl px-4 py-5 sm:px-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}" wire:navigate
                   class="flex h-8 w-8 items-center justify-center rounded-lg border border-white/25 text-white hover:bg-white/10 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-white">Create User</h1>
                    <p class="text-sm text-orange-100">Add a new account to the system</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-3xl px-4 py-5 sm:px-6">
        <form wire:submit.prevent="store" class="space-y-4">

            {{-- Basic info --}}
            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Basic information</h2>
                </div>
                <div class="space-y-4 p-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:input wire:model="user.name" label="Full name" placeholder="John Doe" required />
                        <flux:input wire:model="user.email" type="email" label="Email" placeholder="john@example.com" required />
                    </div>
                    <flux:textarea wire:model="user.bio" label="Bio" rows="2" placeholder="Optional short description" />
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Profile image</label>
                        <input type="file" wire:model="profile_image" accept="image/*"
                            class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-md file:border-0 file:bg-orange-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-orange-700 hover:file:bg-orange-100">
                        @error('profile_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Security --}}
            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Security</h2>
                </div>
                <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
                    <flux:input wire:model="password" type="password" label="Password" required />
                    <flux:input wire:model="password_confirmation" type="password" label="Confirm password" required />
                </div>
            </div>

            {{-- Roles --}}
            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                    <h2 class="text-xs font-bold uppercase tracking-wide text-slate-500">Roles</h2>
                    <p class="mt-0.5 text-xs text-slate-400">Select at least one role</p>
                </div>
                <div class="grid grid-cols-1 gap-2 p-4 sm:grid-cols-2">
                    @foreach($roles as $role)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition
                            {{ in_array($role->id, $selectedRoles)
                                ? 'border-orange-600 bg-orange-50 dark:border-orange-600 dark:bg-orange-950/20'
                                : 'border-slate-200 hover:border-slate-300 dark:border-zinc-700 dark:hover:border-zinc-600' }}">
                            <input type="checkbox" wire:model.live="selectedRoles" value="{{ $role->id }}"
                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                            <div class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $role->display_name }}</span>
                                @if($role->description)
                                    <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $role->description }}</span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('selectedRoles') <p class="px-4 pb-4 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @include('livewire.users.partials.course-access-picker')

            {{-- Status --}}
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:checkbox wire:model="user.is_active" label="Account is active" description="Inactive users cannot sign in" />
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 border-t border-slate-200 pt-4 dark:border-zinc-800">
                <flux:button type="submit" variant="primary" class="!bg-orange-600 hover:!bg-orange-700">
                    Create user
                </flux:button>
                <flux:button href="{{ route('admin.users.index') }}" variant="ghost" class="!text-blue-600 hover:!bg-blue-50" wire:navigate>
                    Cancel
                </flux:button>
            </div>
        </form>
    </div>
</div>
