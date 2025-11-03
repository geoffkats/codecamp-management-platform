<div class="flex flex-col gap-6 p-6">
    {{-- Course Header --}}
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-xl shadow-lg p-8 text-white">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('courses.show', $course) }}" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold">{{ $course->title }}</h1>
                </div>
                <p class="text-blue-100 mb-4">{{ $course->description }}</p>
                <div class="flex items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Instructor: {{ $course->instructor->name }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>{{ $totalLessons }} Lessons</span>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-4xl font-bold">{{ $courseProgress }}%</div>
                <div class="text-blue-100 text-sm">Complete</div>
            </div>
        </div>
        {{-- Progress Bar --}}
        <div class="mt-6">
            <div class="w-full bg-white/20 rounded-full h-3 overflow-hidden">
                <div class="h-full bg-white rounded-full transition-all duration-500" 
                     style="width: {{ $courseProgress }}%"></div>
            </div>
            <div class="flex justify-between text-sm text-blue-100 mt-2">
                <span>{{ $completedLessons }} of {{ $totalLessons }} lessons completed</span>
                <span>{{ round($courseProgress) }}%</span>
            </div>
        </div>
    </div>

    {{-- Course Modules and Lessons --}}
    <div class="space-y-6">
        @foreach($modules as $module)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $module->order_index }}</span>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $module->title }}</h2>
                                @if($module->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $module->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $module->lessons->filter(fn($l) => $this->isLessonCompleted($l->id))->count() }} / {{ $module->lessons->count() }} completed
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($module->lessons as $lesson)
                        @php
                            $progress = $this->getLessonProgress($lesson->id);
                            $isCompleted = $this->isLessonCompleted($lesson->id);
                        @endphp
                        <a href="{{ route('lessons.view', $lesson) }}" wire:navigate class="block">
                            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0">
                                        @if($isCompleted)
                                            <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        @elseif($progress)
                                            <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                <span class="text-gray-600 dark:text-gray-400 font-semibold">{{ $lesson->order_index }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $lesson->title }}</h3>
                                            @if($lesson->video_url || $lesson->lesson_type === 'video')
                                                <flux:badge size="xs" variant="primary">Video</flux:badge>
                                            @else
                                                <flux:badge size="xs" variant="ghost">Content</flux:badge>
                                            @endif
                                        </div>
                                        @if($lesson->description)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">{{ $lesson->description }}</p>
                                        @endif
                                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                            @if($lesson->duration_minutes)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $lesson->duration_minutes }} min
                                                </span>
                                            @endif
                                            @php
                                                $points = 10;
                                                if ($lesson->difficulty_level) {
                                                    $points = match(strtolower($lesson->difficulty_level)) {
                                                        'beginner' => 5,
                                                        'intermediate' => 10,
                                                        'advanced' => 15,
                                                        default => 10,
                                                    };
                                                }
                                            @endphp
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $points }} XP
                                            </span>
                                            @if($progress && $progress->started_at)
                                                <span>Started {{ $progress->started_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        @if($isCompleted)
                                            <flux:badge variant="success">Completed</flux:badge>
                                        @elseif($progress)
                                            <flux:button variant="primary" size="sm">Continue</flux:button>
                                        @else
                                            <flux:button variant="ghost" size="sm">Start</flux:button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

