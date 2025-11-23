<div class="flex h-screen bg-gray-100 dark:bg-gray-900" 
     x-data="{ 
         collapsed: @entangle('sidebarCollapsed'),
         init() {
             // Keyboard shortcut: Ctrl+B or Cmd+B to toggle sidebar
             document.addEventListener('keydown', (e) => {
                 if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                     e.preventDefault();
                     $wire.toggleSidebar();
                 }
             });
         }
     }">
    {{-- Left Sidebar - Course List or Course Structure --}}
    <div class="transition-all duration-300 bg-white dark:bg-gray-800 overflow-hidden {{ $sidebarCollapsed ? 'w-0 border-0' : 'w-80 border-r border-gray-200 dark:border-gray-700 overflow-y-auto' }}">
        <div class="{{ $sidebarCollapsed ? 'hidden' : 'block' }}" style="width: 320px;">
        @if(!$courseId)
            {{-- Course Selection --}}
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Select Course</h2>
                
                @if($courses->count() > 0)
                    <div class="space-y-3">
                        @foreach($courses as $courseOption)
                            <a href="{{ route('curriculum.builder', ['course' => $courseOption->id]) }}" 
                               wire:navigate
                               class="block p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-lg transition-all">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-gray-900 dark:text-white">{{ $courseOption->title }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $courseOption->modules->count() }} modules
                                        </p>
                                    </div>
                                    @if($courseOption->instructor_id !== auth()->id())
                                        <span class="px-2 py-1 text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full whitespace-nowrap">
                                            Collaborator
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400">No courses available</p>
                @endif
            </div>
        @else
            {{-- Course Structure Tree --}}
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Structure</h2>
                    <a href="{{ route('curriculum.builder') }}" wire:navigate class="text-sm text-blue-600 hover:text-blue-700">
                        ← Courses
                    </a>
                </div>
                
                @if($course)
                    {{-- Course Title --}}
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <h3 class="font-bold text-blue-900 dark:text-blue-100">{{ $course->title }}</h3>
                    </div>
                    
                    {{-- Add Module Button --}}
                    <button wire:click="selectItem('module')" 
                            class="w-full mb-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        + Add Module
                    </button>
                    
                    {{-- Modules List --}}
                    <div class="space-y-2">
                        @foreach($course->modules as $module)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                {{-- Module Header --}}
                                <button wire:click="selectItem('module', {{ $module->id }})"
                                        class="w-full px-4 py-3 text-left bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors
                                               {{ $selectedType === 'module' && $selectedId === $module->id ? 'bg-blue-50 dark:bg-blue-900/30' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $module->title }}</span>
                                        <span class="text-xs text-gray-500">{{ $module->lessons->count() }} lessons</span>
                                    </div>
                                </button>
                                
                                {{-- Lessons List --}}
                                <div class="pl-4">
                                    @foreach($module->lessons as $lesson)
                                        <div class="border-l-2 border-gray-200 dark:border-gray-700 ml-2">
                                            {{-- Lesson Item --}}
                                            <div class="flex items-center group">
                                                <button wire:click="selectItem('lesson', {{ $lesson->id }})"
                                                        class="flex-1 px-4 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors
                                                               {{ $selectedType === 'lesson' && $selectedId === $lesson->id ? 'bg-blue-50 dark:bg-blue-900/30' : '' }}">
                                                    <div class="flex items-center gap-2">
                                                        {{-- Approval Status Icon --}}
                                                        @if($lesson->approval_status === 'approved')
                                                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                            </svg>
                                                        @elseif($lesson->approval_status === 'pending')
                                                            <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                            </svg>
                                                        @elseif($lesson->approval_status === 'rejected')
                                                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                            </svg>
                                                        @else
                                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        @endif
                                                        <span class="text-gray-700 dark:text-gray-300 flex-1 truncate">{{ $lesson->title }}</span>
                                                        @if($lesson->assessments->count() > 0)
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $lesson->assessments->count() }}</span>
                                                        @endif
                                                    </div>
                                                </button>
                                                {{-- Lock/Unlock Toggle --}}
                                                <button wire:click.stop="toggleLessonLock({{ $lesson->id }})"
                                                        title="{{ $lesson->is_locked ? 'Unlock lesson' : 'Lock lesson' }}"
                                                        class="px-2 py-2 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                                                    @if($lesson->is_locked)
                                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                        </svg>
                                                    @endif
                                                </button>
                                            </div>
                                            
                                            {{-- Assessments under this lesson --}}
                                            @if($lesson->assessments->count() > 0)
                                                <div class="pl-6">
                                                    @foreach($lesson->assessments as $assessment)
                                                        <div class="flex items-center group">
                                                            <button wire:click="selectItem('assessment', {{ $assessment->id }})"
                                                                    class="flex-1 px-3 py-1.5 text-left text-xs hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors
                                                                           {{ $selectedType === 'assessment' && $selectedId === $assessment->id ? 'bg-blue-50 dark:bg-blue-900/30' : '' }}">
                                                                <div class="flex items-center gap-2">
                                                                    <svg class="w-3 h-3 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                    <span class="text-gray-600 dark:text-gray-400 truncate">{{ $assessment->title }}</span>
                                                                </div>
                                                            </button>
                                                            {{-- Lock/Unlock Toggle for Assessment --}}
                                                            <button wire:click.stop="toggleAssessmentLock({{ $assessment->id }})"
                                                                    title="{{ $assessment->is_locked ? 'Unlock quiz' : 'Lock quiz' }}"
                                                                    class="px-2 py-1.5 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                                                                @if($assessment->is_locked)
                                                                    <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                @else
                                                                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                                    </svg>
                                                                @endif
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            
                                            {{-- Add Assessment Button --}}
                                            <div class="pl-6">
                                                <button wire:click="selectItem('assessment', null, {{ $lesson->id }})"
                                                        class="w-full px-3 py-1.5 text-left text-xs text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                                                    + Add Assessment
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    {{-- Add Lesson Button --}}
                                    <button wire:click="selectItem('lesson', null, {{ $module->id }})"
                                            class="w-full px-4 py-2 text-left text-sm text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                        + Add Lesson
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
        </div>
    </div>
    
    {{-- Sidebar Toggle Button --}}
    <div class="fixed left-0 top-1/2 -translate-y-1/2 z-50 transition-all duration-300 {{ $sidebarCollapsed ? 'translate-x-0' : 'translate-x-80' }}">
        <button wire:click="toggleSidebar" 
                class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-r-lg shadow-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow-xl transition-all duration-200 relative"
                x-data="{ showTooltip: false }"
                @mouseenter="showTooltip = true"
                @mouseleave="showTooltip = false">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-all duration-300 {{ $sidebarCollapsed ? '' : 'rotate-180' }}" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            
            {{-- Tooltip --}}
            <div x-show="showTooltip" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-x-2"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-2"
                 class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg shadow-lg whitespace-nowrap pointer-events-none">
                {{ $sidebarCollapsed ? 'Show sidebar' : 'Hide sidebar' }}
                <span class="text-xs text-gray-400 ml-2">(Ctrl+B)</span>
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900 dark:border-r-gray-700"></div>
            </div>
        </button>
    </div>
    
    {{-- Main Content Area --}}
    <div class="flex-1 overflow-y-auto" wire:key="content-{{ $selectedType }}-{{ $selectedId }}">
        {{-- Success Message --}}
        @if (session()->has('message'))
            <div class="mx-8 mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-green-800 dark:text-green-200 font-medium">{{ session('message') }}</p>
                </div>
            </div>
        @endif
        
        {{-- Error Message --}}
        @if (session()->has('error'))
            <div class="mx-8 mt-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif
        
        @if(!$courseId)
            {{-- Welcome Screen --}}
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Curriculum Builder</h2>
                    <p class="text-gray-600 dark:text-gray-400">Select a course from the sidebar to start building</p>
                    

                </div>
            </div>
        @elseif($showForm && $selectedType === 'lesson')
            {{-- Lesson Form - Code.org / Google Classroom Style --}}
            <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6 md:p-8">
                <div class="max-w-5xl mx-auto">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ $selectedId ? 'Edit Lesson' : 'Create New Lesson' }}
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Build structured, engaging lessons for your students</p>
                        </div>
                        <button wire:click="closeForm" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if(!$course)
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                            <p class="text-red-800 dark:text-red-200">Error: Course not loaded. Please select a course first.</p>
                        </div>
                    @else
                    <form wire:submit.prevent="saveLesson" class="space-y-6">
                        {{-- Two Column Layout --}}
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            {{-- Left Column - Main Content --}}
                            <div class="lg:col-span-2 space-y-6">
                                
                                {{-- 1. Basic Information --}}
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Basic Information</h2>
                                    </div>
                                    
                                    <div class="space-y-5">
                                        {{-- Title --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Lesson Title <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" wire:model="formData.title" 
                                                   class="w-full px-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   placeholder="e.g., Introduction to Variables">
                                            @error('formData.title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- Module & Type Row --}}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    Module <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="formData.module_id" 
                                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                    <option value="">Select Module</option>
                                                    @foreach($course->modules as $module)
                                                        <option value="{{ $module->id }}">{{ $module->title }}</option>
                                                    @endforeach
                                                </select>
                                                @error('formData.module_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    Lesson Type <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model.live="formData.lesson_type" 
                                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                    <option value="text">📝 Text Lesson</option>
                                                    <option value="video">🎥 Video Lesson</option>
                                                    <option value="interactive">💻 Interactive</option>
                                                    <option value="quiz">✅ Quiz</option>
                                                </select>
                                                @error('formData.lesson_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                        </div>

                                        {{-- Summary --}}
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Short Summary
                                            </label>
                                            <textarea wire:model="formData.summary" rows="2"
                                                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                      placeholder="Brief description of this lesson (1-2 sentences)"></textarea>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This appears in lesson previews and search results</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. Learning Objectives --}}
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Learning Objectives</h2>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            What should students know or be able to do after this lesson?
                                        </label>
                                        <textarea wire:model="formData.objectives" rows="5"
                                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                                                  placeholder="• Understand the concept of variables&#10;• Create and assign values to variables&#10;• Use variables in simple programs&#10;• Explain the difference between variable types"></textarea>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use bullet points (•) or numbers to list objectives clearly</p>
                                    </div>
                                </div>

                                {{-- 3. Lesson Content --}}
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Lesson Content</h2>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Main lesson content and instructions
                                        </label>
                                        
                                        {{-- TipTap Editor Container with wire:ignore for performance --}}
                                        <div wire:ignore
                                             class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-white dark:bg-gray-700"
                                             x-data="setupTipTapEditor($wire.entangle('formData.content'))"
                                             x-init="init($refs.editor, '{{ $courseId }}')">
                                            <div x-show="loading" class="p-4 text-center text-gray-500">
                                                <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <p class="mt-2 text-sm">Loading editor...</p>
                                            </div>
                                            <div x-ref="editor" x-show="!loading" class="min-h-[300px]"></div>
                                        </div>
                                        
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            ✨ Rich text editor with formatting, images, and code blocks. Auto-saves every 10 seconds.
                                        </p>
                                    </div>
                                </div>

                                {{-- 4. Video Lesson Settings (Conditional) --}}
                                @if($formData['lesson_type'] === 'video')
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-blue-200 dark:border-blue-800 p-6">
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Video Settings</h2>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Video URL <span class="text-red-500">*</span>
                                            </label>
                                            <input type="url" wire:model="formData.video_url" 
                                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   placeholder="https://example.com/video.mp4 or Vimeo/YouTube URL">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Paste direct video URL (MP4, Vimeo, YouTube, etc.)</p>
                                            @error('formData.video_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Video Duration (minutes)
                                            </label>
                                            <input type="number" wire:model="formData.video_duration" 
                                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   placeholder="e.g., 15">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Approximate length of the video</p>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- 5. Interactive Components (for Interactive Lessons) --}}
                                @if($formData['lesson_type'] === 'interactive')
                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg shadow-sm border-2 border-purple-200 dark:border-purple-800 p-6">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 bg-purple-100 dark:bg-purple-900/50 rounded-lg">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Interactive Components</h2>
                                            <p class="text-sm text-purple-700 dark:text-purple-300">Add visual elements to make your lesson engaging</p>
                                        </div>
                                    </div>

                                    {{-- Step-by-Step Instructions --}}
                                    <div class="mb-6">
                                        <div class="flex items-center gap-2 mb-3">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                            <label class="text-sm font-semibold text-gray-900 dark:text-white">
                                                1. Step-by-Step Instructions
                                            </label>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-3">
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                                💡 Break down your lesson into clear steps. Example: "Step 1: Open editor" → "Step 2: Create file"
                                            </p>
                                        </div>
                                        <textarea 
                                            wire:model="formData.lesson_steps_text" 
                                            rows="6"
                                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                                            placeholder="Step 1: Open your code editor&#10;Step 2: Create a new file&#10;Step 3: Write your code&#10;Step 4: Test your program"></textarea>
                                    </div>

                                    {{-- Scratch Project Embed --}}
                                    <div class="mb-6">
                                        <div class="flex items-center gap-2 mb-3">
                                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <label class="text-sm font-semibold text-gray-900 dark:text-white">
                                                2. Scratch Project Embed (Optional)
                                            </label>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-3">
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                                🎮 For Scratch lessons: Add the project ID from scratch.mit.edu (e.g., "1234567890")
                                            </p>
                                        </div>
                                        <input 
                                            type="text" 
                                            wire:model="formData.scratch_project_id"
                                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            placeholder="e.g., 1234567890 (leave empty if not a Scratch lesson)">
                                    </div>

                                    {{-- Code Examples --}}
                                    <div class="mb-6">
                                        <div class="flex items-center gap-2 mb-3">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                            </svg>
                                            <label class="text-sm font-semibold text-gray-900 dark:text-white">
                                                3. Code Examples (Optional)
                                            </label>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-3">
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                                💻 Add code snippets or blocks students will use. One per line.
                                            </p>
                                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                                Examples:<br>
                                                • Scratch: "move (10) steps"<br>
                                                • Python: "print('Hello World')"<br>
                                                • HTML: "&lt;h1&gt;My Title&lt;/h1&gt;"
                                            </div>
                                        </div>
                                        <textarea 
                                            wire:model="formData.code_examples_text" 
                                            rows="5"
                                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                                            placeholder="print('Hello World')&#10;name = 'Student'&#10;if age > 18:&#10;    print('Adult')"></textarea>
                                    </div>

                                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                        <div class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                                <strong>Preview:</strong> Students will see these components displayed beautifully when they view the lesson. All fields are optional - add only what you need!
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                            </div>

                            {{-- Right Column - Settings & Metadata --}}
                            <div class="space-y-6">
                                
                                {{-- Publishing & Access --}}
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Publishing</h3>
                                    
                                    <div class="space-y-4">
                                        {{-- Published Toggle --}}
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white">Published</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Make visible to students</div>
                                            </div>
                                            <div class="relative">
                                                <input type="checkbox" wire:model="formData.is_published" class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-2 peer-focus:ring-blue-300 transition-colors"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>

                                        {{-- Active Toggle --}}
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white">Active</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Enable lesson access</div>
                                            </div>
                                            <div class="relative">
                                                <input type="checkbox" wire:model="formData.is_active" class="sr-only peer" checked>
                                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-2 peer-focus:ring-blue-300 transition-colors"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>

                                        {{-- Free Preview Toggle --}}
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white">Free Preview</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Allow non-enrolled users</div>
                                            </div>
                                            <div class="relative">
                                                <input type="checkbox" wire:model="formData.is_free_preview" class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-2 peer-focus:ring-blue-300 transition-colors"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>

                                        {{-- Locked Toggle --}}
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white">Locked</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Require prerequisites</div>
                                            </div>
                                            <div class="relative">
                                                <input type="checkbox" wire:model="formData.is_locked" class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:bg-gray-600 peer-focus:ring-2 peer-focus:ring-gray-300 transition-colors"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Lesson Settings --}}
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Settings</h3>
                                    
                                    <div class="space-y-4">
                                        {{-- Difficulty Level --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Difficulty Level
                                            </label>
                                            <select wire:model="formData.difficulty_level" 
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                <option value="beginner">🟢 Beginner</option>
                                                <option value="intermediate">🟡 Intermediate</option>
                                                <option value="advanced">🔴 Advanced</option>
                                            </select>
                                        </div>

                                        {{-- Duration --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Duration (minutes)
                                            </label>
                                            <input type="number" wire:model="formData.duration_minutes" 
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   placeholder="30">
                                        </div>

                                        {{-- Order Index --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Order Position
                                            </label>
                                            <input type="number" wire:model="formData.order_index" 
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   placeholder="1">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Position in module</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Approval Status (if editing existing lesson) --}}
                                @if($selectedId && isset($formData['approval_status']))
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Approval Status</h3>
                                    
                                    <div class="mb-4">
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                            ];
                                            $status = $formData['approval_status'] ?? 'draft';
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$status] }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </div>
                                    
                                    @if(auth()->user()->hasAnyRole(['admin', 'supervisor']))
                                        {{-- Admin/Supervisor Actions --}}
                                        @if($status !== 'approved')
                                        <div class="space-y-2">
                                            <button type="button" wire:click="approveLesson" 
                                                    wire:loading.attr="disabled"
                                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                                <span wire:loading.remove wire:target="approveLesson">✓ Approve Lesson</span>
                                                <span wire:loading wire:target="approveLesson">Approving...</span>
                                            </button>
                                            <button type="button" wire:click="openRejectModal" 
                                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                                                ✗ Reject Lesson
                                            </button>
                                        </div>
                                        @else
                                        <div class="space-y-2">
                                            <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                                <p class="text-sm text-green-600 dark:text-green-400">
                                                    ✓ This lesson has been approved
                                                </p>
                                                @if($lesson && $lesson->approved_at)
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                    Approved {{ $lesson->approved_at->diffForHumans() }}
                                                </p>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="openRejectModal" 
                                                    class="w-full px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors">
                                                ⚠️ Disapprove Lesson
                                            </button>
                                        </div>
                                        @endif
                                    @else
                                        {{-- Teacher Actions --}}
                                        @if($status === 'rejected')
                                        <div class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                            <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">Rejection Reason:</p>
                                            <p class="text-sm text-red-700 dark:text-red-300">
                                                {{ $lesson->rejection_reason ?? 'No reason provided' }}
                                            </p>
                                        </div>
                                        <button type="button" wire:click="submitForApproval" 
                                                class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                                                📤 Resubmit for Approval
                                        </button>
                                        @elseif($status === 'draft')
                                        <button type="button" wire:click="submitForApproval" 
                                                class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                                                📤 Submit for Approval
                                        </button>
                                        @elseif($status === 'pending')
                                        <p class="text-sm text-yellow-600 dark:text-yellow-400">
                                            ⏳ Waiting for approval from admin/supervisor
                                        </p>
                                        @elseif($status === 'approved')
                                        <p class="text-sm text-green-600 dark:text-green-400">
                                            ✓ This lesson has been approved
                                        </p>
                                        @endif
                                    @endif
                                </div>
                                @endif

                                {{-- Save Actions --}}
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                                    @php
                                        $currentStatus = $formData['approval_status'] ?? 'draft';
                                    @endphp
                                    @if($selectedId && $currentStatus === 'approved' && !auth()->user()->hasAnyRole(['admin', 'supervisor']))
                                        <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                            <div class="flex items-start gap-2">
                                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Re-approval Required</p>
                                                    <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                                                        This lesson is currently approved. Updating it will send it back for re-approval by admin/supervisor.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <button type="submit" 
                                            class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-sm">
                                        {{ $selectedId ? 'Update Lesson' : 'Create Lesson' }}
                                    </button>
                                    <button type="button" wire:click="closeForm"
                                            class="w-full mt-3 px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                                        Cancel
                                    </button>
                                </div>

                            </div>
                        </div>
                    </form>
                    
                    {{-- Rejection Modal --}}
                    @if($showRejectModal ?? false)
                    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeRejectModal">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6" wire:click.stop>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                                {{ $status === 'approved' ? 'Disapprove Lesson' : 'Reject Lesson' }}
                            </h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Reason for {{ $status === 'approved' ? 'Disapproval' : 'Rejection' }} <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="rejectionReason" rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                          placeholder="Explain why this lesson needs revision..."></textarea>
                                @error('rejectionReason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            
                            @if(session()->has('error'))
                                <div class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                                </div>
                            @endif
                            
                            <div class="flex gap-3">
                                <button type="button" 
                                        wire:click="disapproveLesson" 
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50 cursor-not-allowed"
                                        x-data
                                        @click="if(!$wire.rejectionReason || $wire.rejectionReason.trim() === '') { alert('Please provide a reason for disapproval'); $event.stopPropagation(); }"
                                        class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors">
                                    <span wire:loading.remove wire:target="disapproveLesson">
                                        {{ $status === 'approved' ? '⚠️ Disapprove' : '✗ Reject' }}
                                    </span>
                                    <span wire:loading wire:target="disapproveLesson">Processing...</span>
                                </button>
                                <button type="button" wire:click="closeRejectModal" 
                                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @endif
                </div>
            </div>
        @elseif($showForm && $selectedType === 'module')
            {{-- Module Form --}}
            <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6 md:p-8">
                <div class="max-w-3xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ $selectedId ? 'Edit Module' : 'Create New Module' }}
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Organize lessons into structured modules</p>
                        </div>
                        <button wire:click="closeForm" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveModule" class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div class="space-y-5">
                                {{-- Title --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Module Title <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="formData.title" 
                                           class="w-full px-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                           placeholder="e.g., Introduction to Programming">
                                    @error('formData.title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Description
                                    </label>
                                    <textarea wire:model="formData.description" rows="4"
                                              class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                              placeholder="Describe what students will learn in this module"></textarea>
                                </div>

                                {{-- Order Index --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Order Position
                                    </label>
                                    <input type="number" wire:model="formData.order_index" 
                                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                           placeholder="1">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Position in course structure</p>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-3">
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-sm">
                                {{ $selectedId ? 'Update Module' : 'Create Module' }}
                            </button>
                            <button type="button" wire:click="closeForm"
                                    class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($showForm && $selectedType === 'assessment')
            {{-- Assessment Placeholder --}}
            <div class="flex items-center justify-center h-full p-8">
                <div class="max-w-md w-full">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                        <div class="text-center">
                            <div class="mx-auto w-20 h-20 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mb-6">
                                <svg class="w-10 h-10 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Assessment Builder</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-8">
                                Create and manage quizzes, tests, and assignments using the dedicated assessment builder.
                            </p>
                            
                            <div class="space-y-3">
                                @if($selectedId)
                                    {{-- Lock/Unlock Assessment Button --}}
                                    @php
                                        $assessment = \App\Models\Assessment::find($selectedId);
                                    @endphp
                                    @if($assessment)
                                        <button wire:click="toggleAssessmentLock({{ $selectedId }})" 
                                                wire:loading.attr="disabled"
                                                wire:loading.class="opacity-50 cursor-not-allowed"
                                                class="block w-full px-6 py-3 {{ $assessment->is_locked ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white font-semibold rounded-lg transition-colors shadow-sm">
                                            <div class="flex items-center justify-center gap-2">
                                                @if($assessment->is_locked)
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span wire:loading.remove wire:target="toggleAssessmentLock">🔒 Locked - Click to Unlock</span>
                                                @else
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                                    </svg>
                                                    <span wire:loading.remove wire:target="toggleAssessmentLock">🔓 Unlocked - Click to Lock</span>
                                                @endif
                                                <span wire:loading wire:target="toggleAssessmentLock">Processing...</span>
                                            </div>
                                        </button>
                                        
                                        <div class="text-sm text-gray-600 dark:text-gray-400 text-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                            @if($assessment->is_locked)
                                                ⚠️ Students cannot access this assessment
                                            @else
                                                ✅ Students can access this assessment
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <a href="{{ route('assessments.edit', $selectedId) }}" 
                                       class="block w-full px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-sm">
                                        <div class="flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit Assessment
                                        </div>
                                    </a>
                                @else
                                    <a href="{{ route('assessments.create', ['lesson_id' => $lessonId ?? null]) }}" 
                                       class="block w-full px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-sm">
                                        <div class="flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Create New Assessment
                                        </div>
                                    </a>
                                @endif
                                
                                <button wire:click="closeForm" 
                                        class="block w-full px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
                                    Back to Structure
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($showForm)
            {{-- Other Forms --}}
            <div class="p-8">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $selectedId ? 'Edit' : 'Create' }} {{ ucfirst($selectedType) }}
                        </h2>
                        <button wire:click="closeForm" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <p class="text-gray-600 dark:text-gray-400">Form for {{ $selectedType }} will go here</p>
                    </div>
                </div>
            </div>
        @elseif($showForm)
            {{-- Debug: Form should show but conditions not met --}}
            <div class="p-8">
                <div class="bg-red-100 dark:bg-red-900/20 p-6 rounded-lg">
                    <h2 class="text-xl font-bold text-red-900 dark:text-red-100 mb-4">Debug: Form State</h2>
                    <p>showForm: {{ $showForm ? 'TRUE' : 'FALSE' }}</p>
                    <p>selectedType: {{ $selectedType ?? 'NULL' }}</p>
                    <p>selectedId: {{ $selectedId ?? 'NULL' }}</p>
                    <p>courseId: {{ $courseId ?? 'NULL' }}</p>
                    <p>course exists: {{ $course ? 'YES' : 'NO' }}</p>
                </div>
            </div>
        @else
            {{-- Course Overview --}}
            @if($course)
            <div class="p-8">
                <div class="max-w-4xl mx-auto">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">{{ $course->title }}</h2>
                    
                    {{-- Stats --}}
                    <div class="grid grid-cols-3 gap-6 mb-8">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div class="text-3xl font-bold text-blue-600 mb-2">{{ $course->modules->count() }}</div>
                            <div class="text-gray-600 dark:text-gray-400">Modules</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div class="text-3xl font-bold text-green-600 mb-2">{{ $course->modules->sum(fn($m) => $m->lessons->count()) }}</div>
                            <div class="text-gray-600 dark:text-gray-400">Lessons</div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div class="text-3xl font-bold text-purple-600 mb-2">{{ $course->modules->flatMap(fn($m) => $m->lessons)->flatMap(fn($l) => $l->assessments)->count() }}</div>
                            <div class="text-gray-600 dark:text-gray-400">Assessments</div>
                        </div>
                    </div>

                    {{-- Collaborators Section - Admin/Supervisor Only --}}
                    @if(auth()->user()->isAdmin() || auth()->user()->isSupervisor())
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
                            <livewire:course.manage-collaborators :course="$course" :key="'collaborators-'.$course->id" />
                        </div>
                    @endif
                    
                    {{-- Approval Actions (Admin/Supervisor Only) --}}
                    @if((auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor')) && $course->approval_status !== 'approved')
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-200 dark:border-yellow-800 rounded-lg shadow p-6 mb-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Course Approval</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                This course is currently in <span class="font-semibold">{{ ucfirst($course->approval_status) }}</span> status.
                            </p>
                            <div class="flex gap-3">
                                <button wire:click="approveCourse" 
                                        class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                                    ✓ Approve Course
                                </button>
                                <button wire:click="rejectCourse" 
                                        class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold">
                                    ✗ Reject Course
                                </button>
                            </div>
                        </div>
                    @elseif((auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor')) && $course->approval_status === 'approved')
                        <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 rounded-lg shadow p-6 mb-6">
                            <h3 class="text-lg font-bold text-green-900 dark:text-green-100 mb-2">✓ Course Approved</h3>
                            <p class="text-sm text-green-700 dark:text-green-300">
                                This course has been approved and is published.
                            </p>
                        </div>
                    @endif
                    
                    {{-- Content Lock Management --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Content Lock Management
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Control student access to lessons, quizzes, and assignments. Hover over items in the sidebar to lock/unlock them.
                        </p>
                        
                        @php
                            $allLessons = $course->modules->flatMap(fn($m) => $m->lessons);
                            $lockedLessons = $allLessons->where('is_locked', true)->count();
                            $unlockedLessons = $allLessons->where('is_locked', false)->count();
                            
                            $allAssessments = $allLessons->flatMap(fn($l) => $l->assessments);
                            $lockedAssessments = $allAssessments->where('is_locked', true)->count();
                            $unlockedAssessments = $allAssessments->where('is_locked', false)->count();
                        @endphp
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lessons</div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $lockedLessons }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                        </svg>
                                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $unlockedLessons }}</span>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">Locked / Unlocked</div>
                            </div>
                            
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quizzes</div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $lockedAssessments }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                        </svg>
                                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $unlockedAssessments }}</span>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">Locked / Unlocked</div>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Tip:</strong> Hover over any lesson or quiz in the left sidebar to see the lock/unlock button.
                            </p>
                        </div>
                    </div>
                    
                    {{-- Quick Actions --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button wire:click="selectItem('module')" 
                                    class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-left">
                                + Add Module
                            </button>
                            <a href="{{ route('courses.edit', $course->id) }}" wire:navigate
                               class="block w-full px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-center">
                                Edit Course Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @else
                {{-- Course Not Found or No Access --}}
                <div class="p-8">
                    <div class="max-w-2xl mx-auto text-center">
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-8">
                            <svg class="w-16 h-16 text-yellow-600 dark:text-yellow-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Course Not Found</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                The course you're trying to access doesn't exist or you don't have permission to edit it.
                            </p>
                            <a href="{{ route('courses.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors" wire:navigate>
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back to Courses
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>


</div>
