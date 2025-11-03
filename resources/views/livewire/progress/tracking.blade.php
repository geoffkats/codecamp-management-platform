<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Progress Tracking</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Monitor your learning progress across all courses</p>
        </div>
        <div class="w-64">
            <flux:field>
                <flux:label>Filter by Course</flux:label>
                <flux:select wire:model.live="selectedCourse">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-blue-100 text-sm mb-2">Total Courses</p>
            <p class="text-3xl font-bold">{{ $stats['total_courses'] ?? 0 }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-green-100 text-sm mb-2">Completed</p>
            <p class="text-3xl font-bold">{{ $stats['completed_courses'] ?? 0 }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-purple-100 text-sm mb-2">In Progress</p>
            <p class="text-3xl font-bold">{{ $stats['in_progress_courses'] ?? 0 }}</p>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-orange-100 text-sm mb-2">Avg Progress</p>
            <p class="text-3xl font-bold">{{ $stats['avg_progress'] ?? 0 }}%</p>
        </div>
    </div>

    {{-- Course Progress --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Course Progress</h2>
        <div class="space-y-4">
            @forelse($courseProgress as $progress)
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-gray-900 dark:text-white">
                            {{ $progress['course']->title }}
                        </h3>
                        <span class="text-sm font-semibold {{ $progress['completed'] ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                            {{ number_format($progress['progress'], 1) }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 mb-2">
                        <div class="h-2 rounded-full transition-all duration-300 {{ $progress['completed'] ? 'bg-green-500' : 'bg-blue-500' }}" 
                             style="width: {{ min($progress['progress'], 100) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>{{ $progress['lessons_completed'] }} lessons completed</span>
                        @if($progress['completed'])
                            <flux:badge variant="success" size="sm">Completed</flux:badge>
                        @else
                            <flux:badge variant="primary" size="sm">In Progress</flux:badge>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-600 dark:text-gray-400 text-center py-8">No course progress data available</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Recent Activity</h2>
        <div class="space-y-3">
            @forelse($recentActivity as $activity)
                <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        @if($activity->type === 'lesson_completed')
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ ucfirst(str_replace('_', ' ', $activity->type)) }}
                        </p>
                        @if($activity->course)
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $activity->course->title }}</p>
                        @endif
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $activity->created_at->diffForHumans() }}
                    </div>
                </div>
            @empty
                <p class="text-gray-600 dark:text-gray-400 text-center py-8">No recent activity</p>
            @endforelse
        </div>
    </div>
</div>
