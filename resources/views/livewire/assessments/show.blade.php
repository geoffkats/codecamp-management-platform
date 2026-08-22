<div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-6">
        <div class="flex items-start gap-4">
            <a href="{{ auth()->user()->isIctTeacher() ? route('modules.index') : route('courses.show', $assessment->course) }}"
               class="mt-0.5 p-2 rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    @php
                        $typeLabels = ['quiz'=>'Quiz','assignment'=>'Assignment','unit_survey'=>'Survey','rubric_assessment'=>'Rubric','peer_review'=>'Peer Review','self_assessment'=>'Self-Assessment','pre_project_test'=>'Pre-Project Test','post_project_test'=>'Post-Project Test'];
                        $typeColors = ['quiz'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300','assignment'=>'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300','unit_survey'=>'bg-green-100 text-green-700','rubric_assessment'=>'bg-orange-100 text-orange-700','peer_review'=>'bg-pink-100 text-pink-700','self_assessment'=>'bg-indigo-100 text-indigo-700','pre_project_test'=>'bg-yellow-100 text-yellow-700','post_project_test'=>'bg-yellow-100 text-yellow-800'];
                        $typeLabel = $typeLabels[$assessment->assessment_type] ?? ucfirst(str_replace('_', ' ', $assessment->assessment_type));
                        $typeColor = $typeColors[$assessment->assessment_type] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $typeColor }}">{{ $typeLabel }}</span>
                    @if($assessment->is_required)
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">Required</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $assessment->title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $assessment->course->title }}</p>
                @if($assessment->description)
                    <div class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                        <x-rich-text :content="$assessment->description" />
                    </div>
                @endif
                @if($assessment->assessment_type === 'assignment' && filled($assessment->assignment_data['instructions'] ?? null))
                    <div class="mt-3 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-800 dark:text-blue-200 mb-1">Instructions</p>
                        <x-rich-text :content="$assessment->assignment_data['instructions']" class="text-blue-900 dark:text-blue-100" />
                    </div>
                @endif
                @if($assessment->assessment_type === 'assignment' && $assessment->due_date)
                    <p class="text-xs font-semibold text-red-600 dark:text-red-400 mt-2">Due {{ $assessment->due_date->format('M j, Y') }}</p>
                @endif
                @if($assessment->assessment_type === 'assignment' && count($assessment->assignmentAttachments()) > 0)
                    <div class="mt-3 space-y-1">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Brief files</p>
                        @foreach($assessment->assignmentAttachments() as $file)
                            <a href="{{ \App\Support\RichContent::storageUrl($file['path']) }}" target="_blank" class="block text-sm text-blue-600 hover:underline truncate">{{ $file['name'] }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
            {{-- Quick meta --}}
            <div class="hidden sm:flex flex-col items-end gap-1 text-sm text-gray-500 dark:text-gray-400 flex-shrink-0">
                <span>{{ $assessment->max_attempts > 0 ? $assessment->max_attempts . ' attempt' . ($assessment->max_attempts !== 1 ? 's' : '') : 'Unlimited attempts' }}</span>
                @if($assessment->time_limit_minutes)
                    <span>{{ $assessment->time_limit_minutes }} min limit</span>
                @endif
                <span>{{ $assessment->passing_score }}% to pass</span>
                <span class="font-semibold text-orange-600 dark:text-orange-400">+{{ $assessment->xp_reward }} XP</span>
            </div>
        </div>

        {{-- Student: your best score + action --}}
        @if($hasTaken)
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center gap-4">
                @if($bestScore === null)
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                        <span class="text-sm font-semibold text-amber-700 dark:text-amber-400">Pending review</span>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <div class="text-2xl font-bold {{ $bestScore >= $assessment->passing_score ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ number_format($bestScore, 1) }}%
                        </div>
                        <div class="text-xs text-gray-400">best score</div>
                        <div class="w-32 h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $bestScore >= $assessment->passing_score ? 'bg-green-500' : 'bg-red-500' }}" style="width: {{ min($bestScore, 100) }}%"></div>
                        </div>
                    </div>
                @endif
                @if($attemptsRemaining !== 'unlimited')
                    <span class="text-xs text-gray-400">{{ $attemptsRemaining }} attempt{{ $attemptsRemaining !== 1 ? 's' : '' }} left</span>
                @endif
            </div>
        @endif

        {{-- Assignment tasks preview --}}
        @if($assessment->assessment_type === 'assignment' && ($assessment->questions?->count() ?? 0) > 0 && !$isTeacher)
        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4">What you need to complete ({{ $assessment->questions?->count() ?? 0 }} {{ Str::plural('task', $assessment->questions?->count() ?? 0) }})</h2>
            <div class="space-y-4">
                @foreach($assessment->questions as $index => $question)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Task {{ $index + 1 }}</p>
                            <span class="text-xs text-gray-500">{{ $question->points }} pts · {{ str_replace('_', ' ', $question->question_type) }}</span>
                        </div>
                        <x-rich-text :content="$question->question_text" />
                        @if(str_replace(' ', '_', strtolower($question->question_type)) === 'file_upload')
                            <p class="mt-2 text-xs font-semibold text-orange-600 dark:text-orange-400">📎 You will upload a file when you start this assignment.</p>
                        @elseif(str_replace(' ', '_', strtolower($question->question_type)) === 'code_submission')
                            <p class="mt-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400">💻 You will submit code when you start this assignment.</p>
                        @endif
                        @if($question->image_url)
                            <div class="mt-3">
                                <x-storage-image :path="$question->image_url" alt="Task image" class="max-w-md rounded-lg border border-gray-200 dark:border-gray-700" />
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- CTA buttons --}}
        @unless(Auth::user()->isIctTeacher())
        <div class="mt-4 flex flex-wrap gap-2 justify-end">
            @if($isTeacher)
                <a href="{{ route('assessments.edit', $assessment) }}" wire:navigate
                   class="px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Edit Assessment
                </a>
            @endif
            @if($attemptsRemaining === 'unlimited' || $attemptsRemaining > 0)
                <a href="{{ route('assessments.take', $assessment) }}" wire:navigate
                   class="px-5 py-2 text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition-colors shadow-sm">
                    {{ $hasTaken ? 'Take Again' : ($assessment->assessment_type === 'assignment' ? 'Start Assignment' : 'Start Assessment') }}
                </a>
            @else
                <span class="px-4 py-2 text-sm font-semibold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-xl">No Attempts Left</span>
            @endif
        </div>
        @endunless
    </div>

    {{-- ── Teacher stats ────────────────────────────────────────────────── --}}
    @if($isTeacher && $stats)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach([
                ['Total Attempts', $stats['total_attempts'], 'text-gray-900 dark:text-white'],
                ['Unique Students', $stats['unique_students'], 'text-blue-600 dark:text-blue-400'],
                ['Avg Score', $stats['average_score'] . '%', 'text-orange-600 dark:text-orange-400'],
                ['Pass Rate', $stats['pass_rate'] . '%', $stats['pass_rate'] >= 70 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'],
            ] as [$label, $value, $color])
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $label }}</p>
                    <p class="text-2xl font-bold mt-1 {{ $color }}">{{ $value }}</p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Student Submissions (teachers only) ─────────────────────────── --}}
    @if($isTeacher)
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
            <h2 class="text-base font-bold text-gray-900 dark:text-white flex-1">Student Submissions</h2>

            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Search student…"
                       class="pl-9 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors w-48">
            </div>

            {{-- Status filter --}}
            <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-xl p-1">
                @foreach(['all' => 'All', 'passed' => 'Passed', 'failed' => 'Failed', 'pending' => 'Pending'] as $val => $lbl)
                    <button type="button" wire:click="$set('statusFilter', '{{ $val }}')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors
                                {{ $statusFilter === $val
                                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>

        @if($submissions && $submissions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Score</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submitted</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($submissions as $submission)
                            @php
                                $pct = $submission->score !== null && $maxScore > 0
                                    ? min(round(($submission->score / $maxScore) * 100, 1), 100)
                                    : null;
                                $isPending = $submission->score === null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                                            <span class="text-xs font-bold text-orange-700 dark:text-orange-300">
                                                {{ strtoupper(substr($submission->user->name ?? '?', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white leading-tight">{{ $submission->user->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $submission->user->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($isPending)
                                        <span class="text-gray-400">—</span>
                                    @else
                                        <span class="font-bold {{ $submission->is_passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $pct }}%
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($isPending)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending
                                        </span>
                                    @elseif($submission->is_passed)
                                        <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">Passed</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">Failed</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center text-gray-500 dark:text-gray-400">
                                    {{ $submission->time_spent ? $submission->time_spent . 'm' : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                                    {{ $submission->completed_at?->format('d M Y, H:i') ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <a href="{{ route('assessments.results', [$assessment, $submission]) }}" wire:navigate
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-800 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors">
                                        View
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($submissions->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $submissions->links() }}
                </div>
            @endif
        @elseif($submissions !== null)
            <div class="px-6 py-12 text-center">
                @if($search || $statusFilter !== 'all')
                    <p class="text-gray-400 dark:text-gray-500 text-sm">No submissions match your filter.</p>
                    <button type="button" wire:click="$set('search', ''); $set('statusFilter', 'all')"
                            class="mt-2 text-xs text-orange-600 dark:text-orange-400 font-semibold hover:underline">
                        Clear filters
                    </button>
                @else
                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-400 dark:text-gray-500 text-sm">No students have submitted this assessment yet.</p>
                @endif
            </div>
        @endif
    </div>
    @endif

    {{-- ── Your Attempts (students) ─────────────────────────────────────── --}}
    @if($hasTaken && $userAttempts->isNotEmpty())
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Your Attempts</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($userAttempts as $attempt)
                @php
                    $isPendingGrade = $assessment->assessment_type === 'assignment' && $attempt->score === null;
                    $pct = (!$isPendingGrade && $attempt->score !== null && $maxScore > 0)
                        ? min(round(($attempt->score / $maxScore) * 100, 1), 100)
                        : null;
                @endphp
                <div class="flex items-center gap-4 px-6 py-4">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">
                            Attempt #{{ $loop->iteration }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                            {{ $attempt->completed_at?->format('d M Y, H:i') ?? 'In progress' }}
                            @if($attempt->time_spent)
                                · {{ $attempt->time_spent }} min
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        @if($isPendingGrade)
                            <p class="text-sm font-bold text-amber-600 dark:text-amber-400">Pending</p>
                        @elseif($pct !== null)
                            <p class="text-xl font-bold {{ $attempt->is_passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $pct }}%
                            </p>
                            <p class="text-xs {{ $attempt->is_passed ? 'text-green-500' : 'text-red-500' }}">
                                {{ $attempt->is_passed ? 'Passed' : 'Failed' }}
                            </p>
                        @else
                            <p class="text-sm text-gray-400">—</p>
                        @endif
                    </div>
                    @if($assessment->show_results_immediately || $attempt->is_passed !== null)
                        <a href="{{ route('assessments.results', [$assessment, $attempt]) }}" wire:navigate
                           class="px-3 py-1.5 text-xs font-bold text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-800 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors">
                            Review
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Assessment Details (collapsible) ────────────────────────────── --}}
    <div x-data="{ open: false }" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <button type="button" @click="open = !open"
                class="flex items-center justify-between w-full px-6 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Assessment Details</span>
            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-transition:enter="transition-opacity duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="px-6 pb-5 border-t border-gray-100 dark:border-gray-800 pt-4">
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</dt>
                    <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $typeLabel }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Max Attempts</dt>
                    <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $assessment->max_attempts > 0 ? $assessment->max_attempts : 'Unlimited' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Passing Score</dt>
                    <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $assessment->passing_score }}%</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">XP Reward</dt>
                    <dd class="mt-1 font-bold text-orange-600 dark:text-orange-400">{{ $assessment->xp_reward }} XP</dd>
                </div>
                @if($assessment->time_limit_minutes)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time Limit</dt>
                    <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $assessment->time_limit_minutes }} minutes</dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Questions</dt>
                    <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ collect($assessment->questions)->count() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Points</dt>
                    <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $maxScore }}</dd>
                </div>
            </dl>

            @if($assessment->assessment_type === 'rubric_assessment' && $assessment->rubric_criteria)
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Rubric Criteria</p>
                    <div class="space-y-2">
                        @foreach($assessment->rubric_criteria as $i => $criterion)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-sm">
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $criterion['name'] ?? 'Criterion ' . ($i + 1) }}</span>
                                <span class="text-xs text-gray-500">{{ $criterion['max_points'] ?? 0 }} pts</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
