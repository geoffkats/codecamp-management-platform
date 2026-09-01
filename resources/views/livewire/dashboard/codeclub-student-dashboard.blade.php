<div class="max-w-6xl mx-auto py-8 px-4 space-y-6">
    <livewire:attendance.morning-check-in-prompt />
    <div class="rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-700 text-white p-6">
        <p class="text-xs font-semibold uppercase tracking-widest text-blue-100">Code Club</p>
        <h1 class="text-2xl font-extrabold mt-1">Welcome, {{ $user->name }}</h1>
        @if($club)
            <p class="text-blue-100 mt-2">{{ $club->name }} · {{ $club->school?->name }}</p>
            <p class="text-sm text-blue-100/90 mt-1">{{ $club->schedule_label }}</p>
            @if($upcomingSession)
                <p class="text-sm font-semibold mt-3 text-white">
                    @if($upcomingSession['is_today'])
                        Next session: <span class="underline">Today</span> · {{ $upcomingSession['schedule_label'] }}
                    @else
                        Next session: {{ $upcomingSession['date']->format('l, d M Y') }} · {{ $upcomingSession['schedule_label'] }}
                    @endif
                </p>
            @endif
        @else
            <p class="text-blue-100 mt-2">You are not enrolled in an active club yet. Contact your facilitator.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('enrollments.index') }}" wire:navigate class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 hover:border-blue-400 transition">
            <p class="font-semibold text-blue-900 dark:text-blue-200">My Courses</p>
            <p class="text-xs text-blue-700/80 mt-1">Continue learning</p>
        </a>
        <a href="{{ route('assignments.index') }}" wire:navigate class="rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 p-4 hover:border-purple-400 transition">
            <p class="font-semibold text-purple-900 dark:text-purple-200">Assignments</p>
            <p class="text-xs text-purple-700/80 mt-1">Submit your work</p>
        </a>
        <a href="{{ route('assessments.index') }}" wire:navigate class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4 hover:border-green-400 transition">
            <p class="font-semibold text-green-900 dark:text-green-200">Quizzes</p>
            <p class="text-xs text-green-700/80 mt-1">Test your skills</p>
        </a>
        @if($user->studentProfile)
            <a href="{{ route('students.print-credentials', $user->studentProfile->id) }}" target="_blank" class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 hover:border-amber-400 transition">
                <p class="font-semibold text-amber-900 dark:text-amber-200">My Login Card</p>
                <p class="text-xs text-amber-700/80 mt-1">Print your credentials</p>
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:card class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-600 text-white border-0 shadow-xl">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="relative flex items-center justify-between p-6">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">My Courses</p>
                    <p class="text-4xl font-bold">{{ $enrollments->count() }}</p>
                    <p class="text-blue-100 text-xs mt-2">Enrolled courses</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-orange-500 text-white border-0 shadow-xl">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="relative flex items-center justify-between p-6">
                <div>
                    <p class="text-amber-100 text-sm font-medium mb-1">XP Points</p>
                    <p class="text-4xl font-bold">{{ number_format($points->total_points ?? 0) }}</p>
                    <p class="text-amber-100 text-xs mt-2">Total earned</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden bg-gradient-to-br from-purple-500 to-purple-600 text-white border-0 shadow-xl">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="relative flex items-center justify-between p-6">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Level</p>
                    <p class="text-4xl font-bold">{{ $points->level ?? 1 }}</p>
                    <p class="text-purple-100 text-xs mt-2">Current rank</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-bold text-gray-900 dark:text-white mb-4">My Courses</h2>
            <div class="space-y-3">
                @forelse($enrollments as $enrollment)
                    <a href="{{ route('courses.learn', $enrollment->course) }}" wire:navigate class="block rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 hover:border-blue-400">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->course?->title }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $enrollment->progress_percentage ?? 0 }}% complete</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">No course enrollments yet.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-bold text-gray-900 dark:text-white mb-4">Recent Attendance</h2>
            <div class="space-y-3">
                @forelse($recentAttendance as $record)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3">
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $record->attendance_date->format('d M Y') }}</p>
                        <p class="text-xs text-gray-500 mt-1 capitalize">{{ $record->status }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No club attendance records yet.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-900 dark:text-white">Club Leaderboard</h2>
                    @if($club)
                        <a href="{{ route('leaderboards.index', ['clubId' => $club->id, 'period' => 'weekly']) }}" wire:navigate class="text-xs font-bold text-blue-600">This week →</a>
                    @endif
                </div>
                <div class="space-y-3">
                    @forelse($clubLeaderboard as $index => $entry)
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center justify-between {{ $entry->user_id === $user->id ? 'border-blue-400 dark:border-blue-600 bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-gray-400 w-5">{{ $index + 1 }}</span>
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $entry->user?->name ?? 'Student' }}</p>
                            </div>
                            <p class="text-sm font-bold text-amber-600 dark:text-amber-400">{{ number_format($entry->total_points ?? 0) }} this week</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No leaderboard data yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4">Notifications</h2>
                <div class="space-y-3">
                    @forelse($notifications as $notification)
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 {{ $notification->is_read ? 'opacity-70' : '' }}">
                            <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $notification->title }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $notification->message }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No notifications.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
