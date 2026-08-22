<div class="space-y-6">

    {{-- ── Assessments ──────────────────────────────────────────────────── --}}
    @if($lesson->assessments && $lesson->assessments->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-orange-200 dark:border-orange-800/60 shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-200 dark:border-orange-800/60 flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="text-sm font-bold text-orange-900 dark:text-orange-100">
                    Assessments
                    <span class="ml-1 text-xs font-normal text-orange-600 dark:text-orange-400">({{ $lesson->assessments->count() }})</span>
                </h3>
            </div>
            <div class="p-4 space-y-3">
                @foreach($lesson->assessments as $assessment)
                    <x-lesson.assessment-card :assessment="$assessment" />
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Quizzes ───────────────────────────────────────────────────────── --}}
    @if($lesson->quizzes && $lesson->quizzes->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-blue-200 dark:border-blue-800/60 shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800/60 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-sm font-bold text-blue-900 dark:text-blue-100">
                    Quizzes
                    <span class="ml-1 text-xs font-normal text-blue-600 dark:text-blue-400">({{ $lesson->quizzes->count() }})</span>
                </h3>
            </div>
            <div class="p-4 space-y-2">
                @foreach($lesson->quizzes as $quiz)
                    <a href="{{ route('quizzes.take', $quiz) }}" wire:navigate
                       class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200 dark:hover:border-blue-700 border border-gray-200 dark:border-gray-600 transition-colors group">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300 truncate">{{ $quiz->title }}</p>
                            @if($quiz->time_limit)
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $quiz->time_limit }} min</p>
                            @endif
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Assignments ───────────────────────────────────────────────────── --}}
    @if($lesson->assignments && $lesson->assignments->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-purple-200 dark:border-purple-800/60 shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-purple-50 dark:bg-purple-900/20 border-b border-purple-200 dark:border-purple-800/60 flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-sm font-bold text-purple-900 dark:text-purple-100">
                    Assignments
                    <span class="ml-1 text-xs font-normal text-purple-600 dark:text-purple-400">({{ $lesson->assignments->count() }})</span>
                </h3>
            </div>
            <div class="p-4 space-y-3">
                @foreach($lesson->assignments as $assignment)
                    <x-lesson.assignment-card :assignment="$assignment" />
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Navigation ───────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Lesson Navigation</h3>
        </div>
        <div class="p-4 space-y-2">
            @if($previousLesson)
                <a href="{{ route('lessons.view', $previousLesson) }}" wire:navigate
                   class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 transition-colors group">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-400 dark:text-gray-500">Previous</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $previousLesson->title }}</p>
                    </div>
                </a>
            @endif
            @if($nextLesson)
                <a href="{{ route('lessons.view', $nextLesson) }}" wire:navigate
                   class="flex items-center gap-3 p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 border border-orange-200 dark:border-orange-700 transition-colors group">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-orange-500 dark:text-orange-400">Next up</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $nextLesson->title }}</p>
                    </div>
                    <svg class="w-4 h-4 text-orange-400 group-hover:text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <div class="flex items-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <svg class="w-4 h-4 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-semibold text-green-800 dark:text-green-200">Module complete!</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Lesson Details ────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Details</h3>
        </div>
        <div class="p-4 space-y-2">
            @if($lesson->duration_minutes)
                <div class="flex items-center justify-between py-1">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Duration</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $lesson->duration_minutes }} min</span>
                </div>
            @endif
            @if($lesson->points_reward)
                <div class="flex items-center justify-between py-1">
                    <span class="text-sm text-gray-500 dark:text-gray-400">XP Reward</span>
                    <span class="text-sm font-bold text-yellow-600 dark:text-yellow-400">+{{ $lesson->points_reward }} XP</span>
                </div>
            @endif
            <div class="flex items-center justify-between py-1">
                <span class="text-sm text-gray-500 dark:text-gray-400">Type</span>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ ucfirst($lesson->content_type ?? $lesson->lesson_type ?? 'text') }}</span>
            </div>
            @if($lesson->difficulty_level)
                <div class="flex items-center justify-between py-1">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Level</span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ ucfirst($lesson->difficulty_level) }}</span>
                </div>
            @endif
        </div>
    </div>

</div>
