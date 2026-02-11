<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Daily Challenges</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Complete challenges to earn bonus points!
            </p>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-400 text-green-700 dark:text-green-300 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <!-- Challenges Grid -->
    @if($challenges->count() > 0)
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($challenges as $challenge)
                @php
                    $attempt = $userAttempts[$challenge->id] ?? null;
                    $isCompleted = $attempt && $attempt->is_completed;
                    $isAvailable = !$challenge->date || $challenge->date <= now()->toDateString();
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 {{ $isCompleted ? 'ring-2 ring-green-500' : '' }}">
                    @if($isCompleted)
                        <div class="relative">
                            <div class="absolute top-4 right-4 z-10">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $challenge->title }}</h3>
                                @if($challenge->date)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ \Carbon\Carbon::parse($challenge->date)->format('M d, Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $challenge->description }}</p>
                        @if($challenge->course)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Course: {{ $challenge->course->title }}</p>
                        @endif
                        
                        <div class="flex items-center gap-2 mb-4 flex-wrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $challenge->difficulty_level === 'easy' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' : ($challenge->difficulty_level === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300') }}">
                                {{ ucfirst($challenge->difficulty_level) }}
                            </span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300 flex items-center gap-1">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $challenge->reward_points ?? 100 }} XP
                            </span>
                            @if($challenge->category)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $challenge->category }}
                                </span>
                            @endif
                        </div>

                        @if($attempt && !$attempt->is_completed)
                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <p class="text-xs text-blue-700 dark:text-blue-300">Started {{ $attempt->attempted_at->diffForHumans() }}</p>
                            </div>
                        @endif

                        <div class="flex items-center gap-2">
                            @if($isCompleted)
                                <button disabled class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg font-medium text-sm opacity-75 cursor-not-allowed">
                                    Completed ✓
                                </button>
                            @elseif($isAvailable)
                                <button 
                                    wire:click="completeChallenge({{ $challenge->id }})" 
                                    class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm transition-colors"
                                    @if($attempt && !$attempt->is_completed)
                                        wire:loading.attr="disabled"
                                    @endif
                                >
                                    @if($attempt && !$attempt->is_completed)
                                        Complete Challenge
                                    @else
                                        Start Challenge
                                    @endif
                                </button>
                            @else
                                <button disabled class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg font-medium text-sm cursor-not-allowed">
                                    Coming Soon
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No challenges available</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Check back later for new challenges!</p>
            </div>
        </div>
    @endif
</div>

