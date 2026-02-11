<div class="flex flex-col gap-6 p-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Daily Challenges</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Complete daily challenges to earn XP and climb the leaderboard! 🔥
            </p>
        </div>
        @can('manage_badges')
            <flux:button href="{{ route('daily-challenges.create') }}" variant="primary" icon="plus" wire:navigate>
                Create Challenge
            </flux:button>
        @endcan
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg shadow-lg overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <div>
                    <p class="text-sm font-medium text-orange-100">Total Challenges</p>
                    <p class="mt-2 text-3xl font-bold">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-full bg-orange-400/20 p-3">
                    <span class="text-3xl">🎯</span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <div>
                    <p class="text-sm font-medium text-green-100">Completed</p>
                    <p class="mt-2 text-3xl font-bold">{{ $stats['completed'] }}</p>
                </div>
                <div class="rounded-full bg-green-400/20 p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <div>
                    <p class="text-sm font-medium text-blue-100">Active Today</p>
                    <p class="mt-2 text-3xl font-bold">{{ $stats['active'] }}</p>
                </div>
                <div class="rounded-full bg-blue-400/20 p-3">
                    <span class="text-3xl">🔥</span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg shadow-lg overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <div>
                    <p class="text-sm font-medium text-purple-100">Total Points</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($stats['totalPoints']) }}</p>
                </div>
                <div class="rounded-full bg-purple-400/20 p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Challenges -->
    @if($todayChallenges->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">Today's Challenges 🔥</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Complete these to earn bonus XP!</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($todayChallenges as $challenge)
                        @php
                            $attempt = $challenge->attempts->first();
                            $isCompleted = $attempt && $attempt->is_completed;
                        @endphp
                        <div class="relative overflow-hidden rounded-lg border-2 {{ $isCompleted ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-orange-500 bg-white dark:bg-gray-800' }} hover:shadow-lg transition">
                            @if($isCompleted)
                                <div class="absolute top-2 right-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                            @endif
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $challenge->title }}</h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $challenge->description }}</p>
                                        @if($challenge->course)
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Course: {{ $challenge->course->title }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-4">
                                    <div class="flex items-center gap-2">
                                        <flux:badge variant="{{ $challenge->difficulty_level === 'Easy' ? 'success' : ($challenge->difficulty_level === 'Medium' ? 'warning' : 'danger') }}" size="sm">
                                            {{ $challenge->difficulty_level }}
                                        </flux:badge>
                                        <flux:badge variant="primary" size="sm">
                                            {{ $challenge->reward_points ?? 100 }} XP
                                        </flux:badge>
                                    </div>
                                    @if(!$isCompleted)
                                        <flux:button href="{{ route('daily-challenges.show', $challenge) }}" variant="primary" size="sm" wire:navigate>
                                            Start Challenge
                                        </flux:button>
                                    @else
                                        <flux:badge variant="success" size="sm">
                                            Completed
                                        </flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    label="Search Challenges"
                    placeholder="Search by title or description..."
                />

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            wire:click="filterByStatus('active')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filterStatus === 'active' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            Active
                        </button>
                        <button 
                            wire:click="filterByStatus('available')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filterStatus === 'available' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            Upcoming
                        </button>
                        <button 
                            wire:click="filterByStatus('all')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filterStatus === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            All
                        </button>
                    </div>
                </div>

<div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Difficulty</label>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            wire:click="filterByDifficulty('all')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filterDifficulty === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            All
                        </button>
                        <button 
                            wire:click="filterByDifficulty('easy')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filterDifficulty === 'easy' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            Easy
                        </button>
                        <button 
                            wire:click="filterByDifficulty('medium')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filterDifficulty === 'medium' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            Medium
                        </button>
                        <button 
                            wire:click="filterByDifficulty('hard')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filterDifficulty === 'hard' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            Hard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Challenges Grid -->
    @if($challenges->count() > 0)
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($challenges as $challenge)
                @php
                    $attempt = $userAttempts[$challenge->id] ?? null;
                    $isCompleted = $attempt && $attempt->is_completed;
                    $isAvailable = !$challenge->date || $challenge->date <= now()->toDateString();
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 {{ $isCompleted ? 'ring-2 ring-green-500' : '' }}">
                    @if($isCompleted)
                        <div class="absolute top-4 right-4 z-10">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    @endif
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $challenge->title }}</h3>
                                @if($challenge->date)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ \Carbon\Carbon::parse($challenge->date)->format('M d, Y') }}
                                    </p>
                                @endif
                            </div>
                            @can('manage_badges')
                                <flux:button 
                                    href="{{ route('daily-challenges.edit', $challenge) }}" 
                                    variant="ghost" 
                                    size="sm" 
                                    icon="pencil"
                                    wire:navigate
                                >
                                    Edit
                                </flux:button>
                            @endcan
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">{{ $challenge->description }}</p>
                        
                        <div class="flex items-center gap-2 mb-4">
                            <flux:badge variant="{{ $challenge->difficulty_level === 'Easy' ? 'success' : ($challenge->difficulty_level === 'Medium' ? 'warning' : 'danger') }}" size="sm">
                                {{ $challenge->difficulty_level }}
                            </flux:badge>
                            <flux:badge variant="primary" size="sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $challenge->reward_points ?? 100 }} XP
                            </flux:badge>
                            @if($challenge->course)
                                <flux:badge variant="ghost" size="sm">{{ $challenge->course->title }}</flux:badge>
                            @endif
                            @if($challenge->category)
                                <flux:badge variant="ghost" size="sm">{{ $challenge->category }}</flux:badge>
                            @endif
                        </div>

                        @if($attempt && !$attempt->is_completed)
                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <p class="text-xs text-blue-700 dark:text-blue-300">Started {{ $attempt->attempted_at->diffForHumans() }}</p>
                            </div>
                        @endif

                        <div class="flex items-center gap-2">
                            @if($isCompleted)
                                <flux:badge variant="success" size="lg" class="flex-1 justify-center">
                                    Completed ✓
                                </flux:badge>
                            @elseif($attempt)
                                <flux:button href="{{ route('daily-challenges.show', $challenge) }}" variant="primary" class="flex-1" wire:navigate>
                                    Continue Challenge
                                </flux:button>
                            @elseif($isAvailable)
                                <flux:button href="{{ route('daily-challenges.show', $challenge) }}" variant="primary" class="flex-1" wire:navigate>
                                    Start Challenge
                                </flux:button>
                            @else
                                <flux:badge variant="ghost" size="lg" class="flex-1 justify-center">
                                    Coming Soon
                                </flux:badge>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $challenges->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No challenges found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters</p>
            </div>
        </div>
    @endif
</div>
