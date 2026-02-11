<div class="max-w-5xl mx-auto p-6 space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Assessment Result</p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $assessment->title }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Submitted {{ $attempt->completed_at?->format('M d, Y H:i') ?? '—' }}
                </p>
            </div>
            <div class="text-right">
                @if($isPending)
                    <div class="text-yellow-600 dark:text-yellow-400 text-xl font-semibold">Pending Review</div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Your submission is awaiting grading.</p>
                @else
                    <div class="text-3xl font-bold {{ $attempt->is_passed ? 'text-green-600' : 'text-red-600' }}">
                        {{ $percentage !== null ? number_format($percentage, 1) : '—' }}%
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Passing score: {{ $assessment->passing_score }}%
                    </p>
                @endif
            </div>
        </div>

        @if(!$isPending && $percentage !== null)
            <div class="mt-4">
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                    <div class="h-3 rounded-full {{ $attempt->is_passed ? 'bg-green-500' : 'bg-red-500' }}" style="width: {{ min($percentage, 100) }}%"></div>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Score</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">
                @if($isPending)
                    —
                @else
                    {{ $attempt->score ?? '—' }}
                @endif
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Max Points</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ $maxScore }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Time Spent</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ $attempt->time_spent ? $attempt->time_spent . ' mins' : '—' }}
            </p>
        </div>
    </div>

    @if(!$isPending && count($incorrectQuestions) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Questions to Review</h2>
            <div class="space-y-4">
                @foreach($incorrectQuestions as $item)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $item['question'] }}</p>
                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <p><span class="font-medium text-gray-800 dark:text-gray-300">Your answer:</span> {{ $item['your_answer'] }}</p>
                            <p><span class="font-medium text-gray-800 dark:text-gray-300">Correct answer:</span> {{ $item['correct_answer'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(!$isPending && count($correctQuestions) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Questions You Got Right</h2>
            <div class="space-y-4">
                @foreach($correctQuestions as $item)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $item['question'] }}</p>
                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <p><span class="font-medium text-gray-800 dark:text-gray-300">Your answer:</span> {{ $item['your_answer'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6 flex flex-wrap gap-3 justify-end">
        <flux:button href="{{ route('assessments.show', $assessment) }}" wire:navigate variant="ghost">
            Back to Assessment
        </flux:button>
        <flux:button 
            href="{{ $assessment->lesson
                ? route('lessons.view', $assessment->lesson)
                : (auth()->user()->isIctTeacher() ? route('modules.index') : route('courses.show', $assessment->course)) }}" 
            wire:navigate 
            variant="primary">
            {{ $assessment->lesson ? 'Back to Lesson' : 'Back to Course' }}
        </flux:button>
    </div>
</div>
