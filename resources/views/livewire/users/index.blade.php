<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 p-6 space-y-8">
    {{-- Hero Header Section --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8 text-white">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold">User Management</h1>
                </div>
                <p class="text-blue-100 text-lg">Manage all platform users, roles, and permissions</p>
                <div class="flex items-center gap-4 mt-4">
                    <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </div>
            <flux:button href="{{ route('admin.users.create') }}" icon="plus" variant="ghost" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white border-white/30" wire:navigate>
                Add User
            </flux:button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Total Users Card --}}
        <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['total']) }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span>All platform users</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Users Card --}}
        <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-green-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-2 py-1 rounded-full">{{ number_format(($stats['active'] / max($stats['total'], 1)) * 100, 1) }}%</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Active Users</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['active']) }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span>Currently active</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Inactive Users Card --}}
        <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 to-red-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded-full">{{ number_format(($stats['inactive'] / max($stats['total'], 1)) * 100, 1) }}%</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Inactive Users</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['inactive']) }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span>Deactivated accounts</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Role Distribution Preview --}}
        <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-purple-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Roles</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $roles->count() }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        <span>{{ $roles->pluck('name')->join(', ') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filter & Search</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by name or email..." class="md:col-span-2" />
            <flux:select wire:model.live="filterRole" label="Role">
                <option value="all">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="filterStatus" label="Status">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </flux:select>
        </div>
    </div>

    {{-- Users Table --}}
    @if($users->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Users List</h2>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $users->total() }} total</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Roles</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Last Login</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach($users as $user)
                            <tr class="hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-purple-50/50 dark:hover:from-gray-800 dark:hover:to-gray-800 transition-all duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('admin.users.show', $user) }}" class="flex items-center gap-3 group cursor-pointer">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg group-hover:scale-110 transition-transform">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $user->name }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="ml-2 text-xs text-blue-600 dark:text-blue-400 font-medium">(You)</span>
                                            @endif
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</div>
                                    @if($user->email_verified_at)
                                        <div class="flex items-center gap-1 mt-1">
                                            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-xs text-green-600 dark:text-green-400">Verified</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($user->roles as $role)
                                            @php
                                                $roleColors = [
                                                    'admin' => 'from-red-500 to-red-600',
                                                    'teacher' => 'from-blue-500 to-blue-600',
                                                    'student' => 'from-green-500 to-green-600',
                                                    'supervisor' => 'from-purple-500 to-purple-600',
                                                ];
                                                $color = $roleColors[strtolower($role->name)] ?? 'from-gray-500 to-gray-600';
                                            @endphp
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold text-white bg-gradient-to-r {{ $color }} shadow-sm">
                                                {{ ucfirst($role->name) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <flux:badge size="sm" variant="{{ $user->is_active ? 'success' : 'danger' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </flux:badge>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                                    </div>
                                    @if($user->last_login_at)
                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">
                                            {{ $user->last_login_at->format('M d, Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button href="{{ route('admin.users.show', $user) }}" variant="ghost" size="sm" wire:navigate title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </flux:button>
                                        <flux:button href="{{ route('admin.users.edit', $user) }}" variant="ghost" size="sm" wire:navigate title="Edit User">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </flux:button>
                                        <flux:button wire:click="openResetModal({{ $user->id }})" variant="ghost" size="sm" title="Reset Password">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                            </svg>
                                        </flux:button>
                                        <flux:button wire:click="toggleStatus({{ $user->id }})" variant="ghost" size="sm" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($user->is_active)
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                @endif
                                            </svg>
                                        </flux:button>
                                        @if($user->id !== auth()->id())
                                            <flux:button wire:click="deleteUser({{ $user->id }})" variant="danger" size="sm" wire:confirm="Are you sure you want to delete this user? This action cannot be undone." title="Delete User">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No users found</h3>
            <p class="text-gray-600 dark:text-gray-400">Try adjusting your search or filters</p>
        </div>
    @endif

    {{-- Password Reset Modal --}}
    @if($showResetModal)
        <flux:modal name="reset-password" :show="$showResetModal" wire:model="showResetModal">
            <form wire:submit.prevent="confirmResetPassword">
                <div class="p-6">
                    @if(!$newPassword)
                        {{-- Confirmation Step --}}
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Reset Password</h2>
                            <p class="text-gray-600 dark:text-gray-400">
                                Are you sure you want to reset the password for <strong>{{ $selectedUser->name ?? '' }}</strong> ({{ $selectedUser->email ?? '' }})?
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                                A new secure password will be generated and displayed for you to share with the user.
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-6">
                            <flux:button type="button" wire:click="closeResetModal" variant="ghost">Cancel</flux:button>
                            <flux:button type="submit" variant="primary">Reset Password</flux:button>
                        </div>
                    @else
                        {{-- Success Step with Password Display --}}
                        <div class="mb-6">
                            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2 text-center">Password Reset Successful!</h2>
                            <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                                Password has been reset for <strong>{{ $selectedUser->name ?? '' }}</strong>
                            </p>

                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    New Password
                                </label>
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="text" 
                                        value="{{ $newPassword }}" 
                                        id="new-password-field"
                                        readonly
                                        class="flex-1 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg font-mono text-lg font-bold text-gray-900 dark:text-white"
                                    >
                                    <button 
                                        type="button"
                                        id="copy-btn-index"
                                        onclick="copyPasswordIndex(this)"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                                    >
                                        Copy
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    ⚠️ Please copy this password now. It will not be shown again.
                                </p>
                            </div>

                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                    <strong>Important:</strong> Share this password securely with the user. They should change it after their first login.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-center mt-6">
                            <flux:button type="button" wire:click="closeResetModal" variant="primary">Done</flux:button>
                        </div>

                        <script>
                            function copyPasswordIndex(btn) {
                                const field = document.getElementById('new-password-field');
                                if (!field) return;
                                
                                // Select the text
                                field.select();
                                field.setSelectionRange(0, 99999); // For mobile devices
                                
                                // Copy to clipboard
                                try {
                                    navigator.clipboard.writeText(field.value).then(function() {
                                        // Show feedback
                                        const originalText = btn.textContent;
                                        btn.textContent = 'Copied!';
                                        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                                        btn.classList.add('bg-green-600', 'hover:bg-green-700');
                                        setTimeout(() => {
                                            btn.textContent = originalText;
                                            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                                            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                                        }, 2000);
                                    });
                                } catch (err) {
                                    // Fallback for older browsers
                                    document.execCommand('copy');
                                    const originalText = btn.textContent;
                                    btn.textContent = 'Copied!';
                                    btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                                    btn.classList.add('bg-green-600', 'hover:bg-green-700');
                                    setTimeout(() => {
                                        btn.textContent = originalText;
                                        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                                        btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                                    }, 2000);
                                }
                            }
                        </script>
                    @endif
                </div>
            </form>
        </flux:modal>
    @endif
</div>
