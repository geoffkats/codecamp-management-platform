@php
    $totalXP = $completedLessons * 10;
    $allLessons = collect();
    foreach ($modules as $module) {
        foreach ($module->lessons as $lesson) {
            $allLessons->push($lesson);
        }
    }
    $nextLessonId = null;
    foreach ($allLessons as $lesson) {
        if ($lesson->is_locked) {
            continue;
        }
        if (!$this->isLessonCompleted($lesson->id)) {
            $nextLessonId = $lesson->id;
            break;
        }
    }
    $lessonOrder = $allLessons->pluck('id')->flip();
    $nextLesson = $nextLessonId ? $allLessons->firstWhere('id', $nextLessonId) : null;
@endphp

<div class="p-6 space-y-8">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div class="space-y-2">
                <a href="{{ route('courses.show', $course) }}" class="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Back to course</a>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $course->title }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">Instructor: {{ $course->instructor->name }}</p>
            </div>
            @if($nextLesson)
                <flux:button href="{{ route('lessons.view', $nextLesson) }}" variant="primary" size="sm" wire:navigate>
                    Resume Course
                </flux:button>
            @endif
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                <p class="text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">Course Progress</p>
                <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ round($courseProgress) }}%</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $completedLessons }} of {{ $totalLessons }} lessons</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                <p class="text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">XP Earned</p>
                <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalXP) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                <p class="text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">Streak</p>
                <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">0 days</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                <p class="text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">Next Lesson</p>
                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $nextLesson?->title ?? 'All lessons completed' }}
                </p>
            </div>
        </div>

        <details class="mt-6 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
            <summary class="cursor-pointer text-sm font-semibold text-gray-900 dark:text-white">About this course</summary>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                {{ $course->description }}
            </p>
        </details>
    </div>

    <div class="space-y-6">
        @foreach($modules as $module)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $module->title }}</h2>
                            @if($module->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $module->description }}</p>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $module->lessons->count() }} lessons</p>
                    </div>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($module->lessons as $lesson)
                        @php
                            $progress = $this->getLessonProgress($lesson->id);
                            $isCompleted = $this->isLessonCompleted($lesson->id);
                            $isCurrent = $nextLessonId && $lesson->id === $nextLessonId;

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
                        <div class="px-6 py-4 {{ $isCurrent ? 'bg-amber-50 dark:bg-amber-950/20' : '' }}">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $lesson->order_index }}.</span>
                                        <a href="{{ route('lessons.view', $lesson) }}" wire:navigate class="text-base font-semibold text-gray-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400">
                                            {{ $lesson->title }}
                                        </a>
                                        @if($lesson->is_locked)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700 dark:text-amber-300">Locked</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $points }} XP</span>
                                        @if($lesson->duration_minutes)
                                            <span>{{ $lesson->duration_minutes }} min</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                        @if($lesson->is_locked)
                                            Locked
                                        @elseif($isCompleted)
                                            Completed
                                        @elseif($isCurrent)
                                            Current
                                        @else
                                            Not started
                                        @endif
                                    </span>
                                    @if($isCurrent && !$lesson->is_locked)
                                        <flux:button href="{{ route('lessons.view', $lesson) }}" variant="primary" size="sm" wire:navigate>
                                            Continue
                                        </flux:button>
                                    @endif
                                </div>
                            </div>

                            @if($isCurrent && $progress && !$isCompleted)
                                <div class="mt-3">
                                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-800">
                                        <div class="h-2 rounded-full bg-amber-500" style="width: {{ $progress->progress_percentage ?? 0 }}%"></div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ round($progress->progress_percentage ?? 0) }}% complete
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
