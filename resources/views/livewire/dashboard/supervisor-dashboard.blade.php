<div class="min-h-screen bg-gradient-to-br from-gray-50 via-orange-50 to-yellow-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 p-6 space-y-8">
    {{-- Hero Header Section --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-orange-600 via-yellow-600 to-orange-600 rounded-2xl shadow-2xl p-8 text-white">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold">Supervisor Dashboard</h1>
                </div>
                <p class="text-orange-100 text-lg">Welcome back, <span class="font-semibold">{{ auth()->user()->name }}</span>! Content Review & Approval Center</p>
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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        {{-- Pending Approvals Card --}}
        <a href="{{ route('content-approvals.index') }}" class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer">
            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/10 to-orange-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    @if(($stats['pendingApprovals'] ?? 0) > 0)
                        <span class="text-xs font-semibold text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30 px-2 py-1 rounded-full animate-pulse">Action Required</span>
                    @endif
                </div>
                    <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Pending Approvals</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['pendingApprovals'] ?? 0) }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        @if(($stats['pendingApprovals'] ?? 0) > 0)
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

        {{-- Approved Today Card --}}
        <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-green-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                    <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Approved Today</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['approvedToday'] ?? 0) }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span>Great progress!</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rejected Today Card --}}
        <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 to-red-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
                    <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Rejected Today</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['rejectedToday'] ?? 0) }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span>Quality control</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Reviewed Card --}}
        <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                    <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Reviewed</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['totalReviewed'] ?? 0) }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span>All time</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approval Rate Card --}}
        <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-purple-600/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Approval Rate</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ number_format($stats['approvalRate'] ?? 0, 1) }}%</p>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-purple-500 to-purple-600 rounded-full transition-all duration-500" 
                                 style="width: {{ min($stats['approvalRate'] ?? 0, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Approval Rate</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['approval_rate'] ?? 0, 1) }}%</p>
            <div class="mt-2 h-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-green-500 to-green-600" style="width: {{ min($performanceMetrics['approval_rate'] ?? 0, 100) }}%"></div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Avg Review Time</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['avg_review_time'] ?? 0, 1) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">hours</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">High Priority</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['pending_by_priority']['high'] ?? 0) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">pending</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Medium Priority</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['pending_by_priority']['medium'] ?? 0) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">pending</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Normal Priority</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($performanceMetrics['pending_by_priority']['normal'] ?? 0) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">pending</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">This Week</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['approvedThisWeek'] ?? 0) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">approved</p>
        </div>
        </div>

    {{-- Quick Stats Today/Week/Month --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-orange-50 to-yellow-50 dark:from-orange-900/20 dark:to-yellow-900/30 rounded-xl shadow-lg border border-orange-200 dark:border-orange-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Today</h3>
                <span class="text-2xl">📅</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Approved</span>
                    <span class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ $quickStats['today_approved'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Rejected</span>
                    <span class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ $quickStats['today_rejected'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Reviewed</span>
                    <span class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ ($quickStats['today_approved'] ?? 0) + ($quickStats['today_rejected'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/30 rounded-xl shadow-lg border border-green-200 dark:border-green-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">This Week</h3>
                <span class="text-2xl">📊</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Approved</span>
                    <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ $quickStats['week_approved'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Reviewed</span>
                    <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ $approvalTrends['this_week'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Avg per Day</span>
                    <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ $approvalTrends['this_week'] > 0 ? round($approvalTrends['this_week'] / 7, 1) : 0 }}</span>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/30 rounded-xl shadow-lg border border-purple-200 dark:border-purple-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">This Month</h3>
                <span class="text-2xl">🎯</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Approved</span>
                    <span class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $quickStats['month_approved'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Reviewed</span>
                    <span class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $approvalTrends['this_month'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Approval Rate</span>
                    <span class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['approvalRate'] ?? 0, 1) }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 text-center hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $approvalBreakdown['courses'] ?? 0 }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Courses Pending</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 text-center hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $approvalBreakdown['modules'] ?? 0 }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Modules Pending</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 text-center hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $approvalBreakdown['lessons'] ?? 0 }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Lessons Pending</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 text-center hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $approvalBreakdown['assessments'] ?? 0 }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Assessments Pending</p>
        </div>
        </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Pending Approvals List --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Content Approvals</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Review and manage content submissions</p>
                        </div>
                    </div>
                    <flux:badge color="orange" size="lg">{{ count($recentApprovals) }}</flux:badge>
                </div>
                <div class="p-6">
                    <div class="flex gap-2 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <flux:button wire:click="filterByStatus('all')" variant="{{ $filterStatus === 'all' ? 'primary' : 'ghost' }}" size="sm">
                            All
                        </flux:button>
                        <flux:button wire:click="filterByStatus('pending')" variant="{{ $filterStatus === 'pending' ? 'primary' : 'ghost' }}" size="sm">
                            Pending
                        </flux:button>
                        <flux:button wire:click="filterByStatus('approved')" variant="{{ $filterStatus === 'approved' ? 'primary' : 'ghost' }}" size="sm">
                            Approved
                        </flux:button>
                        <flux:button wire:click="filterByStatus('rejected')" variant="{{ $filterStatus === 'rejected' ? 'primary' : 'ghost' }}" size="sm">
                            Rejected
                        </flux:button>
                    </div>
                @if($approvals->count() > 0)
                        <div class="space-y-3">
                        @foreach($approvals as $approval)
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                                {{ class_basename($approval->approvable_type) }}: {{ $this->getApprovableTitle($approval) }}
                                            </h3>
                                            <flux:badge size="sm" variant="{{ $approval->status === 'approved' ? 'success' : ($approval->status === 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($approval->status) }}
                                            </flux:badge>
                                        @if($approval->priority)
                                                    <flux:badge size="xs" variant="{{ $approval->priority === 'high' ? 'danger' : ($approval->priority === 'medium' ? 'warning' : 'ghost') }}">
                                                {{ ucfirst($approval->priority) }} Priority
                                            </flux:badge>
                                        @endif
                                    </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Submitted by <span class="font-medium">{{ $approval->submitter->name ?? 'Unknown' }}</span> {{ $approval->submitted_at?->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2 ml-4">
                                        @if($approval->status === 'pending')
                                            <flux:button wire:click="approveContent({{ $approval->id }})" variant="primary" size="sm">
                                                Approve
                                            </flux:button>
                                            <flux:button wire:click="rejectContent({{ $approval->id }}, 'Incomplete content')" variant="danger" size="sm">
                                                Reject
                                            </flux:button>
                                        @endif
                                        <flux:button href="{{ route('content-approvals.review', $approval) }}" variant="ghost" size="sm" wire:navigate>
                                            Review
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $approvals->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">All Clear!</h3>
                            <p class="text-gray-600 dark:text-gray-400">No {{ $filterStatus === 'all' ? '' : $filterStatus }} approvals at this time</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            {{-- Recent Pending Approvals --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Recent Pending</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Latest submissions</p>
                </div>
                <div class="p-6">
                    @if(count($recentApprovals) > 0)
                        <div class="space-y-3">
                            @foreach($recentApprovals as $approval)
                                <a href="{{ route('content-approvals.review', $approval['id']) }}" class="block">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                @if(!empty($approval['priority'] ?? null))
                                                    <flux:badge size="xs" :color="($approval['priority'] ?? null) === 'high' ? 'red' : ((($approval['priority'] ?? null) === 'medium') ? 'orange' : 'yellow')">
                                                        {{ ucfirst($approval['priority'] ?? 'normal') }}
                                                    </flux:badge>
                                                @endif
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $approval['type'] ?? 'Content' }}</span>
                                            </div>
                                            <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ $approval['title'] ?? 'Untitled' }}</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                by {{ $approval['submitted_by'] ?? 'Unknown' }} {{ $approval['submitted_at'] ?? '' }}
                                            </p>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-4 text-center">
                            <flux:button href="{{ route('content-approvals.index') }}" variant="ghost" size="sm" wire:navigate>
                                View All
                            </flux:button>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                            <p class="text-sm">No pending approvals</p>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
    </div>
