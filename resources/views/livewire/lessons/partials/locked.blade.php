{{-- Locked Lesson View for Students --}}
<div class="flex flex-col items-center justify-center min-h-screen p-6">
    <div class="max-w-2xl w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-8 text-center">
        <div class="mb-6">
            <svg class="w-24 h-24 mx-auto text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Lesson Locked</h2>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
            This lesson is currently locked. Please wait for your instructor to unlock it.
        </p>
        
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
            <p class="text-sm text-blue-800 dark:text-blue-200">
                <strong>{{ $lesson->title }}</strong><br>
                Module {{ $lesson->module->order_index }}: {{ $lesson->module->title }}
            </p>
        </div>
        
        {{-- Show available quizzes and assignments --}}
        @if($lesson->assessments->count() > 0 || $lesson->assignments->count() > 0)
            <div class="text-left mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Available Activities</h3>
                
                @if($lesson->assessments->count() > 0)
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quizzes & Assessments</h4>
                        <div class="space-y-2">
                            @foreach($lesson->assessments as $assessment)
                                @if(!$assessment->is_locked)
                                    {{-- Unlocked Assessment --}}
                                    <a href="{{ route('assessments.take', $assessment) }}" 
                                       class="block p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                </svg>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $assessment->title }}</span>
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                        <p class="text-xs text-green-700 dark:text-green-300 mt-1 ml-7">✓ Available - Click to start</p>
                                    </a>
                                @else
                                    {{-- Locked Assessment --}}
                                    <div class="block p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg opacity-75">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $assessment->title }}</span>
                                            </div>
                                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs text-red-700 dark:text-red-300 mt-1 ml-7">🔒 Locked - Wait for instructor to unlock</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @if($lesson->assignments->count() > 0)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assignments</h4>
                        <div class="space-y-2">
                            @foreach($lesson->assignments as $assignment)
                                @if(!$assignment->is_locked)
                                    {{-- Unlocked Assignment --}}
                                    <a href="{{ route('assignments.show', $assignment) }}" 
                                       class="block p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                </svg>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $assignment->title }}</span>
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                        <p class="text-xs text-green-700 dark:text-green-300 mt-1 ml-7">✓ Available - Click to view</p>
                                    </a>
                                @else
                                    {{-- Locked Assignment --}}
                                    <div class="block p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg opacity-75">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $assignment->title }}</span>
                                            </div>
                                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs text-red-700 dark:text-red-300 mt-1 ml-7">🔒 Locked - Wait for instructor to unlock</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
        
        <a href="{{ route('courses.learn', $course) }}" 
           class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Course
        </a>
    </div>
</div>
