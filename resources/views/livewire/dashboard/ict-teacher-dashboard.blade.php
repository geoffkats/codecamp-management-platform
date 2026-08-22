<div class="flex flex-col gap-6 p-6">
    <div class="rounded-2xl border border-slate-200/70 dark:border-slate-600/40 bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-800/80 dark:via-gray-800/70 dark:to-gray-800/80 shadow-sm">
        <div class="flex flex-col gap-6 p-6 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/80 dark:bg-gray-700/80 px-3 py-1 text-xs font-semibold text-blue-700 dark:text-blue-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    ICT Teacher Dashboard
                </div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    Welcome back, {{ $user->name }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-200">
                    ICT Program — {{ $school?->name ?? 'School not assigned' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button href="{{ route('students.create-ict') }}" icon="plus" variant="primary" wire:navigate>
                    Add ICT Student
                </flux:button>
                <flux:button href="{{ route('students.index') }}" icon="users" variant="ghost" wire:navigate>
                    View Students
                </flux:button>
                <flux:button href="{{ route('students.index') }}" icon="printer" variant="ghost" wire:navigate>
                    Print Credentials
                </flux:button>
                <flux:button href="{{ route('test-marks.index') }}" icon="clipboard-document-list" variant="ghost" wire:navigate>
                    Enter Test Marks
                </flux:button>
                <flux:button href="{{ route('icdl-exam-marks.index') }}" icon="document-check" variant="ghost" wire:navigate>
                    ICDL Exam Marks
                </flux:button>
            </div>
        </div>
    </div>

    @if(!$school)
        <flux:card class="border-l-4 border-l-yellow-500">
            <div class="p-4">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    Your account does not have a school assignment yet. Please contact an administrator to be linked to a school.
                </p>
            </div>
        </flux:card>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <flux:card class="border border-blue-100/80 dark:border-blue-700/40 shadow-sm">
            <div class="p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-blue-600 dark:text-blue-200 font-semibold">Students</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['totalStudents']) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-blue-100 dark:bg-blue-800/60 flex items-center justify-center text-blue-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4" />
                            <circle cx="9" cy="7.5" r="3.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 11.5c1.657 0 3-1.567 3-3.5S18.657 4.5 17 4.5" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-200">Exam-ready</span>
                        <span class="font-semibold text-emerald-600">{{ number_format($stats['examReady']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-200">Needs practice</span>
                        <span class="font-semibold text-orange-600">{{ number_format($stats['needsPractice']) }}</span>
                    </div>
                </div>
            </div>
        </flux:card>

        <flux:card class="border border-indigo-100/80 dark:border-indigo-700/40 shadow-sm">
            <div class="p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-indigo-600 dark:text-indigo-200 font-semibold">Modules</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['activeModules']) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-indigo-100 dark:bg-indigo-800/60 flex items-center justify-center text-indigo-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5h16M4 12h16M4 17.5h10" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-200">Near completion</span>
                    <span class="font-semibold text-purple-600">{{ number_format($stats['modulesNearCompletion']) }}</span>
                </div>
            </div>
        </flux:card>

        <flux:card class="border border-emerald-100/80 dark:border-emerald-700/40 shadow-sm">
            <div class="p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-emerald-600 dark:text-emerald-200 font-semibold">Exams</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['pendingExamRequests']) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-emerald-100 dark:bg-emerald-800/60 flex items-center justify-center text-emerald-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.5h6M7 7h10M6 11h12M5 15h9" />
                        </svg>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-200">Approved sessions</span>
                        <span class="font-semibold text-emerald-600">{{ number_format($stats['approvedExamSessions']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-200">Awaiting results</span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['studentsAwaitingResults']) }}</span>
                    </div>
                </div>
            </div>
        </flux:card>

        @if($stats['outstandingBalances'] > 0)
            <flux:card class="border border-rose-100/80 dark:border-rose-900/50 shadow-sm">
                <div class="p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-rose-600 dark:text-rose-300 font-semibold">Payments</p>
                            <p class="text-3xl font-bold text-rose-600">{{ number_format($stats['outstandingBalances']) }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center text-rose-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16M4 12h16M4 16.5h8" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Outstanding balances need follow-up.</p>
                </div>
            </flux:card>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <flux:card class="lg:col-span-2">
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h2>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Last 7 days</span>
                </div>
                <div class="mt-4 space-y-4">
                    @forelse($recentActivity as $activity)
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-2.5 w-2.5 rounded-full bg-blue-600"></div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $activity['message'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $activity['time']?->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-4 text-sm text-gray-500 dark:text-gray-400">
                            No recent ICT activity yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="p-5 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h2>
                <div class="space-y-2">
                    <a href="{{ route('students.create-ict') }}" wire:navigate class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span>Add ICT Student</span>
                        <span class="text-xs text-gray-400">+ New</span>
                    </a>
                    <a href="{{ route('students.index') }}" wire:navigate class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span>View Students</span>
                        <span class="text-xs text-gray-400">Profiles</span>
                    </a>
                    <a href="{{ route('students.index') }}" wire:navigate class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span>Print Credentials</span>
                        <span class="text-xs text-gray-400">Logins</span>
                    </a>
                    <a href="{{ route('test-marks.index') }}" wire:navigate class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span>Enter Test Marks</span>
                        <span class="text-xs text-gray-400">Assess</span>
                    </a>
                    <a href="{{ route('icdl-exam-marks.index') }}" wire:navigate class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span>ICDL Exam Marks</span>
                        <span class="text-xs text-gray-400">Review</span>
                    </a>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card>
        <div class="p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Assessment Results</h2>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">ICT assessments</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($recentAssessmentResults as $attempt)
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
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $attempt->user?->name ?? 'Student' }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                {{ $assessment?->title ?? 'Assessment' }} · {{ $attempt->completed_at?->format('M j, Y') ?? '—' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($percentage, 1) }}%</span>
                            <span class="px-2 py-1 rounded-full text-xs {{ $attempt->is_passed ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $attempt->is_passed ? 'Pass' : 'Fail' }}
                            </span>
                            <a href="{{ route('assessments.results', ['assessment' => $assessment?->id, 'attempt' => $attempt->id]) }}" wire:navigate class="text-xs text-indigo-600 dark:text-indigo-300">View</a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-4 text-sm text-gray-500 dark:text-gray-400">
                        No assessment results yet.
                    </div>
                @endforelse
            </div>
        </div>
    </flux:card>
</div>
