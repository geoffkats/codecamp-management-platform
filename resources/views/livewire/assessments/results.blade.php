<div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6">
    @php
        $passed = ! $isPending && $attempt->is_passed;
        $rightCount = count($correctQuestions);
        $wrongCount = count($incorrectQuestions);
        $ring = 2 * M_PI * 52;
        $ringFill = $isPending || $percentage === null ? 0 : $ring * min(max((float) $percentage, 0), 100) / 100;
        $timeDisplay = null;
        if ($attempt->time_spent && $attempt->time_spent < 1440) {
            $mins = (int) $attempt->time_spent;
            $timeDisplay = $mins >= 60 ? intdiv($mins, 60).'h'.($mins % 60 > 0 ? ' '.($mins % 60).'m' : '') : $mins.' min';
        }
    @endphp

    {{-- ── Score stage ─────────────────────────────────────────────── --}}
    <div class="relative isolate overflow-hidden rounded-3xl bg-[#46178f] p-6 text-white shadow-xl sm:p-8">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -left-20 -top-20 size-64 rotate-12 rounded-[3rem] bg-white/5"></div>
            <div class="absolute -bottom-24 -right-16 size-72 rounded-full bg-black/10"></div>
            @if($passed)
                <svg class="absolute left-[12%] top-8 size-5 rotate-12 fill-[#d89e00]" viewBox="0 0 32 32"><path d="M16 3 30 28H2Z"/></svg>
                <svg class="absolute right-[18%] top-6 size-4 fill-[#26890c]" viewBox="0 0 32 32"><rect x="4" y="4" width="24" height="24" rx="2"/></svg>
                <svg class="absolute bottom-10 left-[8%] size-4 fill-[#1368ce]" viewBox="0 0 32 32"><path d="M16 2 30 16 16 30 2 16Z"/></svg>
                <svg class="absolute bottom-16 right-[10%] size-5 fill-[#e21b3c]" viewBox="0 0 32 32"><circle cx="16" cy="16" r="13"/></svg>
            @endif
        </div>

        <div class="flex flex-col items-center gap-6 text-center sm:flex-row sm:text-left">
            {{-- Score ring --}}
            <div class="relative grid size-36 shrink-0 place-items-center">
                <svg class="absolute inset-0 size-36 -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                    <circle cx="60" cy="60" r="52" fill="rgba(0,0,0,0.2)" stroke="rgba(255,255,255,0.15)" stroke-width="10" />
                    <circle cx="60" cy="60" r="52" fill="none" stroke-width="10" stroke-linecap="round"
                            stroke="{{ $isPending ? '#d89e00' : ($passed ? '#ffffff' : '#ff3355') }}"
                            stroke-dasharray="{{ $ring }}" stroke-dashoffset="{{ $ring - $ringFill }}" />
                </svg>
                <div class="relative">
                    @if($isPending)
                        <flux:icon name="clock" class="mx-auto size-10 text-[#ffd166]" />
                        <p class="mt-1 text-xs font-black uppercase tracking-wider text-white/80">Pending</p>
                    @else
                        <p class="text-4xl font-black tabular-nums leading-none">{{ $percentage !== null ? number_format($percentage, 0) : '—' }}<span class="text-xl">%</span></p>
                        <p class="mt-1 text-xs font-bold text-white/70">score</p>
                    @endif
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <span @class([
                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider shadow-[0_3px_0_rgba(0,0,0,0.25)]',
                    'bg-[#d89e00]' => $isPending,
                    'bg-[#26890c]' => $passed,
                    'bg-[#e21b3c]' => ! $isPending && ! $passed,
                ])>
                    @if($isPending) Waiting for your teacher @elseif($passed) 🎉 Passed! @else Not passed yet @endif
                </span>
                <h1 class="mt-3 text-2xl font-black leading-tight tracking-tight sm:text-3xl">{{ $assessment->title }}</h1>
                <p class="mt-1 text-sm font-semibold text-white/70">
                    Submitted {{ $attempt->completed_at?->format('d M Y, H:i') ?? '—' }}
                    @if($timeDisplay) &nbsp;·&nbsp; {{ $timeDisplay }} @endif
                    @if($canGrade && $attempt->user) &nbsp;·&nbsp; {{ $attempt->user->name }} @endif
                </p>

                @if($isPending)
                    <p class="mt-3 text-sm font-semibold text-white/90">Your submission is awaiting instructor review. You'll be notified when it's graded.</p>
                @elseif(! $passed)
                    <p class="mt-3 text-sm font-semibold text-white/90">You need {{ $assessment->passing_score }}% to pass. Check the answers below, then retake when you're ready.</p>
                @endif
            </div>
        </div>

        @if(! $isPending)
            {{-- Stat tiles --}}
            <div class="mt-6 grid grid-cols-3 gap-2 sm:gap-3">
                <div class="rounded-2xl bg-[#26890c] p-3 shadow-[0_4px_0_rgba(0,0,0,0.25)] sm:p-4">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-white/85">
                        <x-assessments.answer-shape shape="square" class="size-3.5" /> Correct
                    </div>
                    <p class="mt-1 text-2xl font-black tabular-nums sm:text-3xl">{{ $rightCount }}</p>
                </div>
                <div class="rounded-2xl bg-[#e21b3c] p-3 shadow-[0_4px_0_rgba(0,0,0,0.25)] sm:p-4">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-white/85">
                        <x-assessments.answer-shape shape="triangle" class="size-3.5" /> Wrong
                    </div>
                    <p class="mt-1 text-2xl font-black tabular-nums sm:text-3xl">{{ $wrongCount }}</p>
                </div>
                <div class="rounded-2xl bg-[#1368ce] p-3 shadow-[0_4px_0_rgba(0,0,0,0.25)] sm:p-4">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-white/85">
                        <x-assessments.answer-shape shape="diamond" class="size-3.5" /> Points
                    </div>
                    <p class="mt-1 text-2xl font-black tabular-nums sm:text-3xl">
                        {{ rtrim(rtrim(number_format($attempt->scoreAsPoints() ?? 0, 1), '0'), '.') }}<span class="text-base font-bold text-white/70"> / {{ rtrim(rtrim(number_format((float) $maxScore, 1), '0'), '.') }}</span>
                    </p>
                </div>
            </div>

            @if($percentage !== null)
                <div class="mt-5">
                    <div class="relative h-3 overflow-hidden rounded-full bg-black/25">
                        <div class="h-full rounded-full {{ $passed ? 'bg-white' : 'bg-[#ff3355]' }}" style="width: {{ min($percentage, 100) }}%"></div>
                        @if($assessment->passing_score)
                            <div class="absolute inset-y-0 w-1 rounded bg-[#ffd166]" style="left: calc({{ min($assessment->passing_score, 100) }}% - 2px)"></div>
                        @endif
                    </div>
                    @if($assessment->passing_score)
                        <p class="mt-1.5 text-right text-xs font-bold text-white/70">
                            <span class="mr-1 inline-block size-2 rounded-sm bg-[#ffd166]"></span>Pass mark {{ $assessment->passing_score }}%
                        </p>
                    @endif
                </div>
            @endif
        @endif

        {{-- Actions --}}
        <div class="mt-6 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
            @unless($canGrade)
                @if(! $isPending && ! $passed)
                    <a href="{{ route('assessments.take', $assessment) }}" wire:navigate
                       class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-black text-[#46178f] shadow-[0_4px_0_rgba(0,0,0,0.25)] transition hover:bg-gray-100 active:translate-y-1 active:shadow-none">
                        <flux:icon name="arrow-path" variant="mini" class="size-4" /> Retake
                    </a>
                @endif
                <a href="{{ $assessment->lesson
                        ? route('lessons.view', $assessment->lesson)
                        : (auth()->user()->isIctTeacher() ? route('modules.index') : route('courses.show', $assessment->course)) }}"
                   wire:navigate
                   @class([
                       'inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-black transition active:translate-y-1',
                       'bg-white text-[#46178f] shadow-[0_4px_0_rgba(0,0,0,0.25)] hover:bg-gray-100 active:shadow-none' => $passed,
                       'bg-white/15 text-white hover:bg-white/25' => ! $passed,
                   ])>
                    {{ $assessment->lesson ? 'Back to Lesson' : 'Back to Course' }}
                    <flux:icon name="arrow-right" variant="mini" class="size-4" />
                </a>
            @endunless
            <a href="{{ route('assessments.show', $assessment) }}" wire:navigate
               class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/25">
                {{ $canGrade ? '← All Submissions' : 'Assessment details' }}
            </a>
        </div>
    </div>

    {{-- ── Wrong answers first, so students see what to improve ─────── --}}
    @if(! $isPending && $wrongCount > 0)
        <section>
            <h2 class="mb-3 flex items-center gap-2 text-lg font-black text-gray-900 dark:text-white">
                <span class="grid size-7 place-items-center rounded-lg bg-[#e21b3c]"><x-assessments.answer-shape shape="triangle" class="size-4" /></span>
                Let's fix these
                <span class="rounded-full bg-[#e21b3c]/10 px-2 py-0.5 text-sm font-black text-[#e21b3c]">{{ $wrongCount }}</span>
            </h2>
            <div class="space-y-4">
                @foreach($incorrectQuestions as $index => $item)
                    <div class="rounded-2xl bg-white p-5 shadow-[0_4px_0_rgba(0,0,0,0.06)] ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700">
                        <div class="mb-4 flex items-start gap-3 text-[15px] font-bold text-gray-900 dark:text-white">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-[#46178f] text-xs font-black text-white">{{ $index + 1 }}</span>
                            <x-question-text :text="$item['question']" class="min-w-0 flex-1 pt-1" />
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <div class="flex items-start gap-3 rounded-xl bg-[#e21b3c] p-3.5 text-white shadow-[0_4px_0_rgba(0,0,0,0.2)]">
                                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-white text-[#e21b3c]"><flux:icon name="x-mark" variant="mini" class="size-4" /></span>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-white/80">Your answer</p>
                                    <p class="text-sm font-bold [overflow-wrap:anywhere]">{{ $item['your_answer'] ?: 'No answer given' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 rounded-xl bg-[#26890c] p-3.5 text-white shadow-[0_4px_0_rgba(0,0,0,0.2)]">
                                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-white text-[#26890c]"><flux:icon name="check" variant="mini" class="size-4" /></span>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-white/80">Correct answer</p>
                                    <p class="text-sm font-bold [overflow-wrap:anywhere]">{{ $item['correct_answer'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ── Questions answered correctly ── --}}
    @if(! $isPending && $rightCount > 0)
        <section>
            <h2 class="mb-3 flex items-center gap-2 text-lg font-black text-gray-900 dark:text-white">
                <span class="grid size-7 place-items-center rounded-lg bg-[#26890c]"><x-assessments.answer-shape shape="square" class="size-3.5" /></span>
                You got these right
                <span class="rounded-full bg-[#26890c]/10 px-2 py-0.5 text-sm font-black text-[#26890c]">{{ $rightCount }}</span>
            </h2>
            <div class="space-y-3">
                @foreach($correctQuestions as $index => $item)
                    <div class="flex items-start gap-3 rounded-2xl bg-white p-4 ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-[#26890c] text-white"><flux:icon name="check" variant="mini" class="size-4" /></span>
                        <div class="min-w-0 flex-1">
                            <x-question-text :text="$item['question']" class="pt-1 text-sm font-semibold text-gray-900 dark:text-white" />
                            <p class="mt-2 inline-flex rounded-lg bg-[#26890c]/10 px-2.5 py-1 text-sm font-bold text-[#1f6f0a] dark:text-green-300">{{ $item['your_answer'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
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
                                    <x-question-text :text="$row['question']" class="text-sm font-semibold text-gray-900 dark:text-white" />
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
                        class="w-full py-2.5 text-sm font-bold bg-[#46178f] hover:brightness-110 text-white rounded-xl transition-colors">
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
</div>
