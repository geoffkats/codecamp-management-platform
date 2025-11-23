<div class="flex flex-col gap-6 p-6">
    {{-- Course Header --}}
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-xl shadow-lg p-8 text-white">
        <div class="flex items-start justify-between mb-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('courses.show', $course) }}" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold">{{ $course->title }}</h1>
                </div>
                <p class="text-blue-100 mb-4">{{ $course->description }}</p>
                <div class="flex items-center gap-6 text-sm flex-wrap">
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
                    @php
                        // Calculate total XP earned
                        $totalXP = $completedLessons * 10; // Base calculation, can be enhanced
                    @endphp
                    <div class="flex items-center gap-2 bg-white/20 rounded-full px-3 py-1">
                        <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="font-bold">{{ number_format($totalXP) }} XP</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col items-end gap-3">
                <div class="text-right">
                    <div class="text-4xl font-bold">{{ $courseProgress }}%</div>
                    <div class="text-blue-100 text-sm">Complete</div>
                </div>
                {{-- Streak Counter (placeholder - can be enhanced with real data) --}}
                <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2 flex items-center gap-2">
                    <span class="text-2xl">🔥</span>
                    <div class="text-left">
                        <div class="text-xl font-bold leading-none">0</div>
                        <div class="text-xs text-blue-100">Day Streak</div>
                    </div>
                </div>
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
                            
                            // Determine lesson subject/type for icon
                            $lessonIcon = '📚';
                            $iconBg = 'bg-gray-100 dark:bg-gray-700';
                            $iconColor = 'text-gray-600 dark:text-gray-400';
                            
                            if ($lesson->scratch_project_id || stripos($lesson->title, 'scratch') !== false) {
                                $lessonIcon = '🟦'; // Scratch
                                $iconBg = 'bg-orange-100 dark:bg-orange-900/30';
                                $iconColor = 'text-orange-600 dark:text-orange-400';
                            } elseif (stripos($lesson->title, 'python') !== false || stripos($lesson->content ?? '', 'python') !== false) {
                                $lessonIcon = '🐍'; // Python
                                $iconBg = 'bg-blue-100 dark:bg-blue-900/30';
                                $iconColor = 'text-blue-600 dark:text-blue-400';
                            } elseif (stripos($lesson->title, 'web') !== false || stripos($lesson->title, 'html') !== false || stripos($lesson->title, 'css') !== false) {
                                $lessonIcon = '🌐'; // Web Dev
                                $iconBg = 'bg-green-100 dark:bg-green-900/30';
                                $iconColor = 'text-green-600 dark:text-green-400';
                            } elseif ($lesson->video_url || $lesson->lesson_type === 'video') {
                                $lessonIcon = '🎥'; // Video
                                $iconBg = 'bg-purple-100 dark:bg-purple-900/30';
                                $iconColor = 'text-purple-600 dark:text-purple-400';
                            } elseif ($lesson->lesson_type === 'interactive') {
                                $lessonIcon = '⚡'; // Interactive
                                $iconBg = 'bg-yellow-100 dark:bg-yellow-900/30';
                                $iconColor = 'text-yellow-600 dark:text-yellow-400';
                            }
                        @endphp
                        <a href="{{ route('lessons.view', $lesson) }}" wire:navigate class="block">
                            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-200 cursor-pointer group">
                                <div class="flex items-center gap-4">
                                    {{-- Status/Subject Icon --}}
                                    <div class="flex-shrink-0">
                                        @if($isCompleted)
                                            <div class="w-16 h-16 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
                                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-16 h-16 rounded-xl {{ $iconBg }} flex flex-col items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
                                                <span class="text-3xl">{{ $lessonIcon }}</span>
                                                @if($lesson->order_index)
                                                    <span class="text-xs font-bold {{ $iconColor }} mt-0.5">{{ $lesson->order_index }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                                {{ $lesson->title }}
                                            </h3>
                                            @if($lesson->lesson_type === 'interactive')
                                                <flux:badge size="xs" class="bg-gradient-to-r from-purple-500 to-pink-500 text-white border-0">Interactive</flux:badge>
                                            @elseif($lesson->video_url || $lesson->lesson_type === 'video')
                                                <flux:badge size="xs" variant="primary">Video</flux:badge>
                                            @endif
                                            @if($lesson->difficulty_level)
                                                <flux:badge size="xs" variant="ghost">{{ ucfirst($lesson->difficulty_level) }}</flux:badge>
                                            @endif
                                        </div>
                                        @if($lesson->summary ?? $lesson->description)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                                                {{ $lesson->summary ?? $lesson->description }}
                                            </p>
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
                                            <span class="flex items-center gap-1 font-semibold text-yellow-600 dark:text-yellow-400">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                                {{ $points }} XP
                                            </span>
                                            @if($progress && $progress->started_at)
                                                <span>Started {{ $progress->started_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                        
                                        {{-- Progress Bar for In-Progress Lessons --}}
                                        @if($progress && !$isCompleted)
                                            <div class="mt-3">
                                                <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                                    <span>In Progress</span>
                                                    <span class="font-semibold">{{ round($progress->progress_percentage ?? 0) }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-full rounded-full transition-all duration-500" 
                                                         style="width: {{ $progress->progress_percentage ?? 0 }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-shrink-0 flex flex-col items-end gap-2">
                                        @if($isCompleted)
                                            <flux:badge variant="success" class="shadow-md">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Completed
                                            </flux:badge>
                                        @elseif($progress)
                                            <flux:button variant="primary" size="sm" class="shadow-md hover:shadow-lg transition-shadow">
                                                Continue →
                                            </flux:button>
                                        @else
                                            <flux:button variant="ghost" size="sm" class="group-hover:bg-purple-50 dark:group-hover:bg-purple-900/20 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                                Start Lesson
                                            </flux:button>
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

