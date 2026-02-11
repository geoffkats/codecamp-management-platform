@if($showResults)
    {{-- Results View --}}
    <div class="max-w-4xl mx-auto p-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-8">
            @if($assessment->assessment_type === 'assignment' && $attempt && $attempt->score === null)
                {{-- Assignment Pending Grading --}}
                <div class="text-center mb-6">
                    <div class="w-24 h-24 mx-auto mb-4 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900/30">
                        <svg class="w-12 h-12 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Assignment Submitted Successfully!
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">
                        Your submission is pending instructor review
                    </p>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                    <p class="text-yellow-800 dark:text-yellow-200 font-semibold">
                        Your assignment has been submitted and is awaiting instructor evaluation. You will be notified once it has been graded.
                    </p>
                </div>

                @if($attempt->answers && isset($attempt->answers['files']) && !empty($attempt->answers['files']))
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                        <p class="text-blue-800 dark:text-blue-200 font-semibold mb-2">Uploaded Files:</p>
                        <ul class="list-disc list-inside space-y-1 text-blue-700 dark:text-blue-300">
                            @foreach($attempt->answers['files'] as $file)
                                <li>
                                    <a href="{{ Storage::disk('public')->url($file) }}" target="_blank" class="hover:underline">
                                        {{ basename($file) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($assessment->show_correct_answers && $assessment->allow_review)
                    {{-- Detailed Answer Review --}}
                    <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Answer Review</h2>
                        <div class="space-y-4">
                            @php
                                $questions = $this->getQuestions(); // Use cached shuffled questions
                            @endphp
                            @foreach($questions as $index => $question)
                                @php
                                    $userAnswer = $answers[$question->id] ?? null;
                                    $isCorrect = false;
                                    
                                    if (in_array($question->question_type, ['multiple_choice', 'multiple_select', 'choice', 'true_false'])) {
                                        $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
                                        
                                        // Ensure correct options are converted to proper type
                                        $correctOptions = array_map(function($opt) {
                                            return is_numeric($opt) ? (int)$opt : $opt;
                                        }, $correctOptions);
                                        sort($correctOptions);
                                        
                                        if (is_array($userAnswer)) {
                                            // Convert array answers to proper type
                                            $userAnswer = array_map(function($val) {
                                                return is_numeric($val) ? (int)$val : $val;
                                            }, array_filter($userAnswer));
                                            sort($userAnswer);
                                            $isCorrect = $userAnswer === $correctOptions;
                                        } else {
                                            // Convert to proper type for comparison
                                            $userAnswerInt = is_numeric($userAnswer) ? (int)$userAnswer : $userAnswer;
                                            $isCorrect = in_array($userAnswerInt, $correctOptions, true);
                                        }
                                    }
                                @endphp
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ !$isCorrect && $userAnswer ? 'bg-red-50 dark:bg-red-900/10' : ($isCorrect ? 'bg-green-50 dark:bg-green-900/10' : 'bg-gray-50 dark:bg-gray-900/50') }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">Question {{ $index + 1 }}</h3>
                                        @if($userAnswer)
                                            @if($isCorrect)
                                                <span class="px-3 py-1 bg-green-500 text-white rounded-full text-sm font-semibold">
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Correct
                                                </span>
                                            @else
                                                <span class="px-3 py-1 bg-red-500 text-white rounded-full text-sm font-semibold">
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Incorrect
                                                </span>
                                            @endif
                                        @else
                                            <span class="px-3 py-1 bg-gray-400 text-white rounded-full text-sm font-semibold">
                                                Not Answered
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-gray-700 dark:text-gray-300 mb-3">{{ $question->question_text }}</p>
                                    
                                    {{-- Question Image --}}
                                    @if($question->image_url)
                                        <div class="mt-3 mb-3">
                                            <img src="{{ asset('storage/' . $question->image_url) }}" 
                                                 alt="Question image" 
                                                 class="max-w-md rounded-lg border border-gray-200 dark:border-gray-700">
                                        </div>
                                    @endif
                                    
                                    @if(in_array($question->question_type, ['multiple_choice', 'multiple_select', 'choice', 'true_false']))
                                        <div class="space-y-2">
                                            @foreach($question->options as $option)
                                                @php
                                                    $isUserAnswer = is_array($userAnswer) ? in_array($option->id, $userAnswer) : ($userAnswer == $option->id);
                                                    $isCorrectOption = $option->is_correct;
                                                @endphp
                                                <div class="p-3 rounded-lg border-2 {{ $isCorrectOption ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : ($isUserAnswer ? 'border-red-300 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700') }}">
                                                    <div class="flex items-start gap-2">
                                                        @if($isCorrectOption)
                                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        @elseif($isUserAnswer)
                                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        @else
                                                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        @endif
                                                        <div class="flex-1">
                                                            <span class="font-medium {{ $isCorrectOption ? 'text-green-700 dark:text-green-300' : ($isUserAnswer ? 'text-red-700 dark:text-red-300' : 'text-gray-600 dark:text-gray-400') }}">
                                                                {{ $option->option_text }}
                                                            </span>
                                                            @if($isCorrectOption)
                                                                <span class="ml-2 text-xs text-green-600 dark:text-green-400 font-semibold">(Correct Answer)</span>
                                                            @elseif($isUserAnswer && !$isCorrectOption)
                                                                <span class="ml-2 text-xs text-red-600 dark:text-red-400 font-semibold">(Your Answer)</span>
                                                            @endif
                                                            @if($option->image_url)
                                                                <div class="mt-2">
                                                                    <img src="{{ asset('storage/' . $option->image_url) }}" 
                                                                         alt="Option image" 
                                                                         class="max-w-xs rounded border border-gray-200 dark:border-gray-700">
                                                                </div>
                                                            @endif
                                                            @if($option->explanation)
                                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $option->explanation }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3 mb-2">
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Your Answer:</p>
                                            <p class="text-gray-900 dark:text-white">{{ is_array($userAnswer) ? json_encode($userAnswer) : ($userAnswer ?? 'Not answered') }}</p>
                                        </div>
                                    @endif
                                    
                                    @if($question->explanation)
                                        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                                <span class="font-semibold">Explanation:</span> {{ $question->explanation }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-center gap-3 mt-6">
                    <flux:button href="{{ route('assessments.show', $assessment) }}" wire:navigate variant="primary">
                        View Details
                    </flux:button>
                </div>
            @endif
        </div>
    </div>
@else
    {{-- Assessment Taking Interface --}}
    <div class="max-w-5xl mx-auto p-6">
        {{-- Assessment Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-6 text-white mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ $assessment->title }}</h1>
                    <p class="text-blue-100 mt-1">{{ $assessment->course->title }}</p>
                </div>
                @if($timeRemaining !== null)
                    <div class="text-center bg-white/20 rounded-lg px-4 py-2">
                        <p class="text-sm text-blue-100">Time Remaining</p>
                        <p class="text-2xl font-bold" x-data="{ 
                            minutes: Math.floor({{ $timeRemaining }} / 60),
                            seconds: {{ $timeRemaining }} % 60
                        }">
                            <span x-text="String(minutes).padStart(2, '0')"></span>:<span x-text="String(seconds).padStart(2, '0')"></span>
                        </p>
                    </div>
                @endif
            </div>

            @if($assessment->assessment_type !== 'assignment')
                {{-- Progress Bar --}}
                <div class="mt-4">
                    @php
                        $safeTotalQuestions = max($totalQuestions, 1);
                        $progressPercent = $totalQuestions > 0
                            ? round((($currentQuestionIndex + 1) / $safeTotalQuestions) * 100)
                            : 0;
                    @endphp
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span>Question {{ $currentQuestionIndex + 1 }} of {{ $totalQuestions }}</span>
                        <span>{{ $progressPercent }}%</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-2">
                        <div class="h-2 bg-white rounded-full transition-all duration-300" 
                             style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Assignment Submission Form --}}
        @if($assessment->assessment_type === 'assignment')
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Assignment Submission</h2>
                
                @if($assessment->description)
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $assessment->description }}</p>
                    </div>
                @endif

                @if($assessment->assignment_data && isset($assessment->assignment_data['instructions']))
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <h3 class="font-semibold text-blue-900 dark:text-blue-200 mb-2">Instructions:</h3>
                        <p class="text-blue-800 dark:text-blue-300 whitespace-pre-wrap">{{ $assessment->assignment_data['instructions'] }}</p>
                    </div>
                @endif

                <form wire:submit="submitAssessment">
                    <div class="space-y-6">
                        <div>
                            <flux:field label="Your Submission" required>
                                <flux:textarea 
                                    wire:model="submissionText"
                                    rows="12"
                                    placeholder="Enter your assignment submission here... You can paste text from documents, write your response, or include code snippets." />
                                <flux:error name="submissionText" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Minimum 10 characters. You can copy and paste content from PDFs, Word documents, or any text source.
                                </p>
                            </flux:field>
                        </div>

                        <div>
                            <flux:field label="Upload Files (Optional)">
                                <flux:input 
                                    type="file" 
                                    wire:model="submissionFiles" 
                                    multiple
                                    accept=".pdf,.doc,.docx,.txt,.zip,.rar,.jpg,.jpeg,.png"
                                />
                                <flux:error name="submissionFiles.*" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Maximum 10MB per file. Supported formats: PDF, DOC, DOCX, TXT, ZIP, RAR, JPG, PNG
                                </p>
                            </flux:field>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <flux:button href="{{ route('assessments.show', $assessment) }}" wire:navigate variant="ghost">
                                Cancel
                            </flux:button>
                            <flux:button type="submit" variant="primary">
                                Submit Assignment
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </flux:button>
                        </div>
                    </div>
                </form>
            </div>
        @else

        {{-- Auto-save Indicator --}}
        @if($autoSaveEnabled && $lastSavedAt)
            <div class="mb-4 flex items-center justify-end text-sm text-gray-600 dark:text-gray-400" 
                 x-data="{ show: false }"
                 x-init="
                     $watch('@progress-saved', () => { show = true; setTimeout(() => show = false, 2000); });
                 ">
                <span x-show="show" 
                      x-transition
                      class="flex items-center gap-2 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Progress saved
                </span>
            </div>
        @endif

        {{-- Question Navigation with Bookmarks and Flags --}}
        @if($totalQuestions > 1)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Question Navigation</h3>
                    <div class="flex items-center gap-4 text-xs">
                        <span class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded bg-green-500"></span>
                            Answered
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded bg-yellow-500"></span>
                            Bookmarked
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded bg-red-500"></span>
                            Flagged
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @php $questions = $this->getQuestions(); @endphp
                    @if($questions && $questions->isNotEmpty())
                    @foreach($questions as $index => $question)
                        @php
                            $isAnswered = isset($answers[$question->id]) && !empty($answers[$question->id]);
                            $isBookmarked = in_array($index, $bookmarkedQuestions);
                            $isFlagged = in_array($index, $flaggedQuestions);
                            $isCurrent = $index === $currentQuestionIndex;
                        @endphp
                        <button 
                            wire:click="goToQuestion({{ $index }})"
                            aria-label="Go to question {{ $index + 1 }}"
                            class="relative w-10 h-10 rounded-lg text-sm font-semibold transition-all
                                {{ $isCurrent ? 'ring-2 ring-blue-500 bg-blue-600 text-white scale-110' : '' }}
                                {{ $isAnswered && !$isCurrent ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 border-2 border-green-500' : '' }}
                                {{ !$isAnswered && !$isCurrent ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : '' }}
                                hover:bg-gray-200 dark:hover:bg-gray-600
                                focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{ $index + 1 }}
                            @if($isBookmarked)
                                <span class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-500 rounded-full" title="Bookmarked"></span>
                            @endif
                            @if($isFlagged)
                                <span class="absolute -bottom-1 -right-1 w-3 h-3 bg-red-500 rounded-full" title="Flagged for review"></span>
                            @endif
                        </button>
                    @endforeach
                    @endif
                </div>
            </div>
        @endif

        {{-- Review Screen Modal --}}
        @if($showReviewScreen)
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" 
                 x-data="{ show: @entangle('showReviewScreen') }"
                 x-show="show"
                 x-transition>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Review Your Answers</h2>
                        <button wire:click="hideReview" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        @php $questions = $this->getQuestions(); @endphp
                        @if($questions && $questions->isNotEmpty())
                        @foreach($questions as $index => $question)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Question {{ $index + 1 }}</h3>
                                    <button wire:click="goToQuestion({{ $index }})" 
                                            onclick="$wire.hideReview()"
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Go to question →
                                    </button>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300 mb-3">{{ $question->question_text }}</p>
                                
                                {{-- Question Image --}}
                                @if($question->image_url)
                                    <div class="mt-3 mb-3">
                                        <img src="{{ asset('storage/' . $question->image_url) }}" 
                                             alt="Question image" 
                                             class="max-w-md rounded-lg border border-gray-200 dark:border-gray-700">
                                    </div>
                                @endif
                                
                                <div class="bg-gray-50 dark:bg-gray-900/50 rounded p-3">
                                    @if(isset($answers[$question->id]) && !empty($answers[$question->id]))
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Your answer:</p>
                                        <p class="text-gray-900 dark:text-white mt-1">{{ is_array($answers[$question->id]) ? json_encode($answers[$question->id]) : $answers[$question->id] }}</p>
                                    @else
                                        <p class="text-sm text-red-600 dark:text-red-400 italic">Not answered</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        @endif
                    </div>
                    <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 p-6 flex items-center justify-end gap-3">
                        <button wire:click="hideReview" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">
                            Cancel
                        </button>
                        <button wire:click="confirmSubmit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            Confirm & Submit
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Current Question --}}
        @if($currentQuestion)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                    Question {{ $currentQuestionIndex + 1 }}
                                </h2>
                                <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                                    {{ $currentQuestion->points }} points
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                wire:click="toggleBookmark({{ $currentQuestionIndex }})"
                                aria-label="{{ in_array($currentQuestionIndex, $bookmarkedQuestions) ? 'Remove bookmark' : 'Bookmark question' }}"
                                class="p-2 rounded-lg transition-colors
                                    {{ in_array($currentQuestionIndex, $bookmarkedQuestions) 
                                        ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                <svg class="w-5 h-5" fill="{{ in_array($currentQuestionIndex, $bookmarkedQuestions) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </button>
                            <button 
                                wire:click="toggleFlag({{ $currentQuestionIndex }})"
                                aria-label="{{ in_array($currentQuestionIndex, $flaggedQuestions) ? 'Remove flag' : 'Flag for review' }}"
                                class="p-2 rounded-lg transition-colors
                                    {{ in_array($currentQuestionIndex, $flaggedQuestions) 
                                        ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="text-lg text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $currentQuestion->question_text }}</p>
                    
                    {{-- Question Image --}}
                    @if($currentQuestion->image_url)
                        <div class="mt-4">
                            <img src="{{ asset('storage/' . $currentQuestion->image_url) }}" 
                                 alt="Question image" 
                                 class="max-w-full rounded-lg border border-gray-200 dark:border-gray-700"
                                 onerror="console.log('Image failed to load:', this.src); this.style.display='none';"
                                 onload="console.log('Image loaded successfully:', this.src);">
                        </div>
                    @endif
                </div>

                {{-- Question Options (Multiple Choice, Multiple Select, Choice) --}}
                @if(in_array($currentQuestion->question_type, ['multiple_choice', 'multiple_select', 'choice']))
                    @php
                        $userAnswer = $answers[$currentQuestion->id] ?? null;
                        $isMultiple = $currentQuestion->question_type === 'multiple_select';
                    @endphp
                    <div class="space-y-3">
                        @foreach($currentQuestion->options as $option)
                            <label wire:key="option-{{ $currentQuestion->id }}-{{ $option->id }}" class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-colors
                                {{ ($isMultiple ? in_array($option->id, (array)$userAnswer) : ($userAnswer == $option->id))
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                <input 
                                    type="{{ $currentQuestion->question_type === 'multiple_choice' || $currentQuestion->question_type === 'choice' ? 'radio' : 'checkbox' }}"
                                    wire:model.live="answers.{{ $currentQuestion->id }}"
                                    value="{{ $option->id }}"
                                    class="mt-1 w-5 h-5 text-blue-600 focus:ring-blue-500" />
                                <div class="flex-1">
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $option->option_text }}</p>
                                    @if($option->image_url)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $option->image_url) }}" 
                                                 alt="Option image" 
                                                 class="max-w-xs rounded border border-gray-200 dark:border-gray-700"
                                                 onerror="console.log('Option image failed to load:', this.src); this.style.display='none';"
                                                 onload="console.log('Option image loaded successfully:', this.src);">
                                        </div>
                                    @endif
                                    @if($option->explanation)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $option->explanation }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @elseif($currentQuestion->question_type === 'true_false')
                    {{-- True/False --}}
                    @php
                        // Robustly detect true/false options even if text casing/spacing differs
                        $normalize = fn($text) => strtolower(trim($text ?? ''));
                        $trueOption = $currentQuestion->options->first(fn($opt) => in_array($normalize($opt->option_text), ['true', 't', 'yes', '1'], true));
                        $falseOption = $currentQuestion->options->first(fn($opt) => in_array($normalize($opt->option_text), ['false', 'f', 'no', '0'], true));

                        // Fallback: if not found by text, use first two options by order
                        if (!$trueOption && $currentQuestion->options->count() > 0) {
                            $trueOption = $currentQuestion->options->first();
                        }
                        if (!$falseOption && $currentQuestion->options->count() > 1) {
                            $falseOption = $currentQuestion->options->skip(1)->first();
                        }

                        $userAnswer = $answers[$currentQuestion->id] ?? null;
                    @endphp
                    <div class="space-y-3">
                        @if($trueOption)
                            <label wire:key="true-option-{{ $currentQuestion->id }}" class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-colors
                                {{ $userAnswer == $trueOption->id
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                <input 
                                    type="radio"
                                    wire:model.live="answers.{{ $currentQuestion->id }}"
                                    value="{{ $trueOption->id }}"
                                    class="mt-1 w-5 h-5 text-blue-600 focus:ring-blue-500" />
                                <div class="flex-1">
                                    <span class="text-gray-900 dark:text-white font-medium">True</span>
                                    @if($trueOption->image_url)
                                        <img src="{{ Storage::disk('public')->url($trueOption->image_url) }}" 
                                             alt="True option" 
                                             class="mt-2 max-w-xs rounded border border-gray-200 dark:border-gray-700">
                                    @endif
                                </div>
                            </label>
                        @endif
                        @if($falseOption)
                            <label wire:key="false-option-{{ $currentQuestion->id }}" class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-colors
                                {{ $userAnswer == $falseOption->id
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                <input 
                                    type="radio"
                                    wire:model.live="answers.{{ $currentQuestion->id }}"
                                    value="{{ $falseOption->id }}"
                                    class="mt-1 w-5 h-5 text-blue-600 focus:ring-blue-500" />
                                <div class="flex-1">
                                    <span class="text-gray-900 dark:text-white font-medium">False</span>
                                    @if($falseOption->image_url)
                                        <img src="{{ Storage::disk('public')->url($falseOption->image_url) }}" 
                                             alt="False option" 
                                             class="mt-2 max-w-xs rounded border border-gray-200 dark:border-gray-700">
                                    @endif
                                </div>
                            </label>
                        @endif
                    </div>
                @elseif($currentQuestion->question_type === 'short_answer')
                    {{-- Short Answer --}}
                    <flux:field>
                        <flux:textarea 
                            wire:model="answers.{{ $currentQuestion->id }}"
                            rows="3"
                            placeholder="Enter your answer..." />
                    </flux:field>
                @elseif($currentQuestion->question_type === 'essay')
                    {{-- Essay --}}
                    <flux:field>
                        <flux:textarea 
                            wire:model="answers.{{ $currentQuestion->id }}"
                            rows="10"
                            placeholder="Enter your essay response..." />
                    </flux:field>
                @elseif($currentQuestion->question_type === 'file_upload')
                    {{-- File Upload --}}
                    @php
                        $settings = $currentQuestion->settings ?? [];
                        $allowedTypes = $settings['allowed_types'] ?? 'pdf,doc,docx,jpg,png';
                        $maxFiles = $settings['max_files'] ?? 1;
                        $maxSize = $settings['max_size'] ?? 10;
                    @endphp
                    <div class="space-y-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <p class="text-sm text-blue-800 dark:text-blue-200 mb-2">
                                <strong>Requirements:</strong>
                            </p>
                            <ul class="text-sm text-blue-700 dark:text-blue-300 list-disc list-inside space-y-1">
                                <li>Allowed types: {{ $allowedTypes }}</li>
                                <li>Max files: {{ $maxFiles }}</li>
                                <li>Max size per file: {{ $maxSize }}MB</li>
                            </ul>
                        </div>
                        <flux:field>
                            <flux:input 
                                type="file" 
                                wire:model="tempFiles.{{ $currentQuestion->id }}" 
                                multiple="{{ $maxFiles > 1 }}"
                                accept="{{ '.' . str_replace(',', ',.', $allowedTypes) }}" />
                        </flux:field>
                        @if(isset($tempFiles[$currentQuestion->id]) && !empty($tempFiles[$currentQuestion->id]))
                            <div class="space-y-2">
                                @foreach($tempFiles[$currentQuestion->id] as $fileIndex => $file)
                                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3">
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $file->getClientOriginalName() }}</span>
                                        <button type="button" 
                                                wire:click="removeFile({{ $currentQuestion->id }}, {{ $fileIndex }})"
                                                class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif($currentQuestion->question_type === 'code_submission')
                    {{-- Code Submission with CodeMirror --}}
                    @php
                        $settings = $currentQuestion->settings ?? [];
                        $language = $settings['code_submission']['language'] ?? 'javascript';
                        $template = $settings['code_submission']['template'] ?? '';
                        $modes = [
                            'javascript' => 'javascript',
                            'python' => 'python',
                            'java' => 'clike',
                            'php' => 'php',
                            'html' => 'htmlmixed',
                            'css' => 'css',
                            'sql' => 'sql',
                        ];
                        $mode = $modes[strtolower($language)] ?? 'javascript';
                    @endphp
                    <div class="space-y-4" x-data="{ 
                        editor: null,
                        language: @js($language),
                        init() {
                            // Load CodeMirror from CDN
                            if (typeof CodeMirror === 'undefined') {
                                const link = document.createElement('link');
                                link.rel = 'stylesheet';
                                link.href = 'https://cdn.jsdelivr.net/npm/codemirror@5/lib/codemirror.css';
                                document.head.appendChild(link);
                                
                                const script = document.createElement('script');
                                script.src = 'https://cdn.jsdelivr.net/npm/codemirror@5/lib/codemirror.js';
                                script.onload = () => {
                                    const modeScript = document.createElement('script');
                                    modeScript.src = `https://cdn.jsdelivr.net/npm/codemirror@5/mode/${this.getMode(@js($mode))}/${this.getMode(@js($mode))}.js`;
                                    modeScript.onload = () => this.initEditor();
                                    document.head.appendChild(modeScript);
                                };
                                document.head.appendChild(script);
                            } else {
                                this.initEditor();
                            }
                        },
                        getMode(lang) {
                            const modes = { javascript: 'javascript', python: 'python', clike: 'clike', php: 'php', htmlmixed: 'htmlmixed', css: 'css', sql: 'sql' };
                            return modes[lang] || 'javascript';
                        },
                        initEditor() {
                            this.$nextTick(() => {
                                const textarea = this.$el.querySelector('textarea');
                                if (textarea && CodeMirror) {
                                    this.editor = CodeMirror.fromTextArea(textarea, {
                                        lineNumbers: true,
                                        mode: @js($mode),
                                        theme: 'default',
                                        indentUnit: 4,
                                        indentWithTabs: false,
                                        lineWrapping: true,
                                    });
                                    const value = @entangle('answers.' . $currentQuestion->id);
                                    this.editor.setValue(value || @js($template));
                                    this.editor.on('change', (cm) => {
                                        value = cm.getValue();
                                    });
                                }
                            });
                        }
                    }">
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
                            <p class="text-sm text-indigo-800 dark:text-indigo-200">
                                <strong>Language:</strong> {{ ucfirst($language) }}
                            </p>
                        </div>
                        <textarea 
                            id="code-editor-{{ $currentQuestion->id }}"
                            wire:model="answers.{{ $currentQuestion->id }}"
                            rows="15"
                            placeholder="Enter your code here..."
                            class="font-mono text-sm w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 dark:bg-gray-900 dark:text-white"></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Write your code solution in {{ ucfirst($language) }}</p>
                        @if($template)
                            <details class="mt-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <summary class="cursor-pointer font-medium text-gray-900 dark:text-white mb-2">
                                    View Code Template
                                </summary>
                                <pre class="mt-2 text-sm font-mono bg-white dark:bg-gray-800 p-4 rounded border border-gray-200 dark:border-gray-700 overflow-x-auto"><code>{{ $template }}</code></pre>
                            </details>
                        @endif
                    </div>
                @elseif($currentQuestion->question_type === 'matching')
                    {{-- Matching --}}
                    @php
                        $matchingData = $this->getShuffledQuestionData($currentQuestion->id, 'matching');
                        $pairs = $matchingData['pairs'] ?? [];
                        $rightItems = $matchingData['rightItems'] ?? [];
                        $answers = $answers[$currentQuestion->id] ?? [];
                    @endphp
                    <div class="space-y-4">
                        @foreach($pairs as $index => $pair)
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                                <p class="font-medium text-gray-900 dark:text-white mb-3">{{ $pair['left_item'] }}</p>
                                <flux:select wire:model="answers.{{ $currentQuestion->id }}.{{ $index }}" class="w-full">
                                    <option value="">Select match...</option>
                                    @foreach($rightItems as $rightItem)
                                        <option value="{{ $rightItem }}">{{ $rightItem }}</option>
                                    @endforeach
                                </flux:select>
                            </div>
                        @endforeach
                    </div>
                @elseif($currentQuestion->question_type === 'ordering')
                    {{-- Ordering --}}
                    @php
                        $orderingData = $this->getShuffledQuestionData($currentQuestion->id, 'ordering');
                        $items = $orderingData['items'] ?? [];
                        $shuffledItems = $orderingData['shuffledItems'] ?? [];
                    @endphp
                    <div class="space-y-3" 
                         x-data="{ 
                             items: @js(array_column($shuffledItems, 'item_text')),
                             init() {
                                 // Load SortableJS from CDN
                                 if (typeof Sortable === 'undefined') {
                                     const script = document.createElement('script');
                                     script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js';
                                     script.onload = () => this.initSortable();
                                     document.head.appendChild(script);
                                 } else {
                                     this.initSortable();
                                 }
                             },
                             initSortable() {
                                 this.$nextTick(() => {
                                     const container = this.$el.querySelector('[data-sortable]');
                                     if (container && Sortable) {
                                         new Sortable(container, {
                                             animation: 150,
                                             handle: '.drag-handle',
                                             ghostClass: 'opacity-50',
                                             onEnd: (evt) => {
                                                 const movedItem = this.items[evt.oldIndex];
                                                 this.items.splice(evt.oldIndex, 1);
                                                 this.items.splice(evt.newIndex, 0, movedItem);
                                                 // Update Livewire answers
                                                 @this.set('answers.{{ $currentQuestion->id }}', this.items);
                                             }
                                         });
                                     }
                                 });
                             }
                         }">
                        <div data-sortable class="space-y-2">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border-2 border-gray-200 dark:border-gray-700 cursor-move touch-none">
                                    <svg class="w-6 h-6 text-gray-400 drag-handle" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                    <span class="flex-1 text-gray-900 dark:text-white font-medium" x-text="item"></span>
                                    <span class="text-sm text-gray-500" x-text="(index + 1)"></span>
                                </div>
                            </template>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 italic">Drag items to reorder them correctly</p>
                    </div>
                @elseif($currentQuestion->question_type === 'fill_blank')
                    {{-- Fill in the Blank --}}
                    @php
                        $settings = $currentQuestion->settings ?? [];
                        $blanks = $settings['fill_blank']['blanks'] ?? [];
                        
                        // Fallback for legacy data - parse question text for blanks
                        if (empty($blanks)) {
                            $questionText = $currentQuestion->question_text;
                            // Count blanks in the question
                            $blankCount = substr_count($questionText, '_____');
                            if ($blankCount > 0) {
                                for ($i = 0; $i < $blankCount; $i++) {
                                    $blanks[] = [
                                        'position' => $i,
                                        'correct_answer' => '',
                                        'case_sensitive' => false,
                                        'alternative_answers' => []
                                    ];
                                }
                            }
                        }
                        
                        $questionText = $currentQuestion->question_text;
                    @endphp
                    <div class="space-y-4">
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 mb-4">
                            <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ $questionText }}</p>
                        </div>
                        @foreach($blanks as $index => $blank)
                            <flux:field>
                                <flux:label>Blank {{ $index + 1 }}</flux:label>
                                <flux:input 
                                    wire:model="answers.{{ $currentQuestion->id }}.{{ $index }}"
                                    placeholder="Enter answer for blank {{ $index + 1 }}" />
                            </flux:field>
                        @endforeach
                    </div>
                @elseif($currentQuestion->question_type === 'rating')
                    {{-- Rating Scale --}}
                    @php
                        $settings = $currentQuestion->settings ?? [];
                        $ratingSettings = $settings['rating_scale'] ?? ['min' => 1, 'max' => 5];
                        $min = $ratingSettings['min'] ?? 1;
                        $max = $ratingSettings['max'] ?? 5;
                        $labels = $ratingSettings['labels'] ?? [];
                    @endphp
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 justify-center">
                            @for($i = $min; $i <= $max; $i++)
                                <label class="flex flex-col items-center gap-2 p-4 border-2 rounded-lg cursor-pointer transition-colors
                                    {{ (isset($answers[$currentQuestion->id]) && $answers[$currentQuestion->id] == $i)
                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
                                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                    <input 
                                        type="radio"
                                        wire:model="answers.{{ $currentQuestion->id }}"
                                        value="{{ $i }}"
                                        class="w-5 h-5 text-blue-600 focus:ring-blue-500" />
                                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $i }}</span>
                                    @if(isset($labels[$i - $min]))
                                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ $labels[$i - $min] }}</span>
                                    @endif
                                </label>
                            @endfor
                        </div>
                    </div>
                @elseif($currentQuestion->question_type === 'rubric_criteria')
                    {{-- Rubric Criteria --}}
                    @php
                        $settings = $currentQuestion->settings ?? [];
                        $criteria = $settings['rubric_criteria'] ?? [];
                    @endphp
                    <div class="space-y-6">
                        @foreach($criteria as $criterionIndex => $criterion)
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">{{ $criterion['name'] }}</h4>
                                @if($criterion['description'])
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $criterion['description'] }}</p>
                                @endif
                                <flux:select wire:model="answers.{{ $currentQuestion->id }}.{{ $criterionIndex }}" class="w-full">
                                    <option value="">Select performance level...</option>
                                    @foreach($criterion['performance_levels'] ?? [] as $levelIndex => $level)
                                        <option value="{{ $levelIndex }}">
                                            {{ $level['level'] }} ({{ $level['points'] }} points)
                                        </option>
                                    @endforeach
                                </flux:select>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Fallback for unsupported question types --}}
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <p class="text-yellow-800 dark:text-yellow-200">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <strong>Warning:</strong> This question type ({{ $currentQuestion->question_type }}) is not yet fully supported in the assessment interface.
                        </p>
                    </div>
                @endif

                @if($currentQuestion->explanation)
                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <span class="font-semibold">Hint:</span> {{ $currentQuestion->explanation }}
                        </p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Error Message --}}
        @if(session()->has('error'))
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-red-800 dark:text-red-200">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('error') }}
                </p>
            </div>
        @endif
        
        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-red-800 dark:text-red-200 font-semibold mb-2">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Please fix the following errors:
                </p>
                <ul class="list-disc list-inside text-red-700 dark:text-red-300 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Navigation Buttons (only for non-assignment assessments) --}}
        <div class="flex items-center justify-between">
            <flux:button 
                wire:click="previousQuestion"
                variant="ghost"
                :disabled="$currentQuestionIndex === 0">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Previous
            </flux:button>

            <div class="flex items-center gap-3">
                @if($currentQuestionIndex < $totalQuestions - 1)
                    @php
                        $isAnswered = $this->isQuestionAnswered($currentQuestion);
                    @endphp
                    
                    @if($isAnswered)
                        {{-- Next button when answered --}}
                        <flux:button 
                            wire:click="nextQuestion"
                            variant="primary">
                            Next
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </flux:button>
                    @else
                        {{-- Skip button when not answered --}}
                        <flux:button 
                            wire:click="nextQuestion"
                            variant="ghost">
                            Skip
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                        </flux:button>
                    @endif
                @else
                    <button 
                        wire:click="submitAssessment"
                        type="button"
                        wire:loading.attr="disabled"
                        onclick="console.log('Submit clicked', @js($answers)); console.log('Questions:', @js($totalQuestions));"
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-400 disabled:cursor-not-allowed text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
                        <span wire:loading.remove wire:target="submitAssessment" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Submit Assessment
                        </span>
                        <span wire:loading wire:target="submitAssessment" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Submitting...
                        </span>
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Timer Script --}}
    @if($timeRemaining !== null)
        <script>
            document.addEventListener('livewire:init', () => {
                let timerInterval = setInterval(() => {
                    @this.call('updateTimer');
                }, 1000);
                
                Livewire.on('assessment-completed', () => {
                    clearInterval(timerInterval);
                });
            });
        </script>
    @endif
    
    {{-- Auto-Save Script --}}
    @if($autoSaveEnabled)
        <script>
            document.addEventListener('livewire:init', () => {
                let autoSaveInterval;
                
                Livewire.on('start-auto-save', () => {
                    // Auto-save every 30 seconds
                    autoSaveInterval = setInterval(() => {
                        @this.call('saveProgress');
                    }, 30000);
                });
                
                // Auto-save on answer changes (debounced)
                let saveTimeout;
                Livewire.on('trigger-auto-save', () => {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        @this.call('saveProgress');
                    }, 2000); // Debounce 2 seconds
                });
                
                // Save on page unload
                window.addEventListener('beforeunload', () => {
                    @this.call('saveProgress');
                });
                
                Livewire.on('assessment-completed', () => {
                    clearInterval(autoSaveInterval);
                });
            });
        </script>
    @endif
    
    {{-- Keyboard Navigation --}}
    <script>
        document.addEventListener('keydown', (e) => {
            // Arrow keys for navigation (when not in input/textarea)
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'SELECT') {
                if (e.key === 'ArrowLeft' && !e.shiftKey) {
                    @this.call('previousQuestion');
                    e.preventDefault();
                } else if (e.key === 'ArrowRight' && !e.shiftKey) {
                    @this.call('nextQuestion');
                    e.preventDefault();
                }
            }
            // Alt+S to save progress
            if (e.altKey && e.key === 's') {
                @this.call('saveProgress');
                e.preventDefault();
            }
            // Alt+R to review
            if (e.altKey && e.key === 'r' && @js($currentQuestionIndex === $totalQuestions - 1)) {
                @this.call('showReview');
                e.preventDefault();
            }
        });
    </script>
@endif
