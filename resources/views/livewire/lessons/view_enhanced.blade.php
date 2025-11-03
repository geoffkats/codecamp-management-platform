{{-- Sticky Navigation Bar --}}
<div x-data="{ showOutline: @entangle('showOutline') }" 
     x-on:keydown.ctrl.n.prevent="$wire.nextLesson && window.location.href='{{ $nextLesson ? route('lessons.view', $nextLesson) : '#' }}'"
     x-on:keydown.ctrl.p.prevent="$wire.previousLesson && window.location.href='{{ $previousLesson ? route('lessons.view', $previousLesson) : '#' }}'"
     x-on:keydown.ctrl.o.prevent="showOutline = !showOutline"
     class="flex flex-col min-h-screen">
    
    {{-- Sticky Top Bar --}}
    <div class="sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <button wire:click="toggleOutline" 
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            title="Toggle Course Outline (Ctrl+O)">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ $lesson->title }}</h1>
                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ $course->title }}</p>
                    </div>
                </div>
                
                {{-- Course Progress --}}
                <div class="hidden md:flex items-center gap-3">
                    <div class="text-right">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ round($courseProgress) }}%</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500">Course Progress</div>
                    </div>
                    <div class="w-24 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full transition-all duration-300" 
                             style="width: {{ $courseProgress }}%"></div>
                    </div>
                </div>
                
                {{-- Navigation Buttons --}}
                <div class="flex items-center gap-2">
                    @if($previousLesson)
                        <a href="{{ route('lessons.view', $previousLesson) }}" wire:navigate
                           class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                           title="Previous Lesson (Ctrl+P)">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif
                    @if($nextLesson)
                        <a href="{{ route('lessons.view', $nextLesson) }}" wire:navigate
                           class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                           title="Next Lesson (Ctrl+N)">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                    @if($isLessonCompleted)
                        <flux:badge variant="success" size="sm">✓ Completed</flux:badge>
                    @else
                        <flux:button wire:click="completeLesson" variant="primary" size="sm">
                            Mark Complete
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-1 overflow-hidden">
        {{-- Course Outline Sidebar --}}
        <div x-show="showOutline" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="-translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="-translate-x-full opacity-0"
             class="fixed md:sticky top-[57px] left-0 z-30 w-80 h-[calc(100vh-57px)] bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto"
             style="display: none;">
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Course Outline</h2>
                    <button wire:click="toggleOutline" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    @foreach($allLessons as $moduleData)
                        <div>
                            <div class="flex items-center gap-2 mb-2 px-2">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Module {{ $moduleData['module']->order_index }}: {{ $moduleData['module']->title }}
                                </span>
                            </div>
                            <div class="space-y-1">
                                @foreach($moduleData['lessons'] as $lessonItem)
                                    <a href="{{ route('lessons.view', $lessonItem['id']) }}" wire:navigate
                                       class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors
                                              {{ $lessonItem['is_current'] 
                                                  ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500' 
                                                  : 'hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        <div class="flex-shrink-0">
                                            @if($lessonItem['is_completed'])
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @elseif($lessonItem['is_in_progress'])
                                                <div class="w-5 h-5 rounded-full border-2 border-blue-500 border-t-transparent animate-spin"></div>
                                            @else
                                                <div class="w-5 h-5 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $lessonItem['order_index'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="text-sm {{ $lessonItem['is_current'] ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }} truncate">
                                            {{ $lessonItem['title'] }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="flex-1 overflow-y-auto">
            <div class="container mx-auto px-4 py-6 max-w-7xl">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Main Content --}}
                    <div class="lg:col-span-2 space-y-6">
                        {{-- Lesson Header Card --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <a href="{{ route('courses.learn', $course) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                            </svg>
                                        </a>
                                        <div>
                                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h1>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                <a href="{{ route('courses.show', $course) }}" class="hover:underline">{{ $course->title }}</a>
                                                <span class="mx-2">•</span>
                                                <span>Module {{ $lesson->module->order_index }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    @if($lesson->description)
                                        <p class="text-gray-600 dark:text-gray-400">{{ $lesson->description }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Video Progress for Video Lessons --}}
                            @if($lesson->content_type === 'video' && $videoProgress > 0)
                                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span class="text-gray-700 dark:text-gray-300">Video Progress</span>
                                        <span class="font-semibold">{{ round($videoProgress) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                             style="width: {{ $videoProgress }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Learning Objectives --}}
                        @if($lesson->objectives)
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl shadow-lg border border-blue-200 dark:border-blue-800 p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Learning Objectives
                                </h2>
                                <div class="prose dark:prose-invert max-w-none">
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $lesson->objectives }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Completion Requirements Checklist --}}
                        @php
                            $requirements = $this->getCompletionRequirements();
                        @endphp
                        @if(count($requirements) > 0)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    Completion Requirements
                                </h2>
                                <div class="space-y-3">
                                    @foreach($requirements as $requirement)
                                        <div class="flex items-center gap-3 p-3 rounded-lg {{ $requirement['completed'] ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-700' }}">
                                            @if($requirement['completed'])
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded flex-shrink-0"></div>
                                            @endif
                                            <span class="{{ $requirement['completed'] ? 'text-green-700 dark:text-green-300 line-through' : 'text-gray-700 dark:text-gray-300' }}">
                                                {{ $requirement['label'] }}
                                            </span>
                                            @if(isset($requirement['id']))
                                                @if($requirement['type'] === 'assessment')
                                                    <a href="{{ route('assessments.show', $requirement['id']) }}" wire:navigate class="ml-auto text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                                        View →
                                                    </a>
                                                @elseif($requirement['type'] === 'assignment')
                                                    <a href="{{ route('assignments.show', $requirement['id']) }}" wire:navigate class="ml-auto text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                                        View →
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                @if(!$isLessonCompleted && count(array_filter($requirements, fn($r) => !$r['completed'])) > 0)
                                    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            ⚠️ Please complete all requirements before marking this lesson as complete.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Video Player --}}
                        @if(($lesson->video_url || $lesson->lesson_type === 'video') && $lesson->video_url)
                            <div class="bg-black rounded-xl shadow-lg overflow-hidden">
                                <div class="aspect-video">
                                    <video 
                                        id="lesson-video"
                                        class="w-full h-full"
                                        controls
                                        preload="metadata"
                                        @play="$wire.dispatch('video-started')"
                                        @timeupdate="handleVideoProgress()"
                                        @ended="handleVideoEnded()"
                                    >
                                        <source src="{{ $lesson->video_url }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const video = document.getElementById('lesson-video');
                                    if (video) {
                                        @if($videoWatchedSeconds > 0)
                                            video.currentTime = {{ $videoWatchedSeconds }};
                                        @endif

                                        let updateInterval;

                                        function handleVideoProgress() {
                                            const currentTime = video.currentTime;
                                            const duration = video.duration;
                                            
                                            if (duration > 0) {
                                                clearTimeout(updateInterval);
                                                updateInterval = setTimeout(() => {
                                                    @this.updateVideoProgress(
                                                        Math.floor(currentTime),
                                                        Math.floor(duration),
                                                        false
                                                    );
                                                }, 1000);
                                            }
                                        }

                                        function handleVideoEnded() {
                                            @this.updateVideoProgress(
                                                Math.floor(video.duration),
                                                Math.floor(video.duration),
                                                true
                                            );
                                            
                                            setTimeout(() => {
                                                @if(!$isLessonCompleted)
                                                    @this.completeLesson();
                                                @endif
                                            }, 500);
                                        }

                                        video.addEventListener('timeupdate', handleVideoProgress);
                                        video.addEventListener('ended', handleVideoEnded);
                                    }
                                });
                            </script>
                        @endif

                        {{-- Text Content with Table of Contents --}}
                        @if($lesson->content)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Lesson Content</h2>
                                    @php
                                        $hasHtml = strip_tags($lesson->content) !== $lesson->content;
                                        $content = $hasHtml ? $lesson->content : nl2br(e($lesson->content));
                                        // Simple TOC extraction for HTML content
                                        $tocItems = [];
                                        if ($hasHtml) {
                                            preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $lesson->content, $matches, PREG_SET_ORDER);
                                            foreach ($matches as $match) {
                                                $level = (int)$match[1];
                                                $text = strip_tags($match[2]);
                                                $id = 'heading-' . uniqid();
                                                $tocItems[] = ['level' => $level, 'text' => $text, 'id' => $id];
                                            }
                                        }
                                    @endphp
                                    @if(count($tocItems) > 0)
                                        <button onclick="document.getElementById('toc-sidebar').classList.toggle('hidden')" 
                                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                            📑 Table of Contents
                                        </button>
                                    @endif
                                </div>
                                
                                @if(count($tocItems) > 0)
                                    <div id="toc-sidebar" class="hidden md:block mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Contents</h3>
                                        <nav class="space-y-1">
                                            @foreach($tocItems as $item)
                                                <a href="#{{ $item['id'] }}" 
                                                   class="block text-sm text-blue-600 dark:text-blue-400 hover:underline pl-{{ ($item['level'] - 1) * 4 }}">
                                                    {{ $item['text'] }}
                                                </a>
                                            @endforeach
                                        </nav>
                                    </div>
                                @endif

                                <div class="prose prose-lg dark:prose-invert max-w-none 
                                            prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white
                                            prose-p:text-gray-700 dark:prose-p:text-gray-300 prose-p:leading-relaxed prose-p:mb-4
                                            {{ $hasHtml ? '' : 'whitespace-pre-wrap' }} break-words">
                                    {!! $content !!}
                                </div>
                            </div>
                        @endif

                        {{-- Enhanced Attachments Section --}}
                        @if($lesson->attachments && count(json_decode($lesson->attachments, true) ?? []) > 0)
                            @php
                                $attachments = json_decode($lesson->attachments, true);
                                $grouped = collect($attachments)->groupBy(function($item) {
                                    $ext = pathinfo($item['path'] ?? '', PATHINFO_EXTENSION);
                                    if (in_array($ext, ['pdf'])) return 'pdf';
                                    if (in_array($ext, ['doc', 'docx'])) return 'document';
                                    if (in_array($ext, ['zip', 'rar'])) return 'archive';
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) return 'image';
                                    return 'other';
                                });
                            @endphp
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Resources & Downloads
                                </h2>
                                <div class="space-y-4">
                                    @foreach($grouped as $type => $files)
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 capitalize">{{ $type }}s</h3>
                                            <div class="space-y-2">
                                                @foreach($files as $attachment)
                                                    <a href="{{ asset('storage/' . $attachment['path']) }}" target="_blank" 
                                                       class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                        <span class="text-gray-900 dark:text-white flex-1">{{ $attachment['name'] ?? 'Download' }}</span>
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Enhanced Sidebar --}}
                    <div class="space-y-6">
                        {{-- To-Do Summary --}}
                        @php
                            $todo = $this->getToDoSummary();
                        @endphp
                        @if($todo['total'] > 0)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    To-Do Summary
                                </h3>
                                <div class="space-y-3">
                                    @if($todo['pending_assessments'] > 0)
                                        <div class="flex items-center justify-between p-2 bg-blue-50 dark:bg-blue-900/20 rounded">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Pending Assessments</span>
                                            <flux:badge variant="primary" size="sm">{{ $todo['pending_assessments'] }}</flux:badge>
                                        </div>
                                    @endif
                                    @if($todo['pending_assignments'] > 0)
                                        <div class="flex items-center justify-between p-2 {{ $todo['overdue_assignments'] > 0 ? 'bg-red-50 dark:bg-red-900/20' : 'bg-purple-50 dark:bg-purple-900/20' }} rounded">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Pending Assignments</span>
                                            <flux:badge variant="{{ $todo['overdue_assignments'] > 0 ? 'danger' : 'primary' }}" size="sm">
                                                {{ $todo['pending_assignments'] }}
                                                @if($todo['overdue_assignments'] > 0)
                                                    ({{ $todo['overdue_assignments'] }} overdue)
                                                @endif
                                            </flux:badge>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Quick Stats Card --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quick Stats</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Time Spent</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $timeSpentMinutes }} min</span>
                                </div>
                                @if($lastAccessed)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Last Accessed</span>
                                        <span class="font-semibold text-gray-900 dark:text-white text-sm">
                                            {{ $lastAccessed->diffForHumans() }}
                                        </span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Status</span>
                                    @if($isLessonCompleted)
                                        <flux:badge variant="success" size="sm">Completed</flux:badge>
                                    @elseif($videoProgress > 0 || $timeSpentMinutes > 0)
                                        <flux:badge variant="primary" size="sm">In Progress</flux:badge>
                                    @else
                                        <flux:badge variant="ghost" size="sm">Not Started</flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Lesson Details --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Lesson Details</h3>
                            <div class="space-y-3">
                                @if($lesson->duration_minutes)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Duration</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $lesson->duration_minutes }} min</span>
                                    </div>
                                @endif
                                @if($lesson->points_reward)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">XP Reward</span>
                                        <span class="font-semibold text-yellow-600 dark:text-yellow-400">{{ $lesson->points_reward }} XP</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Type</span>
                                    <flux:badge size="sm" variant="primary">{{ ucfirst($lesson->content_type ?? $lesson->lesson_type ?? 'text') }}</flux:badge>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Difficulty</span>
                                    <flux:badge size="sm" variant="outline">{{ ucfirst($lesson->difficulty_level ?? 'N/A') }}</flux:badge>
                                </div>
                            </div>
                        </div>

                        {{-- Navigation --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Navigation</h3>
                            <div class="space-y-3">
                                @if($previousLesson)
                                    <a href="{{ route('lessons.view', $previousLesson) }}" wire:navigate
                                       class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Previous</p>
                                            <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $previousLesson->title }}</p>
                                        </div>
                                    </a>
                                @endif
                                @if($nextLesson)
                                    <a href="{{ route('lessons.view', $nextLesson) }}" wire:navigate
                                       class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <div class="flex-1 min-w-0 text-right">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Next</p>
                                            <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $nextLesson->title }}</p>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                @else
                                    <div class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm font-semibold text-green-800 dark:text-green-200">All lessons completed!</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Assessments (Existing implementation with enhancements) --}}
                        @if($lesson->assessments && $lesson->assessments->count() > 0)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span>📝</span>
                                    <span>Assessments</span>
                                </h3>
                                <div class="space-y-4">
                                    @foreach($lesson->assessments as $assessment)
                                        @php
                                            $typeInfo = [
                                                'quiz' => ['label' => 'Quiz', 'color' => 'bg-blue-500', 'icon' => '📝', 'desc' => 'Question-based assessment'],
                                                'assignment' => ['label' => 'Assignment', 'color' => 'bg-purple-500', 'icon' => '📄', 'desc' => 'File or text submission'],
                                                'unit_survey' => ['label' => 'Survey', 'color' => 'bg-green-500', 'icon' => '📊', 'desc' => 'Feedback collection'],
                                                'rubric_assessment' => ['label' => 'Rubric', 'color' => 'bg-orange-500', 'icon' => '📋', 'desc' => 'Criteria-based evaluation'],
                                                'peer_review' => ['label' => 'Peer Review', 'color' => 'bg-pink-500', 'icon' => '👥', 'desc' => 'Evaluate peers'],
                                                'self_assessment' => ['label' => 'Self-Assessment', 'color' => 'bg-indigo-500', 'icon' => '🔍', 'desc' => 'Reflect on learning'],
                                                'pre_project_test' => ['label' => 'Pre-Project Test', 'color' => 'bg-yellow-500', 'icon' => '⏮️', 'desc' => 'Baseline evaluation'],
                                                'post_project_test' => ['label' => 'Post-Project Test', 'color' => 'bg-yellow-600', 'icon' => '⏭️', 'desc' => 'Post-project evaluation'],
                                            ];
                                            $info = $typeInfo[$assessment->assessment_type] ?? ['label' => ucfirst(str_replace('_', ' ', $assessment->assessment_type)), 'color' => 'bg-gray-500', 'icon' => '📝', 'desc' => ''];
                                            $userAttempts = $assessment->attempts ?? collect();
                                            $bestAttempt = $userAttempts->sortByDesc('percentage_score')->first();
                                            $attemptCount = $userAttempts->count();
                                            $canTake = $assessment->max_attempts == 0 || $attemptCount < $assessment->max_attempts;
                                        @endphp
                                        <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="px-2 py-1 rounded text-xs font-semibold text-white {{ $info['color'] }}">
                                                            {{ $info['icon'] }} {{ $info['label'] }}
                                                        </span>
                                                        @if($assessment->is_required)
                                                            <span class="px-2 py-1 rounded text-xs font-semibold text-white bg-red-500">
                                                                Required
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-1">{{ $assessment->title }}</h4>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $info['desc'] }}</p>
                                                    @if($assessment->description)
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ \Illuminate\Support\Str::limit($assessment->description, 80) }}</p>
                                                    @endif
                                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                                        @if($assessment->time_limit_minutes)
                                                            <span>⏱️ {{ $assessment->time_limit_minutes }} min</span>
                                                        @endif
                                                        @if($assessment->questions && $assessment->questions->count() > 0)
                                                            <span>❓ {{ $assessment->questions->count() }} questions</span>
                                                        @endif
                                                        <span>⭐ {{ $assessment->xp_reward }} XP</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            @if($attemptCount > 0 && $bestAttempt)
                                                <div class="mb-3 p-2 bg-gray-50 dark:bg-gray-900 rounded text-sm">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-gray-600 dark:text-gray-400">Best Score:</span>
                                                        @php
                                                            $maxScore = ($assessment->questions && $assessment->questions->count() > 0) 
                                                                ? $assessment->questions->sum('points') 
                                                                : 100;
                                                            $attemptScore = $bestAttempt->score ?? 0;
                                                            $percentage = $maxScore > 0 ? ($attemptScore / $maxScore) * 100 : 0;
                                                        @endphp
                                                        <span class="font-bold {{ $bestAttempt->is_passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                            {{ number_format($percentage, 1) }}%
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                        {{ $attemptCount }} attempt(s)
                                                        @if($canTake && $assessment->max_attempts > 0)
                                                            • {{ $assessment->max_attempts - $attemptCount }} remaining
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <div class="flex items-center gap-2">
                                                <flux:button 
                                                    href="{{ route('assessments.show', $assessment) }}" 
                                                    wire:navigate 
                                                    variant="outline" 
                                                    size="sm"
                                                    class="flex-1">
                                                    View Details
                                                </flux:button>
                                                @if($canTake)
                                                    <flux:button 
                                                        href="{{ route('assessments.take', $assessment) }}" 
                                                        wire:navigate 
                                                        variant="primary" 
                                                        size="sm"
                                                        class="flex-1">
                                                        {{ $attemptCount > 0 ? 'Retake' : 'Start' }}
                                                    </flux:button>
                                                @else
                                                    <flux:badge variant="danger" size="sm">Max Attempts</flux:badge>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Assignments (Existing implementation) --}}
                        @if($lesson->assignments && $lesson->assignments->count() > 0)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span>📄</span>
                                    <span>Assignments</span>
                                </h3>
                                <div class="space-y-4">
                                    @foreach($lesson->assignments as $assignment)
                                        @php
                                            $userSubmission = $assignment->submissions->first() ?? null;
                                            $isSubmitted = $userSubmission && $userSubmission->submitted_at;
                                            $isGraded = $userSubmission && $userSubmission->graded_at;
                                            $isOverdue = $assignment->due_date && $assignment->due_date->isPast() && !$isSubmitted;
                                        @endphp
                                        <div class="border-2 {{ $isSubmitted ? 'border-green-500 dark:border-green-400' : ($isOverdue ? 'border-red-500 dark:border-red-400' : 'border-gray-200 dark:border-gray-700') }} rounded-lg p-4 hover:border-purple-500 dark:hover:border-purple-400 transition-colors">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="px-2 py-1 rounded text-xs font-semibold text-white bg-purple-500">
                                                            📄 Assignment
                                                        </span>
                                                        @if($isSubmitted)
                                                            <span class="px-2 py-1 rounded text-xs font-semibold text-white {{ $isGraded ? 'bg-green-500' : 'bg-yellow-500' }}">
                                                                {{ $isGraded ? '✓ Graded' : 'Submitted' }}
                                                            </span>
                                                        @endif
                                                        @if($isOverdue)
                                                            <span class="px-2 py-1 rounded text-xs font-semibold text-white bg-red-500">
                                                                Overdue
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-1">{{ $assignment->title }}</h4>
                                                    @if($assignment->description)
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ \Illuminate\Support\Str::limit($assignment->description, 80) }}</p>
                                                    @endif
                                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                                        @if($assignment->due_date)
                                                            <span class="{{ $isOverdue ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                                                                📅 Due: {{ $assignment->due_date->format('M d, Y') }}
                                                            </span>
                                                        @endif
                                                        <span>⭐ {{ $assignment->max_points ?? 100 }} points</span>
                                                    </div>
                                                    @if($isGraded && $userSubmission->points_earned !== null)
                                                        <div class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 rounded">
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-sm text-gray-600 dark:text-gray-400">Grade:</span>
                                                                <span class="font-bold text-green-600 dark:text-green-400">
                                                                    {{ number_format($userSubmission->points_earned, 1) }} / {{ $assignment->max_points ?? 100 }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <flux:button 
                                                href="{{ route('assignments.show', $assignment) }}" 
                                                wire:navigate 
                                                variant="{{ $isSubmitted ? 'outline' : 'primary' }}" 
                                                size="sm"
                                                class="w-full">
                                                {{ $isSubmitted ? ($isGraded ? 'View Grade' : 'View Submission') : 'Submit Assignment' }}
                                            </flux:button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session()->has('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('message') }}
    </div>
@endif


