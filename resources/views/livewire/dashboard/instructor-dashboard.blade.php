<div class="flex flex-col gap-6 p-6">
        <livewire:daily-reports.optional-reminder-banner />

        <!-- Welcome Header -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Welcome back, {{ $user->name }}! 👨‍🏫
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Manage your courses and track student progress
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('submissions.index', ['filter' => 'pending']) }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    Grade submissions
                    @if($pendingGradingCount > 0)
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-white text-indigo-700 text-xs font-bold">{{ $pendingGradingCount }}</span>
                    @endif
                </a>
                <flux:button href="{{ route('courses.create') }}" icon="plus" variant="primary" wire:navigate>
                    Create Course
                </flux:button>
                <flux:button href="{{ route('assessments.create') }}" icon="clipboard-document-list" variant="ghost" wire:navigate>
                    New Assessment
                </flux:button>
                <a href="{{ route('attendance.dashboard') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Attendance
                </a>
                <a href="{{ route('leaderboards.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-600 text-white text-sm font-semibold transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    Leaderboard
                </a>
                <a href="{{ route('daily-challenges.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    New Challenge
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <flux:card class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-600 text-white border-0 shadow-xl">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative flex items-center justify-between p-6">
                    <div>
                        <p class="text-blue-100 text-sm font-medium mb-1">Total Courses</p>
                        <p class="text-4xl font-bold">{{ $stats['totalCourses'] }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-blue-100 text-xs">✓ {{ $stats['publishedCourses'] }} published</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
            </flux:card>

            <flux:card class="relative overflow-hidden bg-gradient-to-br from-green-500 to-green-600 text-white border-0 shadow-xl">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative flex items-center justify-between p-6">
                    <div>
                        <p class="text-green-100 text-sm font-medium mb-1">Total Enrollments</p>
                        <p class="text-4xl font-bold">{{ number_format($stats['totalEnrollments']) }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-green-100 text-xs">👥 {{ $stats['activeStudents'] }} active</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </flux:card>

            <flux:card class="relative overflow-hidden bg-gradient-to-br from-yellow-500 to-yellow-600 text-white border-0 shadow-xl">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative flex items-center justify-between p-6">
                    <div>
                        <p class="text-yellow-100 text-sm font-medium mb-1">Pending Approvals</p>
                        <p class="text-4xl font-bold">{{ $stats['pendingApprovals'] }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-yellow-100 text-xs">📝 {{ $stats['draftCourses'] }} drafts</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </flux:card>

            <flux:card class="relative overflow-hidden bg-gradient-to-br from-purple-500 to-purple-600 text-white border-0 shadow-xl">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative flex items-center justify-between p-6">
                    <div>
                        <p class="text-purple-100 text-sm font-medium mb-1">Avg. Progress</p>
                        <p class="text-4xl font-bold">{{ number_format($studentAnalytics['averageProgress'], 1) }}%</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-purple-100 text-xs">⭐ {{ number_format($studentAnalytics['averageScore'], 1) }}% avg score</span>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </flux:card>
        </div>

        <!-- Quick Stats Row -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <flux:card class="border-l-4 border-l-blue-500">
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($studentAnalytics['completionRate'], 1) }}%
                            </p>
                        </div>
                        <div class="rounded-full bg-blue-100 dark:bg-blue-900/20 p-3">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </flux:card>

            <flux:card class="border-l-4 border-l-green-500">
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Students</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $studentAnalytics['totalStudents'] }}
                            </p>
                        </div>
                        <div class="rounded-full bg-green-100 dark:bg-green-900/20 p-3">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </flux:card>

            <a href="{{ route('submissions.index', ['filter' => 'pending']) }}" wire:navigate class="block">
            <flux:card class="border-l-4 border-l-purple-500 hover:bg-purple-50/50 dark:hover:bg-purple-900/10 transition">
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pending Grading</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $pendingGradingCount }}
                            </p>
                            <p class="mt-1 text-xs text-purple-600 dark:text-purple-400 font-medium">Open queue →</p>
                        </div>
                        <div class="rounded-full bg-purple-100 dark:bg-purple-900/20 p-3">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>
            </flux:card>
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
    </div>
</div>
