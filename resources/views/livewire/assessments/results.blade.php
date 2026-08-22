<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-6">

    {{-- ── Pass / Fail hero card ──────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">

        {{-- Coloured top stripe --}}
        <div class="h-1.5 {{ $isPending ? 'bg-amber-400' : ($attempt->is_passed ? 'bg-green-500' : 'bg-red-500') }}"></div>

        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Assessment Result</p>
                    <h1 class="text-xl font-extrabold text-gray-900 dark:text-white leading-tight">{{ $assessment->title }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Submitted {{ $attempt->completed_at?->format('d M Y, H:i') ?? '—' }}
                        @if($attempt->time_spent && $attempt->time_spent < 1440)
                            @php
                                $mins = (int) $attempt->time_spent;
                                $timeDisplay = $mins >= 60
                                    ? intdiv($mins, 60) . 'h' . ($mins % 60 > 0 ? ' ' . ($mins % 60) . 'm' : '')
                                    : $mins . ' min';
                            @endphp
                            &nbsp;·&nbsp; {{ $timeDisplay }}
                        @endif
                    </p>
                </div>

                {{-- Score circle --}}
                <div class="flex-shrink-0 text-center">
                    @if($isPending)
                        <div class="w-20 h-20 rounded-full border-4 border-amber-300 dark:border-amber-600 flex items-center justify-center bg-amber-50 dark:bg-amber-900/20">
                            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 mt-2">Pending</p>
                    @else
                        <div class="w-20 h-20 rounded-full border-4 {{ $attempt->is_passed ? 'border-green-400 dark:border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/20' }} flex items-center justify-center">
                            <span class="text-xl font-extrabold {{ $attempt->is_passed ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                {{ $percentage !== null ? number_format($percentage, 0) : '—' }}%
                            </span>
                        </div>
                        <p class="text-xs font-bold mt-2 {{ $attempt->is_passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $attempt->is_passed ? '🎉 Passed!' : 'Not Passed' }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Score progress bar --}}
            @if(!$isPending && $percentage !== null)
            <div class="mt-5">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                    <span>Your score: <strong class="text-gray-900 dark:text-white">{{ number_format($attempt->scoreAsPoints() ?? 0, 1) }} / {{ $maxScore }} pts</strong></span>
                    <span>Passing: <strong class="text-gray-900 dark:text-white">{{ $assessment->passing_score }}%</strong></span>
                </div>
                <div class="relative h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 {{ $attempt->is_passed ? 'bg-green-500' : 'bg-red-500' }}"
                         style="width: {{ min($percentage, 100) }}%"></div>
                    {{-- Passing threshold marker --}}
                    @if($assessment->passing_score)
                    <div class="absolute top-0 bottom-0 w-0.5 bg-gray-400 dark:bg-gray-500 opacity-60"
                         style="left: {{ $assessment->passing_score }}%"></div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Failed banner --}}
            @if(!$isPending && !$attempt->is_passed)
            <div class="mt-4 flex items-start gap-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/60 rounded-xl">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-red-800 dark:text-red-200">
                        You need {{ $assessment->passing_score }}% to pass — review the answers below, then retake when you're ready.
                    </p>
                </div>
            </div>
            @endif

            {{-- Pending banner --}}
            @if($isPending)
            <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/60 rounded-xl text-sm text-amber-800 dark:text-amber-200">
                Your submission is awaiting instructor review. You'll be notified when it's graded.
            </div>
            @endif
        </div>
    </div>

    {{-- ── Wrong answers — shown first so students see what to improve ── --}}
    @if(!$isPending && count($incorrectQuestions) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-red-200 dark:border-red-800/50 overflow-hidden">
        <div class="px-6 py-4 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800/50 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-base font-bold text-red-900 dark:text-red-100">
                Questions You Got Wrong
                <span class="ml-1 text-sm font-normal text-red-600 dark:text-red-400">({{ count($incorrectQuestions) }})</span>
            </h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($incorrectQuestions as $index => $item)
            <div class="px-6 py-5">
                <p class="text-sm font-bold text-gray-900 dark:text-white mb-3">
                    <span class="text-gray-400 dark:text-gray-500 font-normal mr-1">Q{{ $index + 1 }}.</span>
                    {{ $item['question'] }}
                </p>
                <div class="space-y-2">
                    {{-- Student's wrong answer --}}
                    <div class="flex items-start gap-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <div>
                            <p class="text-[11px] font-bold text-red-600 dark:text-red-400 uppercase tracking-wide mb-0.5">Your answer</p>
                            <p class="text-sm text-red-800 dark:text-red-200">{{ $item['your_answer'] ?: 'No answer given' }}</p>
                        </div>
                    </div>
                    {{-- Correct answer --}}
                    <div class="flex items-start gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                        <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-[11px] font-bold text-green-700 dark:text-green-400 uppercase tracking-wide mb-0.5">Correct answer</p>
                            <p class="text-sm text-green-800 dark:text-green-200 font-semibold">{{ $item['correct_answer'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Questions answered correctly ── --}}
    @if(!$isPending && count($correctQuestions) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-green-200 dark:border-green-800/50 overflow-hidden">
        <div class="px-6 py-4 bg-green-50 dark:bg-green-900/20 border-b border-green-200 dark:border-green-800/50 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <h2 class="text-base font-bold text-green-900 dark:text-green-100">
                Questions You Got Right
                <span class="ml-1 text-sm font-normal text-green-600 dark:text-green-400">({{ count($correctQuestions) }})</span>
            </h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($correctQuestions as $index => $item)
            <div class="px-6 py-4 flex items-start gap-3">
                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        <span class="text-gray-400 dark:text-gray-500 font-normal mr-1">Q{{ $index + 1 }}.</span>
                        {{ $item['question'] }}
                    </p>
                    <p class="text-sm text-green-700 dark:text-green-300 mt-1">{{ $item['your_answer'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Teacher grading panel (assignments + any pending-review attempt) ── --}}
    @if($canGrade && ($assessment->assessment_type === 'assignment' || $isPending || !$attempt->auto_scored))
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4 gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Grade This Submission</h2>
                    @if($attempt->user)
                        <p class="text-sm text-gray-500 dark:text-gray-400">Student: {{ $attempt->user->name }}</p>
                    @endif
                </div>
                @if($isPending)
                    <span class="px-3 py-1 text-xs font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-full">Pending grading</span>
                @else
                    <span class="px-3 py-1 text-xs font-bold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full">
                        Graded: {{ number_format($percentage ?? 0, 1) }}%
                    </span>
                @endif
            </div>

            @if($autoGradablePoints > 0 || $manualQuestionCount > 0)
                <div class="mb-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-3">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-500">Auto-graded</p>
                        <p class="text-lg font-extrabold text-gray-900 dark:text-white mt-1">
                            {{ number_format($autoEarnedPoints, 1) }}
                            <span class="text-sm font-semibold text-gray-400">/ {{ number_format($autoGradablePoints, 1) }}</span>
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-3">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-500">Needs your score</p>
                        <p class="text-lg font-extrabold text-gray-900 dark:text-white mt-1">{{ $manualQuestionCount }} question{{ $manualQuestionCount === 1 ? '' : 's' }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-3">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-500">Max points</p>
                        <p class="text-lg font-extrabold text-gray-900 dark:text-white mt-1">{{ number_format($maxScore, 1) }}</p>
                    </div>
                </div>
            @endif

            @if(count($reviewQuestions) > 0)
                <div class="mb-5 space-y-3">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Full answer review</p>

                    @foreach($reviewQuestions as $row)
                        <div @class([
                            'rounded-xl border p-4',
                            'border-green-300 dark:border-green-800 bg-green-50/70 dark:bg-green-900/15' => $row['needs_manual'] === false && $row['is_correct'] === true,
                            'border-red-300 dark:border-red-800 bg-red-50/70 dark:bg-red-900/15' => $row['needs_manual'] === false && $row['is_correct'] === false,
                            'border-amber-300 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/15' => $row['needs_manual'] === true,
                            'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30' => $row['needs_manual'] === false && $row['is_correct'] === null,
                        ])>
                            <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Q{{ $row['number'] }}</span>
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-white/80 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                            {{ $row['type_label'] }}
                                        </span>
                                        <span class="text-[11px] text-gray-500">{{ number_format($row['points'], 1) }} pts</span>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($row['question']), 180) }}
                                    </p>
                                </div>

                                @if($row['needs_manual'])
                                    <span class="text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full bg-amber-200/80 dark:bg-amber-900/50 text-amber-900 dark:text-amber-200">
                                        Grade manually
                                    </span>
                                @elseif($row['is_correct'] === true)
                                    <span class="text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full bg-green-200/80 dark:bg-green-900/50 text-green-900 dark:text-green-200">
                                        Correct · {{ number_format($row['earned'], 1) }}/{{ number_format($row['points'], 1) }}
                                    </span>
                                @elseif($row['is_correct'] === false)
                                    <span class="text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full bg-red-200/80 dark:bg-red-900/50 text-red-900 dark:text-red-200">
                                        Incorrect · 0/{{ number_format($row['points'], 1) }}
                                    </span>
                                @endif
                            </div>

                            @if(!empty($row['options']))
                                <div class="mt-3 space-y-1.5">
                                    @foreach($row['options'] as $option)
                                        <div @class([
                                            'flex items-start gap-2 rounded-lg px-3 py-2 text-sm border',
                                            'border-green-400 bg-green-100/80 dark:bg-green-900/40 text-green-900 dark:text-green-100' => $option['is_correct'],
                                            'border-red-300 bg-red-100/70 dark:bg-red-900/30 text-red-900 dark:text-red-100' => $option['selected'] && ! $option['is_correct'],
                                            'border-gray-200 dark:border-gray-700 bg-white/70 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300' => ! $option['selected'] && ! $option['is_correct'],
                                        ])>
                                            <span class="mt-0.5 text-xs font-bold w-4 flex-shrink-0">
                                                @if($option['selected'] && $option['is_correct']) ✓
                                                @elseif($option['selected']) ✗
                                                @elseif($option['is_correct']) ○
                                                @else ·
                                                @endif
                                            </span>
                                            <span class="flex-1">{{ $option['text'] }}</span>
                                            <span class="text-[10px] font-semibold uppercase tracking-wide flex-shrink-0 opacity-80">
                                                @if($option['selected']) Student @endif
                                                @if($option['selected'] && $option['is_correct']) · @endif
                                                @if($option['is_correct']) Correct @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($row['type'] === 'file_upload')
                                @if(!empty($row['files']))
                                    <div class="mt-3 space-y-2">
                                        @foreach($row['files'] as $file)
                                            @php
                                                $filePath = is_array($file) ? ($file['path'] ?? '') : (string) $file;
                                                $fileName = is_array($file) ? ($file['name'] ?? basename($filePath)) : basename($filePath);
                                            @endphp
                                            @if($filePath === '') @continue @endif
                                            <a href="{{ \App\Support\SubmissionFile::downloadUrl($filePath, $fileName) }}"
                                               class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-colors">
                                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $fileName }}</span>
                                                <span class="ml-auto text-xs text-gray-400">Download</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-2 text-sm text-gray-500 italic">No file uploaded</p>
                                @endif
                            @elseif($row['type'] === 'code_submission')
                                @if(filled($row['student_answer']) && $row['student_answer'] !== '— No answer —')
                                    <pre class="mt-3 text-xs font-mono bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap text-gray-900 dark:text-gray-100">{{ $row['student_answer'] }}</pre>
                                @else
                                    <p class="mt-2 text-sm text-gray-500 italic">No code submitted</p>
                                @endif
                            @else
                                <div class="mt-3 space-y-2">
                                    <div class="rounded-lg bg-white/80 dark:bg-gray-800/70 border border-gray-200 dark:border-gray-700 px-3 py-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500 mb-1">Student answer</p>
                                        <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $row['student_answer'] }}</p>
                                    </div>
                                    @if(! $row['needs_manual'] && filled($row['correct_answer']))
                                        <div class="rounded-lg bg-green-100/60 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-3 py-2">
                                            <p class="text-[10px] font-bold uppercase tracking-wide text-green-700 dark:text-green-300 mb-1">Correct answer</p>
                                            <p class="text-sm font-medium text-green-900 dark:text-green-100">{{ $row['correct_answer'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @elseif(!empty($submissionFiles))
                <div class="mb-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Uploaded Files</p>
                    <div class="space-y-1">
                            @foreach($submissionFiles as $file)
                            <a href="{{ \App\Support\SubmissionFile::downloadUrl($file['path'], $file['name'] ?? null) }}"
                               class="flex items-center gap-2 px-3 py-2 text-sm text-blue-600 dark:text-blue-400 hover:underline bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                {{ $file['name'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($attempt->answers && isset($attempt->answers['submission_text']) && $attempt->answers['submission_text'])
                <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Student's Response</p>
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $attempt->answers['submission_text'] }}</p>
                </div>
            @endif

            @if(!$showGradeForm)
                <button type="button" wire:click="$set('showGradeForm', true)"
                        class="w-full py-2.5 text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition-colors">
                    {{ $isPending ? 'Enter Final Grade' : 'Update Grade' }}
                </button>
            @else
                <div class="border-t border-gray-100 dark:border-gray-700 pt-4 space-y-4">
                    @if($isPending && $autoGradablePoints > 0)
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Suggested starting score is the auto-graded total
                            (<strong class="text-gray-800 dark:text-gray-200">{{ number_format($autoEarnedPoints, 1) }}</strong>
                            — add points for short answers / open responses up to
                            <strong class="text-gray-800 dark:text-gray-200">{{ number_format($maxScore, 1) }}</strong>.
                        </p>
                    @endif
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                            Final score (out of {{ $maxScore }}) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" wire:model="gradeScore"
                                   min="0" max="{{ $maxScore }}" step="0.5"
                                   placeholder="e.g. 85"
                                   class="w-32 px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <span class="text-sm text-gray-500">/ {{ $maxScore }} (passing: {{ $assessment->passing_score }}%)</span>
                        </div>
                        @error('gradeScore') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Feedback (optional)</label>
                        <textarea wire:model="gradeFeedback" rows="3"
                                  placeholder="Write feedback for the student…"
                                  class="w-full px-3 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
                        @error('gradeFeedback') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="submitGrade"
                                class="flex-1 py-2.5 text-sm font-bold bg-green-600 hover:bg-green-700 text-white rounded-xl transition-colors">
                            Save Grade &amp; Notify Student
                        </button>
                        <button type="button" wire:click="$set('showGradeForm', false)"
                                class="px-4 py-2.5 text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Bottom action bar ── --}}
    <div class="flex flex-wrap items-center gap-3 justify-between">
        <div class="flex gap-2">
            <flux:button href="{{ route('assessments.show', $assessment) }}" wire:navigate variant="ghost" size="sm">
                {{ $canGrade ? '← All Submissions' : 'Back to Assessment' }}
            </flux:button>
        </div>
        <div class="flex gap-2">
            @unless($canGrade)
                {{-- Retake button for failed / not-yet-passed attempts --}}
                @if(!$isPending && !$attempt->is_passed)
                    <flux:button href="{{ route('assessments.take', $assessment) }}" wire:navigate variant="primary" size="sm">
                        Retake Assessment
                    </flux:button>
                @endif
                <flux:button
                    href="{{ $assessment->lesson
                        ? route('lessons.view', $assessment->lesson)
                        : (auth()->user()->isIctTeacher() ? route('modules.index') : route('courses.show', $assessment->course)) }}"
                    wire:navigate
                    variant="{{ $attempt->is_passed ? 'primary' : 'ghost' }}"
                    size="sm"
                >
                    {{ $assessment->lesson ? 'Back to Lesson' : 'Back to Course' }}
                </flux:button>
            @endunless
        </div>
    </div>

</div>
