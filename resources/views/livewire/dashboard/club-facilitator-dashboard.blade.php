<div class="max-w-6xl mx-auto py-8 px-4 space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Code Club Facilitator</h1>
        <p class="text-sm text-gray-500 mt-1">Overview of your assigned clubs, attendance, and session reports.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <a href="{{ route('attendance.club') }}" wire:navigate class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 px-4 py-3 hover:border-blue-400 transition">
            <p class="font-semibold text-blue-900 dark:text-blue-200 text-sm">Club Attendance</p>
            <p class="text-xs text-blue-700/80 mt-0.5">Mark today's roster</p>
        </a>
        <a href="{{ route('club-session-reports.submit') }}" wire:navigate class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-4 py-3 hover:border-amber-400 transition">
            <p class="font-semibold text-amber-900 dark:text-amber-200 text-sm">Submit Session Report</p>
            <p class="text-xs text-amber-700/80 mt-0.5">Log session notes</p>
        </a>
        @if($clubs->count() === 1)
            <a href="{{ route('admin.code-clubs.reports.bulk-download', $clubs->first()) }}" class="rounded-xl border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-4 py-3 hover:border-orange-400 transition">
                <p class="font-semibold text-orange-900 dark:text-orange-200 text-sm">Term Reports</p>
                <p class="text-xs text-orange-700/80 mt-0.5">Download all member PDFs</p>
            </a>
        @else
            <a href="{{ route('admin.code-clubs.index') }}" wire:navigate class="rounded-xl border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-4 py-3 hover:border-orange-400 transition">
                <p class="font-semibold text-orange-900 dark:text-orange-200 text-sm">Term Reports</p>
                <p class="text-xs text-orange-700/80 mt-0.5">Open a club to download PDFs</p>
            </a>
        @endif
        <a href="{{ route('leaderboards.index', $clubs->count() === 1 ? ['clubId' => $clubs->first()->id] : []) }}" wire:navigate class="rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 px-4 py-3 hover:border-purple-400 transition">
            <p class="font-semibold text-purple-900 dark:text-purple-200 text-sm">Leaderboard</p>
            <p class="text-xs text-purple-700/80 mt-0.5">Club XP rankings</p>
        </a>
    </div>

    @if($pendingReportClubs->isNotEmpty())
        <div class="rounded-2xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-4">
            <p class="font-bold text-amber-900 dark:text-amber-200 text-sm">Session report needed today</p>
            <ul class="mt-2 space-y-1">
                @foreach($pendingReportClubs as $summary)
                    <li class="text-sm text-amber-800 dark:text-amber-300">
                        <strong>{{ $summary['club']->name }}</strong> meets today — no report submitted yet.
                        <a href="{{ route('club-session-reports.submit') }}" wire:navigate class="font-semibold underline ml-1">Submit now</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:card class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-600 text-white border-0 shadow-xl">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="relative flex items-center justify-between p-6">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">My Clubs</p>
                    <p class="text-4xl font-bold">{{ $clubs->count() }}</p>
                    <p class="text-blue-100 text-xs mt-2">Assigned to you</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden bg-gradient-to-br from-green-500 to-green-600 text-white border-0 shadow-xl">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="relative flex items-center justify-between p-6">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Active Members</p>
                    <p class="text-4xl font-bold">{{ $activeMembers }}</p>
                    <p class="text-green-100 text-xs mt-2">Across your clubs</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden bg-gradient-to-br from-purple-500 to-purple-600 text-white border-0 shadow-xl">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="relative flex items-center justify-between p-6">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Attendance This Month</p>
                    <p class="text-4xl font-bold">{{ $attendanceThisMonth }}</p>
                    <p class="text-purple-100 text-xs mt-2">Club session check-ins</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-bold mb-4">My Clubs</h2>
            @forelse($clubSummaries as $summary)
                @php $club = $summary['club']; @endphp
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 mb-3 {{ $summary['meets_today'] ? 'border-green-400 dark:border-green-600' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <a href="{{ route('admin.code-clubs.show', $club) }}" wire:navigate class="font-semibold hover:text-blue-600">{{ $club->name }}</a>
                        @if($summary['meets_today'])
                            <span class="shrink-0 text-xs font-bold uppercase tracking-wide text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/40 px-2 py-0.5 rounded-full">Today</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $club->school?->name }} · {{ $club->schedule_label }}</p>
                    @if($summary['pending_report'])
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-2 font-medium">⚠ Session report not submitted for today</p>
                    @endif
                    <div class="flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('attendance.club') }}" wire:navigate class="text-xs font-semibold text-blue-600 hover:underline">Attendance</a>
                        <a href="{{ route('club-session-reports.submit') }}" wire:navigate class="text-xs font-semibold text-blue-600 hover:underline">Report</a>
                        <a href="{{ route('admin.code-clubs.reports.bulk-download', $club) }}" class="text-xs font-semibold text-orange-600 hover:underline">Term Reports</a>
                        @if($summary['club']->activeMemberships->first())
                            <a href="{{ route('admin.code-clubs.reports.preview', ['club' => $club, 'student' => $summary['club']->activeMemberships->first()->student_id]) }}" target="_blank" class="text-xs font-semibold text-blue-600 hover:underline">Preview Report</a>
                        @endif
                        <a href="{{ route('leaderboards.index', ['clubId' => $club->id]) }}" wire:navigate class="text-xs font-semibold text-blue-600 hover:underline">Leaderboard</a>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No clubs assigned.</p>
            @endforelse
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold">Recent Session Reports</h2>
                    <a href="{{ route('club-session-reports.submit') }}" wire:navigate class="text-xs font-bold text-blue-600">Submit report →</a>
                </div>
                @forelse($recentReports as $report)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 mb-3">
                        <p class="font-semibold text-sm">{{ $report->club?->name }}</p>
                        <p class="text-xs text-gray-500">{{ $report->session_date->format('d M Y') }} · {{ ucfirst($report->status) }}</p>
                        @if($report->retentionRate() !== null)
                            <p class="text-xs text-gray-500 mt-1">Retention: {{ $report->retentionRate() }}%</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No session reports yet.</p>
                @endforelse
            </div>

            @if($retentionAlerts->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-red-200 dark:border-red-800 p-5">
                    <h2 class="font-bold text-red-700 dark:text-red-400 mb-1">Retention Alerts</h2>
                    <p class="text-xs text-gray-500 mb-4">Members with 3+ absences in the last 4 sessions</p>
                    @foreach($retentionAlerts as $alert)
                        <div class="rounded-xl border border-red-100 dark:border-red-900 px-4 py-3 mb-3">
                            <p class="font-semibold text-sm">{{ $alert['profile']->user?->name ?? 'Unknown student' }}</p>
                            <p class="text-xs text-gray-500">{{ $alert['club']->name }} · {{ $alert['absences'] }} absences</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
