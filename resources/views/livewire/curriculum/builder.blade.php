<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-8 p-8">
        {{-- Header Section --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent dark:from-blue-400 dark:to-purple-400">
                    Curriculum Builder
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg">Visual pipeline builder for your course structure</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if($courses->count() > 0)
                    <div class="min-w-[250px] flex-1">
                        <flux:select wire:model.live="courseId" wire:change="loadCourse" class="w-full">
                            <option value="">Select Course</option>
                            @foreach($courses as $courseOption)
                                <option value="{{ $courseOption->id }}">{{ $courseOption->title }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif
                @if($courseId)
                    <flux:button href="{{ route('courses.edit', $courseId) }}" variant="outline" wire:navigate class="whitespace-nowrap">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Course
                    </flux:button>
                @endif
            </div>
        </div>

        @if($courseId && $course)
            {{-- Stats Bar --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Modules</p>
                            <p class="text-3xl font-bold mt-2">{{ count($modules) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Total Lessons</p>
                            <p class="text-3xl font-bold mt-2">{{ collect($modules)->sum(fn($m) => count($m['lessons'] ?? [])) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Total Assessments</p>
                            <p class="text-3xl font-bold mt-2">{{ collect($modules)->flatMap(fn($m) => collect($m['lessons'] ?? [])->flatMap(fn($l) => $l['assessments'] ?? []))->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Course Status</p>
                            <p class="text-lg font-bold mt-2 capitalize">{{ $course->approval_status }}</p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Course Header --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-white">{{ $course->title }}</h2>
                            <p class="text-white/90 mt-2 flex items-center gap-4">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    {{ $course->category }}
                                </span>
                                <span>•</span>
                                <span>{{ count($modules) }} Modules</span>
                                <span>•</span>
                                <span>{{ collect($modules)->sum(fn($m) => count($m['lessons'] ?? [])) }} Lessons</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kanban Board --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 overflow-x-auto pb-4">
                    {{-- Course Info Column --}}
                    <div class="min-w-[300px]">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-4 mb-4 shadow-md">
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Course Overview
                            </h2>
                        </div>
                        <div class="border-2 border-dashed border-blue-300 dark:border-blue-700 rounded-xl p-6 bg-blue-50/50 dark:bg-blue-900/10">
                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-white">{{ substr($course->title, 0, 1) }}</span>
                                </div>
                                <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $course->title }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $course->category }}</p>
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $course->short_description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modules Column --}}
                    <div class="min-w-[300px]">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 mb-4 shadow-md flex items-center justify-between">
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Modules
                            </h2>
                            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create_lessons'))
                                <flux:button wire:click="selectItem('module')" variant="ghost" class="text-white hover:bg-white/20 px-3 py-1 text-sm font-medium">
                                    + Add
                                </flux:button>
                            @endif
                        </div>
                        <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                            @forelse($modules as $module)
                                <div class="border-2 border-green-300 dark:border-green-700 rounded-xl p-5 bg-white dark:bg-gray-800 hover:shadow-lg transition-all cursor-pointer group hover:border-green-400 dark:hover:border-green-600" 
                                     wire:click="selectItem('module', {{ $module['id'] }})">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1">
                                            <h3 class="font-bold text-base text-gray-900 dark:text-white mb-2 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                                                {{ $module['title'] }}
                                            </h3>
                                            <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                    </svg>
                                                    {{ count($module['lessons'] ?? []) }} lessons
                                                </span>
                                            </div>
                                            @if(!empty($module['description']))
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">{{ $module['description'] }}</p>
                                            @endif
                                        </div>
                                        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('delete_courses'))
                                            <button wire:click.stop="deleteItem('module', {{ $module['id'] }})" 
                                                    class="text-red-500 hover:text-red-700 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded p-1 transition-colors"
                                                    wire:confirm="Are you sure you want to delete this module?">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-12 text-center bg-gray-50 dark:bg-gray-900/50">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400 font-medium">No modules yet</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Click + Add to create your first module</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Lessons Column --}}
                    <div class="min-w-[300px]">
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-4 mb-4 shadow-md">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    Lessons
                                </h2>
                                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create_lessons'))
                                    <flux:button wire:click="selectItem('lesson')" variant="ghost" class="text-white hover:bg-white/20 px-3 py-1 text-sm font-medium">
                                        + Add
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                        
                        <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                            @forelse($modules as $module)
                                @foreach($module['lessons'] ?? [] as $lesson)
                                    <div class="border-2 border-purple-300 dark:border-purple-700 rounded-xl p-5 bg-white dark:bg-gray-800 hover:shadow-lg transition-all cursor-pointer group hover:border-purple-400 dark:hover:border-purple-600"
                                         wire:click="selectItem('lesson', {{ $lesson['id'] }})">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 capitalize">
                                                        {{ $lesson['type'] }}
                                                    </span>
                                                </div>
                                                <h3 class="font-bold text-base text-gray-900 dark:text-white mb-1 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                                    {{ $lesson['title'] }}
                                                </h3>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Order: {{ $lesson['order_index'] ?? 'N/A' }}</p>
                                            </div>
                                            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('delete_lessons'))
                                                <button wire:click.stop="deleteItem('lesson', {{ $lesson['id'] }})" 
                                                        class="text-red-500 hover:text-red-700 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded p-1 transition-colors"
                                                        wire:confirm="Are you sure you want to delete this lesson?">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @empty
                                <div class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-12 text-center bg-gray-50 dark:bg-gray-900/50">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400 font-medium">No lessons yet</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Add a module first to create lessons</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Assessments Column --}}
                    <div class="min-w-[300px]">
                        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-4 mb-4 shadow-md">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Assessments
                                </h2>
                                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create_assessments'))
                                    <flux:button wire:click="selectItem('assessment')" variant="ghost" class="text-white hover:bg-white/20 px-3 py-1 text-sm font-medium">
                                        + Add
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                            @forelse($modules as $module)
                                @foreach($module['lessons'] ?? [] as $lesson)
                                    @foreach($lesson['assessments'] ?? [] as $assessment)
                                        <div class="border-2 border-yellow-300 dark:border-yellow-700 rounded-xl p-5 bg-white dark:bg-gray-800 hover:shadow-lg transition-all cursor-pointer group hover:border-yellow-400 dark:hover:border-yellow-600"
                                             wire:click="selectItem('assessment', {{ $assessment['id'] }})">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 capitalize">
                                                            {{ $assessment['type'] }}
                                                        </span>
                                                    </div>
                                                    <h3 class="font-bold text-base text-gray-900 dark:text-white mb-1 group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition-colors">
                                                        {{ $assessment['title'] }}
                                                    </h3>
                                                </div>
                                                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('delete_courses'))
                                                    <button wire:click.stop="deleteItem('assessment', {{ $assessment['id'] }})" 
                                                            class="text-red-500 hover:text-red-700 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded p-1 transition-colors"
                                                            wire:confirm="Are you sure you want to delete this assessment?">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            @empty
                                <div class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-12 text-center bg-gray-50 dark:bg-gray-900/50">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400 font-medium">No assessments yet</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Add lessons first to create assessments</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-16">
                <div class="text-center max-w-md mx-auto">
                    <div class="mx-auto w-32 h-32 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center mb-8 shadow-lg">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Get Started</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-8 text-lg">Select a course from above to start building your curriculum</p>
                    @if($courses->count() === 0)
                        <flux:button href="{{ route('courses.create') }}" variant="primary" class="px-6 py-3 text-lg" wire:navigate>
                            Create Your First Course
                        </flux:button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Large Professional Modal --}}
        @if($showModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
                
                {{-- Modal Dialog --}}
                <div class="flex min-h-full items-center justify-center p-4 sm:p-8">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl transition-all w-full max-w-4xl">
                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 px-8 py-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold text-white">
                                        {{ $modalType === 'create' ? 'Create' : 'Edit' }} {{ ucfirst($selectedType) }}
                                    </h3>
                                    <p class="text-white/80 text-sm mt-1">Fill in the details below</p>
                                </div>
                                <button type="button" wire:click="closeModal" class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-colors">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        {{-- Modal Body --}}
                        <div class="px-8 py-6 max-h-[70vh] overflow-y-auto">
                            <form wire:submit.prevent="saveItem" class="space-y-6">
                                @if($selectedType === 'module')
                                    <div class="grid grid-cols-1 gap-6">
                                        <flux:field>
                                            <flux:label class="text-base font-semibold">Title *</flux:label>
                                            <flux:input wire:model="formData.title" placeholder="Enter module title" class="text-base" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label class="text-base font-semibold">Description</flux:label>
                                            <flux:textarea wire:model="formData.description" rows="4" placeholder="Describe what students will learn in this module" />
                                        </flux:field>
                                        <flux:field>
                                            <flux:label class="text-base font-semibold">Overview</flux:label>
                                            <flux:textarea wire:model="formData.overview" rows="4" placeholder="Module overview and objectives" />
                                        </flux:field>
                                        <div class="grid grid-cols-2 gap-4">
                                            <flux:field>
                                                <flux:label class="text-base font-semibold">Order Index</flux:label>
                                                <flux:input type="number" wire:model="formData.order_index" />
                                            </flux:field>
                                        </div>
                                    </div>
                                @elseif($selectedType === 'lesson')
                                    <div class="grid grid-cols-1 gap-6">
                                        {{-- Basic Information --}}
                                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h4>
                                            
                                            <flux:field>
                                                <flux:label class="text-base font-semibold">Title *</flux:label>
                                                <flux:input wire:model="formData.title" placeholder="Enter lesson title" class="text-base" />
                                            </flux:field>
                                            
                                            <div class="grid grid-cols-2 gap-4 mt-4">
                                                <flux:field>
                                                    <flux:label class="text-base font-semibold">Module *</flux:label>
                                                    <flux:select wire:model="formData.module_id">
                                                        <option value="">Select Module</option>
                                                        @foreach($modules as $module)
                                                            <option value="{{ $module['id'] }}">{{ $module['title'] }}</option>
                                                        @endforeach
                                                    </flux:select>
                                                </flux:field>
                                                <flux:field>
                                                    <flux:label class="text-base font-semibold">Lesson Type *</flux:label>
                                                    <flux:select wire:model.live="formData.lesson_type">
                                                        <option value="text">Text</option>
                                                        <option value="video">Video</option>
                                                        <option value="interactive">Interactive</option>
                                                        <option value="quiz">Quiz</option>
                                                    </flux:select>
                                                </flux:field>
                                            </div>
                                            
                                            <flux:field class="mt-4">
                                                <flux:label class="text-base font-semibold">Summary</flux:label>
                                                <flux:textarea wire:model="formData.summary" rows="2" placeholder="Short summary/preview text (max 500 characters)" />
                                                <flux:description>Brief description that appears in lesson lists</flux:description>
                                            </flux:field>
                                        </div>

                                        {{-- Content Section --}}
                                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Content</h4>
                                            
                                            <flux:field>
                                                <flux:label class="text-base font-semibold">Content</flux:label>
                                                <flux:textarea wire:model="formData.content" rows="8" placeholder="Lesson content, instructions, or description (supports HTML)" />
                                            </flux:field>
                                            
                                            <flux:field class="mt-4">
                                                <flux:label class="text-base font-semibold">Learning Objectives</flux:label>
                                                <flux:textarea wire:model="formData.objectives" rows="4" placeholder="What will students learn from this lesson?" />
                                                <flux:description>Displayed prominently at the top of the lesson</flux:description>
                                            </flux:field>
                                            
                                            <flux:field class="mt-4">
                                                <flux:label class="text-base font-semibold">Implementation Guidance</flux:label>
                                                <flux:textarea wire:model="formData.implementation_guidance" rows="3" placeholder="Additional guidance for instructors and students" />
                                            </flux:field>
                                        </div>

                                        {{-- Video Settings (Conditional) --}}
                                        <div x-show="$wire.formData.lesson_type === 'video'" 
                                             x-transition
                                             class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                Video Settings
                                            </h4>
                                            
                                            <flux:field>
                                                <flux:label class="text-base font-semibold">Video URL *</flux:label>
                                                <flux:input wire:model="formData.video_url" type="url" placeholder="https://example.com/video.mp4 or YouTube/Vimeo URL" />
                                                <flux:description>Required when lesson type is Video</flux:description>
                                            </flux:field>
                                            
                                            <flux:field class="mt-4">
                                                <flux:label class="text-base font-semibold">Video Duration (minutes)</flux:label>
                                                <flux:input type="number" wire:model="formData.video_duration" min="1" placeholder="Video length in minutes" />
                                            </flux:field>
                                        </div>

                                        {{-- Settings & Configuration --}}
                                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Settings & Configuration</h4>
                                            
                                            <div class="grid grid-cols-2 gap-4">
                                                <flux:field>
                                                    <flux:label class="text-base font-semibold">Duration (minutes)</flux:label>
                                                    <flux:input type="number" wire:model="formData.duration_minutes" min="1" />
                                                </flux:field>
                                                
                                                <flux:field>
                                                    <flux:label class="text-base font-semibold">Difficulty Level</flux:label>
                                                    <flux:select wire:model="formData.difficulty_level">
                                                        <option value="beginner">Beginner</option>
                                                        <option value="intermediate">Intermediate</option>
                                                        <option value="advanced">Advanced</option>
                                                    </flux:select>
                                                </flux:field>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-4 mt-4">
                                                <flux:field>
                                                    <flux:label class="text-base font-semibold">Order Index</flux:label>
                                                    <flux:input type="number" wire:model="formData.order_index" min="1" />
                                                </flux:field>
                                                
                                                <flux:field>
                                                    <flux:label class="text-base font-semibold">Question of Day</flux:label>
                                                    <flux:input wire:model="formData.question_of_day" placeholder="Special daily question ID or text" />
                                                </flux:field>
                                            </div>
                                            
                                            {{-- Multi-Level Settings --}}
                                            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                                                <flux:field>
                                                    <flux:checkbox wire:model="formData.has_levels" label="Enable Multiple Levels" />
                                                    <flux:description>Allow this lesson to have multiple difficulty levels</flux:description>
                                                </flux:field>
                                                
                                                <div x-show="$wire.formData.has_levels" x-transition class="mt-3">
                                                    <flux:field>
                                                        <flux:label class="text-base font-semibold">Total Levels</flux:label>
                                                        <flux:input type="number" wire:model="formData.total_levels" min="1" max="10" />
                                                    </flux:field>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Approval Status --}}
                                        @if($modalType === 'edit' || ($modalType === 'create' && auth()->user()->isAdmin()))
                                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Approval Status
                                                </h4>
                                                
                                                @php
                                                    $approvalStatus = $formData['approval_status'] ?? 'draft';
                                                    $statusColors = [
                                                        'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                        'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                                    ];
                                                    $statusLabels = [
                                                        'draft' => 'Draft',
                                                        'pending' => 'Pending Approval',
                                                        'approved' => 'Approved',
                                                        'rejected' => 'Rejected',
                                                    ];
                                                @endphp
                                                
                                                <div class="space-y-4">
                                                    @if(auth()->user()->isAdmin())
                                                        <flux:field>
                                                            <flux:label class="text-base font-semibold">Approval Status</flux:label>
                                                            <flux:select wire:model="formData.approval_status">
                                                                <option value="draft">Draft</option>
                                                                <option value="pending">Pending</option>
                                                                <option value="approved">Approved</option>
                                                                <option value="rejected">Rejected</option>
                                                            </flux:select>
                                                            <flux:description>Admins can directly set approval status</flux:description>
                                                        </flux:field>
                                                    @else
                                                        <div class="flex items-center gap-3 p-3 rounded-lg {{ $statusColors[$approvalStatus] ?? $statusColors['draft'] }}">
                                                            <span class="font-semibold">{{ $statusLabels[$approvalStatus] ?? 'Draft' }}</span>
                                                            @if($approvalStatus === 'pending')
                                                                <span class="text-xs opacity-75">Awaiting supervisor/admin review</span>
                                                            @elseif($approvalStatus === 'approved')
                                                                <span class="text-xs opacity-75">Ready to publish</span>
                                                            @elseif($approvalStatus === 'rejected')
                                                                <span class="text-xs opacity-75">Needs revision</span>
                                                            @endif
                                                        </div>
                                                        @if($modalType === 'edit' && $approvalStatus === 'draft')
                                                            <div class="pt-2">
                                                                <flux:button 
                                                                    type="button"
                                                                    wire:click="submitLessonForApproval"
                                                                    variant="primary"
                                                                    class="w-full"
                                                                    wire:confirm="Submit this lesson for approval? It will be reviewed by a supervisor or admin.">
                                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    Submit for Approval
                                                                </flux:button>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Status & Visibility --}}
                                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status & Visibility</h4>
                                            
                                            <div class="grid grid-cols-2 gap-4">
                                                <flux:field>
                                                    @php
                                                        $canPublish = ($formData['approval_status'] ?? 'draft') === 'approved' || auth()->user()->isAdmin();
                                                    @endphp
                                                    <flux:checkbox 
                                                        wire:model="formData.is_published" 
                                                        label="Published"
                                                        @if(!$canPublish) disabled @endif />
                                                    <flux:description>
                                                        @if(!$canPublish)
                                                            <span class="text-orange-600 dark:text-orange-400">
                                                                Lesson must be approved before publishing
                                                            </span>
                                                        @else
                                                            Make this lesson visible to students
                                                        @endif
                                                    </flux:description>
                                                </flux:field>
                                                
                                                <flux:field>
                                                    <flux:checkbox wire:model="formData.is_free_preview" label="Free Preview" />
                                                    <flux:description>Allow free access without enrollment</flux:description>
                                                </flux:field>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-4 mt-4">
                                                <flux:field>
                                                    <flux:checkbox wire:model="formData.is_locked" label="Locked" />
                                                    <flux:description>Prevent student access</flux:description>
                                                </flux:field>
                                                
                                                <flux:field>
                                                    <flux:checkbox wire:model="formData.is_active" label="Active" />
                                                    <flux:description>Lesson is active and available</flux:description>
                                                </flux:field>
                                            </div>
                                        </div>

                                        {{-- Attachments Section --}}
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>
                                                Attachments & Resources
                                            </h4>
                                            
                                            {{-- File Upload Area --}}
                                            <div class="mb-4">
                                                <flux:field>
                                                    <flux:label class="text-base font-semibold">Upload Files</flux:label>
                                                    <input type="file" 
                                                           wire:model="attachmentFiles" 
                                                           multiple 
                                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar,.txt"
                                                           class="block w-full text-sm text-gray-900 dark:text-gray-300
                                                                  file:mr-4 file:py-2 file:px-4
                                                                  file:rounded-lg file:border-0
                                                                  file:text-sm file:font-semibold
                                                                  file:bg-blue-50 file:text-blue-700
                                                                  hover:file:bg-blue-100
                                                                  dark:file:bg-blue-900/30 dark:file:text-blue-300
                                                                  dark:hover:file:bg-blue-900/50
                                                                  cursor-pointer border border-gray-300 dark:border-gray-600
                                                                  rounded-lg bg-white dark:bg-gray-700
                                                                  focus:outline-none focus:ring-2 focus:ring-blue-500
                                                                  focus:border-transparent">
                                                    <flux:description>Select one or more files (PDF, Word, Excel, Images, etc.). Max 10MB per file.</flux:description>
                                                    <flux:error name="attachmentFiles" />
                                                </flux:field>
                                                
                                                {{-- Upload Progress --}}
                                                <div wire:loading wire:target="attachmentFiles" class="mt-2">
                                                    <div class="text-sm text-blue-600 dark:text-blue-400 flex items-center gap-2">
                                                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        Processing files...
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Existing Attachments (when editing) --}}
                                            @if(count($existingAttachments) > 0)
                                                <div class="mb-4">
                                                    <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Existing Attachments</h5>
                                                    <div class="space-y-2">
                                                        @foreach($existingAttachments as $index => $attachment)
                                                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                                                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                                    </svg>
                                                                    <div class="flex-1 min-w-0">
                                                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                                            {{ $attachment['name'] ?? 'Unnamed File' }}
                                                                        </p>
                                                                        @if(isset($attachment['size']))
                                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                                {{ number_format($attachment['size'] / 1024, 2) }} KB
                                                                            </p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <button type="button" 
                                                                        wire:click="removeAttachment({{ $index }})"
                                                                        wire:confirm="Are you sure you want to remove this attachment?"
                                                                        class="ml-3 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- New Files to Upload --}}
                                            @if(count($attachmentFiles) > 0)
                                                <div>
                                                    <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">New Files (will be uploaded on save)</h5>
                                                    <div class="space-y-2">
                                                        @foreach($attachmentFiles as $index => $file)
                                                            <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                                    </svg>
                                                                    <div class="flex-1 min-w-0">
                                                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                                            {{ $file->getClientOriginalName() }}
                                                                        </p>
                                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                            {{ number_format($file->getSize() / 1024, 2) }} KB
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <button type="button" 
                                                                        wire:click="removeNewAttachment({{ $index }})"
                                                                        class="ml-3 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Empty State --}}
                                            @if(count($existingAttachments) === 0 && count($attachmentFiles) === 0)
                                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">No attachments yet. Upload files above to add resources to this lesson.</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($selectedType === 'assessment')
                                    <div class="grid grid-cols-1 gap-6">
                                        <flux:field>
                                            <flux:label class="text-base font-semibold">Title *</flux:label>
                                            <flux:input wire:model="formData.title" placeholder="Enter assessment title" class="text-base" />
                                        </flux:field>
                                        <div class="grid grid-cols-2 gap-4">
                                            <flux:field>
                                                <flux:label class="text-base font-semibold">Lesson *</flux:label>
                                                <flux:select wire:model="formData.lesson_id">
                                                    <option value="">Select Lesson</option>
                                                    @foreach($modules as $module)
                                                        @foreach($module['lessons'] ?? [] as $lesson)
                                                            <option value="{{ $lesson['id'] }}">{{ $lesson['title'] }}</option>
                                                        @endforeach
                                                    @endforeach
                                                </flux:select>
                                            </flux:field>
                                            <flux:field>
                                                <flux:label class="text-base font-semibold">Assessment Type *</flux:label>
                                                <flux:select wire:model="formData.assessment_type">
                                                    <option value="quiz">Quiz</option>
                                                    <option value="assignment">Assignment</option>
                                                    <option value="survey">Survey</option>
                                                    <option value="rubric">Rubric</option>
                                                    <option value="peer_review">Peer Review</option>
                                                    <option value="self_assessment">Self Assessment</option>
                                                </flux:select>
                                            </flux:field>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <flux:field>
                                                <flux:label class="text-base font-semibold">Max Attempts</flux:label>
                                                <flux:input type="number" wire:model="formData.max_attempts" min="1" />
                                            </flux:field>
                                            <flux:field>
                                                <flux:label class="text-base font-semibold">Passing Score (%)</flux:label>
                                                <flux:input type="number" wire:model="formData.passing_score" min="0" max="100" />
                                            </flux:field>
                                        </div>
                                    </div>
                                @endif

                                {{-- Modal Footer --}}
                                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700 mt-8">
                                    <flux:button type="button" wire:click="closeModal" variant="ghost" class="px-6 py-2.5">
                                        Cancel
                                    </flux:button>
                                    <flux:button type="submit" variant="primary" class="px-6 py-2.5">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Save {{ ucfirst($selectedType) }}
                                    </flux:button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
