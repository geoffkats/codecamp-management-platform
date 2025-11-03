<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Question Bank</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Browse and manage all questions</p>
        </div>
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('questions.create') }}" wire:navigate variant="primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Question
            </flux:button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Search --}}
            <div class="md:col-span-2">
                <flux:field>
                    <flux:label>Search Questions</flux:label>
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by question text..." />
                </flux:field>
            </div>

            {{-- Type Filter --}}
            <div>
                <flux:field>
                    <flux:label>Type</flux:label>
                    <flux:select wire:model.live="filterType">
                        <option value="all">All Types</option>
                        @foreach($questionTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            {{-- Quiz Filter --}}
            <div>
                <flux:field>
                    <flux:label>Quiz</flux:label>
                    <flux:select wire:model.live="filterQuiz">
                        <option value="all">All Quizzes</option>
                        @foreach($quizzes as $quiz)
                            <option value="{{ $quiz->id }}">{{ $quiz->title }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
        </div>
    </div>

    {{-- Questions List --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($questions as $question)
                <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ Str::limit($question->question_text, 150) }}
                                </h3>
                                <flux:badge size="sm" variant="primary">
                                    {{ $questionTypes[$question->question_type] ?? $question->question_type }}
                                </flux:badge>
                                @if($question->points)
                                    <flux:badge size="sm" variant="ghost">
                                        {{ $question->points }} pts
                                    </flux:badge>
                                @endif
                            </div>
                            
                            <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mt-2">
                                @if($question->quiz)
                                    <span>Quiz: {{ $question->quiz->title }}</span>
                                @endif
                                @if($question->assessment)
                                    <span>Assessment: {{ $question->assessment->title }}</span>
                                @endif
                                @if($question->options->count() > 0)
                                    <span>{{ $question->options->count() }} options</span>
                                @endif
                            </div>

                            {{-- Options Preview --}}
                            @if($question->options->count() > 0 && in_array($question->question_type, ['multiple_choice', 'multiple_select']))
                                <div class="mt-3 space-y-1">
                                    @foreach($question->options->take(3) as $option)
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="w-2 h-2 rounded-full {{ $option->is_correct ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                            <span class="text-gray-600 dark:text-gray-400">{{ Str::limit($option->option_text, 80) }}</span>
                                            @if($option->is_correct)
                                                <flux:badge size="xs" variant="success">Correct</flux:badge>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if($question->options->count() > 3)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            +{{ $question->options->count() - 3 }} more options
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 ml-4">
                            <flux:button href="{{ route('questions.edit', $question) }}" wire:navigate variant="ghost" size="sm">
                                Edit
                            </flux:button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400 text-lg">No questions found</p>
                    <p class="text-gray-500 dark:text-gray-500 text-sm mt-2">Try adjusting your filters or create a new question</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
            {{ $questions->links() }}
        </div>
    </div>
</div>
