<div class="max-w-6xl mx-auto p-6 space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold mb-2">Grade {{ $submissionType === 'assignment' ? 'Assignment' : 'Assessment' }}</h1>
                <p class="text-purple-100">
                    @if($submissionType === 'assignment')
                        {{ $submission->assignment->title }}
                    @else
                        {{ $submission->assessment->title }}
                    @endif
                </p>
                <p class="text-sm text-purple-200 mt-1">
                    Submitted by: {{ $submission->user->name }}
                </p>
            </div>
            <a href="{{ route('submissions.index') }}" wire:navigate class="text-white/80 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Grading Area --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Submission Content --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Submission</h2>
                
                {{-- Submission Text Content --}}
                @if($submissionContent)
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Text Submission</h3>
                        <div class="prose dark:prose-invert max-w-none p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="whitespace-pre-wrap break-words text-gray-900 dark:text-gray-100">
                                {!! nl2br(e($submissionContent)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Attachments from Assignment or Assessment --}}
                @if(count($submissionFiles) > 0)
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">
                            @if($submissionType === 'assessment')
                                Uploaded Files & Attachments
                            @else
                                Attachments
                            @endif
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($submissionFiles as $file)
                                @php
                                    if (is_array($file)) {
                                        $filePath = is_string($file['path'] ?? null) ? $file['path'] : '';
                                        $fileName = is_string($file['name'] ?? null) ? $file['name'] : basename($filePath);
                                        $questionText = $file['question_text'] ?? null;
                                        $questionId = $file['question_id'] ?? null;
                                    } else {
                                        $filePath = (string) $file;
                                        $fileName = basename($filePath);
                                        $questionText = null;
                                        $questionId = null;
                                    }
                                @endphp
                                @if($filePath === '') @continue @endif
                                <a href="{{ \App\Support\SubmissionFile::downloadUrl($filePath, $fileName) }}"
                                   class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors border border-gray-200 dark:border-gray-600">
                                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $fileName }}</p>
                                        @if($questionText)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate" title="{{ $questionText }}">
                                                Q: {{ Str::limit($questionText, 40) }}
                                            </p>
                                        @else
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Click to download</p>
                                        @endif
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Assessment Questions with File Uploads --}}
                @if($submissionType === 'assessment' && $assessmentQuestions->count() > 0)
                    @php
                        $answers = $submission->answers ?? [];
                        $hasQuestionAnswers = false;
                        foreach ($assessmentQuestions as $question) {
                            if (isset($answers[$question->id]) && $question->question_type !== 'file_upload') {
                                $hasQuestionAnswers = true;
                                break;
                            }
                        }
                    @endphp
                    
                    @if($hasQuestionAnswers)
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Question Answers</h3>
                            <div class="space-y-4">
                                @foreach($assessmentQuestions as $question)
                                    @if(isset($answers[$question->id]) && $question->question_type !== 'file_upload')
                                        @php
                                            $questionAnswer = $answers[$question->id];
                                            $answerValue = is_array($questionAnswer) ? ($questionAnswer['value'] ?? $questionAnswer) : $questionAnswer;
                                        @endphp
                                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ Str::limit($question->question_text, 100) }}
                                            </p>
                                            <div class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">
                                                {{ is_string($answerValue) ? $answerValue : json_encode($answerValue, JSON_PRETTY_PRINT) }}
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Empty State --}}
                @if(empty($submissionContent) && count($submissionFiles) === 0)
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p>No submission content available</p>
                    </div>
                @endif
            </div>

            {{-- Rubric Grading (if available) --}}
            @if(!empty($rubricCriteria))
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Rubric Assessment</h2>
                    <div class="space-y-6">
                        @foreach($rubricCriteria as $index => $criterion)
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">
                                            {{ $criterion['name'] ?? 'Criterion ' . ($index + 1) }}
                                        </h3>
                                        @if(isset($criterion['description']))
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                {{ $criterion['description'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                                        {{ $criterion['max_points'] ?? 0 }} points max
                                    </span>
                                </div>
                                
                                <div class="space-y-3">
                                    <div>
                                        <flux:field>
                                            <flux:label>Score</flux:label>
                                            <flux:input 
                                                type="number" 
                                                wire:model.live="rubricScores.{{ $index }}.score"
                                                min="0"
                                                max="{{ $criterion['max_points'] ?? 0 }}"
                                                step="0.5" />
                                        </flux:field>
                                    </div>
                                    <div>
                                        <flux:field>
                                            <flux:label>Feedback (Optional)</flux:label>
                                            <flux:textarea 
                                                wire:model="rubricScores.{{ $index }}.feedback"
                                                rows="2"
                                                placeholder="Provide feedback for this criterion..." />
                                        </flux:field>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Score: {{ $rubricScores[$index]['score'] ?? 0 }} / {{ $criterion['max_points'] ?? 0 }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Manual Scoring --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Scoring</h2>
                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Score</flux:label>
                            <flux:input 
                                type="number" 
                                wire:model.live="totalScore"
                                min="0"
                                max="{{ $maxScore }}"
                                step="0.5" />
                            <flux:description>Maximum: {{ $maxScore }} points</flux:description>
                        </flux:field>
                    </div>
                </div>
            @endif

            {{-- Feedback --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Feedback</h2>
                <flux:field>
                    <flux:textarea 
                        wire:model="feedback"
                        rows="8"
                        placeholder="Provide detailed feedback to the student..." />
                </flux:field>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Grade Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Grade Summary</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Score</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format((float) $totalScore, 2) }} / {{ $maxScore }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Percentage</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format((float) $percentage, 1) }}%
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Letter Grade</p>
                        <p class="text-3xl font-bold {{ $percentage >= 70 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $letterGrade }}
                        </p>
                    </div>
                    
                    {{-- Progress Bar --}}
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="h-3 rounded-full transition-all duration-300 {{ $percentage >= 70 ? 'bg-green-600' : 'bg-red-600' }}" 
                                 style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submission Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Submission Details</h3>
                <div class="space-y-2 text-sm">
                    @if($submissionType === 'assignment')
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Course</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $submission->assignment?->course?->title ?? '—' }}</p>
                        </div>
                        @if($submission->assignment?->due_date)
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Due Date</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $submission->assignment->due_date->format('M d, Y H:i') }}
                                </p>
                            </div>
                        @endif
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Submitted</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y H:i') : 'Not submitted' }}
                            </p>
                        </div>
                    @else
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Course</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $submission->assessment?->course?->title ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Assessment Type</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ ucfirst(str_replace('_', ' ', $submission->assessment?->assessment_type ?? '—')) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Submitted</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $submission->completed_at ? $submission->completed_at->format('M d, Y H:i') : 'Not submitted' }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="space-y-3">
                <flux:button wire:click="save" variant="primary" class="w-full">
                    Save Grade
                </flux:button>
                <flux:button href="{{ route('submissions.index') }}" wire:navigate variant="ghost" class="w-full">
                    Cancel
                </flux:button>
            </div>
        </div>
    </div>
</div>

@if(session()->has('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
         class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('message') }}
    </div>
@endif
