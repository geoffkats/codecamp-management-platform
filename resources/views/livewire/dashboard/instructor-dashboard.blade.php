<div class="relative flex flex-col gap-5 p-4 pb-24 sm:p-6">
        <style>[x-cloak]{display:none!important}</style>
        <livewire:daily-reports.optional-reminder-banner />

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Welcome back, {{ $user->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Manage your courses and track student progress
                </p>
            </div>
            <a href="{{ route('submissions.index', ['filter' => 'pending']) }}" wire:navigate
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                Grade submissions
                @if($pendingGradingCount > 0)
                    <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-white px-1.5 text-xs font-bold text-indigo-700">{{ $pendingGradingCount }}</span>
                @endif
            </a>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-6">
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500 dark:text-zinc-400">Courses</p>
                <p class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">{{ $stats['totalCourses'] }}</p>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400">{{ $stats['publishedCourses'] }} published · {{ $stats['draftCourses'] }} drafts</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500 dark:text-zinc-400">Enrollments</p>
                <p class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['totalEnrollments']) }}</p>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400">{{ $stats['activeStudents'] }} active</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500 dark:text-zinc-400">Students</p>
                <p class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">{{ $studentAnalytics['totalStudents'] }}</p>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400">{{ $stats['pendingApprovals'] }} pending approvals</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500 dark:text-zinc-400">Avg. progress</p>
                <p class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">{{ number_format($studentAnalytics['averageProgress'], 1) }}%</p>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400">{{ number_format($studentAnalytics['averageScore'], 1) }}% avg score</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500 dark:text-zinc-400">Completion</p>
                <p class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">{{ number_format($studentAnalytics['completionRate'], 1) }}%</p>
                <p class="text-[11px] text-slate-500 dark:text-zinc-400">course finish rate</p>
            </div>
            <a href="{{ route('submissions.index', ['filter' => 'pending']) }}" wire:navigate
               class="rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2.5 transition hover:border-indigo-300 dark:border-indigo-800 dark:bg-indigo-950/40">
                <p class="text-[11px] font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-300">To grade</p>
                <p class="mt-0.5 text-xl font-bold text-indigo-900 dark:text-indigo-100">{{ $pendingGradingCount }}</p>
                <p class="text-[11px] font-medium text-indigo-600 dark:text-indigo-300">Open queue →</p>
            </a>
        </div>

        <!-- Needs grading -->
        <flux:card class="shadow-lg border border-orange-200 dark:border-orange-800">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-6 border-b border-orange-100 dark:border-orange-900/40 bg-gradient-to-r from-orange-50 to-white dark:from-orange-950/40 dark:to-gray-900">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Needs grading</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        @if($pendingGradingCount > 0)
                            {{ $pendingGradingCount }} {{ \Illuminate\Support\Str::plural('submission', $pendingGradingCount) }} waiting for marks
                        @else
                            You're all caught up — no submissions waiting for marks
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('submissions.index', ['filter' => 'pending']) }}" wire:navigate
                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold">
                        Pending only
                    </a>
                    <a href="{{ route('submissions.index') }}" wire:navigate
                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                        All submissions
                    </a>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                @if($recentSubmissions->count() > 0)
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($recentSubmissions as $item)
                            <div class="flex flex-col gap-3 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="h-10 w-10 shrink-0 rounded-full bg-gradient-to-br from-orange-400 to-pink-500 flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($item->studentName, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $item->studentName }}</p>
                                        <p class="text-sm text-gray-800 dark:text-gray-200 truncate">{{ $item->title }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $item->courseTitle }}
                                            · {{ $item->typeLabel }}
                                            @if($item->submittedAt)
                                                · {{ $item->submittedAt->diffForHumans() }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 sm:shrink-0 pl-12 sm:pl-0">
                                    <a href="{{ route('submissions.show', ['submissionId' => $item->id, 'type' => $item->type]) }}" wire:navigate
                                       class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium">
                                        View
                                    </a>
                                    @can('grade_submissions')
                                        <a href="{{ route('grades.grade', $item->submission) }}" wire:navigate
                                           class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">
                                            Grade
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($pendingGradingCount > $recentSubmissions->count())
                        <div class="mt-4 text-center">
                            <a href="{{ route('submissions.index', ['filter' => 'pending']) }}" wire:navigate
                               class="text-sm font-semibold text-orange-700 dark:text-orange-300 hover:underline">
                                View all {{ $pendingGradingCount }} pending submissions
                            </a>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        When students submit assignments or quizzes, they will show up here so you can view details and give marks.
                    </p>
                @endif
            </div>
        </flux:card>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Courses List -->
            <div class="lg:col-span-2">
                <flux:card class="shadow-lg">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-900">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">My Courses</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage and track your courses</p>
                        </div>
                        <flux:button href="{{ route('courses.index') }}" variant="ghost" size="sm" wire:navigate>
                            View All
                        </flux:button>
                    </div>
                    <div class="p-6">
                        @if($courses->count() > 0)
                            <div class="space-y-4">
                                @foreach($courses as $course)
                                    <div class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $course->title }}</h3>
                                                <flux:badge size="sm" variant="{{ $course->approval_status === 'approved' ? 'success' : ($course->approval_status === 'pending' ? 'warning' : 'ghost') }}">
                                                    {{ ucfirst($course->approval_status) }}
                                                </flux:badge>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $course->short_description }}</p>
                                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                <span>{{ $course->enrollments_count }} enrollments</span>
                                                <span>{{ $course->lessons_count }} lessons</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <flux:button href="{{ route('courses.edit', $course) }}" variant="ghost" size="sm" wire:navigate>
                                                Edit
                                            </flux:button>
                                            <a href="{{ route('courses.preview', $course) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Preview Lessons
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $courses->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No courses assigned</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ask an admin to assign courses from the Users menu, or create a course if you have permission.</p>
                                <div class="mt-6">
                                    <flux:button href="{{ route('courses.create') }}" variant="primary" wire:navigate>
                                        Create Course
                                    </flux:button>
                                </div>
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Pending Approvals -->
                @if($pendingApprovals->count() > 0)
                    <flux:card>
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-semibold">Pending Approvals</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            @foreach($pendingApprovals as $approval)
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                                        {{ $this->getApprovableTitle($approval) }}
                                    </h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                        Submitted {{ $approval->submitted_at->diffForHumans() }}
                                    </p>
                                    <flux:button href="{{ route('content-approvals.review', $approval) }}" variant="ghost" size="sm" class="mt-2 w-full" wire:navigate>
                                        Review
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                @endif

                <!-- Recent Enrollments -->
                @if($recentEnrollments->count() > 0)
                    <flux:card>
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-semibold">Recent Enrollments</h2>
                        </div>
                        <div class="p-6 space-y-3">
                            @foreach($recentEnrollments->take(5) as $enrollment)
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold">
                                        {{ substr($enrollment->user->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $enrollment->user->name }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ $enrollment->course->title }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                @endif
            </div>
        </div>

        <div class="pointer-events-none fixed bottom-6 right-6 z-40"
             x-data="{ open: false }"
             @keydown.escape.window="open = false">
            <div class="pointer-events-auto flex flex-col items-end gap-3" @click.outside="open = false">
                <div x-show="open"
                     x-cloak
                     x-transition.origin.bottom.right
                     class="w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="px-4 pt-3 pb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-zinc-400">Quick actions</p>
                    <div class="flex flex-col pb-2">
                        <a href="{{ route('courses.create') }}" wire:navigate class="px-4 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50 dark:text-zinc-100 dark:hover:bg-zinc-800">Create course</a>
                        <a href="{{ route('assessments.create') }}" wire:navigate class="px-4 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50 dark:text-zinc-100 dark:hover:bg-zinc-800">New assessment</a>
                        <a href="{{ route('daily-challenges.create') }}" wire:navigate class="px-4 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50 dark:text-zinc-100 dark:hover:bg-zinc-800">New challenge</a>
                        <a href="{{ route('attendance.dashboard') }}" wire:navigate class="px-4 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50 dark:text-zinc-100 dark:hover:bg-zinc-800">Attendance</a>
                        <a href="{{ route('leaderboards.index') }}" wire:navigate class="px-4 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50 dark:text-zinc-100 dark:hover:bg-zinc-800">Leaderboard</a>
                        <a href="{{ Route::has('lessons.locks') ? route('lessons.locks') : url('/lesson-locks') }}" wire:navigate class="px-4 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50 dark:text-zinc-100 dark:hover:bg-zinc-800">Lesson locks</a>
                        <a href="{{ route('admin.xp-manager') }}" wire:navigate class="px-4 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50 dark:text-zinc-100 dark:hover:bg-zinc-800">Award XP</a>
                        <a href="{{ route('daily-reports.submit') }}" wire:navigate class="px-4 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50 dark:text-zinc-100 dark:hover:bg-zinc-800">Daily report</a>
                    </div>
                </div>
                <button type="button"
                        @click.stop="open = !open"
                        :aria-expanded="open.toString()"
                        aria-label="Quick actions"
                        class="flex h-14 w-14 items-center justify-center rounded-full bg-orange-600 text-white shadow-lg ring-4 ring-white transition hover:bg-orange-700 dark:ring-zinc-900">
                    <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                    </svg>
                    <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
</div>
