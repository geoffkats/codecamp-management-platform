<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Attendance Check-In/Out</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ now()->format('l, F j, Y') }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            @if($isCodeClubStudent)
                @if($hasSessionToday)
                    {{ $checkInWindow['club_name'] ?? 'Club' }} session today:
                    {{ $checkInWindow['session_start_display'] ?? $checkInWindow['session_start'] ?? '—' }}
                    – {{ $checkInWindow['session_end_display'] ?? $checkInWindow['session_end'] ?? '—' }}
                    · Check-in opens at {{ $checkInWindow['check_in_opens_display'] ?? $checkInWindow['start'] ?? '—' }}
                @else
                    No club session scheduled for today.
                @endif
            @else
                Check-in window: {{ $checkInWindow['start'] ?? '08:00' }} – {{ $checkInWindow['end'] ?? '10:00' }}
            @endif
        </p>
    </div>

    <div class="p-6">
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-red-800 dark:text-red-200 font-semibold">{{ session('error') }}</p>
            </div>
        @endif

        @if(!$studentProfile)
            <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <p class="text-yellow-800 dark:text-yellow-200 font-semibold">No Student Profile Found</p>
                <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-2">
                    You need a student profile to use this feature. Please contact your administrator.
                </p>
            </div>
        @endif

        <div class="max-w-2xl mx-auto">
            @if($todayRecord)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Today's Status</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                            <div class="text-sm text-green-600 dark:text-green-400 mb-1">Check-In</div>
                            <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                                {{ $todayRecord->formattedClockIn() ?? '--:--' }}
                            </div>
                            @if($todayRecord->status === 'late')
                                <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Late arrival</span>
                            @endif
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">Check-Out</div>
                            <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                {{ $todayRecord->formattedClockOut() ?? '--:--' }}
                            </div>
                            @if($todayRecord->clock_out && str_contains((string) $todayRecord->notes, 'Auto checked out'))
                                <span class="text-xs text-blue-500 dark:text-blue-300 font-medium">Auto checked out</span>
                            @endif
                        </div>
                    </div>

                    @if($todayRecord->clock_in && $todayRecord->clock_out)
                        @php
                            $checkIn = $todayRecord->clockInCarbon();
                            $checkOut = $todayRecord->clockOutCarbon();
                            $totalHours = $checkIn->diffInHours($checkOut, true);
                        @endphp
                        <div class="mt-4 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-center">
                            <div class="text-sm text-indigo-600 dark:text-indigo-400 mb-1">Total Time on Premises</div>
                            <div class="text-3xl font-bold text-indigo-700 dark:text-indigo-300">
                                {{ number_format($totalHours, 2) }} hours
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        @if(!$todayRecord || !$todayRecord->clock_in)
                            Check In
                        @elseif(!$todayRecord->clock_out)
                            Check Out
                        @else
                            All Done for Today!
                        @endif
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        @if(!$todayRecord || !$todayRecord->clock_in)
                            @if($canCheckInNow)
                                Tap the button to mark yourself present. No code needed.
                            @elseif($isCodeClubStudent && !$hasSessionToday)
                                Check-in is only available on your club's scheduled session days.
                            @elseif($checkInStatus === 'before')
                                Check-in opens at {{ $checkInWindow['check_in_opens_display'] ?? $checkInWindow['start'] ?? '—' }}
                                (session {{ $checkInWindow['session_start_display'] ?? $checkInWindow['session_start'] ?? '—' }}
                                – {{ $checkInWindow['session_end_display'] ?? $checkInWindow['session_end'] ?? '—' }}).
                            @elseif($checkInStatus === 'after')
                                Today's session has ended. Check-in is closed.
                            @else
                                Check-in is closed. Window is {{ $checkInWindow['start'] ?? '07:00' }}–{{ $checkInWindow['end'] ?? '14:00' }}.
                            @endif
                        @elseif(!$todayRecord->clock_out)
                            @if($canCheckOut)
                                Tap to check out when you leave.
                            @else
                                You can check out in {{ $minutesRemaining }} minute(s)
                            @endif
                        @else
                            You've completed your attendance for today
                            @if(str_contains((string) $todayRecord->notes, 'Auto checked out'))
                                (auto checked out because checkout was forgotten)
                            @endif
                        @endif
                    </p>
                </div>

                @if(!$todayRecord || !$todayRecord->clock_out)
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            @if(!$todayRecord || !$todayRecord->clock_in)
                                <button wire:click="checkIn"
                                        wire:loading.attr="disabled"
                                        @if(!$canCheckInNow) disabled @endif
                                        class="flex-1 px-6 py-4 font-semibold rounded-lg transition-colors shadow-lg
                                            {{ $canCheckInNow ? 'bg-green-600 hover:bg-green-700 text-white hover:shadow-xl' : 'bg-gray-400 text-gray-200 cursor-not-allowed' }}">
                                    <span wire:loading.remove wire:target="checkIn">I'm here — check in</span>
                                    <span wire:loading wire:target="checkIn">Checking in…</span>
                                </button>
                            @else
                                <button wire:click="checkOut"
                                        wire:loading.attr="disabled"
                                        @if(!$canCheckOut) disabled @endif
                                        class="flex-1 px-6 py-4 font-semibold rounded-lg transition-colors shadow-lg
                                            {{ $canCheckOut ? 'bg-blue-600 hover:bg-blue-700 text-white hover:shadow-xl' : 'bg-gray-400 text-gray-200 cursor-not-allowed opacity-60' }}">
                                    Check Out
                                    @if(!$canCheckOut)
                                        <span class="text-xs block mt-1">({{ $minutesRemaining }} min remaining)</span>
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-600 dark:text-gray-400">See you tomorrow!</p>
                    </div>
                @endif
            </div>

            @if($studentProfile)
                <div class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400">
                    Student ID: {{ $studentProfile->student_id ?? 'N/A' }} |
                    Profile: {{ $studentProfile->full_name ?? 'N/A' }}
                </div>
            @endif
        </div>
    </div>
</div>
