@props(['lesson'])

@if(!empty($lesson->lesson_steps) && is_array($lesson->lesson_steps) && ($lesson->lesson_type === 'interactive' || $lesson->scratch_project_id))
    <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl shadow-lg border-2 border-purple-200 dark:border-purple-800 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Step-by-Step Guide
            </h2>
            <span class="text-xs text-purple-600 dark:text-purple-400 font-semibold bg-purple-100 dark:bg-purple-900/40 px-2 py-1 rounded">
                {{ count($lesson->lesson_steps) }} Steps
            </span>
        </div>
        <div class="space-y-2">
            @foreach($lesson->lesson_steps as $index => $step)
                <x-lesson-step
                    :number="$index + 1"
                    :title="$step['title'] ?? 'Step ' . ($index + 1)"
                    :image="$step['image'] ?? null"
                    :tryItUrl="$step['try_it_url'] ?? null"
                >
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $step['description'] ?? $step['content'] ?? '' }}
                    </p>
                    @if(!empty($step['code']))
                        <pre class="mt-2 p-3 bg-gray-100 dark:bg-gray-900 rounded-lg overflow-x-auto text-xs"><code>{{ $step['code'] }}</code></pre>
                    @endif
                </x-lesson-step>
            @endforeach
        </div>
    </div>
@endif
