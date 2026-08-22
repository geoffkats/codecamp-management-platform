<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Points & Rewards</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track your achievements and level progression</p>
        </div>
    </div>

    {{-- Level and Points Overview --}}
    <div class="bg-gradient-to-r from-yellow-500 via-orange-500 to-red-500 rounded-xl shadow-lg p-8 text-white">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-yellow-100 text-sm mb-2">Current Level</p>
                <p class="text-5xl font-bold">{{ $currentLevel }}</p>
                <p class="text-yellow-100 text-sm mt-2 font-semibold">{{ $rankName }}</p>
            </div>
            <div class="text-right">
                <p class="text-yellow-100 text-sm mb-2">Total Points</p>
                <p class="text-5xl font-bold">{{ number_format($totalPoints) }}</p>
            </div>
        </div>

        {{-- Level Progress --}}
        <div class="bg-white/20 rounded-lg p-4">
            <div class="flex items-center justify-between text-sm mb-2">
                <span>Progress to Level {{ $currentLevel + 1 }}</span>
                <span>{{ $pointsInCurrentLevel }} / 100 XP</span>
            </div>
            <div class="w-full bg-white/20 rounded-full h-3">
                <div class="h-3 bg-white rounded-full transition-all duration-300" 
                     style="width: {{ ($pointsInCurrentLevel / 100) * 100 }}%"></div>
            </div>
            <p class="text-xs text-yellow-100 mt-2">
                {{ $pointsNeededForNextLevel }} XP needed for next level
            </p>
        </div>
    </div>

    {{-- Points Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Lessons Completed</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($breakdown['lesson_completed']) }} XP</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Quizzes Completed</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($breakdown['quiz_completed']) }} XP</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Course Enrollments</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($breakdown['course_enrolled']) }} XP</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Points History --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Points History</h2>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($pointsHistory as $activity)
                <div class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ ucfirst(str_replace('_', ' ', $activity->type)) }}
                            </p>
                            @if($activity->course)
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $activity->course->title }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">
                            +{{ $activity->points_earned }} XP
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $activity->created_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <p class="text-gray-600 dark:text-gray-400">No points history available</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($pointsHistory->hasPages())
            <div class="mt-4">
                {{ $pointsHistory->links() }}
            </div>
        @endif
    </div>
</div>
