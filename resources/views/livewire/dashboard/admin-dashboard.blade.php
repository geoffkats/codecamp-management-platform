{{-- Admin Dashboard — Code Academy Uganda --}}
<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">

    {{-- ── HEADER ──────────────────────────────────────────────── --}}
    <div class="bg-orange-600">
        <div class="mx-auto max-w-6xl px-4 py-5 sm:px-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-orange-200">{{ now()->format('l, F j, Y') }}</p>
                    <h1 class="mt-0.5 text-xl font-bold text-white">
                        Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }}
                    </h1>
                </div>
                <div class="flex items-center gap-2">
                    @if(($stats['pending_approvals'] ?? 0) > 0)
                        <a href="{{ route('content-approvals.index') }}" wire:navigate
                           class="flex items-center gap-1.5 rounded-md border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20 transition">
                            <span class="flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white leading-none">{{ $stats['pending_approvals'] }}</span>
                            Approvals
                        </a>
                    @endif
                    @if(config('features.code_club', false) && ($codeClubStats['pending_reports'] ?? 0) > 0)
                        <a href="{{ route('admin.club-session-reports.index') }}" wire:navigate
                           class="flex items-center gap-1.5 rounded-md border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20 transition">
                            <span class="flex h-4 w-4 items-center justify-center rounded-full bg-amber-400 text-xs font-bold text-slate-900 leading-none">{{ $codeClubStats['pending_reports'] }}</span>
                            Club Reports
                        </a>
                    @endif
                    <button wire:click="refresh"
                            class="rounded-md border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20 transition">
                        Refresh
                    </button>
                </div>
            </div>

            {{-- TODAY BAR --}}
            <div class="mt-4 grid grid-cols-4 gap-2">
                @foreach([
                    ['label' => 'New Users',       'value' => $quickStats['today_new_users'] ?? 0],
                    ['label' => 'Enrollments',      'value' => $quickStats['today_enrollments'] ?? 0],
                    ['label' => 'Active (7d)',      'value' => $performanceMetrics['active_learners_7d'] ?? 0],
                    ['label' => 'Completions',      'value' => $quickStats['today_completions'] ?? 0],
                ] as $qs)
                <div class="rounded-md border border-white/20 bg-white/10 px-3 py-2">
                    <p class="text-base font-bold text-white">{{ number_format($qs['value']) }}</p>
                    <p class="text-xs text-orange-200">{{ $qs['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── MAIN CONTENT ─────────────────────────────────────────── --}}
    <div class="mx-auto max-w-6xl space-y-4 px-4 py-5 sm:px-6">

        {{-- ── CORE METRICS ──────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

            <a href="{{ route('students.index') }}" wire:navigate
               class="rounded-lg border border-slate-200 bg-white p-4 transition hover:border-orange-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-orange-50 dark:bg-orange-950/30">
                        <svg class="h-4 w-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    @if(($stats['users_change'] ?? 0) != 0)
                        <span class="rounded px-1.5 py-0.5 text-xs font-semibold {{ ($stats['users_change'] ?? 0) > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                            {{ ($stats['users_change'] ?? 0) > 0 ? '+' : '' }}{{ number_format($stats['users_change'] ?? 0, 1) }}%
                        </span>
                    @endif
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['active_users'] ?? 0) }}</p>
                <p class="mt-0.5 text-xs font-semibold text-slate-600 dark:text-slate-400">Active Students</p>
                <p class="text-xs text-slate-400">{{ number_format($stats['total_users'] ?? 0) }} total</p>
            </a>

            <a href="{{ route('admin.enrollments') }}" wire:navigate
               class="rounded-lg border border-slate-200 bg-white p-4 transition hover:border-blue-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-blue-50 dark:bg-blue-950/30">
                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    @if(($stats['enrollments_change'] ?? 0) != 0)
                        <span class="rounded px-1.5 py-0.5 text-xs font-semibold {{ ($stats['enrollments_change'] ?? 0) > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                            {{ ($stats['enrollments_change'] ?? 0) > 0 ? '+' : '' }}{{ number_format($stats['enrollments_change'] ?? 0, 1) }}%
                        </span>
                    @endif
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['total_enrollments'] ?? 0) }}</p>
                <p class="mt-0.5 text-xs font-semibold text-slate-600 dark:text-slate-400">Enrollments</p>
                <div class="mt-2">
                    <div class="mb-1 flex justify-between text-xs text-slate-400">
                        <span>Completion</span>
                        <span class="font-medium text-slate-600 dark:text-slate-300">{{ number_format($stats['completion_rate'] ?? 0, 0) }}%</span>
                    </div>
                    <div class="h-1 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-zinc-800">
                        <div class="h-full rounded-full bg-blue-500" style="width: {{ min($stats['completion_rate'] ?? 0, 100) }}%"></div>
                    </div>
                </div>
            </a>

            <a href="{{ route('courses.index') }}" wire:navigate
               class="rounded-lg border border-slate-200 bg-white p-4 transition hover:border-slate-400 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-100 dark:bg-zinc-800">
                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    @if(($stats['courses_change'] ?? 0) != 0)
                        <span class="rounded px-1.5 py-0.5 text-xs font-semibold {{ ($stats['courses_change'] ?? 0) > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                            {{ ($stats['courses_change'] ?? 0) > 0 ? '+' : '' }}{{ number_format($stats['courses_change'] ?? 0, 1) }}%
                        </span>
                    @endif
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['published_courses'] ?? 0) }}</p>
                <p class="mt-0.5 text-xs font-semibold text-slate-600 dark:text-slate-400">Published Courses</p>
                <p class="text-xs text-slate-400">{{ number_format($stats['total_lessons'] ?? 0) }} lessons total</p>
            </a>

            <a href="{{ route('admin.schools') }}" wire:navigate
               class="rounded-lg border border-slate-200 bg-white p-4 transition hover:border-slate-400 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-100 dark:bg-zinc-800">
                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format(count($ictSchoolPerformance ?? [])) }}</p>
                <p class="mt-0.5 text-xs font-semibold text-slate-600 dark:text-slate-400">ICT Schools</p>
                <p class="text-xs text-slate-400">{{ number_format($performanceMetrics['active_learners_30d'] ?? 0) }} active (30d)</p>
            </a>
        </div>

        {{-- ── PERFORMANCE BAND ────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach([
                ['label' => 'Engagement',   'value' => number_format($performanceMetrics['engagement_rate'] ?? 0, 1) . '%', 'sub' => 'learners with progress'],
                ['label' => 'Retention',    'value' => number_format($performanceMetrics['retention_rate'] ?? 0, 1) . '%',  'sub' => '30-day active'],
                ['label' => 'This Month',   'value' => number_format($quickStats['month_enrollments'] ?? 0),               'sub' => 'new enrollments'],
                ['label' => 'This Week',    'value' => number_format($quickStats['week_enrollments'] ?? 0),                'sub' => 'new enrollments'],
            ] as $band)
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $band['value'] }}</p>
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $band['label'] }}</p>
                <p class="text-xs text-slate-400">{{ $band['sub'] }}</p>
            </div>
            @endforeach
        </div>

        @if(config('features.code_club', false))
        {{-- ── CODE CLUB OVERVIEW ───────────────────────────────── --}}
        <div class="rounded-lg border border-violet-200 bg-white dark:border-violet-900/40 dark:bg-zinc-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-violet-100 px-4 py-3 dark:border-violet-900/30">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wide text-violet-700 dark:text-violet-300">Code Club</h3>
                    <p class="text-xs text-slate-500">School clubs · Student ID login · Session reports</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.code-clubs.index') }}" wire:navigate class="text-xs font-semibold text-violet-600 hover:text-violet-700">All clubs →</a>
                    @if(Route::has('admin.club-session-reports.index'))
                        <a href="{{ route('admin.club-session-reports.index') }}" wire:navigate class="text-xs font-semibold text-violet-600 hover:text-violet-700">Session reports →</a>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach([
                    ['label' => 'Active Clubs', 'value' => $codeClubStats['active_clubs'] ?? 0],
                    ['label' => 'Club Members', 'value' => $codeClubStats['total_members'] ?? 0],
                    ['label' => 'CC Students', 'value' => $codeClubStats['students'] ?? 0],
                    ['label' => 'New This Month', 'value' => $codeClubStats['new_students_month'] ?? 0],
                    ['label' => 'Reports Pending', 'value' => $codeClubStats['pending_reports'] ?? 0, 'alert' => ($codeClubStats['pending_reports'] ?? 0) > 0],
                    ['label' => 'Follow-up', 'value' => $codeClubStats['follow_up_reports'] ?? 0, 'alert' => ($codeClubStats['follow_up_reports'] ?? 0) > 0],
                ] as $cc)
                <div class="rounded-md border border-slate-100 bg-slate-50 px-3 py-2 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <p class="text-lg font-bold {{ !empty($cc['alert']) ? 'text-amber-600' : 'text-slate-900 dark:text-white' }}">{{ number_format($cc['value']) }}</p>
                    <p class="text-xs font-medium text-slate-500">{{ $cc['label'] }}</p>
                </div>
                @endforeach
            </div>
            @if(count($codeClubHighlights ?? []) > 0)
            <div class="border-t border-violet-100 px-4 py-3 dark:border-violet-900/30">
                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Recent Clubs</p>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($codeClubHighlights as $club)
                        <a href="{{ route('admin.code-clubs.show', $club['id']) }}" wire:navigate
                           class="rounded-md border border-slate-100 px-3 py-2 transition hover:border-violet-300 dark:border-zinc-800 dark:hover:border-violet-700">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $club['name'] }}</p>
                            <p class="truncate text-xs text-slate-400">{{ $club['school'] }} · {{ $club['members'] }} members</p>
                            <p class="truncate text-xs text-violet-600 dark:text-violet-400">{{ $club['schedule'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- ── RECENT USERS + TOP PERFORMERS ──────────────────── --}}
        <div class="grid gap-4 lg:grid-cols-2">

            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Recent Users</h3>
                    <a href="{{ route('admin.users.index') }}" wire:navigate class="text-xs font-semibold text-orange-600 hover:text-orange-700">View all →</a>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-zinc-800">
                    @forelse(array_slice($recentUsers ?? [], 0, 6) as $u)
                        <a href="{{ route('admin.users.show', $u['id']) }}" wire:navigate
                           class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-orange-600 text-xs font-bold text-white">
                                {{ strtoupper(substr($u['name'], 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $u['name'] }}</p>
                                <p class="truncate text-xs text-slate-400">{{ $u['email'] ?: ($u['login_id'] ?? '—') }}@if($u['roles']) · {{ $u['roles'] }}@endif</p>
                            </div>
                            <div class="flex flex-shrink-0 flex-col items-end gap-0.5">
                                <span class="text-xs text-slate-400">{{ $u['created_at'] }}</span>
                                <span class="flex items-center gap-1 text-xs {{ $u['is_active'] ? 'text-emerald-600' : 'text-red-400' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $u['is_active'] ? 'bg-emerald-500' : 'bg-red-400' }}"></span>
                                    {{ $u['is_active'] ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="px-4 py-8 text-center text-xs text-slate-400">No recent users.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Top Performers</h3>
                    <span class="text-xs text-slate-400">by XP</span>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-zinc-800">
                    @forelse(array_slice($topPerformers ?? [], 0, 6) as $i => $p)
                        <div class="flex items-center gap-3 px-4 py-2.5">
                            <span class="w-5 flex-shrink-0 text-center text-xs">
                                @if($i === 0) 🥇 @elseif($i === 1) 🥈 @elseif($i === 2) 🥉
                                @else <span class="font-bold text-slate-400">{{ $i + 1 }}</span>
                                @endif
                            </span>
                            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">
                                {{ strtoupper(substr($p['name'], 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $p['name'] }}</p>
                                <p class="text-xs text-slate-400">{{ $p['badges_count'] }} badges · Lv {{ $p['level'] ?? 1 }}</p>
                            </div>
                            <span class="flex-shrink-0 rounded bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950/30 dark:text-blue-300">
                                {{ $p['points'] }} XP
                            </span>
                        </div>
                    @empty
                        <p class="px-4 py-8 text-center text-xs text-slate-400">No points data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── COURSES + ICT SCHOOLS ───────────────────────────── --}}
        <div class="grid gap-4 lg:grid-cols-2">

            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Recent Courses</h3>
                    <a href="{{ route('courses.index') }}" wire:navigate class="text-xs font-semibold text-orange-600 hover:text-orange-700">View all →</a>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-zinc-800">
                    @forelse(array_slice($recentCourses ?? [], 0, 5) as $course)
                        <a href="{{ route('courses.show', $course['id']) }}" wire:navigate
                           class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-md bg-slate-100 dark:bg-zinc-800">
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $course['title'] }}</p>
                                <p class="text-xs text-slate-400">{{ $course['instructor'] ?? 'No instructor' }} · {{ $course['enrollments'] }} enrolled</p>
                            </div>
                            <span class="flex-shrink-0 rounded px-2 py-0.5 text-xs font-medium
                                {{ $course['is_published'] ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                {{ $course['is_published'] ? 'Live' : ucfirst($course['status'] ?? 'Draft') }}
                            </span>
                        </a>
                    @empty
                        <p class="px-4 py-8 text-center text-xs text-slate-400">No courses yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">ICT School Performance</h3>
                    <a href="{{ route('admin.schools') }}" wire:navigate class="text-xs font-semibold text-orange-600 hover:text-orange-700">View all →</a>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-zinc-800">
                    @forelse(array_slice($ictSchoolPerformance ?? [], 0, 5) as $school)
                        <div class="px-4 py-2.5">
                            <div class="flex items-center justify-between">
                                <p class="mr-2 truncate text-sm font-medium text-slate-800 dark:text-slate-200">{{ $school['school_name'] }}</p>
                                <span class="flex-shrink-0 text-xs font-bold {{ $school['pass_rate'] >= 70 ? 'text-emerald-600' : ($school['pass_rate'] >= 50 ? 'text-amber-600' : 'text-red-500') }}">
                                    {{ $school['pass_rate'] }}%
                                </span>
                            </div>
                            <div class="mt-1.5 h-1 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full {{ $school['pass_rate'] >= 70 ? 'bg-emerald-500' : ($school['pass_rate'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                     style="width: {{ min($school['pass_rate'], 100) }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">{{ number_format($school['passed_attempts']) }} / {{ number_format($school['total_attempts']) }} passed</p>
                        </div>
                    @empty
                        <p class="px-4 py-8 text-center text-xs text-slate-400">No ICT data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── BOTTOM ROW: APPROVALS + QUICK ACCESS + ACTIVITY ── --}}
        <div class="grid gap-4 lg:grid-cols-3">

            {{-- Pending Approvals --}}
            @if(count($pendingApprovals ?? []) > 0)
            <div class="rounded-lg border border-amber-200 bg-white dark:border-amber-900/40 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-amber-100 px-4 py-3 dark:border-amber-900/30">
                    <div class="flex items-center gap-2">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Approvals</h3>
                        <span class="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-bold text-amber-700">{{ count($pendingApprovals) }}</span>
                    </div>
                    <a href="{{ route('content-approvals.index') }}" wire:navigate class="text-xs font-semibold text-amber-600 hover:text-amber-700">Review →</a>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-zinc-800">
                    @foreach(array_slice($pendingApprovals, 0, 4) as $approval)
                        <div class="px-4 py-2.5">
                            <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $approval['title'] }}</p>
                            <p class="text-xs text-slate-400">{{ $approval['type'] }} · {{ $approval['submitted_by'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick Access --}}
            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 {{ count($pendingApprovals ?? []) === 0 ? 'lg:col-span-2' : '' }}">
                <div class="border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Quick Access</h3>
                </div>
                <div class="grid grid-cols-3 gap-2 p-3">
                    @foreach([
                        ['label' => 'Users',       'route' => 'admin.users.index',           'bg' => 'bg-orange-600'],
                        ['label' => 'Camps',       'route' => 'admin.camps.index',           'bg' => 'bg-blue-600'],
                        ['label' => 'Reports',     'route' => 'admin.daily-reports.index',   'bg' => 'bg-slate-600'],
                        ['label' => 'Enrollments', 'route' => 'admin.enrollments',           'bg' => 'bg-slate-600'],
                        ['label' => 'Progress',    'route' => 'admin.student-progress.index','bg' => 'bg-slate-600'],
                        ['label' => 'Settings',    'route' => 'admin.settings',              'bg' => 'bg-slate-600'],
                    ] as $action)
                        @if(Route::has($action['route']))
                            <a href="{{ route($action['route']) }}" wire:navigate
                               class="flex items-center justify-center rounded-md px-2 py-2 text-xs font-semibold text-white transition hover:opacity-90 {{ $action['bg'] }}">
                                {{ $action['label'] }}
                            </a>
                        @endif
                    @endforeach
                    @if(config('features.code_club', false))
                        @foreach([
                            ['label' => 'Code Clubs', 'route' => 'admin.code-clubs.index', 'bg' => 'bg-violet-600'],
                            ['label' => 'Club Reports', 'route' => 'admin.club-session-reports.index', 'bg' => 'bg-violet-600'],
                        ] as $action)
                            @if(Route::has($action['route']))
                                <a href="{{ route($action['route']) }}" wire:navigate
                                   class="flex items-center justify-center rounded-md px-2 py-2 text-xs font-semibold text-white transition hover:opacity-90 {{ $action['bg'] }}">
                                    {{ $action['label'] }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- This Week --}}
            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">This Week</h3>
                </div>
                <div class="divide-y divide-slate-50 dark:divide-zinc-800">
                    @foreach([
                        ['label' => 'New Courses',         'value' => $recentActivity['course_creations'] ?? 0],
                        ['label' => 'Badges Earned',        'value' => $recentActivity['new_badges_earned'] ?? 0],
                        ['label' => 'Challenges Done',      'value' => $recentActivity['challenges_completed'] ?? 0],
                        ['label' => 'Discussions',          'value' => $recentActivity['discussions_created'] ?? 0],
                    ] as $act)
                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="text-sm text-slate-600 dark:text-slate-400">{{ $act['label'] }}</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ number_format($act['value']) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>
