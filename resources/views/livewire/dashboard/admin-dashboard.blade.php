<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 p-6 space-y-8">
        {{-- Hero Header Section --}}
        <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8 text-white">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h1 class="text-4xl font-bold">Admin Dashboard</h1>
                    </div>
                    <p class="text-blue-100 text-lg">Welcome back, <span class="font-semibold">{{ auth()->user()->name }}</span>! Here's your system overview</p>
                    <div class="flex items-center gap-4 mt-4">
                        <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm">{{ now()->format('l, F j, Y') }}</span>
                        </div>
                    </div>
                </div>
                <flux:button wire:click="refresh" icon="arrow-path" variant="ghost" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white border-white/30">
                    Refresh Data
                </flux:button>
            </div>
        </div>

        {{-- Main Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Users Card --}}
            <a href="{{ route('admin.users.index') }}" class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        @php
                            $usersChange = $stats['users_change'] ?? 0;
                            $usersChangeClass = $usersChange >= 0 ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30';
                            $usersChangeSign = $usersChange >= 0 ? '+' : '';
                        @endphp
                        <span class="text-xs font-semibold {{ $usersChangeClass }} px-2 py-1 rounded-full">{{ $usersChangeSign }}{{ number_format($usersChange, 1) }}%</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['total_users'] ?? 0) }}</p>
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            <span>{{ number_format($stats['active_users'] ?? 0) }} active</span>
                        </div>
                    </div>
                </div>
            </a>

            {{-- Total Courses Card --}}
            <a href="{{ route('courses.index') }}" class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-green-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            </svg>
                        </div>
                        @php
                            $coursesChange = $stats['courses_change'] ?? 0;
                            $coursesChangeClass = $coursesChange >= 0 ? 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30' : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30';
                            $coursesChangeSign = $coursesChange >= 0 ? '+' : '';
                        @endphp
                        <span class="text-xs font-semibold {{ $coursesChangeClass }} px-2 py-1 rounded-full">{{ $coursesChangeSign }}{{ number_format($coursesChange, 1) }}%</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Courses</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['total_courses'] ?? 0) }}</p>
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span>{{ number_format($stats['published_courses'] ?? 0) }} published</span>
                        </div>
                    </div>
                </div>
            </a>

            {{-- Enrollments Card --}}
            <a href="{{ route('enrollments.index') }}" class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-purple-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        @php
                            $enrollmentsChange = $stats['enrollments_change'] ?? 0;
                            $enrollmentsChangeClass = $enrollmentsChange >= 0 ? 'text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30' : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30';
                            $enrollmentsChangeSign = $enrollmentsChange >= 0 ? '+' : '';
                        @endphp
                        <span class="text-xs font-semibold {{ $enrollmentsChangeClass }} px-2 py-1 rounded-full">{{ $enrollmentsChangeSign }}{{ number_format($enrollmentsChange, 1) }}%</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Enrollments</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['total_enrollments'] ?? 0) }}</p>
                        <div class="mt-2">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-600 dark:text-gray-400">Completion Rate</span>
                                <span class="font-semibold text-purple-600 dark:text-purple-400">{{ number_format($stats['completion_rate'] ?? 0, 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-purple-500 to-purple-600 rounded-full transition-all duration-500" 
                                     style="width: {{ min($stats['completion_rate'] ?? 0, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            {{-- Pending Approvals Card --}}
            <a href="{{ route('content-approvals.index') }}" class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-orange-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        @if(($stats['pending_approvals'] ?? 0) > 0)
                            <span class="text-xs font-semibold text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30 px-2 py-1 rounded-full animate-pulse">Action Required</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Pending Approvals</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['pending_approvals'] ?? 0) }}</p>
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            @if(($stats['pending_approvals'] ?? 0) > 0)
                                <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                                <span>Requires attention</span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                <span>All clear!</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Performance Metrics --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Engagement Rate</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['engagement_rate'] ?? 0, 1) }}%</p>
                <div class="mt-2 h-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600" style="width: {{ min($performanceMetrics['engagement_rate'] ?? 0, 100) }}%"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Completion Rate</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['completion_rate'] ?? 0, 1) }}%</p>
                <div class="mt-2 h-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-green-500 to-green-600" style="width: {{ min($performanceMetrics['completion_rate'] ?? 0, 100) }}%"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Retention Rate</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['retention_rate'] ?? 0, 1) }}%</p>
                <div class="mt-2 h-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-purple-600" style="width: {{ min($performanceMetrics['retention_rate'] ?? 0, 100) }}%"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Active (7d)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['active_learners_7d'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">learners</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Active (30d)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['active_learners_30d'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">learners</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Avg Completion</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($quickStats['avg_completion_time'] ?? 0, 0) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">days</p>
            </div>
        </div>

        {{-- Quick Stats Today/Week/Month --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/30 rounded-xl shadow-lg border border-blue-200 dark:border-blue-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Today</h3>
                    <span class="text-2xl">📅</span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">New Users</span>
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $quickStats['today_new_users'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Enrollments</span>
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $quickStats['today_enrollments'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Completions</span>
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $quickStats['today_completions'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/30 rounded-xl shadow-lg border border-green-200 dark:border-green-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">This Week</h3>
                    <span class="text-2xl">📊</span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Enrollments</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ $quickStats['week_enrollments'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Courses Created</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ $recentActivity['course_creations'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Badges Earned</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ $recentActivity['new_badges_earned'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/30 rounded-xl shadow-lg border border-purple-200 dark:border-purple-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">This Month</h3>
                    <span class="text-2xl">🎯</span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Enrollments</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $quickStats['month_enrollments'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Challenges</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $recentActivity['challenges_completed'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Discussions</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $recentActivity['discussions_created'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Tabs Section --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <a href="{{ route('lessons.index') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow border border-gray-200 dark:border-gray-700 p-5 cursor-pointer group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Lessons</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_lessons'] ?? 0) }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('badges.index') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow border border-gray-200 dark:border-gray-700 p-5 cursor-pointer group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center group-hover:bg-yellow-200 dark:group-hover:bg-yellow-900/50 transition">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Badges</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_badges'] ?? 0) }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('daily-challenges.index') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow border border-gray-200 dark:border-gray-700 p-5 cursor-pointer group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 transition">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Challenges</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['active_challenges'] ?? 0) }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('notifications.index') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow border border-gray-200 dark:border-gray-700 p-5 cursor-pointer group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900/50 transition">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Notifications</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_notifications'] ?? 0) }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('notifications.index') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow border border-gray-200 dark:border-gray-700 p-5 cursor-pointer group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center group-hover:bg-red-200 dark:group-hover:bg-red-900/50 transition">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Unread</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['unread_notifications'] ?? 0) }}</p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Pending Approvals --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pending Approvals</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Content awaiting review</p>
                            </div>
                        </div>
                        <flux:badge color="orange" size="lg">{{ count($pendingApprovals) }}</flux:badge>
                    </div>

                    <div class="p-6">
                        @if(count($pendingApprovals) > 0)
                            <div class="space-y-3">
                                @foreach($pendingApprovals as $approval)
                                    <a href="{{ route('content-approvals.review', $approval['id']) }}" class="block">
                                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <flux:badge size="sm" :color="$approval['priority'] === 'urgent' ? 'red' : ($approval['priority'] === 'high' ? 'orange' : 'yellow')">
                                                        {{ ucfirst($approval['priority']) }}
                                                    </flux:badge>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $approval['type'] }}</span>
                                                </div>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $approval['title'] }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                    Submitted by <span class="font-medium">{{ $approval['submitted_by'] }}</span> {{ $approval['submitted_at'] }}
                                                </p>
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <div class="mt-4 text-center">
                                <flux:button href="{{ route('content-approvals.index') }}" variant="ghost" wire:navigate>
                                    View All Approvals
                                </flux:button>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">All Clear!</h3>
                                <p class="text-gray-600 dark:text-gray-400">No pending approvals at this time</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Recent Courses --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Recent Courses</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Latest course additions</p>
                        </div>
                        <flux:button size="sm" variant="ghost" href="{{ route('courses.index') }}" wire:navigate>
                            View All
                        </flux:button>
                    </div>

                    <div class="p-6">
                        @if(count($recentCourses) > 0)
                            <div class="space-y-3">
                                @foreach($recentCourses as $course)
                                    <a href="{{ route('courses.show', $course['id']) }}" class="block">
                                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $course['title'] }}</p>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">by {{ $course['instructor'] }}</p>
                                                    <span class="text-gray-400">•</span>
                                                    <flux:badge size="sm" :color="$course['status'] === 'approved' ? 'green' : 'yellow'">
                                                        {{ ucfirst($course['status']) }}
                                                    </flux:badge>
                                                </div>
                                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <span>{{ $course['enrollments'] }} enrollments</span>
                                                    <span>{{ $course['created_at'] }}</span>
                                                </div>
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                </svg>
                                <p>No courses yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- System Health --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">System Health</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Monitor system status</p>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach($systemHealth as $key => $health)
                            @if($key !== 'active_sessions')
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $iconColor = $health['color'] ?? 'green';
                                            $colorClasses = [
                                                'green' => 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30',
                                                'yellow' => 'text-yellow-600 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/30',
                                                'red' => 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30',
                                                'orange' => 'text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/30',
                                            ];
                                        @endphp
                                        <div class="w-10 h-10 rounded-lg {{ $colorClasses[$iconColor] ?? $colorClasses['green'] }} flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $key) ?: 'Storage' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $health['message'] ?? ($key === 'storage' ? $health['percent'] . '% free space' : 'Healthy') }}</p>
                                        </div>
                                    </div>
                                    <flux:badge size="sm" :color="$health['color'] ?? 'green'">
                                        {{ ucfirst($health['status'] ?? 'ok') }}
                                    </flux:badge>
                                </div>
                            @endif
                        @endforeach
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Active Sessions</span>
                                <flux:badge size="sm" color="blue">
                                    {{ $systemHealth['active_sessions']['count'] ?? 0 }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Top Performers --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Top Performers</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Leaderboard champions</p>
                    </div>
                    <div class="p-6">
                        @if(count($topPerformers) > 0)
                            <div class="space-y-3">
                                @foreach($topPerformers as $index => $performer)
                                    <a href="{{ route('admin.users.show', $performer['user_id']) }}" class="block">
                                        <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 text-white font-bold text-sm shadow-lg">
                                                {{ $index + 1 }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $performer['name'] }}</p>
                                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                                    <span>Level {{ $performer['level'] }}</span>
                                                    <span>•</span>
                                                    <span>{{ $performer['badges_count'] }} badges</span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-gray-900 dark:text-white">{{ $performer['points'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">points</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <div class="mt-4 text-center">
                                <flux:button href="{{ route('leaderboards.index') }}" variant="ghost" size="sm" wire:navigate>
                                    View Full Leaderboard
                                </flux:button>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                                <p>No performers yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Enrollment Trends Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Enrollment Trends</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Last 7 days</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <canvas id="enrollmentTrendsChart" height="200"></canvas>
            </div>

            {{-- User Growth Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">User Growth</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Last 6 months</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <canvas id="userGrowthChart" height="200"></canvas>
            </div>

            {{-- Completion Rate Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Completion Rate</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Overall statistics</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <canvas id="completionRateChart" height="200"></canvas>
            </div>

            {{-- Top Courses Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top Courses</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">By enrollments</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                <canvas id="topCoursesChart" height="200"></canvas>
            </div>
        </div>

        {{-- Recent Users Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Recent Users</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Latest user registrations</p>
                </div>
                <flux:button size="sm" variant="ghost" href="{{ route('admin.users.index') }}" wire:navigate>
                    View All
                </flux:button>
            </div>

            <div class="p-6">
                @if(count($recentUsers) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">User</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Email</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Roles</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Last Login</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentUsers as $user)
                                    <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        <td class="py-3 px-4">
                                            <a href="{{ route('admin.users.show', $user['id']) }}" class="flex items-center gap-2 cursor-pointer">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold shadow-lg">
                                                    {{ substr($user['name'], 0, 1) }}
                                                </div>
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $user['name'] }}</span>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $user['email'] }}</td>
                                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $user['roles'] ?: 'No roles' }}</td>
                                        <td class="py-3 px-4">
                                            <flux:badge size="sm" :color="$user['is_active'] ? 'green' : 'red'">
                                                {{ $user['is_active'] ? 'Active' : 'Inactive' }}
                                            </flux:badge>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $user['last_login'] }}</td>
                                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $user['created_at'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <p>No users yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enrollment Trends Chart (Line Chart)
    const enrollmentCtx = document.getElementById('enrollmentTrendsChart');
    if (enrollmentCtx && window.Chart) {
        const enrollmentData = @json($chartData['enrollment_trends'] ?? ['labels' => [], 'data' => []]);
        new Chart(enrollmentCtx, {
            type: 'line',
            data: {
                labels: enrollmentData.labels || [],
                datasets: [{
                    label: 'Enrollments',
                    data: enrollmentData.data || [],
                    borderColor: 'rgb(139, 92, 246)',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // User Growth Chart (Line Chart)
    const userGrowthCtx = document.getElementById('userGrowthChart');
    if (userGrowthCtx && window.Chart) {
        const userGrowthData = @json($chartData['user_growth'] ?? ['labels' => [], 'data' => []]);
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: userGrowthData.labels || [],
                datasets: [{
                    label: 'New Users',
                    data: userGrowthData.data || [],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Completion Rate Chart (Doughnut Chart)
    const completionCtx = document.getElementById('completionRateChart');
    if (completionCtx && window.Chart) {
        const completionData = @json($chartData['completion_rate'] ?? ['completed' => 0, 'total' => 0]);
        const completed = completionData.completed || 0;
        const total = completionData.total || 0;
        const pending = total - completed;
        
        new Chart(completionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress'],
                datasets: [{
                    data: [completed, pending],
                    backgroundColor: [
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Top Courses Chart (Bar Chart)
    const topCoursesCtx = document.getElementById('topCoursesChart');
    if (topCoursesCtx && window.Chart) {
        const topCoursesData = @json($chartData['top_courses'] ?? ['labels' => [], 'enrollments' => []]);
        new Chart(topCoursesCtx, {
            type: 'bar',
            data: {
                labels: topCoursesData.labels || [],
                datasets: [{
                    label: 'Enrollments',
                    data: topCoursesData.enrollments || [],
                    backgroundColor: 'rgba(249, 115, 22, 0.8)',
                    borderColor: 'rgb(249, 115, 22)',
                    borderWidth: 2,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});

// Reinitialize charts when Livewire updates
Livewire.hook('morph.updated', ({ el, component }) => {
    if (component.getName() === 'dashboard.admin-dashboard') {
        setTimeout(() => {
            window.location.reload(); // Simple reload for chart reinit
        }, 100);
    }
});
</script>
@endpush
