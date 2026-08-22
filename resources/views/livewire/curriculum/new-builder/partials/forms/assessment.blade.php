{{-- Assessment Placeholder --}}
<div class="flex items-center justify-center h-full p-8">
    <div class="max-w-md w-full">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>

                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Assessment Builder</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Create and manage quizzes, tests, and assignments for your students.
                </p>

                <div class="space-y-3">
                    @if($selectedId)
                        @if($selectedAssessment)
                            <button wire:click="toggleAssessmentLock({{ $selectedId }})"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    type="button"
                                    class="w-full flex items-center justify-center gap-2 px-5 py-2.5 font-semibold rounded-xl transition-colors shadow-sm text-white
                                        {{ $selectedAssessment->is_locked ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                                @if($selectedAssessment->is_locked)
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span wire:loading.remove wire:target="toggleAssessmentLock">Locked — Click to Unlock</span>
                                @else
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                    </svg>
                                    <span wire:loading.remove wire:target="toggleAssessmentLock">Unlocked — Click to Lock</span>
                                @endif
                                <span wire:loading wire:target="toggleAssessmentLock">Processing...</span>
                            </button>

                            <p class="text-xs text-center py-1.5 px-3 rounded-lg {{ $selectedAssessment->is_locked ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' : 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300' }}">
                                {{ $selectedAssessment->is_locked ? 'Students cannot access this assessment' : 'Students can access this assessment' }}
                            </p>
                        @endif

                        <a href="{{ route('assessments.edit', $selectedId) }}"
                           class="w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-xl transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Assessment
                        </a>
                    @else
                        <a href="{{ route('assessments.create', ['course_id' => $course->id, 'lesson_id' => $lessonId ?? null]) }}"
                           class="w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-xl transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create New Assessment
                        </a>
                    @endif

                    <button wire:click="closeForm"
                            type="button"
                            class="w-full px-5 py-2.5 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 font-medium rounded-xl transition-colors">
                        Back
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
