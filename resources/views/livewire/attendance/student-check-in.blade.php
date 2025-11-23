<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Attendance Check-In/Out</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ now()->format('l, F j, Y') }}</p>
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

        {{-- Debug Info (remove in production) --}}
        @if(!$studentProfile)
            <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <p class="text-yellow-800 dark:text-yellow-200 font-semibold">⚠️ No Student Profile Found</p>
                <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-2">
                    You need a student profile to use this feature. Please contact your administrator to create one for you.
                </p>
            </div>
        @endif

        <div class="max-w-2xl mx-auto">
            {{-- Current Status Card --}}
            @if($todayLog)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Today's Status</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                            <div class="text-sm text-green-600 dark:text-green-400 mb-1">Check-In</div>
                            <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                                {{ $todayLog->check_in_time ? \Carbon\Carbon::parse($todayLog->check_in_time)->format('h:i A') : '--:--' }}
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">Check-Out</div>
                            <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                {{ $todayLog->check_out_time ? \Carbon\Carbon::parse($todayLog->check_out_time)->format('h:i A') : '--:--' }}
                            </div>
                        </div>
                    </div>

                    @if($todayLog->check_in_time && $todayLog->check_out_time)
                        @php
                            $checkIn = \Carbon\Carbon::parse($todayLog->check_in_time);
                            $checkOut = \Carbon\Carbon::parse($todayLog->check_out_time);
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

            {{-- Check-In/Out Form --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        @if(!$todayLog || !$todayLog->check_in_time)
                            Check In
                        @elseif(!$todayLog->check_out_time)
                            Check Out
                        @else
                            All Done for Today!
                        @endif
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        @if(!$todayLog || !$todayLog->check_in_time)
                            Enter today's attendance code to check in
                        @elseif(!$todayLog->check_out_time)
                            @if($canCheckOut)
                                Enter today's attendance code to check out
                            @else
                                You can check out in {{ $minutesRemaining }} minute(s) (minimum 1 hour required)
                            @endif
                        @else
                            You've completed your attendance for today
                        @endif
                    </p>
                </div>

                @if(!$todayLog || !$todayLog->check_out_time)
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Attendance Code
                            </label>
                            <input type="text" 
                                   wire:model="code" 
                                   placeholder="Enter code (e.g., ABC123)"
                                   class="w-full px-4 py-3 text-center text-2xl font-mono font-bold uppercase border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   maxlength="10">
                        </div>

                        <div class="flex gap-3">
                            @if(!$todayLog || !$todayLog->check_in_time)
                                <button wire:click="checkIn" 
                                        class="flex-1 px-6 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors shadow-lg hover:shadow-xl">
                                    ✓ Check In
                                </button>
                            @else
                                <button wire:click="checkOut" 
                                        @if(!$canCheckOut) disabled @endif
                                        class="flex-1 px-6 py-4 font-semibold rounded-lg transition-colors shadow-lg
                                            {{ $canCheckOut 
                                                ? 'bg-blue-600 hover:bg-blue-700 text-white hover:shadow-xl cursor-pointer' 
                                                : 'bg-gray-400 text-gray-200 cursor-not-allowed opacity-60' }}">
                                    → Check Out
                                    @if(!$canCheckOut)
                                        <span class="text-xs block mt-1">({{ $minutesRemaining }} min remaining)</span>
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-6xl mb-4">✅</div>
                        <p class="text-gray-600 dark:text-gray-400">See you tomorrow!</p>
                    </div>
                @endif
            </div>

            {{-- Help Section --}}
            <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-semibold mb-1">Need help?</p>
                        <p>Ask your teacher for today's attendance code. The code is displayed in the classroom and changes daily.</p>
                        <p class="mt-2 text-xs">
                            <strong>Note:</strong> You must wait at least 1 hour after check-in before you can check out.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Troubleshooting --}}
            @if($studentProfile)
                <div class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400">
                    Student ID: {{ $studentProfile->student_id ?? 'N/A' }} | 
                    Profile: {{ $studentProfile->full_name ?? 'N/A' }}
                </div>
            @endif
        </div>
    </div>
</div>
