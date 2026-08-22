    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            #learning-journey-report,
            #learning-journey-report * {
                visibility: visible !important;
            }

            #learning-journey-report {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                background: #fff;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                @if($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}" 
                         alt="{{ $user->name }}" 
                         class="h-20 w-20 rounded-full object-cover border-4 border-blue-500">
                @else
                    <div class="h-20 w-20 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center border-4 border-blue-500">
                        <span class="text-3xl font-bold text-white">{{ $user->initials() }}</span>
                    </div>
                @endif
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                    <p class="text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        @foreach($user->roles as $role)
                            <flux:badge variant="primary" size="sm">{{ ucfirst($role->name) }}</flux:badge>
                        @endforeach
                        <flux:badge variant="{{ $user->is_active ? 'success' : 'danger' }}" size="sm">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
            @can('update', $user)
                <flux:button href="{{ route('admin.users.edit', $user) }}" variant="primary" wire:navigate>
                    Edit Profile
                </flux:button>
            @endcan
                @if(Auth::user()->hasAnyRole(['admin']))
                    <flux:button wire:click="openPointsModal" variant="outline">
                            Override XP
                    </flux:button>
                    <flux:button wire:click="openResetModal" variant="ghost" title="Reset Password">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Reset Password
                    </flux:button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-sm font-medium text-blue-100 mb-1">Total Points</p>
                <p class="text-3xl font-bold">{{ number_format($stats['total_points']) }}</p>
                <p class="text-sm text-blue-100 mt-2">Level {{ $stats['level'] }} · {{ $stats['rank_name'] ?? 'Beginner' }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-sm font-medium text-green-100 mb-1">Courses Completed</p>
                <p class="text-3xl font-bold">{{ $stats['courses_completed'] }}</p>
                <p class="text-sm text-green-100 mt-2">of {{ $stats['courses_enrolled'] }} enrolled</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-sm font-medium text-purple-100 mb-1">Badges Earned</p>
                <p class="text-3xl font-bold">{{ $stats['badges_earned'] }}</p>
                <p class="text-sm text-purple-100 mt-2">achievements</p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6">
                <p class="text-sm font-medium text-orange-100 mb-1">Avg Score</p>
                <p class="text-3xl font-bold">{{ number_format($stats['average_score'], 1) }}%</p>
                <p class="text-sm text-orange-100 mt-2">across all courses</p>
            </div>
        </div>

        <!-- Leaderboard Position -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Leaderboard Position</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Overall ranking</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold text-blue-600 dark:text-blue-400">#{{ $leaderboardPosition }}</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">of {{ $totalUsers }} users</p>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Created Courses -->
            @if($user->hasRole('teacher') && $recentActivity['recent_courses']->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Created Courses</h2>
                    <div class="space-y-3">
                        @foreach($recentActivity['recent_courses'] as $course)
                            <a href="{{ route('courses.show', $course) }}" class="block">
                                <div class="flex items-center gap-4 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $course->title }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $course->enrollments_count ?? 0 }} enrollments
                                        </p>
                                    </div>
                                    <flux:badge variant="{{ $course->is_published ? 'success' : 'warning' }}" size="sm">
                                        {{ $course->is_published ? 'Published' : 'Draft' }}
                                    </flux:badge>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @if($recentActivity['recent_courses']->hasPages())
                        <div class="mt-4">
                            {{ $recentActivity['recent_courses']->links() }}
                        </div>
                    @endif
                </div>
            @endif

            <!-- All Enrollments -->
            @if($recentActivity['recent_enrollments']->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">All Enrollments</h2>
                    <div class="space-y-3">
                        @foreach($recentActivity['recent_enrollments'] as $enrollment)
                            <a href="{{ route('courses.show', $enrollment->course) }}" class="block">
                                <div class="flex items-center gap-4 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->course->title }}</h3>
                                        <div class="mt-2">
                                            <div class="flex items-center justify-between text-xs mb-1">
                                                <span class="text-gray-600 dark:text-gray-400">Progress</span>
                                                <span class="font-semibold">{{ number_format($enrollment->progress_percentage ?? 0, 1) }}%</span>
                                            </div>
                                            <div class="h-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-blue-500 rounded-full" style="width: {{ $enrollment->progress_percentage ?? 0 }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($enrollment->completed_at)
                                        <flux:badge variant="success" size="sm">Completed</flux:badge>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @if($recentActivity['recent_enrollments']->hasPages())
                        <div class="mt-4">
                            {{ $recentActivity['recent_enrollments']->links() }}
                        </div>
                    @endif
                </div>
            @endif

            <!-- All Badges -->
            @if($recentActivity['recent_badges']->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">All Badges</h2>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($recentActivity['recent_badges'] as $badge)
                            <div class="text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 shadow-lg">
                                    <div class="text-white filter drop-shadow-lg">
                                        <x-badge-icon :icon="$badge->icon ?? 'trophy'" class="w-12 h-12" />
                                    </div>
                                </div>
                                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white line-clamp-2">{{ $badge->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    @if($badge->pivot->earned_at)
                                        {{ \Carbon\Carbon::parse($badge->pivot->earned_at)->diffForHumans() }}
                                    @else
                                        Earned
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                    @if($recentActivity['recent_badges']->hasPages())
                        <div class="mt-4">
                            {{ $recentActivity['recent_badges']->links() }}
                        </div>
                    @endif
                </div>
            @endif

            <!-- Bio Section -->
            @if($user->bio)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">About</h2>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $user->bio }}</p>
                </div>
            @endif
        </div>

        <!-- Activity Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Activity Summary</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Lessons Completed</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['lessons_completed'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Courses Created</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['courses_created'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Member Since</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ $user->created_at->format('M Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Last Login</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Learning Journey (In-Depth) -->
        @if($user->hasRole('student'))
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 no-print">
                <div class="flex flex-col gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Learning Journey Filters & Report Tools</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Filter all journey sections by date and course, then export or print.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">From Date</label>
                            <input type="date" wire:model.live="journeyDateFrom" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">To Date</label>
                            <input type="date" wire:model.live="journeyDateTo" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Course</label>
                            <select wire:model.live="journeyCourseId" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                <option value="all">All Courses</option>
                                @foreach($courseFilterOptions as $courseOption)
                                    <option value="{{ $courseOption->id }}">{{ $courseOption->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <flux:button wire:click="resetJourneyFilters" variant="ghost" class="w-full">Reset Filters</flux:button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <flux:button wire:click="exportJourneyCsv" variant="outline">
                            Export CSV Report
                        </flux:button>
                        <flux:button wire:click="exportJourneyPdf" variant="primary">
                            Export PDF Report
                        </flux:button>
                        <flux:button type="button" variant="ghost" onclick="window.print()">
                            Print Student Learning Journey
                        </flux:button>
                    </div>
                </div>
            </div>

            <div id="learning-journey-report" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Learning Journey Overview</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Courses</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $journeyStats['courses_tracked'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Lessons Covered</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $journeyStats['lessons_covered'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Challenges Completed</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $journeyStats['challenge_completed'] }}/{{ $journeyStats['challenge_attempts'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Assessment Pass Rate</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $journeyStats['assessment_pass_rate'] }}%</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Assignments Graded</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $journeyStats['assignment_graded'] }}/{{ $journeyStats['assignment_submissions'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Attendance Rate</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ $journeyStats['attendance_rate'] !== null ? number_format($journeyStats['attendance_rate'], 1) . '%' : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Course Journey</h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                        @forelse($courseJourney as $enrollment)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->course?->title ?? 'Untitled Course' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Enrolled {{ optional($enrollment->enrolled_at)->format('d M Y') ?? 'N/A' }}
                                            @if($enrollment->completed_at)
                                                • Completed {{ $enrollment->completed_at->format('d M Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <flux:badge variant="{{ $enrollment->completed_at ? 'success' : 'primary' }}" size="sm">
                                        {{ number_format((float) ($enrollment->progress_percentage ?? 0), 1) }}%
                                    </flux:badge>
                                </div>
                                <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                                    Lessons: {{ $enrollment->lessons_completed ?? 0 }} • Quizzes: {{ $enrollment->quizzes_completed ?? 0 }} • Avg Score: {{ number_format((float) ($enrollment->average_quiz_score ?? 0), 1) }}%
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No enrollment journey recorded yet.</p>
                        @endforelse
                    </div>
                    @if($courseJourney instanceof \Illuminate\Pagination\LengthAwarePaginator && $courseJourney->hasPages())
                        <div class="mt-4">
                            {{ $courseJourney->links() }}
                        </div>
                    @endif
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lessons Covered (Progress Events)</h3>
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-3 py-2 text-left">Lesson</th>
                                    <th class="px-3 py-2 text-left">Course</th>
                                    <th class="px-3 py-2 text-left">Type</th>
                                    <th class="px-3 py-2 text-left">Score</th>
                                    <th class="px-3 py-2 text-left">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lessonProgress as $progress)
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $progress->lesson?->title ?? 'Lesson #' . $progress->lesson_id }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $progress->course?->title ?? 'N/A' }}</td>
                                        <td class="px-3 py-2">
                                            <flux:badge variant="primary" size="sm">{{ strtoupper((string) $progress->type) }}</flux:badge>
                                        </td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $progress->score !== null ? number_format((float) $progress->score, 1) . '%' : '—' }}</td>
                                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ optional($progress->completed_at ?? $progress->created_at)->format('d M Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No lesson progress data yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($lessonProgress instanceof \Illuminate\Pagination\LengthAwarePaginator && $lessonProgress->hasPages())
                        <div class="mt-4">
                            {{ $lessonProgress->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daily Challenges</h3>
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-3 py-2 text-left">Challenge</th>
                                    <th class="px-3 py-2 text-left">Difficulty</th>
                                    <th class="px-3 py-2 text-left">Points</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2 text-left">Attempted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($challengeAttempts as $attempt)
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $attempt->challenge?->title ?? 'Challenge #' . $attempt->challenge_id }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ ucfirst($attempt->challenge?->difficulty_level ?? 'N/A') }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ (int) ($attempt->points_earned ?? 0) }}</td>
                                        <td class="px-3 py-2">
                                            <flux:badge variant="{{ $attempt->is_completed ? 'success' : 'warning' }}" size="sm">
                                                {{ $attempt->is_completed ? 'Completed' : 'Attempted' }}
                                            </flux:badge>
                                        </td>
                                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ optional($attempt->attempted_at ?? $attempt->created_at)->format('d M Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No challenge activity yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($challengeAttempts instanceof \Illuminate\Pagination\LengthAwarePaginator && $challengeAttempts->hasPages())
                        <div class="mt-4">
                            {{ $challengeAttempts->links() }}
                        </div>
                    @endif
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assessments & Results</h3>
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-3 py-2 text-left">Assessment</th>
                                    <th class="px-3 py-2 text-left">Type</th>
                                    <th class="px-3 py-2 text-left">Score</th>
                                    <th class="px-3 py-2 text-left">Result</th>
                                    <th class="px-3 py-2 text-left">Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assessmentAttempts as $attempt)
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $attempt->assessment?->title ?? 'Assessment #' . $attempt->assessment_id }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', (string) $attempt->assessment?->assessment_type)) }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $attempt->score !== null ? number_format((float) $attempt->score, 1) . '%' : '—' }}</td>
                                        <td class="px-3 py-2">
                                            <flux:badge variant="{{ $attempt->is_passed ? 'success' : 'danger' }}" size="sm">
                                                {{ $attempt->is_passed ? 'Passed' : 'Not Passed' }}
                                            </flux:badge>
                                        </td>
                                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ optional($attempt->completed_at ?? $attempt->started_at)->format('d M Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No assessment attempts yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($assessmentAttempts instanceof \Illuminate\Pagination\LengthAwarePaginator && $assessmentAttempts->hasPages())
                        <div class="mt-4">
                            {{ $assessmentAttempts->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assignment Submissions</h3>
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-3 py-2 text-left">Assignment</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2 text-left">Points</th>
                                    <th class="px-3 py-2 text-left">Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignmentSubmissions as $submission)
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $submission->assignment?->title ?? 'Assignment #' . $submission->assignment_id }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ ucfirst((string) $submission->status) }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $submission->points_earned !== null ? number_format((float) $submission->points_earned, 1) : '—' }}</td>
                                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ optional($submission->submitted_at ?? $submission->created_at)->format('d M Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No assignment submissions yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($assignmentSubmissions instanceof \Illuminate\Pagination\LengthAwarePaginator && $assignmentSubmissions->hasPages())
                        <div class="mt-4">
                            {{ $assignmentSubmissions->links() }}
                        </div>
                    @endif
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Attendance History</h3>
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-3 py-2 text-left">Date</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2 text-left">Course</th>
                                    <th class="px-3 py-2 text-left">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendanceRecords as $attendance)
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="px-3 py-2 text-gray-900 dark:text-white">{{ optional($attendance->attendance_date)->format('d M Y') }}</td>
                                        <td class="px-3 py-2">
                                            <flux:badge variant="{{ in_array($attendance->status, ['present', 'late']) ? 'success' : 'danger' }}" size="sm">{{ ucfirst((string) $attendance->status) }}</flux:badge>
                                        </td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $attendance->course?->title ?? 'General' }}</td>
                                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400">
                                            {{ $attendance->clockInCarbon()?->format('H:i') ?? '--:--' }}
                                            -
                                            {{ $attendance->clockOutCarbon()?->format('H:i') ?? '--:--' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No attendance records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($attendanceRecords instanceof \Illuminate\Pagination\LengthAwarePaginator && $attendanceRecords->hasPages())
                        <div class="mt-4">
                            {{ $attendanceRecords->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Learning Timeline</h3>
                <div class="space-y-3 max-h-[28rem] overflow-y-auto pr-1">
                    @forelse($learningTimeline as $event)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $event['title'] }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $event['detail'] }}</p>
                                </div>
                                <div class="text-right">
                                    <flux:badge variant="primary" size="sm">{{ ucfirst($event['type']) }}</flux:badge>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ optional($event['at'])->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No timeline events yet.</p>
                    @endforelse
                </div>
            </div>
            </div>
        @endif
    </div>

    {{-- Password Reset Modal --}}
    @if($showResetModal)
        <flux:modal name="reset-password" :show="$showResetModal" wire:model="showResetModal">
            <form wire:submit.prevent="confirmResetPassword">
                <div class="p-6">
                    @if(!$newPassword)
                        {{-- Confirmation Step --}}
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Reset Password</h2>
                            <p class="text-gray-600 dark:text-gray-400">
                                Are you sure you want to reset the password for <strong>{{ $user->name }}</strong> ({{ $user->email }})?
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                                A new secure password will be generated and displayed for you to share with the user.
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-6">
                            <flux:button type="button" wire:click="closeResetModal" variant="ghost">Cancel</flux:button>
                            <flux:button type="submit" variant="primary">Reset Password</flux:button>
                        </div>
                    @else
                        {{-- Success Step with Password Display --}}
                        <div class="mb-6">
                            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2 text-center">Password Reset Successful!</h2>
                            <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                                Password has been reset for <strong>{{ $user->name }}</strong>
                            </p>

                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    New Password
                                </label>
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="text" 
                                        value="{{ $newPassword }}" 
                                        id="new-password-field-show"
                                        readonly
                                        class="flex-1 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg font-mono text-lg font-bold text-gray-900 dark:text-white"
                                    >
                                    <button 
                                        type="button"
                                        id="copy-btn-show"
                                        onclick="copyPasswordShow(this)"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                                    >
                                        Copy
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    ⚠️ Please copy this password now. It will not be shown again.
                                </p>
                            </div>

                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                    <strong>Important:</strong> Share this password securely with the user. They should change it after their first login.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-center mt-6">
                            <flux:button type="button" wire:click="closeResetModal" variant="primary">Done</flux:button>
                        </div>

                        <script>
                            function copyPasswordShow(btn) {
                                const field = document.getElementById('new-password-field-show');
                                if (!field) return;
                                
                                // Select the text
                                field.select();
                                field.setSelectionRange(0, 99999); // For mobile devices
                                
                                // Copy to clipboard
                                try {
                                    navigator.clipboard.writeText(field.value).then(function() {
                                        // Show feedback
                                        const originalText = btn.textContent;
                                        btn.textContent = 'Copied!';
                                        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                                        btn.classList.add('bg-green-600', 'hover:bg-green-700');
                                        setTimeout(() => {
                                            btn.textContent = originalText;
                                            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                                            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                                        }, 2000);
                                    });
                                } catch (err) {
                                    // Fallback for older browsers
                                    document.execCommand('copy');
                                    const originalText = btn.textContent;
                                    btn.textContent = 'Copied!';
                                    btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                                    btn.classList.add('bg-green-600', 'hover:bg-green-700');
                                    setTimeout(() => {
                                        btn.textContent = originalText;
                                        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                                        btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                                    }, 2000);
                                }
                            }
                        </script>
                    @endif
                </div>
            </form>
        </flux:modal>
    @endif

    {{-- XP Override Modal --}}
    @if($showPointsModal)
        <flux:modal name="override-points" :show="$showPointsModal" wire:model="showPointsModal">
            <form wire:submit.prevent="updatePoints">
                <div class="p-6 space-y-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Override XP</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Level and rank are calculated automatically from total XP.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Points</label>
                            <input type="number" min="0" wire:model.live="pointsForm.total_points" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                            @error('pointsForm.total_points')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Level (auto)</label>
                            <div class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 font-semibold text-gray-900 dark:text-white">
                                Level {{ $pointsForm['level'] }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rank (auto)</label>
                            <div class="w-full rounded-lg border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 px-3 py-2 font-semibold text-purple-800 dark:text-purple-200">
                                {{ \App\Support\LevelSystem::rankName($pointsForm['total_points'] ?? 0) }}
                                <span class="text-xs font-normal ml-1">({{ $pointsForm['points_to_next_level'] ?? 100 }} XP to next level)</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">XP Multiplier</label>
                            <input type="number" min="0" step="0.1" wire:model.defer="pointsForm.xp_multiplier" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                            @error('pointsForm.xp_multiplier')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Multiplier Expires At</label>
                            <input type="datetime-local" wire:model.defer="pointsForm.multiplier_expires_at" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                            @error('pointsForm.multiplier_expires_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                            <input type="text" wire:model.defer="pointsForm.multiplier_reason" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                            @error('pointsForm.multiplier_reason')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <flux:button type="button" wire:click="closePointsModal" variant="ghost">Cancel</flux:button>
                        <flux:button type="submit" variant="primary">Save</flux:button>
                    </div>
                </div>
            </form>
        </flux:modal>
    @endif
</div>
