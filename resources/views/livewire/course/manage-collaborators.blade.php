<div class="space-y-4">
    {{-- Success/Error Messages --}}
    @if (session()->has('message'))
        <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-green-800 dark:text-green-200">{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Course Collaborators</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Manage who can access and edit this course</p>
        </div>
        <button wire:click="openAddModal" 
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
            + Add Collaborator
        </button>
    </div>

    {{-- Collaborators List --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        @if($collaborators->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($collaborators as $collaborator)
                    <div class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                {{ substr($collaborator->user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $collaborator->user->name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $collaborator->user->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Role Selector --}}
                            <select wire:change="updateRole({{ $collaborator->id }}, $event.target.value)"
                                    class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="editor" {{ $collaborator->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                <option value="viewer" {{ $collaborator->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                            </select>

                            {{-- Remove Button --}}
                            <button wire:click="removeCollaborator({{ $collaborator->id }})"
                                    wire:confirm="Are you sure you want to remove this collaborator?"
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-gray-600 dark:text-gray-400">No collaborators yet</p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Add teachers to help manage this course</p>
            </div>
        @endif
    </div>

    {{-- Add Collaborator Modal --}}
    @if($showAddModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeAddModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Collaborator</h3>
                        <button wire:click="closeAddModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        {{-- Search Users --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Search Teacher or Trainer
                            </label>
                            <input type="text" 
                                   wire:model.live.debounce.300ms="searchTerm"
                                   placeholder="Search by name, email, or role..."
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Type part of a name, email address, or role like trainer.</p>
                        </div>

                        {{-- Smart User Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select Teacher or Trainer <span class="text-red-500">*</span>
                            </label>
                            @if($selectedUser)
                                <div class="mb-3 flex items-start justify-between gap-3 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 px-4 py-3">
                                    <div>
                                        <p class="font-medium text-blue-900 dark:text-blue-100">{{ $selectedUser->name }}</p>
                                        <p class="text-sm text-blue-700 dark:text-blue-300">{{ $selectedUser->email }}</p>
                                    </div>
                                    <button type="button" wire:click="clearSelectedUser"
                                            class="text-sm font-medium text-blue-700 hover:text-blue-900 dark:text-blue-300 dark:hover:text-blue-100">
                                        Clear
                                    </button>
                                </div>
                            @endif

                            <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                @forelse($availableUsers as $user)
                                    @php
                                        $primaryRole = $user->roles->pluck('display_name')->filter()->first()
                                            ?? $user->roles->pluck('name')->map(fn ($role) => str_replace('_', ' ', $role))->first()
                                            ?? 'User';
                                    @endphp
                                    <button type="button"
                                            wire:click="selectUser({{ $user->id }})"
                                            class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-600/50 transition-colors {{ (int) $selectedUserId === $user->id ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="font-medium text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-300 truncate">{{ $user->email }}</p>
                                            </div>
                                            <span class="shrink-0 rounded-full bg-gray-100 dark:bg-gray-600 px-2.5 py-1 text-xs text-gray-700 dark:text-gray-200 capitalize">
                                                {{ $primaryRole }}
                                            </span>
                                        </div>
                                    </button>
                                @empty
                                    <div class="px-4 py-6 text-sm text-center text-gray-500 dark:text-gray-400">
                                        No teachers or trainers matched your search.
                                    </div>
                                @endforelse
                            </div>
                            @error('selectedUserId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Role Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Role <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="selectedRole"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="editor">Editor - Can edit curriculum content and review notes</option>
                                <option value="viewer">Viewer - Can open the builder and read curriculum notes</option>
                            </select>
                            @error('selectedRole') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button wire:click="addCollaborator"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                            Add Collaborator
                        </button>
                        <button wire:click="closeAddModal"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
