<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $module->title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $module->course?->title ?? 'Course' }}</p>
            </div>
            <a href="{{ route('modules.index') }}" wire:navigate class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg">Back</a>
        </div>
    </div>

    <div class="p-6 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Module Overview</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $module->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $module->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Lessons</p>
                    <p class="text-gray-900 dark:text-white font-medium">{{ $module->lessons->count() }}</p>
                </div>
                @if($module->estimated_duration_hours)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Estimated Duration</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $module->estimated_duration_hours }} hours</p>
                    </div>
                @endif
                @if($module->approval_status)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Approval Status</p>
                        <p class="text-gray-900 dark:text-white font-medium">{{ ucfirst($module->approval_status) }}</p>
                    </div>
                @endif
            </div>
            @if($module->overview)
                <div class="px-6 pb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{!! $module->overview !!}</p>
                </div>
            @elseif($module->description)
                <div class="px-6 pb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $module->description }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lessons</h2>
            </div>
            <div class="p-6">
                @if($module->lessons->count() > 0)
                    <div class="space-y-3">
                        @foreach($module->lessons as $lesson)
                            <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $lesson->title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($lesson->lesson_type ?? 'lesson') }}</p>
                                </div>
                                <a href="{{ route('lessons.show', $lesson->id) }}" wire:navigate class="text-blue-600 hover:text-blue-700 text-sm">View</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">No lessons assigned yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
