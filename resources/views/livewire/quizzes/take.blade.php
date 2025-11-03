<div class="flex flex-col gap-6 p-6">
    @if(!$showResults)
        <!-- Quiz Header -->
        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ $quiz->title }}</h1>
                    <p class="text-blue-100">{{ $quiz->description }}</p>
                </div>
                <div class="text-right">
                    @if($timeRemaining !== null)
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2 mb-2">
                            <p class="text-sm text-blue-100">Time Remaining</p>
                            <p class="text-2xl font-bold" x-data="{ time: {{ $timeRemaining }} }" x-init="setInterval(() => { if(time > 0) time--; else $wire.submitQuiz(); }, 1000)">
                                <span x-text="Math.floor(time / 60)"></span>:<span x-text="String(time % 60).padStart(2, '0')"></span>
                            </p>
                        </div>
                    @endif
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                        <p class="text-sm text-blue-100">Question</p>
                        <p class="text-xl font-bold">{{ $currentQuestionIndex + 1 }} / {{ $totalQuestions }}</p>
                    </div>
                </div>
            </div>

            @if($quiz->instructions)
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mt-4">
                    <p class="text-sm font-medium mb-1">Instructions:</p>
                    <p class="text-sm text-blue-50">{{ $quiz->instructions }}</p>
                </div>
            @endif

            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="flex items-center justify-between text-xs text-blue-100 mb-2">
                    <span>Progress</span>
                    <span>{{ $answeredCount }} / {{ $totalQuestions }} answered ({{ $progress }}%)</span>
                </div>
                <div class="h-3 w-full bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        </div>

        <!-- Question Navigation -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4">
            <div class="flex flex-wrap gap-2">
                @foreach($questions as $index => $question)
                    @php
                        $isAnswered = isset($answers[$question->id]);
                        $isCurrent = $index === $currentQuestionIndex;
                    @endphp
                    <button 
                        wire:click="goToQuestion({{ $index }})"
                        class="w-10 h-10 rounded-lg font-medium transition {{ $isCurrent ? 'bg-blue-500 text-white ring-2 ring-blue-300' : ($isAnswered ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600') }}">
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Current Question -->
        @if($currentQuestion)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
                <div class="mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <flux:badge variant="primary">Question {{ $currentQuestionIndex + 1 }}</flux:badge>
                                <flux:badge variant="ghost">{{ $currentQuestion->points }} points</flux:badge>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                                {{ $currentQuestion->question_text }}
                            </h2>
                        </div>
                    </div>

                    @if($currentQuestion->image_url)
                        <div class="mb-6">
                            <img src="{{ asset('storage/' . $currentQuestion->image_url) }}" 
                                 alt="{{ $currentQuestion->image_alt_text ?? 'Question image' }}" 
                                 class="rounded-lg max-w-full">
                        </div>
                    @endif

                    <!-- Answer Options -->
                    @if(in_array($currentQuestion->question_type, ['multiple_choice', 'true_false']) && $currentQuestion->options && $currentQuestion->options->isNotEmpty())
                        @php
                            $isMultiple = $currentQuestion->question_type === 'multiple_choice';
                            $userAnswer = $answers[$currentQuestion->id] ?? null;
                        @endphp
                        <div class="space-y-3">
                            @foreach($currentQuestion->options as $option)
                                @php
                                    $isSelected = $isMultiple 
                                        ? (is_array($userAnswer) && in_array($option->id, $userAnswer))
                                        : ($userAnswer == $option->id);
                                @endphp
                                <label class="flex items-start gap-4 p-4 rounded-lg border-2 cursor-pointer transition {{ $isSelected ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600' }}">
                                    @if($isMultiple)
                                        <input 
                                            type="checkbox" 
                                            wire:change="updateMultipleAnswer({{ $currentQuestion->id }}, {{ $option->id }}, $event.target.checked)"
                                            {{ $isSelected ? 'checked' : '' }}
                                            class="mt-1 h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                    @else
                                        <input 
                                            type="radio" 
                                            wire:change="updateAnswer({{ $currentQuestion->id }}, {{ $option->id }})"
                                            name="question_{{ $currentQuestion->id }}"
                                            value="{{ $option->id }}"
                                            {{ $isSelected ? 'checked' : '' }}
                                            class="mt-1 h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    @endif
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $option->option_text }}</p>
                                        @if($option->explanation)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $option->explanation }}</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @elseif($currentQuestion->question_type === 'short_answer')
                        <flux:field>
                            <flux:textarea 
                                wire:model="answers.{{ $currentQuestion->id }}"
                                rows="4"
                                placeholder="Enter your answer..." />
                        </flux:field>
                    @elseif($currentQuestion->options && $currentQuestion->options->isNotEmpty())
                        @php
                            $isMultiple = $currentQuestion->question_type === 'multiple_choice';
                            $userAnswer = $answers[$currentQuestion->id] ?? null;
                        @endphp
                        <div class="space-y-3">
                            @foreach($currentQuestion->options as $option)
                                @php
                                    $isSelected = $isMultiple 
                                        ? (is_array($userAnswer) && in_array($option->id, $userAnswer))
                                        : ($userAnswer == $option->id);
                                @endphp
                                <label class="flex items-start gap-4 p-4 rounded-lg border-2 cursor-pointer transition {{ $isSelected ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600' }}">
                                    @if($isMultiple)
                                        <input 
                                            type="checkbox" 
                                            wire:change="updateMultipleAnswer({{ $currentQuestion->id }}, {{ $option->id }}, $event.target.checked)"
                                            {{ $isSelected ? 'checked' : '' }}
                                            class="mt-1 h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                    @else
                                        <input 
                                            type="radio" 
                                            wire:change="updateAnswer({{ $currentQuestion->id }}, {{ $option->id }})"
                                            name="question_{{ $currentQuestion->id }}"
                                            value="{{ $option->id }}"
                                            {{ $isSelected ? 'checked' : '' }}
                                            class="mt-1 h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    @endif
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $option->option_text }}</p>
                                        @if($option->explanation)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $option->explanation }}</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <p class="text-yellow-800 dark:text-yellow-200">
                                <strong>Notice:</strong> This question type may not have display options configured.
                            </p>
                        </div>
                    @endif

                    @if($currentQuestion->explanation)
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <span class="font-semibold">Hint:</span> {{ $currentQuestion->explanation }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <flux:button 
                        wire:click="previousQuestion" 
                        variant="ghost"
                        :disabled="$currentQuestionIndex === 0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </flux:button>

                    <div class="flex items-center gap-3">
                        @if($answeredCount === $totalQuestions)
                            <flux:button 
                                wire:click="submitQuiz" 
                                variant="primary"
                                wire:confirm="Are you sure you want to submit? You cannot change your answers after submission.">
                                Submit Quiz
                            </flux:button>
                        @else
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $totalQuestions - $answeredCount }} questions remaining
                            </p>
                        @endif
                    </div>

                    <flux:button 
                        wire:click="nextQuestion" 
                        variant="ghost"
                        :disabled="$currentQuestionIndex === $totalQuestions - 1">
                        Next
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </flux:button>
                </div>
            </div>
        @endif
    @else
        <!-- Results View -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 text-center">
            <div class="mb-8">
                @if($isPassed)
                    <div class="mx-auto w-24 h-24 rounded-full bg-green-500 flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">Congratulations! 🎉</h2>
                    <p class="text-xl text-gray-600 dark:text-gray-400">You passed the quiz!</p>
                @else
                    <div class="mx-auto w-24 h-24 rounded-full bg-red-500 flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-red-600 dark:text-red-400 mb-2">Keep Studying! 📚</h2>
                    <p class="text-xl text-gray-600 dark:text-gray-400">You scored {{ number_format($score, 1) }}%. Minimum passing score is {{ $quiz->passing_score }}%.</p>
                @endif
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Your Score</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($score, 1) }}%</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Passing Score</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($quiz->passing_score, 1) }}%</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Status</p>
                        <flux:badge variant="{{ $isPassed ? 'success' : 'danger' }}" size="lg">
                            {{ $isPassed ? 'Passed' : 'Failed' }}
                        </flux:badge>
                    </div>
                </div>
            </div>

            @if($quiz->allow_review && $quiz->show_correct_answers)
                <div class="mb-6">
                    <flux:button wire:click="$set('showResults', false)" variant="ghost">
                        Review Answers
                    </flux:button>
                </div>
            @endif

            <div class="flex items-center justify-center gap-4">
                <flux:button href="{{ route('quizzes.index') }}" variant="ghost" wire:navigate>
                    Back to Quizzes
                </flux:button>
                @if(!$isPassed && ($attempt->attempt_number ?? 0) < $quiz->max_attempts)
                    <flux:button wire:click="$refresh" variant="primary">
                        Retake Quiz
                    </flux:button>
                @endif
            </div>
        </div>
    @endif
</div>
