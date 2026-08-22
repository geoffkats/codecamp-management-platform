<div class="p-6 space-y-6">
    <div class="rounded-2xl border border-indigo-100/70 dark:border-slate-700/60 bg-gradient-to-r from-indigo-50 via-blue-50 to-slate-50 dark:from-slate-900 dark:via-slate-900/70 dark:to-slate-900 shadow-sm">
        <div class="p-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/80 dark:bg-slate-800/70 px-3 py-1 text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                    ICDL Student Dashboard
                </div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back, {{ $user->name }}! 👋</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $studentProfile?->school?->name ?? 'School not assigned' }} · {{ $studentProfile?->class_grade ?? 'Class not set' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('enrollments.index') }}" wire:navigate class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">My Modules</a>
                <a href="{{ route('assessments.index') }}" wire:navigate class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">Internal Tests</a>
                <a href="{{ route('certificates.index') }}" wire:navigate class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium">Certificates</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-indigo-100/70 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-indigo-600 dark:text-indigo-300 font-semibold">Exam Readiness</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                {{ str_replace('_', ' ', $studentProfile?->exam_readiness_status ?? 'not_ready') }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Internal tests & readiness check</p>
        </div>
        <div class="rounded-xl border border-emerald-100/70 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-emerald-600 dark:text-emerald-300 font-semibold">Payment Status</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                {{ str_replace('_', ' ', $studentProfile?->payment_status ?? 'not_submitted') }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fees & receipts</p>
        </div>
        <div class="rounded-xl border border-amber-100/70 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-amber-600 dark:text-amber-300 font-semibold">ICDL Test Status</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                {{ str_replace('_', ' ', $studentProfile?->icdl_test_status ?? 'not_submitted') }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Admin review & approval</p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Modules Enrolled</h2>
            <a href="{{ route('enrollments.index') }}" wire:navigate class="text-sm text-indigo-600 dark:text-indigo-300">View all</a>
        </div>
        <div class="p-6">
            @if($enrollments->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">No modules enrolled yet.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($enrollments as $enrollment)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->course?->title }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">Progress</div>
                            <div class="mt-2 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-2 rounded-full bg-indigo-600" style="width: {{ min(100, max(0, (float) ($enrollment->progress_percentage ?? 0))) }}%"></div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ number_format($enrollment->progress_percentage ?? 0, 1) }}%</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">ICDL Exam Results</h2>
        </div>
        <div class="p-6">
            @if($examResults->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">No ICDL exam results submitted yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($examResults as $result)
                        <div class="flex flex-wrap items-center justify-between gap-2 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $result->module?->title ?? 'Module' }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Session: {{ $result->exam_session }} · {{ $result->exam_date?->format('M j, Y') }}</div>
                            </div>
                            <div class="text-sm">
                                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($result->score, 1) }}</span>
                                <span class="ml-2 px-2 py-1 rounded-full text-xs {{ $result->result === 'pass' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                    {{ $result->result === 'pass' ? 'Pass' : 'Fail' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assessment Performance</h2>
            <a href="{{ route('assessments.index') }}" wire:navigate class="text-sm text-indigo-600 dark:text-indigo-300">View all</a>
        </div>
        <div class="p-6">
            @if($assessmentAttempts->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">No assessment attempts yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($assessmentAttempts as $attempt)
                        @php
                            $assessment = $attempt->assessment;
                            $maxScore = ($assessment?->questions && $assessment->questions->count() > 0)
                                ? $assessment->questions->sum('points')
                                : 100;
                            $attemptScore = $attempt->score ?? 0;
                            $percentage = min($maxScore > 0 ? ($attemptScore / $maxScore) * 100 : 0, 100);
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-3 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $assessment?->title ?? 'Assessment' }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ $assessment?->lesson?->title ?? $assessment?->course?->title ?? 'Module' }}
                                    · {{ $attempt->completed_at?->format('M j, Y') ?? '—' }}
                                </div>
                            </div>
                            <div class="text-sm flex items-center gap-2">
                                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($percentage, 1) }}%</span>
                                <span class="px-2 py-1 rounded-full text-xs {{ $attempt->is_passed ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                    {{ $attempt->is_passed ? 'Pass' : 'Fail' }}
                                </span>
                                <a href="{{ route('assessments.results', ['assessment' => $assessment?->id, 'attempt' => $attempt->id]) }}" wire:navigate class="text-xs text-indigo-600 dark:text-indigo-300">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
