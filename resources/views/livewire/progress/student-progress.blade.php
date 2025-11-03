<div class="flex flex-col gap-6 p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Progress</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track your learning journey and achievements</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm font-medium text-blue-100 mb-1">Total Courses</p>
            <p class="text-3xl font-bold">{{ $stats['totalCourses'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm font-medium text-green-100 mb-1">Completed</p>
            <p class="text-3xl font-bold">{{ $stats['completedCourses'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm font-medium text-purple-100 mb-1">In Progress</p>
            <p class="text-3xl font-bold">{{ $stats['inProgressCourses'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm font-medium text-orange-100 mb-1">Lessons</p>
            <p class="text-3xl font-bold">{{ $stats['totalLessons'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm font-medium text-pink-100 mb-1">Avg. Progress</p>
            <p class="text-3xl font-bold">{{ number_format($stats['averageProgress'], 1) }}%</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm font-medium text-yellow-100 mb-1">Avg. Score</p>
            <p class="text-3xl font-bold">{{ number_format($stats['averageScore'], 1) }}%</p>
        </div>
    </div>

    {{-- Learning Streak --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Learning Streak</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Keep your momentum going!</p>
            </div>
            <div class="text-center">
                <div class="text-5xl font-bold text-orange-500">{{ $learningStreak['current'] }}</div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Day streak 🔥</p>
            </div>
        </div>
    </div>

    {{-- Courses Progress --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Course Progress</h2>
        </div>
        <div class="p-6">
            @if($enrollments->count() > 0)
                <div class="space-y-4">
                    @foreach($enrollments as $enrollment)
                        <a href="{{ route('courses.show', $enrollment->course) }}" class="block">
                            <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->course->title }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $enrollment->course->instructor->name }}</p>
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-gray-600 dark:text-gray-400">Progress</span>
                                            <span class="font-semibold">{{ number_format($enrollment->progress_percentage ?? 0, 1) }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="h-full bg-gradient-to-r from-blue-500 to-purple-600 rounded-full" 
                                                 style="width: {{ $enrollment->progress_percentage ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                @if($enrollment->completed_at)
                                    <flux:badge variant="success">Completed</flux:badge>
                                @else
                                    <flux:button variant="ghost" size="sm">Continue</flux:button>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <p>No progress yet. Start learning!</p>
                </div>
            @endif
        </div>
    </div>
</div>
