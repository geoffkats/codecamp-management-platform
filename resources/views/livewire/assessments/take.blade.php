<div>
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

                @php $submittedFiles = $attempt->submissionFiles(); @endphp
                @if(count($submittedFiles) > 0)
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                        <p class="text-blue-800 dark:text-blue-200 font-semibold mb-2">Uploaded Files:</p>
                        <ul class="space-y-1 text-blue-700 dark:text-blue-300">
                            @foreach($submittedFiles as $file)
                                <li>
                                    <a href="{{ \App\Support\SubmissionFile::downloadUrl($file['path'], $file['name'] ?? null) }}" class="hover:underline">
                                        {{ $file['name'] }}
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
                                    <div class="text-gray-700 dark:text-gray-300 mb-3"><x-rich-text :content="$question->question_text" /></div>
                                    
                                    {{-- Question Image --}}
                                    @if($question->image_url)
                                        <div class="mt-3 mb-3">
                                            <x-storage-image :path="$question->image_url" alt="Question image" class="max-w-md rounded-lg border border-gray-200 dark:border-gray-700" />
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
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $assessment->title }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $assessment->course->title }}</p>
                </div>
                @if($timeRemaining !== null && $timeRemaining > 0)
                    <div class="text-center bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg px-4 py-3"
                         x-data="{
                            remaining: {{ (int) $timeRemaining }},
                            get minutes() { return Math.floor(this.remaining / 60); },
                            get seconds() { return this.remaining % 60; },
                            get isUrgent() { return this.remaining <= 60; },
                            init() {
                                const t = setInterval(() => {
                                    if (this.remaining > 0) {
                                        this.remaining--;
                                    } else {
                                        clearInterval(t);
                                        if (this.remaining === 0) {
                                            $wire.call('submitAssessment');
                                        }
                                    }
                                }, 1000);
                                document.addEventListener('livewire:navigating', () => clearInterval(t), { once: true });
                            }
                         }">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Time Remaining</p>
                        <p class="text-2xl font-bold tabular-nums" :class="isUrgent ? 'text-red-600 dark:text-red-400 animate-pulse' : 'text-orange-600 dark:text-orange-400'">
                            <span x-text="String(minutes).padStart(2, '0')"></span>:<span x-text="String(seconds).padStart(2, '0')"></span>
                        </p>
                    </div>
                @endif
            </div>

            @if($totalQuestions > 0)
                {{-- Progress Bar --}}
                <div class="mt-2">
                    @php
                        $safeTotalQuestions = max($totalQuestions, 1);
                        $progressPercent = round((($currentQuestionIndex + 1) / $safeTotalQuestions) * 100);
                    @endphp
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-gray-600 dark:text-gray-400">
                            {{ $assessment->assessment_type === 'assignment' ? 'Task' : 'Question' }}
                            {{ $currentQuestionIndex + 1 }} of {{ $totalQuestions }}
                        </span>
                        <span class="text-gray-500 dark:text-gray-500">{{ $progressPercent }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                        <div class="h-2 bg-orange-500 rounded-full transition-all duration-300"
                             style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        @if($assessment->assessment_type === 'assignment')
            @include('livewire.assessments.partials.assignment-brief', ['assessment' => $assessment])
        @endif

        @if($assessment->assessment_type === 'assignment' && $totalQuestions === 0)
            @php
                $allowText = $assessment->assignment_data['allow_text'] ?? true;
                $allowFiles = $assessment->assignment_data['allow_files'] ?? true;
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Your Submission</h2>
                <form wire:submit="submitAssessment">
                    <div class="space-y-6">
                        @if($allowText)
                        <div>
                            <flux:field label="Your Response" :required="!$allowFiles">
                                <flux:textarea wire:model="submissionText" rows="12" placeholder="Write your assignment response here..." />
                                <flux:error name="submissionText" />
                            </flux:field>
                        </div>
                        @endif
                        @if($allowFiles)
                        <div>
                            <flux:field label="Upload Files{{ $allowText ? ' (Optional)' : '' }}" :required="!$allowText">
                                <flux:input type="file" wire:model="submissionFiles" multiple accept=".pdf,.doc,.docx,.txt,.zip,.rar,.jpg,.jpeg,.png,.sb3,.sb2,.sb" />
                                <flux:error name="submissionFiles.*" />
                            </flux:field>
                        </div>
                        @endif
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <flux:button href="{{ route('assessments.show', $assessment) }}" wire:navigate variant="ghost">Cancel</flux:button>
                            <flux:button type="submit" variant="primary">Submit Assignment</flux:button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        @if($totalQuestions > 0)

        {{-- Auto-save Indicator --}}
        @if($autoSaveEnabled && $lastSavedAt)
            <div class="mb-4 flex items-center justify-end text-sm text-gray-600 dark:text-gray-400" 
                 x-data="{ show: false }"
                 x-init="
                     window.addEventListener('progress-saved', () => { show = true; setTimeout(() => show = false, 2000); });
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
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-4 mb-6">
                <div class="flex items-center justify-between mb-3 gap-3">
                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 flex-wrap">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-green-500 inline-block"></span> Answered</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-yellow-500 inline-block"></span> Bookmarked</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-red-500 inline-block"></span> Flagged</span>
                    </div>
                    {{-- Review & Submit button — always visible, not just on last question --}}
                    <button
                        wire:click="showReview"
                        type="button"
                        class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l2 2 4-4" />
                        </svg>
                        Review & Submit
                    </button>
                </div>
                {{-- Scrollable grid — no overflow on small screens --}}
                <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto pr-1">
                    @php $questions = $this->getQuestions(); @endphp
                    @if($questions && $questions->isNotEmpty())
                    @foreach($questions as $index => $question)
                        @php
                            $isAnswered = isset($answers[$question->id]) && $answers[$question->id] !== '' && $answers[$question->id] !== [];
                            $isBookmarked = in_array($index, $bookmarkedQuestions);
                            $isFlagged = in_array($index, $flaggedQuestions);
                            $isCurrent = $index === $currentQuestionIndex;
                        @endphp
                        <button
                            wire:click="goToQuestion({{ $index }})"
                            aria-label="Go to question {{ $index + 1 }}"
                            class="relative w-9 h-9 rounded-lg text-xs font-bold transition-all focus:outline-none focus:ring-2 focus:ring-blue-500
                                {{ $isCurrent
                                    ? 'ring-2 ring-offset-1 ring-blue-500 bg-blue-600 text-white scale-110 z-10'
                                    : ($isAnswered
                                        ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 border-2 border-green-400'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600') }}">
                            {{ $index + 1 }}
                            @if($isBookmarked)
                                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-yellow-400 rounded-full border border-white dark:border-gray-800"></span>
                            @elseif($isFlagged)
                                <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border border-white dark:border-gray-800"></span>
                            @endif
                        </button>
                    @endforeach
                    @endif
                </div>
            </div>
        @elseif($totalQuestions === 1)
            {{-- Single question — still show Review & Submit --}}
            <div class="flex justify-end mb-4">
                <button wire:click="showReview" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold bg-green-600 hover:bg-green-700 text-white rounded-xl transition-colors">
                    Review & Submit
                </button>
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
                            @php
                                $userAnswer = $answers[$question->id] ?? null;
                                $hasAnswer = !is_null($userAnswer) && $userAnswer !== '' && $userAnswer !== [];

                                // Build a human-readable display of the answer
                                $displayLines = [];
                                if ($hasAnswer) {
                                    if (in_array($question->question_type, ['multiple_choice', 'choice', 'true_false'])) {
                                        $opt = $question->options->firstWhere('id', (int)$userAnswer)
                                            ?? $question->options->firstWhere('id', $userAnswer);
                                        $displayLines[] = $opt ? $opt->option_text : $userAnswer;
                                    } elseif ($question->question_type === 'multiple_select') {
                                        $selectedIds = array_map('intval', (array) $userAnswer);
                                        $texts = $question->options->whereIn('id', $selectedIds)->pluck('option_text')->all();
                                        $displayLines = count($texts) ? $texts : [(string)json_encode($userAnswer)];
                                    } elseif ($question->question_type === 'matching') {
                                        $matchData = $this->getShuffledQuestionData($question->id, 'matching');
                                        $pairs = $matchData['pairs'] ?? [];
                                        foreach ((array) $userAnswer as $i => $chosen) {
                                            if ($chosen !== '' && isset($pairs[$i])) {
                                                $displayLines[] = $pairs[$i]['left_item'] . ' → ' . $chosen;
                                            }
                                        }
                                    } elseif ($question->question_type === 'ordering') {
                                        $items = (array) $userAnswer;
                                        foreach ($items as $pos => $item) {
                                            $displayLines[] = ($pos + 1) . '. ' . $item;
                                        }
                                    } elseif ($question->question_type === 'fill_blank') {
                                        foreach ((array) $userAnswer as $bi => $val) {
                                            if ($val !== '') {
                                                $displayLines[] = 'Blank ' . ($bi + 1) . ': ' . $val;
                                            }
                                        }
                                    } elseif ($question->question_type === 'rating') {
                                        $displayLines[] = 'Rating: ' . $userAnswer;
                                    } elseif (is_array($userAnswer)) {
                                        $displayLines = array_filter(array_map('strval', $userAnswer));
                                    } else {
                                        $displayLines[] = (string) $userAnswer;
                                    }
                                }
                            @endphp
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4
                                {{ $hasAnswer ? 'border-l-4 border-l-green-400' : 'border-l-4 border-l-red-400' }}">
                                <div class="flex items-start justify-between mb-2 gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">Q{{ $index + 1 }}</span>
                                        @if(!$hasAnswer)
                                            <span class="text-xs font-semibold text-red-600 dark:text-red-400">Unanswered</span>
                                        @endif
                                    </div>
                                    <button wire:click="goToQuestion({{ $index }})" x-on:click="show = false"
                                            class="text-blue-600 dark:text-blue-400 hover:underline text-xs font-semibold flex-shrink-0">
                                        Edit →
                                    </button>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2 line-clamp-2">{{ \Illuminate\Support\Str::limit($question->question_text, 120) }}</p>

                                @if($hasAnswer && count($displayLines))
                                    <div class="mt-2 space-y-1">
                                        @foreach($displayLines as $line)
                                            <div class="flex items-start gap-2 text-sm">
                                                <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-gray-800 dark:text-gray-200">{{ $line }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(!$hasAnswer)
                                    <p class="text-xs text-red-500 dark:text-red-400 italic mt-1">No answer given — you can still go back and answer this.</p>
                                @endif
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

        {{-- Current Question — wire:key forces a clean remount so text/options never bleed across questions --}}
        @if($currentQuestion)
            <div wire:key="question-panel-{{ $currentQuestion->id }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $assessment->assessment_type === 'assignment' ? 'Task' : 'Question' }} {{ $currentQuestionIndex + 1 }}
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
                    <div class="text-lg text-gray-700 dark:text-gray-300">
                        <x-rich-text :content="$currentQuestion->question_text" class="!prose-lg" />
                    </div>
                    
                    {{-- Question Image --}}
                    @if($currentQuestion->image_url)
                        <div class="mt-4">
                            <x-storage-image :path="$currentQuestion->image_url" alt="Question image" />
                        </div>
                    @endif
                </div>

                {{-- Question Options (Multiple Choice, Multiple Select, Choice) --}}
                @if(in_array($currentQuestion->question_type, ['multiple_choice', 'multiple_select', 'choice']))
                    @php
                        $userAnswer = $answers[$currentQuestion->id] ?? null;
                        $isMultiple = $currentQuestion->question_type === 'multiple_select';
                    @endphp
                    <div class="space-y-3" wire:key="options-{{ $currentQuestion->id }}">
                        @foreach($currentQuestion->options as $option)
                            <label wire:key="option-{{ $currentQuestion->id }}-{{ $option->id }}" class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-colors
                                {{ ($isMultiple ? in_array($option->id, (array)$userAnswer) : ($userAnswer == $option->id))
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                <input 
                                    type="{{ $currentQuestion->question_type === 'multiple_choice' || $currentQuestion->question_type === 'choice' ? 'radio' : 'checkbox' }}"
                                    name="answer-{{ $currentQuestion->id }}"
                                    wire:model.live="answers.{{ $currentQuestion->id }}"
                                    value="{{ $option->id }}"
                                    class="mt-1 w-5 h-5 text-blue-600 focus:ring-blue-500" />
                                <div class="flex-1">
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $option->option_text }}</p>
                                    @if($option->image_url)
                                        <div class="mt-2">
                                            <x-storage-image :path="$option->image_url" alt="Option image" class="max-w-xs rounded border border-gray-200 dark:border-gray-700" />
                                        </div>
                                    @endif
                                    {{-- Hide option explanations while answering --}}
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
                    <div class="space-y-3" wire:key="truefalse-{{ $currentQuestion->id }}">
                        @if($trueOption)
                            <label wire:key="true-option-{{ $currentQuestion->id }}" class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-colors
                                {{ $userAnswer == $trueOption->id
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                <input 
                                    type="radio"
                                    name="answer-{{ $currentQuestion->id }}"
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
                                    name="answer-{{ $currentQuestion->id }}"
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
                    {{-- Short Answer — live sync so Skip→Next updates as student types --}}
                    <flux:field>
                        <flux:textarea
                            wire:model.live.debounce.400ms="answers.{{ $currentQuestion->id }}"
                            rows="3"
                            placeholder="Enter your answer..." />
                    </flux:field>
                @elseif($currentQuestion->question_type === 'essay')
                    {{-- Essay — blur sync (large text, no need for per-keystroke updates) --}}
                    <flux:field>
                        <flux:textarea
                            wire:model.blur="answers.{{ $currentQuestion->id }}"
                            rows="10"
                            placeholder="Enter your essay response..." />
                    </flux:field>
                @elseif(($currentQuestionType ?? '') === 'file_upload')
                    @php
                        $settings = $currentQuestion->settings ?? [];
                        $allowedTypes = $settings['allowed_types'] ?? 'html,htm,css,pdf,doc,docx,txt,jpg,jpeg,png,gif,zip';
                        $maxFiles = (int) ($settings['max_files'] ?? 1);
                        $maxSize = (int) ($settings['max_size'] ?? 10);
                    @endphp
                    <div class="space-y-4">
                        <x-assessments.file-upload-field
                            :question-id="$currentQuestion->id"
                            :allowed-types="$allowedTypes"
                            :max-files="$maxFiles"
                            :max-size="$maxSize"
                        />

                        @if(!empty($selectedUploadFiles) || !empty($savedUploadFiles))
                            <div class="space-y-2">
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Selected files</p>
                                @foreach($selectedUploadFiles as $fileIndex => $file)
                                    <div class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                                        <span class="text-sm text-gray-900 dark:text-white truncate">{{ $file->getClientOriginalName() }}</span>
                                        <button type="button"
                                                wire:click="removeFile({{ $currentQuestion->id }}, {{ $fileIndex }})"
                                                class="text-red-600 hover:text-red-700 text-xs font-semibold ml-3 flex-shrink-0">
                                            Remove
                                        </button>
                                    </div>
                                @endforeach
                                @foreach($savedUploadFiles as $fileIndex => $file)
                                    @php
                                        $savedPath = is_array($file) ? ($file['path'] ?? '') : (string) $file;
                                        $savedName = is_array($file)
                                            ? ($file['name'] ?? basename($savedPath))
                                            : basename($savedPath);
                                    @endphp
                                    <div class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                                        <span class="text-sm text-gray-900 dark:text-white truncate">{{ $savedName }}</span>
                                        <button type="button"
                                                wire:click="removeFile({{ $currentQuestion->id }}, {{ $fileIndex }})"
                                                class="text-red-600 hover:text-red-700 text-xs font-semibold ml-3 flex-shrink-0">
                                            Remove
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif(($currentQuestionType ?? '') === 'code_submission')
                    @php
                        $settings = $currentQuestion->settings ?? [];
                        $language = $settings['code_submission']['language'] ?? ($settings['language'] ?? 'javascript');
                        $template = $settings['code_submission']['template'] ?? ($settings['template'] ?? '');
                    @endphp
                    <div class="space-y-4">
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
                            <p class="text-sm text-indigo-800 dark:text-indigo-200">
                                <strong>Language:</strong> {{ ucfirst($language) }}
                            </p>
                        </div>
                        <flux:field>
                            <flux:textarea
                                wire:model.live.debounce.400ms="answers.{{ $currentQuestion->id }}"
                                rows="18"
                                placeholder="Enter your {{ $language }} code here..."
                                class="font-mono text-sm" />
                        </flux:field>
                        @if($template)
                            <details class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                                <summary class="cursor-pointer font-medium text-gray-900 dark:text-white">View starter template</summary>
                                <pre class="mt-2 text-sm font-mono bg-white dark:bg-gray-800 p-4 rounded border border-gray-200 dark:border-gray-700 overflow-x-auto whitespace-pre-wrap"><code>{{ $template }}</code></pre>
                            </details>
                        @endif
                    </div>
                @elseif($currentQuestion->question_type === 'matching')
                    {{-- Matching --}}
                    @php
                        $matchingData = $this->getShuffledQuestionData($currentQuestion->id, 'matching');
                        $pairs = $matchingData['pairs'] ?? [];
                        $rightItems = $matchingData['rightItems'] ?? [];
                        $currentMatchingAnswers = $answers[$currentQuestion->id] ?? [];
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
                                    wire:model.live.debounce.400ms="answers.{{ $currentQuestion->id }}.{{ $index }}"
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

                {{-- Explanations are not shown while the student is answering --}}
            </div>
        @elseif(!$currentQuestion)
            <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-6 text-sm text-amber-900 dark:text-amber-200 mb-6">
                We could not load this task. Please refresh the page. If it keeps happening, contact your instructor.
            </div>
        @endif

        {{-- Error Message --}}
        @error('submit')
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-red-800 dark:text-red-200">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $message }}
                </p>
            </div>
        @enderror

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

        {{-- Navigation Buttons --}}
        <div class="flex items-center justify-between gap-3">
            <button
                wire:click="previousQuestion"
                type="button"
                @if($currentQuestionIndex === 0) disabled @endif
                class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Previous
            </button>

            <div class="flex items-center gap-2">
                @if($currentQuestionIndex < $totalQuestions - 1)
                    @php $isAnswered = $currentQuestion && $this->isQuestionAnswered($currentQuestion); @endphp

                    {{-- Next / Skip --}}
                    <button
                        wire:click="nextQuestion"
                        type="button"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-xl transition-colors
                            {{ $isAnswered ? 'bg-orange-600 hover:bg-orange-700' : 'bg-gray-400 hover:bg-gray-500' }}">
                        {{ $isAnswered ? 'Next' : 'Skip' }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @else
                    @php
                        $uploadWireTarget = $currentQuestion
                            ? 'tempFiles.' . $currentQuestion->id
                            : 'tempFiles';
                    @endphp
                    {{-- Last question: primary submit button --}}
                    <button
                        wire:click="submitAssessment"
                        type="button"
                        wire:loading.attr="disabled"
                        wire:target="submitAssessment, {{ $uploadWireTarget }}"
                        class="flex items-center gap-2 px-6 py-2.5 bg-green-600 hover:bg-green-700 disabled:bg-green-400 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-md hover:shadow-lg active:scale-95 transition-all">
                        <span
                            wire:loading.remove
                            wire:target="submitAssessment, {{ $uploadWireTarget }}"
                            class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $assessment->assessment_type === 'assignment' ? 'Submit Assignment' : 'Submit' }}
                        </span>
                        <span
                            wire:loading
                            wire:target="{{ $uploadWireTarget }}"
                            wire:loading.remove
                            wire:target="submitAssessment"
                            class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading file...
                        </span>
                        <span wire:loading wire:target="submitAssessment" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
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

        @if($totalQuestions === 0 && $assessment->assessment_type !== 'assignment')
        <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-6 text-sm text-amber-900 dark:text-amber-200">
            This assessment has no questions yet. Please contact your instructor.
        </div>
        @endif

        {{-- Save progress before navigating away --}}
        @if($autoSaveEnabled)
            <script>
                document.addEventListener('livewire:init', () => {
                    window.addEventListener('beforeunload', () => {
                        @this.call('saveProgress');
                    });
                });
            </script>
        @endif

        {{-- Keyboard Navigation --}}
        <script>
            document.addEventListener('keydown', (e) => {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'SELECT') {
                    if (e.key === 'ArrowLeft' && !e.shiftKey) {
                        @this.call('previousQuestion');
                        e.preventDefault();
                    } else if (e.key === 'ArrowRight' && !e.shiftKey) {
                        @this.call('nextQuestion');
                        e.preventDefault();
                    }
                }
                if (e.altKey && e.key === 's') {
                    @this.call('saveProgress');
                    e.preventDefault();
                }
                if (e.altKey && e.key === 'r' && @js($currentQuestionIndex === $totalQuestions - 1)) {
                    @this.call('showReview');
                    e.preventDefault();
                }
            });
        </script>
    </div>
@endif
</div>
