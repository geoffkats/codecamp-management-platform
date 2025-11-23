<div class="p-6">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Assignment</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $assignment->title }}</p>
            </div>
            <a href="{{ route('courses.show', $assignment->course) }}" 
               class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Back to Course
            </a>
        </div>

        {{-- Lock/Unlock Control --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Student Access Control</h2>
            
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-700 dark:text-gray-300 font-medium mb-1">
                        Current Status: 
                        @if($assignment->is_locked)
                            <span class="text-red-600 dark:text-red-400">🔒 Locked</span>
                        @else
                            <span class="text-green-600 dark:text-green-400">🔓 Unlocked</span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @if($assignment->is_locked)
                            Students cannot access this assignment. Click unlock to allow access.
                        @else
                            Students can access and submit this assignment. Click lock to restrict access.
                        @endif
                    </p>
                </div>
                
                <button wire:click="toggleLock" 
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-6 py-3 {{ $assignment->is_locked ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} text-white font-semibold rounded-lg transition-colors shadow-sm">
                    <div class="flex items-center gap-2">
                        @if($assignment->is_locked)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                            </svg>
                            <span wire:loading.remove wire:target="toggleLock">Unlock Assignment</span>
                        @else
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span wire:loading.remove wire:target="toggleLock">Lock Assignment</span>
                        @endif
                        <span wire:loading wire:target="toggleLock">Processing...</span>
                    </div>
                </button>
            </div>
        </div>

        {{-- Assignment Details --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Assignment Details</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                    <p class="text-gray-900 dark:text-white">{{ $assignment->title }}</p>
                </div>
                
                @if($assignment->description)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <div class="text-gray-900 dark:text-white prose dark:prose-invert max-w-none">
                        {!! $assignment->description !!}
                    </div>
                </div>
                @endif
                
                @if($assignment->due_date)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                    <p class="text-gray-900 dark:text-white">{{ $assignment->due_date->format('F j, Y g:i A') }}</p>
                </div>
                @endif
                
                @if($assignment->max_points)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maximum Points</label>
                    <p class="text-gray-900 dark:text-white">{{ $assignment->max_points }}</p>
                </div>
                @endif
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    To edit assignment content, questions, or settings, please use the full assignment editor.
                </p>
            </div>
        </div>
    </div>
</div>
