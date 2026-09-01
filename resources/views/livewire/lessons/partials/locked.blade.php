{{-- Locked Lesson View for Students --}}
<div class="flex flex-col items-center justify-center min-h-[70vh] p-6">
    <div class="max-w-lg w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-8 text-center">
        <svg class="w-20 h-20 mx-auto text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>

        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-5 mb-2">Lesson locked</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Your instructor has not opened this lesson yet. Quizzes and assignments stay locked until class is ready.
        </p>

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-6">
            <p class="text-sm text-amber-900 dark:text-amber-100 font-semibold">{{ $lesson->title }}</p>
            @if($lesson->module)
                <p class="text-xs text-amber-800 dark:text-amber-200 mt-1">{{ $lesson->module->title }}</p>
            @endif
        </div>

        @if($course)
            <a href="{{ route('courses.show', $course) }}" wire:navigate
               class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold">
                Back to course
            </a>
        @endif
    </div>
</div>
