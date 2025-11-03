<div class="flex flex-col gap-6 p-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Quizzes</h1>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Test your knowledge with interactive quizzes 📝</p>
                </div>
            </div>
        </div>
        @can('create', \App\Models\Quiz::class)
            <flux:button href="{{ route('quizzes.create') }}" icon="plus" variant="primary" wire:navigate>
                Create Quiz
            </flux:button>
        @endcan
    </div>

    {{-- Stats Cards --}}
    @auth
        @if(Auth::user()->hasRole('student') && $stats['total'] > 0)
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between p-6">
                        <div>
                            <p class="text-sm font-medium text-blue-100">Total Quizzes</p>
                            <p class="mt-2 text-3xl font-bold">{{ $stats['total'] }}</p>
                        </div>
                        <div class="rounded-full bg-blue-400/20 p-3">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg overflow-hidden">
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

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between p-6">
                        <div>
                            <p class="text-sm font-medium text-purple-100">Average Score</p>
                            <p class="mt-2 text-3xl font-bold">{{ $stats['average_score'] }}%</p>
                        </div>
                        <div class="rounded-full bg-purple-400/20 p-3">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg overflow-hidden">
                    <div class="flex items-center justify-between p-6">
                        <div>
                            <p class="text-sm font-medium text-orange-100">Perfect Scores</p>
                            <p class="mt-2 text-3xl font-bold">{{ $stats['perfect_scores'] }}</p>
                        </div>
                        <div class="rounded-full bg-orange-400/20 p-3">
                            <span class="text-3xl">⭐</span>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(Auth::user()->hasRole('teacher'))
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-indigo-100 text-sm font-medium">Your Quizzes</p>
                        <p class="text-3xl font-bold mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="rounded-full bg-white/20 p-4">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search quizzes..." />
            <flux:select wire:model.live="filter" label="Filter">
                <option value="all">All Quizzes</option>
                <option value="available">Available</option>
                <option value="completed">Completed</option>
            </flux:select>
        </div>
    </div>

    {{-- Quizzes Grid --}}
    @if($quizzes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($quizzes as $quiz)
                @php
                    $isPerfect = $quiz->best_attempt && $quiz->best_attempt->percentage_score >= 100;
                    $isCompleted = $quiz->best_attempt && $quiz->best_attempt->percentage_score < 100;
                    $questionCount = $quiz->questions()->count();
                @endphp
                <a href="{{ route('quizzes.show', $quiz) }}" class="block group">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-2 {{ $isPerfect ? 'border-green-500 ring-2 ring-green-500/20' : 'border-gray-200 dark:border-gray-700' }} hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 overflow-hidden cursor-pointer relative">
                        {{-- Completion Badge --}}
                        @if($isPerfect)
                            <div class="absolute top-4 right-4 z-10">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 shadow-lg">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        @endif

                        {{-- Gradient Top Bar --}}
                        <div class="h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>

                        <div class="p-6">
                            {{-- Quiz Header --}}
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $quiz->title }}</h3>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 ml-10">{{ $quiz->description ?: 'Test your knowledge' }}</p>
                                </div>
                            </div>

                            {{-- Quiz Info with Icons --}}
                            <div class="space-y-3 mb-5 ml-10">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <span>Course</span>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ Str::limit($quiz->lesson->course->title ?? 'N/A', 25) }}</span>
                                </div>
                                @if($quiz->time_limit_minutes)
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Time Limit</span>
                                        </div>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $quiz->time_limit_minutes }} min</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Questions</span>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $questionCount }} {{ Str::plural('question', $questionCount) }}</span>
                                </div>
                                @if($quiz->best_attempt)
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            <span>Best Score</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-lg {{ $quiz->best_attempt->percentage_score >= 70 ? 'text-green-600 dark:text-green-400' : ($quiz->best_attempt->percentage_score >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                            {{ $quiz->best_attempt->percentage_score }}%
                                        </span>
                                            @if($quiz->best_attempt->percentage_score >= 70)
                                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Action Button --}}
                            <div class="mt-4">
                                @if($isPerfect)
                                    <div class="w-full py-3 px-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg font-semibold text-center shadow-md flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Perfect Score! ⭐</span>
                                    </div>
                                @elseif($isCompleted)
                                    <flux:button variant="primary" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Retake Quiz
                                    </flux:button>
                            @else
                                    <flux:button variant="primary" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Start Quiz
                                    </flux:button>
                            @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $quizzes->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-2 border-dashed border-gray-300 dark:border-gray-700 p-12 text-center">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No quizzes found</h3>
            <p class="text-gray-600 dark:text-gray-400">Try adjusting your search or filters</p>
        </div>
    @endif
</div>
