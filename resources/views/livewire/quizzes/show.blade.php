<div class="flex flex-col gap-6 p-6">
    {{-- Quiz Header --}}
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg p-8 text-white">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('quizzes.index') }}" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold">{{ $quiz->title }}</h1>
                </div>
                @if($quiz->description)
                    <p class="text-indigo-100 mb-4">{{ $quiz->description }}</p>
                @endif
                <div class="flex items-center gap-6 text-sm flex-wrap">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>{{ $quiz->lesson->course->title ?? 'N/A' }}</span>
                    </div>
                    @if($quiz->time_limit_minutes)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $quiz->time_limit_minutes }} minutes</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $quiz->questions->count() }} questions</span>
                    </div>
                    @if($quiz->passing_score)
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Passing: {{ $quiz->passing_score }}%</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- User Progress --}}
        @if($hasTaken && $bestAttempt)
            <div class="mt-6 bg-white/20 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-indigo-100">Your Best Score</span>
                    <span class="text-2xl font-bold">{{ number_format($bestScore, 1) }}%</span>
                </div>
                <div class="w-full bg-white/20 rounded-full h-2">
                    <div class="h-2 {{ $bestScore >= $quiz->passing_score ? 'bg-green-300' : 'bg-red-300' }} rounded-full transition-all duration-300" 
                         style="width: {{ min($bestScore, 100) }}%"></div>
                </div>
                @if($attemptsRemaining !== 'unlimited')
                    <p class="text-xs text-indigo-100 mt-2">{{ $attemptsRemaining }} attempt{{ $attemptsRemaining !== 1 ? 's' : '' }} remaining</p>
                @endif
            </div>
        @elseif($hasTaken)
            <div class="mt-6 bg-white/20 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-indigo-100">Status</span>
                    <span class="text-yellow-300 font-semibold">In Progress</span>
                </div>
                @if($attemptsRemaining !== 'unlimited')
                    <p class="text-xs text-indigo-100 mt-2">{{ $attemptsRemaining }} attempt{{ $attemptsRemaining !== 1 ? 's' : '' }} remaining</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Statistics (for instructors/admins) --}}
    @if($stats)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Attempts</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_attempts'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Average Score</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['average_score'] }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Pass Rate</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['pass_rate'] }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['completion_rate'] }}%</p>
            </div>
        </div>
    @endif

    {{-- Quiz Instructions --}}
    @if($quiz->instructions)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Instructions</h2>
            <div class="prose dark:prose-invert max-w-none">
                {!! nl2br(e($quiz->instructions)) !!}
            </div>
        </div>
    @endif

    {{-- Quiz Details --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Quiz Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Questions</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $quiz->questions->count() }}</p>
            </div>
            @if($quiz->time_limit_minutes)
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Time Limit</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $quiz->time_limit_minutes }} minutes</p>
                </div>
            @endif
            @if($quiz->passing_score)
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Passing Score</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $quiz->passing_score }}%</p>
                </div>
            @endif
            @if($quiz->max_attempts)
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Max Attempts</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $quiz->max_attempts }}</p>
                </div>
            @else
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Max Attempts</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">Unlimited</p>
                </div>
            @endif
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Randomized</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $quiz->is_randomized ? 'Yes' : 'No' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Show Answers</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $quiz->show_correct_answers ? 'Yes' : 'No' }}</p>
            </div>
        </div>
    </div>

    {{-- Previous Attempts --}}
    @if($hasTaken)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Your Attempts</h2>
            <div class="space-y-3">
                @foreach($userAttempts as $attempt)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $attempt->is_passed ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                        <div class="flex items-center justify-between">
<div>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    Attempt #{{ $attempt->attempt_number }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    @if($attempt->completed_at)
                                        Completed {{ $attempt->completed_at->diffForHumans() }}
                                    @else
                                        Started {{ $attempt->started_at->diffForHumans() }} (In Progress)
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                @if($attempt->completed_at)
                                    <p class="text-lg font-bold {{ $attempt->is_passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ number_format($attempt->percentage_score, 1) }}%
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ $attempt->is_passed ? 'Passed ✓' : 'Failed' }}
                                    </p>
                                @else
                                    <flux:badge variant="primary">In Progress</flux:badge>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="flex items-center gap-4">
        @if($hasTaken && $bestAttempt && $bestAttempt->is_passed && $bestAttempt->percentage_score >= 100)
            <div class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-4 text-center">
                <p class="font-semibold">Perfect Score! ⭐</p>
                <p class="text-sm text-green-100 mt-1">Congratulations on achieving 100%</p>
            </div>
        @else
            @php
                $canTakeQuiz = true;
                $reason = '';
                
                if ($quiz->max_attempts && $attemptsRemaining !== 'unlimited' && $attemptsRemaining <= 0) {
                    $canTakeQuiz = false;
                    $reason = 'You have reached the maximum number of attempts.';
                }
                
                $incompleteAttempt = $userAttempts->whereNull('completed_at')->first();
            @endphp

            @if($incompleteAttempt)
                <flux:button href="{{ route('quizzes.take', $quiz) }}" variant="primary" class="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600" wire:navigate>
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Continue Quiz
                </flux:button>
            @elseif($canTakeQuiz)
                <flux:button href="{{ route('quizzes.take', $quiz) }}" variant="primary" class="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600" wire:navigate>
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $hasTaken ? 'Retake Quiz' : 'Start Quiz' }}
                </flux:button>
            @else
                <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-4 text-center">
                    <p class="font-semibold text-gray-700 dark:text-gray-300">{{ $reason }}</p>
                </div>
            @endif
        @endif

        @can('update', $quiz)
            <flux:button href="{{ route('quizzes.edit', $quiz) }}" variant="ghost" wire:navigate>
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </flux:button>
        @endcan
    </div>
</div>
