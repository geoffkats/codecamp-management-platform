<div class="flex flex-col gap-6 p-6">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('daily-challenges.index') }}" 
           class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Challenges</span>
        </a>
    </div>

    @if(session()->has('success'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-400 text-green-700 dark:text-green-300 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="bg-red-100 dark:bg-red-900/20 border border-red-400 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if(session()->has('info'))
        <div class="bg-blue-100 dark:bg-blue-900/20 border border-blue-400 text-blue-700 dark:text-blue-300 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif

    <!-- Challenge Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        @if($attempt && $attempt->is_completed)
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Challenge Completed!</h3>
                        <p class="text-sm text-green-100">You earned {{ $dailyChallenge->reward_points ?? 100 }} points!</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold">{{ $dailyChallenge->title }}</h2>
                        @if($dailyChallenge->date)
                            <p class="text-sm text-blue-100 mt-1">
                                {{ \Carbon\Carbon::parse($dailyChallenge->date)->format('F d, Y') }}
                            </p>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold">{{ $dailyChallenge->reward_points ?? 100 }}</div>
                        <div class="text-sm text-blue-100">Points</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="p-6">
            <!-- Description -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Description</h3>
                <p class="text-gray-700 dark:text-gray-300">{{ $dailyChallenge->description }}</p>
            </div>

            <!-- Challenge Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Difficulty</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span class="px-3 py-1 rounded-full text-sm {{ $dailyChallenge->difficulty_level === 'easy' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' : ($dailyChallenge->difficulty_level === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300') }}">
                            {{ ucfirst($dailyChallenge->difficulty_level) }}
                        </span>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Type</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ ucfirst(str_replace('_', ' ', $dailyChallenge->type)) }}
                    </div>
                </div>

                @if($dailyChallenge->category)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Category</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ ucfirst($dailyChallenge->category) }}
                    </div>
                </div>
                @endif
            </div>

            @if($dailyChallenge->type === 'forum_participation')
            <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/30">
                <p class="text-sm text-blue-900 dark:text-blue-200">
                    <strong>How to complete:</strong> Post helpful, on-topic discussions or replies in your course forum.
                    Each item must be at least {{ config('daily_challenges.forum.min_reply_characters') }} characters.
                    Only one reply per thread counts. Spam and one-word posts are ignored.
                </p>
                <a href="{{ route('discussions.index') }}" wire:navigate class="mt-2 inline-block text-sm font-semibold text-[#1a3a8f] hover:underline dark:text-blue-300">
                    Go to discussions →
                </a>
            </div>
            @endif

            <!-- Requirements -->
            @if($dailyChallenge->requirements && count($dailyChallenge->requirements) > 0)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Requirements</h3>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <ul class="space-y-2 mb-4">
                        @foreach($dailyChallenge->requirements as $key => $value)
                            <li class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}</span>
                            </li>
                        @endforeach
                    </ul>
                    
                    <!-- Progress Status -->
                    @if(isset($requirementStatus) && is_array($requirementStatus))
                    <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress:</span>
                            <span class="text-sm font-bold {{ $requirementStatus['met'] ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                                {{ $requirementStatus['current'] ?? 0 }} / {{ $requirementStatus['required'] ?? 0 }}
                            </span>
                        </div>
                        @if($requirementStatus['required'] > 0)
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2">
                            <div class="h-3 rounded-full {{ $requirementStatus['met'] ? 'bg-green-500' : 'bg-blue-500' }}" 
                                 style="width: {{ min(100, ($requirementStatus['current'] / $requirementStatus['required']) * 100) }}%">
                            </div>
                        </div>
                        @endif
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $requirementStatus['message'] ?? 'Complete the requirements to finish this challenge.' }}
                        </p>
                        @if($requirementStatus['met'])
                        <div class="mt-3 flex items-center gap-2 text-green-600 dark:text-green-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">Requirements met! You can complete this challenge.</span>
                        </div>
                        @else
                        <div class="mt-3 flex items-center gap-2 text-orange-600 dark:text-orange-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="font-medium">Keep working! Requirements not yet met.</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Progress/Attempt Info -->
            @if($attempt)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Your Progress</h3>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Started:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $attempt->attempted_at->format('M d, Y g:i A') }}
                        </span>
                    </div>
                    @if($attempt->is_completed)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Completed:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $attempt->completed_at->format('M d, Y g:i A') }}
                            </span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Points Earned:</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                    +{{ $attempt->points_earned }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Action Button -->
            @if(!$attempt || !$attempt->is_completed)
            <div class="flex gap-4">
                @if(isset($requirementStatus) && ($requirementStatus['met'] ?? false))
                    <button 
                        wire:click="completeChallenge" 
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition-colors flex items-center justify-center gap-2"
                        wire:loading.attr="disabled"
                        wire:target="completeChallenge"
                    >
                        <svg wire:loading.remove wire:target="completeChallenge" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span wire:loading.remove wire:target="completeChallenge">Complete Challenge & Claim Points</span>
                        <span wire:loading wire:target="completeChallenge">Processing...</span>
                    </button>
                @else
                    <button 
                        disabled
                        class="flex-1 px-6 py-3 bg-gray-400 dark:bg-gray-600 text-white rounded-lg font-semibold cursor-not-allowed flex items-center justify-center gap-2 opacity-75"
                        title="Complete the requirements first"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Requirements Not Met</span>
                    </button>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
