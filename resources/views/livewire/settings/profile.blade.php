<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $profile_image;

    public function mount(): void
    {
        $this->name = Auth::user()->name ?? '';
        $this->email = Auth::user()->email ?? '';
    }

    public function updatedProfileImage(): void
    {
        $this->uploadProfileImage();
    }

    public function uploadProfileImage(): void
    {
        $this->validate([
            'profile_image' => ['required', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->profile_image = $this->profile_image->store('profiles', 'public');
        $user->save();

        $this->reset('profile_image');
        $this->dispatch('profile-updated', name: $user->name);
        session()->flash('photo-status', 'Profile photo updated.');
    }

    public function removeProfileImage(): void
    {
        $user = Auth::user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
            $user->profile_image = null;
            $user->save();
        }

        $this->reset('profile_image');
        session()->flash('photo-status', 'Profile photo removed.');
    }

    private function emailIsOptional(): bool
    {
        return Auth::user()->isCodeClubStudent();
    }

    /**
     * @return list<string|\Illuminate\Validation\Rules\Unique>
     */
    private function emailValidationRules(User $user): array
    {
        $rules = ['string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)];

        array_unshift($rules, $this->emailIsOptional() ? 'nullable' : 'required');

        return $rules;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => $this->emailValidationRules($user),
        ]);

        if ($this->emailIsOptional() && blank($validated['email'] ?? null)) {
            $validated['email'] = null;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your photo, name and email')">

        {{-- Profile photo --}}
        <div class="mb-8 pb-8 border-b border-gray-200 dark:border-gray-700">
            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Profile photo</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Shown on the leaderboard and your profile. JPG or PNG, max 2 MB.</p>

            <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                <div class="flex-shrink-0">
                    @if($profile_image)
                        <img src="{{ $profile_image->temporaryUrl() }}" alt="Preview"
                             class="w-24 h-24 rounded-xl object-cover ring-2 ring-orange-600">
                    @else
                        <x-user-avatar :user="auth()->user()" size="xl" class="ring-2 ring-gray-200 dark:ring-gray-700" />
                    @endif
                </div>

                <div class="flex flex-col gap-3">
                    <label class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold cursor-pointer transition w-fit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Upload photo
                        <input type="file" wire:model="profile_image" accept="image/jpeg,image/png,image/webp" class="sr-only">
                    </label>

                    <div wire:loading wire:target="profile_image" class="text-xs text-gray-500">Uploading…</div>

                    @error('profile_image')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    @if(auth()->user()->profile_image)
                    <button type="button" wire:click="removeProfileImage" wire:confirm="Remove your profile photo?"
                            class="text-xs text-red-600 hover:text-red-700 font-medium text-left w-fit">
                        Remove photo
                    </button>
                    @endif

                    @if(session('photo-status'))
                    <p class="text-xs font-medium text-green-600 dark:text-green-400">{{ session('photo-status') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <form wire:submit="updateProfileInformation" class="w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                @php($emailOptional = auth()->user()->isCodeClubStudent())

                <flux:input wire:model="email" :label="__('Email')" type="email" :required="! $emailOptional" autocomplete="email" />

                @if ($emailOptional)
                    <flux:text class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Optional. Code Club students sign in with their student ID.') }}
                    </flux:text>
                @endif

                @if (auth()->user()->email && auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
