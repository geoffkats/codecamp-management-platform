<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-950 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <!-- Hero Header -->
        <div class="text-center space-y-4">
            <div class="inline-flex items-center justify-center">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 rounded-full blur-xl opacity-50 animate-pulse"></div>
                    <h1 class="relative text-5xl md:text-6xl font-black bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 bg-clip-text text-transparent">
                        🏆 ACHIEVEMENTS 🏆
                    </h1>
                </div>
            </div>
            <p class="text-xl text-gray-600 dark:text-gray-300 font-medium">
                Unlock badges by completing milestones and reaching new heights!
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-yellow-400 to-yellow-600 text-white shadow-2xl transform transition-all hover:scale-105">
                <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
                <div class="relative flex items-center justify-between p-6">
                    <div>
                        <p class="text-sm font-bold text-yellow-100 uppercase tracking-wider mb-2">Total Badges</p>
                        <p class="text-4xl font-black">{{ $stats['total'] }}</p>
                    </div>
                    <div class="rounded-full bg-white/20 p-4 ring-4 ring-yellow-300/50">
                        <span class="text-4xl">🏆</span>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-500 to-green-600 text-white shadow-2xl transform transition-all hover:scale-105">
                <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
                <div class="relative flex items-center justify-between p-6">
                    <div>
                        <p class="text-sm font-bold text-green-100 uppercase tracking-wider mb-2">Earned</p>
                        <p class="text-4xl font-black">{{ $stats['earned'] }}</p>
                    </div>
                    <div class="rounded-full bg-white/20 p-4 ring-4 ring-green-300/50">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-2xl transform transition-all hover:scale-105">
                <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
                <div class="relative flex items-center justify-between p-6">
                    <div>
                        <p class="text-sm font-bold text-blue-100 uppercase tracking-wider mb-2">Available</p>
                        <p class="text-4xl font-black">{{ $stats['available'] }}</p>
                    </div>
                    <div class="rounded-full bg-white/20 p-4 ring-4 ring-blue-300/50">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-2xl transform transition-all hover:scale-105">
                <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
                <div class="relative flex items-center justify-between p-6">
                    <div>
                        <p class="text-sm font-bold text-purple-100 uppercase tracking-wider mb-2">Collection</p>
                        <p class="text-4xl font-black">{{ $stats['completion'] }}%</p>
                    </div>
                    <div class="rounded-full bg-white/20 p-4 ring-4 ring-purple-300/50">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection Progress -->
        <flux:card class="bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm border-2 border-gray-200/50 dark:border-zinc-700/50 shadow-2xl">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white">Collection Progress</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 font-medium">
                            {{ $stats['earned'] }} of {{ $stats['total'] }} badges unlocked
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-4xl font-black bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">
                            {{ $stats['completion'] }}%
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wider font-bold">Complete</p>
                    </div>
                </div>
                <div class="h-6 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 rounded-full transition-all duration-1000 shadow-lg relative overflow-hidden" 
                         style="width: {{ $stats['completion'] }}%">
                        <div class="absolute inset-0 bg-white/30 animate-pulse"></div>
                    </div>
                </div>
            </div>
        </flux:card>

        <!-- Recent Badges -->
        @if($recentBadges->count() > 0)
            <flux:card class="bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm border-2 border-yellow-200/50 dark:border-yellow-800/50 shadow-2xl">
                <div class="p-8">
                    <h2 class="text-2xl font-black mb-6 bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">
                        ⭐ Recently Earned
                    </h2>
                    <div class="grid grid-cols-2 gap-6 md:grid-cols-5">
                        @foreach($recentBadges as $badge)
                            <div class="text-center transform transition-all duration-300 hover:scale-110">
                                <div class="relative mx-auto mb-4">
                                    <div class="absolute inset-0 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full blur-lg opacity-50 animate-pulse"></div>
                                    <div class="relative flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 shadow-2xl ring-4 ring-yellow-300 dark:ring-yellow-600 animate-bounce">
                                        <div class="text-white filter drop-shadow-2xl">
                                            <x-badge-icon :icon="$badge->icon ?? 'trophy'" class="w-16 h-16" />
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2">{{ $badge->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">
                                    @if($badge->pivot->earned_at)
                                        {{ is_string($badge->pivot->earned_at) ? \Carbon\Carbon::parse($badge->pivot->earned_at)->diffForHumans() : $badge->pivot->earned_at->diffForHumans() }}
                                    @else
                                        Recently
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </flux:card>
        @endif

        <!-- Filters -->
        <flux:card class="bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm border-2 border-gray-200/50 dark:border-zinc-700/50 shadow-xl">
            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        label="Search Badges"
                        placeholder="Search by name or description..."
                        icon="magnifying-glass"
                    />

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
                            Filter
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                wire:click="filterByStatus('all')"
                                class="px-4 py-3 rounded-xl font-bold text-sm transition-all duration-300 transform hover:scale-105 {{ $filterStatus === 'all' ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg ring-4 ring-blue-200 dark:ring-blue-800' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                                All
                            </button>
                            <button 
                                wire:click="filterByStatus('earned')"
                                class="px-4 py-3 rounded-xl font-bold text-sm transition-all duration-300 transform hover:scale-105 {{ $filterStatus === 'earned' ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg ring-4 ring-blue-200 dark:ring-blue-800' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                                Earned ({{ $stats['earned'] }})
                            </button>
                            <button 
                                wire:click="filterByStatus('available')"
                                class="px-4 py-3 rounded-xl font-bold text-sm transition-all duration-300 transform hover:scale-105 {{ $filterStatus === 'available' ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg ring-4 ring-blue-200 dark:ring-blue-800' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                                Available ({{ $stats['available'] }})
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </flux:card>

        <!-- Badges Grid -->
        @if($badges->count() > 0)
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($badges as $badge)
                    @php
                        $isEarned = in_array($badge->id, $earnedBadgeIds);
                        $userBadge = $userBadges[$badge->id] ?? null;
                    @endphp
                    <div class="group relative transform transition-all duration-300 hover:scale-105">
                        @if($isEarned)
                            <!-- Earned Badge - Full Color -->
                            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-yellow-400 via-orange-500 to-red-500 p-[3px] shadow-2xl ring-4 ring-yellow-300 dark:ring-yellow-600 animate-pulse">
                                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 text-center h-full">
                                    <!-- Badge Icon -->
                                    <div class="relative mx-auto mb-4">
                                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full blur-xl opacity-50"></div>
                                        <div class="relative flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 shadow-2xl ring-4 ring-yellow-300 dark:ring-yellow-600 mx-auto transform group-hover:rotate-12 transition-transform duration-300">
                                            <div class="text-white filter drop-shadow-2xl">
                                                <x-badge-icon :icon="$badge->icon ?? 'trophy'" class="w-20 h-20" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Badge Info -->
                                    <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">
                                        {{ $badge->name }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-4 min-h-[40px]">
                                        {{ $badge->description }}
                                    </p>

                                    <!-- Badge Details -->
                                    @if($badge->points_reward)
                                        <div class="mb-4">
                                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941a3.535 3.535 0 01-1.676-.662C6.602 13.234 6 12.459 6 11.5c0-.99.602-1.765 1.324-2.246A4.535 4.535 0 0111 8.092V7.151c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="font-black">{{ $badge->points_reward }} XP</span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Earned Status -->
                                    @if($userBadge)
                                        <div class="mt-4 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border-2 border-green-300 dark:border-green-700">
                                            <p class="text-xs font-bold text-green-700 dark:text-green-300 uppercase tracking-wide flex items-center justify-center gap-2">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Earned @if($userBadge->pivot->earned_at)
                                                    {{ is_string($userBadge->pivot->earned_at) ? \Carbon\Carbon::parse($userBadge->pivot->earned_at)->diffForHumans() : $userBadge->pivot->earned_at->diffForHumans() }}
                                                @else
                                                    recently
                                                @endif
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Unearned Badge - Grayed Out -->
                            <div class="relative overflow-hidden rounded-2xl bg-gray-100 dark:bg-zinc-800 border-2 border-gray-300 dark:border-zinc-700 shadow-lg opacity-75">
                                <div class="p-6 text-center h-full">
                                    <!-- Badge Icon -->
                                    <div class="mx-auto mb-4 flex h-28 w-28 items-center justify-center rounded-full bg-gray-200 dark:bg-zinc-700 border-4 border-gray-300 dark:border-zinc-600">
                                        <div class="text-gray-400 dark:text-gray-500 grayscale filter opacity-50">
                                            <x-badge-icon :icon="$badge->icon ?? 'trophy'" class="w-20 h-20" />
                                        </div>
                                    </div>

                                    <!-- Badge Info -->
                                    <h3 class="text-lg font-black text-gray-500 dark:text-gray-400 mb-2">
                                        {{ $badge->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-500 line-clamp-2 mb-4 min-h-[40px]">
                                        {{ $badge->description }}
                                    </p>

                                    <!-- Locked Status -->
                                    <div class="mt-4 p-3 bg-gray-200 dark:bg-zinc-700 rounded-xl">
                                        <p class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide flex items-center justify-center gap-2">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                            Locked
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $badges->links() }}
            </div>
        @else
            <flux:card class="bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    <h3 class="mt-4 text-xl font-black text-gray-900 dark:text-white">No badges found</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters</p>
                </div>
            </flux:card>
        @endif
    </div>
</div>
