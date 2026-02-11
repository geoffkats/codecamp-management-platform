<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-950 py-8 px-4 sm:px-6 lg:px-8" wire:key="leaderboards-root">
    <div class="max-w-7xl mx-auto space-y-8">
        <!-- Hero Header -->
        <div class="text-center space-y-4">
            <div class="inline-flex items-center justify-center">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 rounded-full blur-xl opacity-50 animate-pulse"></div>
                    <h1 class="relative text-5xl md:text-6xl font-black bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 bg-clip-text text-transparent">
                        🏆 LEADERBOARD 🏆
                    </h1>
                </div>
            </div>
            <p class="text-xl text-gray-600 dark:text-gray-300 font-medium">
                Climb the ranks and dominate the competition!
            </p>
        </div>

        <!-- Current User Rank Highlight Card -->
        @if($currentUserRank)
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-yellow-400 via-orange-500 to-red-500 p-[2px] shadow-2xl transform transition-all hover:scale-[1.02]">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="text-center md:text-left">
                            <p class="text-sm font-bold text-yellow-600 dark:text-yellow-400 uppercase tracking-wider mb-2">Your Current Position</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-6xl font-black bg-gradient-to-r from-yellow-500 to-orange-600 bg-clip-text text-transparent">
                                    #{{ $currentUserRank['rank'] }}
                                </span>
                                <span class="text-2xl font-bold text-gray-700 dark:text-gray-300">
                                    / {{ number_format($totalActiveUsers ?? 0) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-8">
                            <div class="text-center">
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Total Points</p>
                                <p class="text-4xl font-black text-gray-900 dark:text-white">{{ number_format($currentUserRank['points']) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">Level</p>
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg">
                                    <span class="text-2xl font-black text-white">{{ $currentUserRank['level'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Premium Filters -->
        <flux:card class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm border-2 border-gray-200/50 dark:border-zinc-700/50 shadow-xl">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Leaderboard Type -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
                            Ranking Type
                        </label>
                        <div class="flex gap-2">
                            <button 
                                wire:click="filterByType('overall')"
                                class="flex-1 px-4 py-3 rounded-xl font-bold text-sm transition-all duration-300 transform hover:scale-105 {{ $leaderboardType === 'overall' ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg ring-4 ring-blue-200 dark:ring-blue-800' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                                🎯 Overall
                            </button>
                            <button 
                                wire:click="filterByType('level')"
                                class="flex-1 px-4 py-3 rounded-xl font-bold text-sm transition-all duration-300 transform hover:scale-105 {{ $leaderboardType === 'level' ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg ring-4 ring-blue-200 dark:ring-blue-800' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                                ⭐ By Level
                            </button>
                        </div>
                    </div>
                    
                    <!-- Time Period -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
                            Time Period
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <button 
                                wire:click="filterByPeriod('all')"
                                class="px-3 py-2 rounded-lg font-semibold text-xs transition-all duration-300 transform hover:scale-105 {{ $period === 'all' ? 'bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-md' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                                All Time
                            </button>
                            <button 
                                wire:click="filterByPeriod('weekly')"
                                class="px-3 py-2 rounded-lg font-semibold text-xs transition-all duration-300 transform hover:scale-105 {{ $period === 'weekly' ? 'bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-md' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                                This Week
                            </button>
                            <button 
                                wire:click="filterByPeriod('monthly')"
                                class="px-3 py-2 rounded-lg font-semibold text-xs transition-all duration-300 transform hover:scale-105 {{ $period === 'monthly' ? 'bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-md' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                                This Month
                            </button>
                            <button 
                                wire:click="filterByPeriod('yearly')"
                                class="px-3 py-2 rounded-lg font-semibold text-xs transition-all duration-300 transform hover:scale-105 {{ $period === 'yearly' ? 'bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-md' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                                This Year
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </flux:card>

        <!-- Premium Podium for Top 3 -->
        @if($topThree->count() >= 3 && $topThree->first()['points'] > 0)
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-yellow-400/20 via-orange-500/20 to-red-500/20 rounded-3xl blur-2xl"></div>
                <flux:card class="relative bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm border-2 border-yellow-200/50 dark:border-yellow-800/50 shadow-2xl">
                    <div class="p-8">
                        <h2 class="text-3xl font-black text-center mb-8 bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">
                            🥇 CHAMPIONS PODIUM 🥇
                        </h2>

                        <div class="flex items-end justify-center gap-4 md:gap-8">
                            <!-- 2nd Place -->
                            <div class="flex flex-col items-center transform transition-all duration-300 hover:scale-105">
                                <div class="relative mb-4">
                                    <div class="absolute inset-0 bg-gradient-to-br from-gray-300 to-gray-500 rounded-full blur-lg opacity-50"></div>
                                    <div class="relative bg-gradient-to-br from-gray-200 to-gray-400 dark:from-gray-600 dark:to-gray-800 rounded-full w-24 h-24 md:w-28 md:h-28 flex items-center justify-center shadow-xl ring-4 ring-gray-300 dark:ring-gray-700">
                                        <span class="text-4xl md:text-5xl">🥈</span>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 rounded-2xl p-5 w-36 md:w-40 text-center h-36 md:h-40 flex flex-col justify-center shadow-xl border-2 border-gray-300 dark:border-gray-600">
                                    <div class="text-3xl font-black text-gray-900 dark:text-white mb-2">#2</div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1 line-clamp-1">{{ $topThree[1]['user']->name }}</p>
                                    <div class="flex items-center justify-center gap-1 mt-2">
                                        <svg class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941a3.535 3.535 0 01-1.676-.662C6.602 13.234 6 12.459 6 11.5c0-.99.602-1.765 1.324-2.246A4.535 4.535 0 0111 8.092V7.151c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-xs font-black text-gray-700 dark:text-gray-300">{{ number_format($topThree[1]['points']) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 1st Place - Tallest & Most Prominent -->
                            <div class="flex flex-col items-center transform transition-all duration-300 hover:scale-110">
                                <div class="relative mb-4">
                                    <div class="absolute inset-0 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full blur-xl opacity-75 animate-pulse"></div>
                                    <div class="relative bg-gradient-to-br from-yellow-300 via-yellow-400 to-orange-500 rounded-full w-32 h-32 md:w-36 md:h-36 flex items-center justify-center shadow-2xl ring-4 ring-yellow-300 dark:ring-yellow-600 animate-bounce">
                                        <span class="text-5xl md:text-6xl">👑</span>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-yellow-400 via-orange-500 to-red-500 rounded-2xl p-6 w-40 md:w-48 text-center h-48 md:h-56 flex flex-col justify-center shadow-2xl border-4 border-yellow-300 dark:border-yellow-600">
                                    <div class="text-4xl font-black text-white mb-2 drop-shadow-lg">#1</div>
                                    <p class="text-base font-black text-white mb-2 line-clamp-1 drop-shadow-lg">{{ $topThree[0]['user']->name }}</p>
                                    <div class="flex items-center justify-center gap-1 mt-2">
                                        <svg class="h-5 w-5 text-yellow-100" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941a3.535 3.535 0 01-1.676-.662C6.602 13.234 6 12.459 6 11.5c0-.99.602-1.765 1.324-2.246A4.535 4.535 0 0111 8.092V7.151c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-sm font-black text-yellow-100 drop-shadow-lg">{{ number_format($topThree[0]['points']) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 3rd Place -->
                            <div class="flex flex-col items-center transform transition-all duration-300 hover:scale-105">
                                <div class="relative mb-4">
                                    <div class="absolute inset-0 bg-gradient-to-br from-orange-300 to-orange-500 rounded-full blur-lg opacity-50"></div>
                                    <div class="relative bg-gradient-to-br from-orange-200 to-orange-400 dark:from-orange-800 dark:to-orange-600 rounded-full w-24 h-24 md:w-28 md:h-28 flex items-center justify-center shadow-xl ring-4 ring-orange-300 dark:ring-orange-700">
                                        <span class="text-4xl md:text-5xl">🥉</span>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-orange-100 to-orange-200 dark:from-orange-900/30 dark:to-orange-800/30 rounded-2xl p-5 w-36 md:w-40 text-center h-32 md:h-36 flex flex-col justify-center shadow-xl border-2 border-orange-300 dark:border-orange-600">
                                    <div class="text-3xl font-black text-gray-900 dark:text-white mb-2">#3</div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-1 line-clamp-1">{{ $topThree[2]['user']->name }}</p>
                                    <div class="flex items-center justify-center gap-1 mt-2">
                                        <svg class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941a3.535 3.535 0 01-1.676-.662C6.602 13.234 6 12.459 6 11.5c0-.99.602-1.765 1.324-2.246A4.535 4.535 0 0111 8.092V7.151c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-xs font-black text-gray-700 dark:text-gray-300">{{ number_format($topThree[2]['points']) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </flux:card>
            </div>
        @elseif($topThree->count() > 0 && $topThree->first()['points'] == 0)
            <!-- Motivational Message When Everyone Has 0 Points -->
            <flux:card class="relative bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 backdrop-blur-sm border-2 border-blue-200/50 dark:border-blue-800/50 shadow-2xl">
                <div class="p-12 text-center">
                    <div class="mb-6">
                        <span class="text-8xl animate-bounce inline-block">🚀</span>
                    </div>
                    <h2 class="text-4xl font-black bg-gradient-to-r from-blue-500 to-purple-600 bg-clip-text text-transparent mb-4">
                        Be the First to Climb the Ranks!
                    </h2>
                    <p class="text-xl text-gray-700 dark:text-gray-300 font-semibold mb-6">
                        The leaderboard is waiting for its first champions. Start earning XP today!
                    </p>
                    <div class="flex flex-col md:flex-row items-center justify-center gap-6 mt-8">
                        <div class="flex items-center gap-3 bg-white dark:bg-zinc-800 px-6 py-3 rounded-xl shadow-lg">
                            <span class="text-3xl">📚</span>
                            <div class="text-left">
                                <p class="text-sm font-bold text-gray-600 dark:text-gray-400">Complete Lessons</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">Earn 10-50 XP</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-white dark:bg-zinc-800 px-6 py-3 rounded-xl shadow-lg">
                            <span class="text-3xl">✅</span>
                            <div class="text-left">
                                <p class="text-sm font-bold text-gray-600 dark:text-gray-400">Finish Courses</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">Earn 100+ XP</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-white dark:bg-zinc-800 px-6 py-3 rounded-xl shadow-lg">
                            <span class="text-3xl">🎯</span>
                            <div class="text-left">
                                <p class="text-sm font-bold text-gray-600 dark:text-gray-400">Daily Challenges</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">Earn bonus XP</p>
                            </div>
                        </div>
                    </div>
                </div>
            </flux:card>
        @endif

        <!-- Full Leaderboard Table -->
        <flux:card class="bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm border-2 border-gray-200/50 dark:border-zinc-700/50 shadow-2xl overflow-hidden">
            <div class="p-6 border-b-2 border-gray-200 dark:border-zinc-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-zinc-800 dark:to-zinc-900">
                <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="text-3xl">📊</span>
                    Full Rankings
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-zinc-800 dark:to-zinc-900">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Learner</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Level</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Points</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        @foreach($leaderboard as $index => $userPoint)
                            @php
                                $rank = $leaderboard->firstItem() + $index;
                                $isCurrentUser = Auth::id() === $userPoint->user_id;
                            @endphp
                            <tr class="transition-all duration-200 {{ $isCurrentUser ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/30 dark:to-purple-900/30 border-l-4 border-blue-500' : 'hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if($rank <= 3)
                                            <span class="text-3xl animate-bounce">
                                                @if($rank == 1) 🥇
                                                @elseif($rank == 2) 🥈
                                                @elseif($rank == 3) 🥉
                                                @endif
                                            </span>
                                        @endif
                                        <div class="flex flex-col">
                                            <span class="text-lg font-black {{ $isCurrentUser ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">
                                                #{{ $rank }}
                                            </span>
                                            @if($rank <= 10)
                                                <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Top 10</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        @if($userPoint->user->profile_image)
                                            <img src="{{ asset('storage/' . $userPoint->user->profile_image) }}" 
                                                 alt="{{ $userPoint->user->name }}" 
                                                 class="h-12 w-12 rounded-full object-cover ring-2 ring-gray-300 dark:ring-zinc-600 shadow-lg">
                                        @else
                                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center ring-2 ring-gray-300 dark:ring-zinc-600 shadow-lg">
                                                <span class="text-white font-black text-lg">{{ $userPoint->user->initials() }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $userPoint->user->name }}</span>
                                                @if($isCurrentUser)
                                                    <flux:badge size="sm" variant="primary" class="animate-pulse">You</flux:badge>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $userPoint->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 shadow-lg">
                                        <span class="text-xs font-black text-white uppercase">Lv</span>
                                        <span class="text-lg font-black text-white">{{ $userPoint->level ?? 1 }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-6 w-6 text-yellow-500 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941a3.535 3.535 0 01-1.676-.662C6.602 13.234 6 12.459 6 11.5c0-.99.602-1.765 1.324-2.246A4.535 4.535 0 0111 8.092V7.151c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-base font-black text-gray-900 dark:text-white">{{ number_format($userPoint->total_points ?? 0) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    @if($userPoint->points_to_next_level)
                                        <div class="w-40">
                                            <div class="flex items-center justify-between text-xs font-bold text-gray-600 dark:text-gray-400 mb-2">
                                                <span>To Level {{ ($userPoint->level ?? 1) + 1 }}</span>
                                                <span class="text-yellow-600 dark:text-yellow-400">{{ number_format($currentLevelPoints = ($userPoint->total_points ?? 0) % 1000) }} / {{ number_format($userPoint->points_to_next_level ?? 1000) }}</span>
                                            </div>
                                            <div class="h-3 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner">
                                                @php
                                                    $progress = min(($currentLevelPoints / ($userPoint->points_to_next_level ?? 1000)) * 100, 100);
                                                @endphp
                                                <div class="h-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-full transition-all duration-500 shadow-lg" style="width: {{ $progress }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-6 border-t-2 border-gray-200 dark:border-zinc-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-zinc-800 dark:to-zinc-900">
                {{ $leaderboard->links() }}
            </div>
        </flux:card>
    </div>
</div>
