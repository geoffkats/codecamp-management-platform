<div class="flex flex-col gap-6 p-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Lesson locks</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Lock a lesson so students cannot open it or take its quizzes until class is ready. Unlock when you want them to work.
            </p>
        </div>
    </div>

    @if($message)
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200">
            {{ $message }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Course</label>
        <select wire:model.live="courseId"
                class="w-full max-w-xl px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 dark:bg-gray-700 dark:text-white">
            @forelse($courses as $option)
                <option value="{{ $option->id }}">{{ $option->title }}</option>
            @empty
                <option value="">No courses assigned</option>
            @endforelse
        </select>
    </div>

    @if($course)
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-3 text-sm">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 dark:bg-green-900/20 px-3 py-1 font-semibold text-green-700 dark:text-green-300">
                    {{ $unlockedCount }} open
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 dark:bg-red-900/20 px-3 py-1 font-semibold text-red-700 dark:text-red-300">
                    {{ $lockedCount }} locked
                </span>
            </div>
            <div class="sm:ml-auto flex flex-wrap gap-2">
                <button type="button" wire:click="unlockAll"
                        class="px-3 py-2 text-sm font-semibold rounded-lg bg-green-600 hover:bg-green-700 text-white">
                    Unlock all
                </button>
                <button type="button" wire:click="lockAll"
                        wire:confirm="Lock every lesson on this course? Students will not be able to open lessons or quizzes until you unlock them."
                        class="px-3 py-2 text-sm font-semibold rounded-lg bg-red-600 hover:bg-red-700 text-white">
                    Lock all
                </button>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($modules as $module)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white">{{ $module->title }}</h2>
                    </div>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($module->lessons as $lesson)
                            <li class="flex flex-col gap-3 sm:flex-row sm:items-center px-4 py-3" wire:key="lock-lesson-{{ $lesson->id }}">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $lesson->title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $lesson->assessments_count }} quiz{{ $lesson->assessments_count === 1 ? '' : 'zes' }} / assignment{{ $lesson->assessments_count === 1 ? '' : 's' }}
                                        ·
                                        @if($lesson->is_locked)
                                            Students cannot open this yet
                                        @else
                                            Students can work on this now
                                        @endif
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                                    <button type="button"
                                            wire:click="unlockOnly({{ $lesson->id }})"
                                            class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Only this open
                                    </button>
                                    <button type="button"
                                            wire:click="toggleLesson({{ $lesson->id }})"
                                            class="px-3 py-1.5 text-xs font-semibold rounded-lg text-white {{ $lesson->is_locked ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                                        {{ $lesson->is_locked ? 'Unlock' : 'Lock' }}
                                    </button>
                                </div>
                            </li>
                        @empty
                            <li class="px-4 py-6 text-sm text-gray-500">No lessons in this module.</li>
                        @endforelse
                    </ul>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center text-gray-500">
                    This course has no modules yet.
                </div>
            @endforelse
        </div>
    @endif
</div>
