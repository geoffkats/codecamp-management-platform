{{-- Welcome Screen: shows real course cards --}}
<div class="min-h-screen bg-gray-50 dark:bg-gray-950 p-8">
    <div class="max-w-4xl mx-auto">

        {{-- Hero --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-orange-100 dark:bg-orange-900/30 mb-4">
                <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Curriculum Builder</h1>
            <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                Pick a course below to start building — add modules, write lessons, and organize your content.
            </p>
        </div>

        @if($courses && $courses->count() > 0)
            {{-- How it works (compact) --}}
            <div class="grid grid-cols-3 gap-3 mb-8 max-w-xl mx-auto">
                <div class="flex flex-col items-center text-center p-3">
                    <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400 font-bold text-sm mb-2">1</div>
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Select a course</p>
                </div>
                <div class="flex flex-col items-center text-center p-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm mb-2">2</div>
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Add modules</p>
                </div>
                <div class="flex flex-col items-center text-center p-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 font-bold text-sm mb-2">3</div>
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Write lessons</p>
                </div>
            </div>

            {{-- Course grid --}}
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">Your courses</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($courses as $courseOption)
                    <a href="{{ route('curriculum.builder', ['course' => $courseOption->id]) }}" wire:navigate
                       class="group flex items-start gap-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:border-orange-300 dark:hover:border-orange-600 hover:shadow-md transition-all duration-200">
                        <div class="w-11 h-11 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-orange-200 dark:group-hover:bg-orange-900/50 transition-colors">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 dark:text-white leading-snug truncate group-hover:text-orange-700 dark:group-hover:text-orange-400 transition-colors">
                                {{ $courseOption->title }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ $courseOption->modules_count }} {{ $courseOption->modules_count === 1 ? 'module' : 'modules' }}
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:text-orange-500 flex-shrink-0 mt-0.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Empty state --}}
            <div class="text-center py-16 max-w-sm mx-auto">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-2">No courses yet</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Create your first course, then come back here to build the curriculum.</p>
                <a href="{{ route('courses.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create a Course
                </a>
            </div>
        @endif

    </div>
</div>
