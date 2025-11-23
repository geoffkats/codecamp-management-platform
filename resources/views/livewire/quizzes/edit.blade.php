<div class="min-h-screen bg-gradient-to-br from-gray-50 via-purple-50 to-indigo-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 p-6">
    <div class="max-w-6xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="relative overflow-hidden bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-2xl shadow-2xl p-8 text-white">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h1 class="text-4xl font-bold">Edit Quiz</h1>
                    </div>
                    <p class="text-purple-100 text-lg">Update quiz settings and questions</p>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button href="{{ route('quizzes.show', $quiz) }}" icon="eye" variant="ghost" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white border-white/30" wire:navigate>
                        View Quiz
                    </flux:button>
                    <flux:button href="{{ route('quizzes.index') }}" icon="arrow-left" variant="ghost" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white border-white/30" wire:navigate>
                        Back
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- Main Form --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Quiz Settings --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Basic Information --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Basic Information
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <flux:label for="title" value="Quiz Title" required />
                            <flux:input id="title" type="text" wire:model="title" placeholder="Enter quiz title" />
                            <flux:error name="title" />
                        </div>

                        <div>
                            <flux:label for="lesson_id" value="Lesson" required />
                            <flux:select id="lesson_id" wire:model="lesson_id">
                                <option value="">Select a lesson</option>
                                @foreach($lessons as $lesson)
                                    <option value="{{ $lesson->id }}">{{ $lesson->course->title ?? 'Unknown Course' }} - {{ $lesson->title }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="lesson_id" />
                        </div>

                        <div>
                            <flux:label for="description" value="Description" />
                            <flux:textarea id="description" wire:model="description" rows="3" placeholder="Brief description of the quiz..." />
                            <flux:error name="description" />
                        </div>

                        <div>
                            <flux:label for="instructions" value="Instructions" />
                            <flux:textarea id="instructions" wire:model="instructions" rows="4" placeholder="Instructions for students taking this quiz..." />
                            <flux:error name="instructions" />
                        </div>
                    </div>
                </div>

                {{-- Quiz Settings --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Quiz Settings
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:label for="time_limit" value="Time Limit (minutes)" />
                            <flux:input id="time_limit" type="number" wire:model="time_limit" min="1" placeholder="Leave empty for no limit" />
                            <flux:error name="time_limit" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited time</p>
                        </div>

                        <div>
                            <flux:label for="max_attempts" value="Max Attempts" required />
                            <flux:input id="max_attempts" type="number" wire:model="max_attempts" min="1" max="10" />
                            <flux:error name="max_attempts" />
                        </div>

                        <div>
                            <flux:label for="passing_score" value="Passing Score (%)" required />
                            <flux:input id="passing_score" type="number" wire:model="passing_score" min="0" max="100" step="0.01" />
                            <flux:error name="passing_score" />
                        </div>

                        <div class="flex items-center pt-8">
                            <flux:checkbox id="is_published" wire:model="is_published" />
                            <flux:label for="is_published" value="Publish Quiz" class="ml-2" />
                            <flux:error name="is_published" />
                        </div>
                    </div>
                </div>

                {{-- Options --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Additional Options
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <div>
                                <flux:label for="is_randomized" value="Randomize Questions" class="font-semibold" />
                                <p class="text-sm text-gray-600 dark:text-gray-400">Questions will appear in random order</p>
                            </div>
                            <flux:checkbox id="is_randomized" wire:model="is_randomized" />
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <div>
                                <flux:label for="show_correct_answers" value="Show Correct Answers" class="font-semibold" />
                                <p class="text-sm text-gray-600 dark:text-gray-400">Display correct answers after submission</p>
                            </div>
                            <flux:checkbox id="show_correct_answers" wire:model="show_correct_answers" />
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <div>
                                <flux:label for="allow_review" value="Allow Review" class="font-semibold" />
                                <p class="text-sm text-gray-600 dark:text-gray-400">Students can review their answers</p>
                            </div>
                            <flux:checkbox id="allow_review" wire:model="allow_review" />
                        </div>
                    </div>
                </div>

                {{-- Questions List --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Questions ({{ count($questions) }})
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">- Total: {{ $totalPoints }} points</span>
                        </h2>
                        <flux:button href="{{ route('questions.create', ['quiz_id' => $quiz->id]) }}" icon="plus" wire:navigate>
                            Add Question
                        </flux:button>
                    </div>

                    @if(count($questions) > 0)
                        <div class="space-y-3">
                            @foreach($questions as $index => $question)
                                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Q{{ $index + 1 }}</span>
                                                <flux:badge size="sm">{{ ucfirst(str_replace('_', ' ', $question['question_type'] ?? 'multiple_choice')) }}</flux:badge>
                                                <flux:badge size="sm" color="green">{{ $question['points'] ?? 0 }} points</flux:badge>
                                            </div>
                                            <p class="text-gray-900 dark:text-white font-medium mb-2">{{ $question['question_text'] ?? 'No question text' }}</p>
                                            @if(isset($question['options']) && count($question['options']) > 0)
                                                <div class="mt-2 space-y-1">
                                                    @foreach($question['options'] as $option)
                                                        <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                                            <span class="w-4 h-4 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center">
                                                                @if($option['is_correct'] ?? false)
                                                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                                                @endif
                                                            </span>
                                                            {{ $option['option_text'] ?? '' }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 ml-4">
                                            <flux:button href="{{ route('questions.edit', $question['id']) }}" size="sm" variant="ghost" icon="pencil" wire:navigate>Edit</flux:button>
                                            <flux:button wire:click="deleteQuestion({{ $question['id'] }})" size="sm" variant="ghost" color="red" icon="trash" onclick="return confirm('Are you sure?')">Delete</flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">No questions added yet</p>
                            <flux:button href="{{ route('questions.create', ['quiz_id' => $quiz->id]) }}" icon="plus" wire:navigate>
                                Add First Question
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column - Summary & Actions --}}
            <div class="space-y-6">
                {{-- Quiz Summary --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quiz Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total Questions</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ count($questions) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total Points</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $totalPoints }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Time Limit</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ $time_limit ? $time_limit . ' min' : 'Unlimited' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Max Attempts</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $max_attempts }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Passing Score</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($passing_score, 1) }}%</span>
                        </div>
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                                <flux:badge :color="$is_published ? 'green' : 'gray'">
                                    {{ $is_published ? 'Published' : 'Draft' }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="space-y-3">
                        <flux:button type="submit" wire:click="save" icon="check" class="w-full" size="lg">
                            Save Changes
                        </flux:button>
                        <flux:button href="{{ route('quizzes.show', $quiz) }}" variant="ghost" class="w-full" wire:navigate>
                            Cancel
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>

        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('message') }}
            </div>
        @endif
    </div>
</div>
