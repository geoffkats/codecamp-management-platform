<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Welcome back, {{ auth()->user()->name }} · {{ now()->format('l, F j, Y') }}</p>
            </div>
            <flux:button wire:click="refresh" icon="arrow-path" variant="ghost">
                Refresh
            </flux:button>
        </div>

        {{-- ZONE 1: ACTION REQUIRED --}}
        @if(($stats['pending_approvals'] ?? 0) > 0)
            <div class="bg-orange-50 dark:bg-orange-900/20 border-2 border-orange-500 dark:border-orange-600 rounded-xl p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-orange-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">🚨 Action Required</h2>
                            <div class="space-y-2">
                                @if(($stats['pending_approvals'] ?? 0) > 0)
                                    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                        <span class="font-semibold text-orange-600 dark:text-orange-400">{{ $stats['pending_approvals'] }}</span>
                                        <span>Pending Content Approvals</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <flux:button href="{{ route('content-approvals.index') }}" variant="primary" wire:navigate>
                        Review Now
                    </flux:button>
                </div>
            </div>
        @else
            <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-500 dark:border-green-600 rounded-xl p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-green-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">✅ All Clear</h2>
                        <p class="text-gray-600 dark:text-gray-400">No action needed right now.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ZONE 2: CORE HEALTH METRICS --}}
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">System Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Active Students --}}
                <a href="{{ route('admin.users.index') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        @php
                            $change = $stats['users_change'] ?? 0;
                        @endphp
                        @if($change != 0)
                            <div class="text-right">
                                <span class="text-xs font-medium {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $change >= 0 ? '↑' : '↓' }} {{ number_format(abs($change), 1) }}%
                                </span>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">vs last month</p>
                            </div>
                        @endif
                    </div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">People</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Active Students</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_users'] ?? 0) }}</p>
                </a>

                {{-- ICT Schools Active --}}
                <a href="{{ route('admin.schools') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">People</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">ICT Schools Active</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format(count($ictSchoolPerformance ?? [])) }}</p>
                </a>

                {{-- Total Courses --}}
                <a href="{{ route('courses.index') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($stats['published_courses'] ?? 0) }} published</span>
                    </div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Content</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Courses</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_courses'] ?? 0) }}</p>
                </a>

                {{-- Active This Month --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Activity</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Active This Month</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['active_learners_30d'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        {{-- ZONE 3: RECENT ACTIVITY --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Latest ICT Exam Results --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Latest ICT Exam Results</h3>
                        <a href="{{ route('admin.schools') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View all →</a>
                    </div>
                </div>
                <div class="p-4">
                    @if($recentIctAssessmentResults && $recentIctAssessmentResults->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($recentIctAssessmentResults->take(5) as $attempt)
                                @php
                                    $assessment = $attempt->assessment;
                                    $maxScore = ($assessment?->questions && $assessment->questions->count() > 0)
                                        ? $assessment->questions->sum('points')
                                        : 100;
                                    $attemptScore = $attempt->score ?? 0;
                                    $percentage = $maxScore > 0 ? ($attemptScore / $maxScore) * 100 : 0;
                                @endphp
                                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $attempt->user?->name ?? 'Student' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ $assessment?->title ?? 'Assessment' }} · {{ $attempt->completed_at?->format('M j') ?? $attempt->created_at?->format('M j') }}
                                        </p>
                                    </div>
                                    <div class="ml-3 flex items-center gap-2">
                                        <span class="text-sm font-semibold {{ $attempt->is_passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($percentage, 0) }}%
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-xs {{ $attempt->is_passed ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                            {{ $attempt->is_passed ? 'Pass' : 'Fail' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No exam results yet</p>
                    @endif
                </div>
            </div>

            {{-- Latest Users --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Latest Users</h3>
                        <a href="{{ route('enrollments.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View all →</a>
                    </div>
                </div>
                <div class="p-4">
                    @if(count($recentUsers ?? []) > 0)
                        <div class="space-y-3">
                            @foreach(array_slice($recentUsers, 0, 5) as $user)
                                <a href="{{ route('admin.users.show', $user['id']) }}" class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 -mx-2 px-2 rounded">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $user['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user['email'] }}</p>
                                    </div>
                                    <span class="ml-3 text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($user['created_at'])->diffForHumans() }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No recent users</p>
                    @endif
                </div>
            </div>

            {{-- Latest Courses --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Latest Courses</h3>
                        <a href="{{ route('courses.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View all →</a>
                    </div>
                </div>
                <div class="p-4">
                    @if(count($recentCourses ?? []) > 0)
                        <div class="space-y-3">
                            @foreach(array_slice($recentCourses, 0, 5) as $course)
                                <a href="{{ route('courses.show', $course['id']) }}" class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 -mx-2 px-2 rounded">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $course['title'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $course['instructor'] ?? 'Unknown' }}</p>
                                    </div>
                                    <span class="ml-3 px-2 py-0.5 rounded text-xs {{ $course['status'] === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ ucfirst($course['status']) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No recent courses</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Quick Access</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-center">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Users</span>
                </a>
                <a href="{{ route('courses.index') }}" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-center">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Courses</span>
                </a>
                <a href="{{ route('admin.schools') }}" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-center">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Schools</span>
                </a>
                <a href="{{ route('content-approvals.index') }}" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-center">
                    <svg class="w-8 h-8 text-orange-600 dark:text-orange-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        Approvals
                        @if(($stats['pending_approvals'] ?? 0) > 0)
                            <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">
                                {{ $stats['pending_approvals'] }}
                            </span>
                        @endif
                    </span>
                </a>
                <a href="{{ route('badges.index') }}" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-center">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Badges</span>
                </a>
                <a href="{{ route('notifications.index') }}" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-center">
                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Notifications</span>
                </a>
            </div>
        </div>
    </div>
</div>