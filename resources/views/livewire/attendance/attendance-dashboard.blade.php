<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Attendance Dashboard</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Track student check-in/check-out records</p>
    </div>

    <div class="p-6">
        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Records</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Completed</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ $stats['completed'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Incomplete</p>
                        <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-2">{{ $stats['incomplete'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Hours</p>
                        <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-2">{{ number_format($stats['total_hours'], 1) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-green-200 dark:border-green-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Currently Present</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ $stats['currently_present'] }}</p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">On premises now</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center relative">
                        <div class="absolute top-0 right-0 w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Real-Time Presence Widget --}}
        @if($currentlyPresent->count() > 0)
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl shadow-sm border border-green-200 dark:border-green-800 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse mr-3"></div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Currently On Premises</h3>
                        <span class="ml-3 px-3 py-1 bg-green-600 text-white text-sm font-bold rounded-full">{{ $currentlyPresent->count() }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($currentlyPresent as $present)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-green-200 dark:border-green-700">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($present->studentProfile->full_name ?? 'N', 0, 1)) }}
                                </div>
                                <div class="ml-2 flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $present->studentProfile->full_name ?? 'Unknown' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($present->check_in_time)->format('h:i A') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Quick Date Range Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div class="flex flex-wrap gap-2">
                    <button wire:click="setDateRange('today')" class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors">
                        Today
                    </button>
                    <button wire:click="setDateRange('week')" class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors">
                        This Week
                    </button>
                    <button wire:click="setDateRange('month')" class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors">
                        This Month
                    </button>
                    <button wire:click="setDateRange('last_week')" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Last Week
                    </button>
                    <button wire:click="setDateRange('last_month')" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Last Month
                    </button>
                </div>
                <button wire:click="exportCsv" class="px-6 py-2 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                    <input type="date" wire:model.live="dateTo" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select wire:model.live="statusFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">All Status</option>
                        <option value="present">Completed (Both In/Out)</option>
                        <option value="checked_in">Checked In Only</option>
                        <option value="incomplete">Incomplete</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text" wire:model.live="search" placeholder="Student name or ID..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
        </div>

        {{-- Attendance Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check-In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check-Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Hours</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-sm">
                                            {{ strtoupper(substr($log->studentProfile->full_name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $log->studentProfile->full_name ?? 'Unknown' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $log->studentProfile->student_id ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $log->attendance_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @if($log->check_in_time)
                                        <span class="font-medium">{{ \Carbon\Carbon::parse($log->check_in_time)->format('h:i A') }}</span>
                                    @else
                                        <span class="text-gray-400">--:--</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @if($log->check_out_time)
                                        <span class="font-medium">{{ \Carbon\Carbon::parse($log->check_out_time)->format('h:i A') }}</span>
                                    @else
                                        <span class="text-gray-400">--:--</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($log->check_in_time && $log->check_out_time)
                                        @php
                                            $checkIn = \Carbon\Carbon::parse($log->check_in_time);
                                            $checkOut = \Carbon\Carbon::parse($log->check_out_time);
                                            $totalHours = $checkIn->diffInHours($checkOut, true);
                                            $hours = floor($totalHours);
                                            $minutes = round(($totalHours - $hours) * 60);
                                        @endphp
                                        <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                                            {{ $hours }}h {{ $minutes }}m
                                        </span>
                                    @else
                                        <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->check_in_time && $log->check_out_time)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                            Present
                                        </span>
                                    @elseif($log->check_in_time)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                            Checked In
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400">
                                            Incomplete
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No attendance records found</p>
                                    <p class="text-sm mt-1">Try adjusting your filters or date range</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
