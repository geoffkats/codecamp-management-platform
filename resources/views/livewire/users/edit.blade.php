<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Success Message --}}
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-green-800 dark:text-green-200 font-medium">{{ session('message') }}</p>
            </div>
        @endif

        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('admin.users.index') }}" wire:navigate class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit User</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Update user information and roles</p>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="update" class="space-y-6">
            {{-- Basic Information --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Basic Information
                    </h2>
                </div>
                
                <div class="p-6 space-y-5">
                    {{-- Current Profile Image --}}
                    @if($currentUser->profile_image)
                    <div class="flex items-center gap-4">
                        <img src="{{ Storage::url($currentUser->profile_image) }}" alt="Profile" class="w-20 h-20 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Current Profile Image</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Upload a new image to replace</p>
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="user.name" 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors">
                            @error('user.name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" wire:model="user.email" 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors">
                            @error('user.email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bio</label>
                        <textarea wire:model="user.bio" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Update Profile Image</label>
                        <input type="file" wire:model="profile_image" accept="image/*"
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('profile_image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Change Password (Optional) --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-teal-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Change Password (Optional)
                    </h2>
                </div>
                
                <div class="p-6 space-y-5">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Leave blank to keep current password</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                            <input type="password" wire:model="password" 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
                                   placeholder="••••••••">
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                            <input type="password" wire:model="password_confirmation" 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-colors"
                                   placeholder="••••••••">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Roles & Permissions --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Roles & Permissions
                    </h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($roles as $role)
                        <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all
                                      {{ in_array($role->id, $selectedRoles) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                            <input type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}" class="mt-1 h-4 w-4 text-blue-600 rounded">
                            <div class="ml-3 flex-1">
                                <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $role->display_name }}</span>
                                <span class="block text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $role->description }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('selectedRoles') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Status --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <label class="flex items-center justify-between cursor-pointer group">
                    <div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Active Status</span>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Enable or disable user account</p>
                    </div>
                    <div class="relative">
                        <input type="checkbox" wire:model="user.is_active" class="sr-only peer">
                        <div class="w-14 h-7 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-blue-600 transition-colors"></div>
                        <div class="absolute left-1 top-1 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-7"></div>
                    </div>
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-sm">
                    Update User
                </button>
                <a href="{{ route('admin.users.index') }}" wire:navigate class="px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
