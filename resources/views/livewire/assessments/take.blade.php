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
                                    <x-question-text :text="$question->question_text" class="mb-3 text-gray-700 dark:text-gray-300" />
                                    
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
    <div class="relative isolate flex min-h-[calc(100vh-6rem)] flex-col overflow-hidden rounded-3xl bg-[#46178f] p-4 text-white shadow-xl sm:p-6">
        {{-- Kahoot-style backdrop shapes --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -left-24 -top-24 size-72 rotate-12 rounded-[3rem] bg-white/5"></div>
            <div class="absolute -right-16 top-1/3 size-56 rotate-45 rounded-[2.5rem] bg-white/5"></div>
            <div class="absolute -bottom-28 left-1/3 size-80 rounded-full bg-black/10"></div>
        </div>

        {{-- Assessment Header --}}
        <div class="mx-auto w-full max-w-5xl">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="truncate text-lg font-black tracking-tight sm:text-xl">{{ $assessment->title }}</h1>
                    <p class="truncate text-xs font-semibold text-white/70 sm:text-sm">{{ $assessment->course->title }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                @if($totalQuestions > 0)
                    <span class="rounded-full bg-white px-3.5 py-1.5 text-sm font-black text-[#46178f] shadow-[0_3px_0_rgba(0,0,0,0.25)] tabular-nums">
                        {{ $currentQuestionIndex + 1 }} <span class="font-bold text-[#46178f]/50">/ {{ $totalQuestions }}</span>
                    </span>
                @endif
                @if($timeRemaining !== null && $timeRemaining > 0)
                    @php $timerTotal = max((int) ($assessment->time_limit_minutes ?? 0) * 60, (int) $timeRemaining, 1); @endphp
                    <div class="relative grid size-16 place-items-center"
                         role="timer" aria-label="Time remaining"
                         x-data="{
                            remaining: {{ (int) $timeRemaining }},
                            total: {{ $timerTotal }},
                            get minutes() { return Math.floor(this.remaining / 60); },
                            get seconds() { return this.remaining % 60; },
                            get isUrgent() { return this.remaining <= 60; },
                            get dash() { return 175.9 * (this.remaining / this.total); },
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
                        <svg class="absolute inset-0 size-16 -rotate-90" viewBox="0 0 64 64" aria-hidden="true">
                            <circle cx="32" cy="32" r="28" fill="rgba(0,0,0,0.25)" stroke="rgba(255,255,255,0.15)" stroke-width="5" />
                            <circle cx="32" cy="32" r="28" fill="none" stroke-width="5" stroke-linecap="round"
                                    stroke-dasharray="175.9" :stroke-dashoffset="175.9 - dash"
                                    :stroke="isUrgent ? '#ff3355' : '#ffffff'" class="transition-all duration-1000 ease-linear" />
                        </svg>
                        <span class="relative text-sm font-black tabular-nums" :class="isUrgent && 'animate-pulse text-[#ffb3c0]'">
                            <span x-text="String(minutes).padStart(2, '0')"></span>:<span x-text="String(seconds).padStart(2, '0')"></span>
                        </span>
                    </div>
                @endif
                </div>
            </div>

            @if($totalQuestions > 0)
                @php $progressPercent = round((($currentQuestionIndex + 1) / max($totalQuestions, 1)) * 100); @endphp
                <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-black/25" role="progressbar" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="h-full rounded-full bg-white transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                </div>
            @endif
        </div>

        <div class="mx-auto mt-6 flex w-full max-w-5xl flex-1 flex-col">

        @if($assessment->assessment_type === 'assignment')
            <div class="text-gray-900 dark:text-white">
                @include('livewire.assessments.partials.assignment-brief', ['assessment' => $assessment])
            </div>
        @endif

        @if($assessment->assessment_type === 'assignment' && $totalQuestions === 0)
            @php
                $allowText = $assessment->assignment_data['allow_text'] ?? true;
                $allowFiles = $assessment->assignment_data['allow_files'] ?? true;
            @endphp
            <div class="mb-6 rounded-2xl bg-white p-6 text-gray-900 shadow-xl dark:bg-gray-900 dark:text-white">
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
            <div class="pointer-events-none fixed bottom-6 right-6 z-40 text-sm"
                 x-data="{ show: false }"
                 x-init="
                     window.addEventListener('progress-saved', () => { show = true; setTimeout(() => show = false, 2000); });
                 ">
                <span x-show="show" x-cloak
                      x-transition
                      class="flex items-center gap-2 rounded-full bg-[#26890c] px-4 py-2 font-bold text-white shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Progress saved
                </span>
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
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2 line-clamp-2">{{ \App\Support\QuestionText::preview($question->question_text, 120)['text'] }}</p>

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
                        <button wire:click="confirmSubmit" class="rounded-xl bg-[#46178f] px-6 py-2.5 font-bold text-white shadow-[0_3px_0_rgba(0,0,0,0.25)] hover:brightness-110">
                            Confirm & Submit
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Current Question — wire:key forces a clean remount so text/options never bleed across questions --}}
        @if($currentQuestion)
            @php
                $isTileType = in_array($currentQuestion->question_type, ['multiple_choice', 'multiple_select', 'choice', 'true_false'], true);
                $tiles = [
                    ['bg' => 'bg-[#e21b3c]', 'text' => 'text-[#e21b3c]', 'shape' => 'triangle'],
                    ['bg' => 'bg-[#1368ce]', 'text' => 'text-[#1368ce]', 'shape' => 'diamond'],
                    ['bg' => 'bg-[#d89e00]', 'text' => 'text-[#d89e00]', 'shape' => 'circle'],
                    ['bg' => 'bg-[#26890c]', 'text' => 'text-[#26890c]', 'shape' => 'square'],
                ];
            @endphp
            <div wire:key="question-panel-{{ $currentQuestion->id }}" class="mb-6">
                <div class="relative mb-5 rounded-2xl bg-white px-5 pb-6 pt-12 text-gray-900 shadow-[0_6px_0_rgba(0,0,0,0.2)] sm:px-10 dark:bg-gray-900 dark:text-white">
                    <div class="absolute inset-x-4 top-3 flex items-center justify-between gap-2">
                        <span class="rounded-full bg-[#46178f]/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-[#46178f] dark:bg-white/10 dark:text-white">
                            {{ $assessment->assessment_type === 'assignment' ? 'Task' : 'Question' }} {{ $currentQuestionIndex + 1 }}
                            · {{ rtrim(rtrim(number_format((float) $currentQuestion->points, 1), '0'), '.') }} {{ (float) $currentQuestion->points === 1.0 ? 'pt' : 'pts' }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button 
                                wire:click="toggleBookmark({{ $currentQuestionIndex }})"
                                aria-label="{{ in_array($currentQuestionIndex, $bookmarkedQuestions) ? 'Remove bookmark' : 'Bookmark question' }}"
                                class="rounded-lg p-1.5 transition-colors
                                    {{ in_array($currentQuestionIndex, $bookmarkedQuestions) 
                                        ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400' 
                                        : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800' }}">
                                <svg class="w-5 h-5" fill="{{ in_array($currentQuestionIndex, $bookmarkedQuestions) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </button>
                            <button 
                                wire:click="toggleFlag({{ $currentQuestionIndex }})"
                                aria-label="{{ in_array($currentQuestionIndex, $flaggedQuestions) ? 'Remove flag' : 'Flag for review' }}"
                                class="rounded-lg p-1.5 transition-colors
                                    {{ in_array($currentQuestionIndex, $flaggedQuestions) 
                                        ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' 
                                        : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <x-question-text :text="$currentQuestion->question_text" :scratch-scale="0.85"
                                     class="mx-auto max-w-3xl text-center text-xl font-bold leading-snug sm:text-2xl [&_[data-scratch]]:text-left" />

                    {{-- Question Image --}}
                    @if($currentQuestion->image_url)
                        <div class="mt-5 flex justify-center">
                            <x-storage-image :path="$currentQuestion->image_url" alt="Question image" class="max-h-72 rounded-xl" />
                        </div>
                    @endif
                </div>

                <div @class(['rounded-2xl bg-white p-5 text-gray-900 shadow-xl sm:p-6 dark:bg-gray-900 dark:text-white' => ! $isTileType])>

                {{-- Question Options (Multiple Choice, Multiple Select, Choice) --}}
                @if(in_array($currentQuestion->question_type, ['multiple_choice', 'multiple_select', 'choice']))
                    @php
                        $userAnswer = $answers[$currentQuestion->id] ?? null;
                        $isMultiple = $currentQuestion->question_type === 'multiple_select';
                    @endphp
                    @if($isMultiple)
                        <p class="mb-3 inline-flex rounded-full bg-white/15 px-3 py-1 text-sm font-bold text-white">Select all that apply</p>
                    @endif
                    @php
                        $hasChoice = $isMultiple ? count(array_filter((array) $userAnswer)) > 0 : filled($userAnswer);
                    @endphp
                    <div class="grid gap-3 sm:grid-cols-2" wire:key="options-{{ $currentQuestion->id }}">
                        @foreach($currentQuestion->options as $option)
                            @php
                                $tile = $tiles[$loop->index % 4];
                                $isChosen = $isMultiple ? in_array($option->id, (array) $userAnswer) : ($userAnswer == $option->id);
                            @endphp
                            <label wire:key="option-{{ $currentQuestion->id }}-{{ $option->id }}" data-answer-key="{{ $loop->iteration }}"
                                   class="relative flex min-h-[5.5rem] cursor-pointer items-center gap-4 rounded-xl p-4 text-white shadow-[0_5px_0_rgba(0,0,0,0.25)] transition duration-150 hover:brightness-110 active:translate-y-1 active:shadow-none has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-white/70
                                {{ $tile['bg'] }}
                                {{ $isChosen ? 'ring-4 ring-white ring-offset-4 ring-offset-[#46178f]' : ($hasChoice ? 'opacity-50 hover:opacity-90' : '') }}">
                                <input 
                                    type="{{ $currentQuestion->question_type === 'multiple_choice' || $currentQuestion->question_type === 'choice' ? 'radio' : 'checkbox' }}"
                                    name="answer-{{ $currentQuestion->id }}"
                                    wire:model.live="answers.{{ $currentQuestion->id }}"
                                    value="{{ $option->id }}"
                                    class="sr-only" />
                                <x-assessments.answer-shape :shape="$tile['shape']" class="size-9 shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-lg font-bold leading-snug [overflow-wrap:anywhere]">{{ $option->option_text }}</p>
                                    @if($option->image_url)
                                        <div class="mt-2">
                                            <x-storage-image :path="$option->image_url" alt="Option image" class="max-h-40 max-w-full rounded-lg bg-white" />
                                        </div>
                                    @endif
                                    {{-- Hide option explanations while answering --}}
                                </div>
                                @if($isChosen)
                                    <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white {{ $tile['text'] }}">
                                        <flux:icon name="check" variant="mini" class="size-5" />
                                    </span>
                                @else
                                    <span class="size-8 shrink-0 border-[3px] border-white/60 {{ $isMultiple ? 'rounded-lg' : 'rounded-full' }}"></span>
                                @endif
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
                    <div class="grid gap-3 sm:grid-cols-2" wire:key="truefalse-{{ $currentQuestion->id }}">
                        @foreach([['option' => $trueOption, 'label' => 'True', 'tile' => $tiles[1], 'key' => 'true'], ['option' => $falseOption, 'label' => 'False', 'tile' => $tiles[0], 'key' => 'false']] as $choice)
                            @continue(! $choice['option'])
                            @php $isChosen = $userAnswer == $choice['option']->id; @endphp
                            <label wire:key="{{ $choice['key'] }}-option-{{ $currentQuestion->id }}" data-answer-key="{{ $loop->iteration }}"
                                   class="relative flex min-h-[7rem] cursor-pointer items-center gap-4 rounded-xl p-5 text-white shadow-[0_5px_0_rgba(0,0,0,0.25)] transition duration-150 hover:brightness-110 active:translate-y-1 active:shadow-none has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-white/70
                                {{ $choice['tile']['bg'] }}
                                {{ $isChosen ? 'ring-4 ring-white ring-offset-4 ring-offset-[#46178f]' : (filled($userAnswer) ? 'opacity-50 hover:opacity-90' : '') }}">
                                <input 
                                    type="radio"
                                    name="answer-{{ $currentQuestion->id }}"
                                    wire:model.live="answers.{{ $currentQuestion->id }}"
                                    value="{{ $choice['option']->id }}"
                                    class="sr-only" />
                                <x-assessments.answer-shape :shape="$choice['tile']['shape']" class="size-10 shrink-0" />
                                <div class="flex-1">
                                    <span class="text-2xl font-black">{{ $choice['label'] }}</span>
                                    @if($choice['option']->image_url)
                                        <img src="{{ Storage::disk('public')->url($choice['option']->image_url) }}" 
                                             alt="{{ $choice['label'] }} option" 
                                             class="mt-2 max-h-40 max-w-full rounded-lg bg-white">
                                    @endif
                                </div>
                                @if($isChosen)
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white {{ $choice['tile']['text'] }}">
                                        <flux:icon name="check" variant="mini" class="size-5" />
                                    </span>
                                @else
                                    <span class="size-9 shrink-0 rounded-full border-[3px] border-white/60"></span>
                                @endif
                            </label>
                        @endforeach
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
                        $initialOrder = array_column($shuffledItems, 'item_text');
                        $savedOrder = $answers[$currentQuestion->id] ?? null;
                        if (is_array($savedOrder) && count($savedOrder) === count($initialOrder)
                            && collect($savedOrder)->map(fn ($t) => (string) $t)->sort()->values()->all() === collect($initialOrder)->sort()->values()->all()) {
                            $initialOrder = array_values(array_map('strval', $savedOrder));
                        }
                    @endphp
                    <div class="space-y-3"
                         x-data="{
                             items: @js($initialOrder),
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
                            <x-question-text :text="$questionText" class="text-gray-900 dark:text-white" />
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

                </div>
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
                class="flex items-center gap-2 rounded-xl bg-white/15 px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-white/25 disabled:cursor-not-allowed disabled:opacity-30">
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
                        class="flex items-center gap-2 rounded-xl px-6 py-3 text-base font-black shadow-[0_4px_0_rgba(0,0,0,0.25)] transition active:translate-y-1 active:shadow-none
                            {{ $isAnswered ? 'bg-white text-[#46178f] hover:bg-gray-100' : 'bg-white/20 text-white hover:bg-white/30' }}">
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
                        class="flex items-center gap-2 rounded-xl bg-[#26890c] px-6 py-3 text-base font-black text-white shadow-[0_4px_0_rgba(0,0,0,0.25)] transition hover:brightness-110 active:translate-y-1 active:shadow-none disabled:cursor-not-allowed disabled:opacity-60">
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

        {{-- Question navigator: answered / bookmarked / flagged at a glance, Review & Submit always reachable --}}
        <div class="mt-auto pt-6">
            <div class="flex flex-col gap-3 rounded-2xl bg-black/20 p-3 sm:flex-row sm:items-center">
                @if($totalQuestions > 1)
                    <div class="flex max-h-28 flex-1 flex-wrap gap-1.5 overflow-y-auto">
                        @foreach($this->getQuestions() as $index => $question)
                            @php
                                $isAnsweredDot = isset($answers[$question->id]) && $answers[$question->id] !== '' && $answers[$question->id] !== [];
                                $isCurrent = $index === $currentQuestionIndex;
                            @endphp
                            <button
                                wire:click="goToQuestion({{ $index }})"
                                type="button"
                                aria-label="Go to question {{ $index + 1 }}{{ $isAnsweredDot ? ' (answered)' : '' }}"
                                @if($isCurrent) aria-current="step" @endif
                                class="relative grid size-8 place-items-center rounded-full text-xs font-black transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white
                                    {{ $isCurrent
                                        ? 'scale-110 bg-white text-[#46178f] shadow'
                                        : ($isAnsweredDot ? 'bg-white/35 text-white hover:bg-white/50' : 'bg-white/10 text-white/70 hover:bg-white/20') }}">
                                {{ $index + 1 }}
                                @if(in_array($index, $bookmarkedQuestions))
                                    <span class="absolute -right-0.5 -top-0.5 size-2.5 rounded-full border border-[#46178f] bg-yellow-400"></span>
                                @elseif(in_array($index, $flaggedQuestions))
                                    <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border border-[#46178f] bg-[#ff3355]"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="flex-1"></div>
                @endif
                <button wire:click="showReview" type="button"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/25">
                    <flux:icon name="clipboard-document-check" variant="mini" class="size-4" />
                    Review &amp; Submit
                </button>
            </div>
        </div>

        @endif

        @if($totalQuestions === 0 && $assessment->assessment_type !== 'assignment')
        <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-6 text-sm text-amber-900 dark:text-amber-200">
            This assessment has no questions yet. Please contact your instructor.
        </div>
        @endif
        </div>

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
                    } else if (/^[1-9]$/.test(e.key) && !e.ctrlKey && !e.metaKey && !e.altKey) {
                        const tile = document.querySelector(`[data-answer-key="${e.key}"]`);
                        if (tile) {
                            tile.click();
                            e.preventDefault();
                        }
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
