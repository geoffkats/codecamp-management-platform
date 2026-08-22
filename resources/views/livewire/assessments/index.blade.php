<div class="p-6 max-w-6xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Assessments</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Complete assessments to finish your lessons and earn points</p>
    </div>

    <!-- Ready to Take Section -->
    @if(count($assessmentsByStatus['ready']) > 0)
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-6 bg-green-500 rounded"></div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Ready to Take
                    <span class="ml-2 inline-block bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 px-3 py-1 rounded-full text-lg">
                        {{ count($assessmentsByStatus['ready']) }}
                    </span>
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($assessmentsByStatus['ready'] as $item)
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-green-200 dark:border-green-900/30 overflow-hidden hover:shadow-lg transition">
                        <div class="bg-green-50 dark:bg-green-900/10 p-4 border-b border-green-200 dark:border-green-900/30">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-semibold text-green-700 dark:text-green-300">Lesson Complete</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ $item['assessment']->title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                <span class="inline-block text-xs">📚 {{ $item['lesson']->title }}</span>
                            </p>
                            <div class="flex gap-2">
                                <a href="{{ route('assessments.take', $item['assessment']) }}" wire:navigate class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition text-center">
                                    Start Assessment
                                </a>
                                <a href="{{ route('assessments.show', $item['assessment']) }}" wire:navigate class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white px-4 py-2 rounded-lg font-semibold transition text-center">
                                    Preview
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Completed Section -->
    @if(count($assessmentsByStatus['completed']) > 0)
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-6 bg-blue-500 rounded"></div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Completed
                    <span class="ml-2 inline-block bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 px-3 py-1 rounded-full text-lg">
                        {{ count($assessmentsByStatus['completed']) }}
                    </span>
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($assessmentsByStatus['completed'] as $item)
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-blue-200 dark:border-blue-900/30 overflow-hidden hover:shadow-lg transition">
                        <div class="bg-blue-50 dark:bg-blue-900/10 p-4 border-b border-blue-200 dark:border-blue-900/30">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">Completed</span>
                                </div>
                                @if($item['passed'])
                                    <span class="inline-block bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 px-2 py-1 rounded text-xs font-semibold">
                                        Passed
                                    </span>
                                @else
                                    <span class="inline-block bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 px-2 py-1 rounded text-xs font-semibold">
                                        Failed
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ $item['assessment']->title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                <span class="inline-block text-xs">📚 {{ $item['lesson']->title }}</span>
                            </p>
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Score</span>
                                    <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $item['score'] ?? 0 }}%</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('assessments.results', [$item['assessment'], $item['attempt']]) }}" wire:navigate class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition text-center text-sm">
                                    View Results
                                </a>
                                <a href="{{ route('assessments.show', $item['assessment']) }}" wire:navigate class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white px-4 py-2 rounded-lg font-semibold transition text-center text-sm">
                                    Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Locked Section -->
    @if(count($assessmentsByStatus['locked']) > 0)
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-6 bg-yellow-500 rounded"></div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Locked
                    <span class="ml-2 inline-block bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 px-3 py-1 rounded-full text-lg">
                        {{ count($assessmentsByStatus['locked']) }}
                    </span>
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($assessmentsByStatus['locked'] as $item)
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-yellow-200 dark:border-yellow-900/30 overflow-hidden opacity-75">
                        <div class="bg-yellow-50 dark:bg-yellow-900/10 p-4 border-b border-yellow-200 dark:border-yellow-900/30">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-semibold text-yellow-700 dark:text-yellow-300">Locked</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ $item['assessment']->title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                <span class="inline-block text-xs">📚 {{ $item['lesson']->title }}</span>
                            </p>
                            <div class="space-y-2 mb-4">
                                <p class="text-xs font-semibold text-gray-900 dark:text-white">To unlock, complete:</p>
                                @foreach($item['missing'] as $requirement)
                                    <div class="flex items-start gap-2 text-xs text-gray-600 dark:text-gray-400">
                                        <span class="mt-1">•</span>
                                        <span>
                                            @if($requirement['type'] === 'video')
                                                Watch video ({{ $requirement['progress'] ?? 0 }}% complete)
                                            @elseif($requirement['type'] === 'assessment')
                                                {{ $requirement['type_label'] }}: {{ $requirement['title'] }}
                                            @elseif($requirement['type'] === 'time')
                                                {{ $requirement['message'] ?? 'Complete lesson content first' }}
                                            @elseif($requirement['type'] === 'locked')
                                                {{ $requirement['message'] ?? 'This assessment is locked' }}
                                            @elseif($requirement['type'] === 'enrollment')
                                                {{ $requirement['message'] ?? 'Enroll in the course to access this assessment' }}
                                            @else
                                                {{ $requirement['type_label'] ?? ucfirst($requirement['type']) }}: {{ $requirement['title'] ?? '' }}
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <button disabled class="w-full bg-gray-400 dark:bg-gray-600 text-gray-600 dark:text-gray-400 cursor-not-allowed px-4 py-2 rounded-lg font-semibold">
                                🔒 Locked
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- No Assessments -->
    @if(count($assessmentsByStatus['ready']) === 0 && count($assessmentsByStatus['locked']) === 0 && count($assessmentsByStatus['completed']) === 0)
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Assessments Available</h3>
            <p class="text-gray-600 dark:text-gray-400">There are no assessments available for you at this time. Complete your courses to unlock assessments.</p>
        </div>
    @endif
</div>
