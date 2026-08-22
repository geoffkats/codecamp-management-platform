<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Mark Student Attendance</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ \Carbon\Carbon::parse($attendanceDate)->format('l, F j, Y') }}</p>
    </div>

    <div class="p-6">
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        <x-attendance.nav-tabs />

        @if($isLocked)
            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                <p class="text-amber-800 dark:text-amber-200 text-sm">Attendance for this date is locked after {{ config('attendance.lock_time', '17:00') }}. Contact an admin to override.</p>
            </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Checked In</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $todayStats['checked_in_today'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Still On Site</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $todayStats['still_in_today'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Late</p>
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $todayStats['late'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Absent</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $todayStats['absent'] ?? 0 }}</p>
            </div>
        </div>

        {{-- Filters Row --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                {{-- Camp --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Camp</label>
                    <select wire:model.live="campId" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">All Camps</option>
                        @foreach($camps as $camp)
                            <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Date Picker --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                    <input type="date" wire:model.live="attendanceDate" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Class Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Class Filter</label>
                    <select wire:model.live="classFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">All Classes</option>
                        @foreach($profiles->pluck('class_grade')->unique()->filter() as $class)
                            <option value="{{ $class }}">{{ $class }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Filter</label>
                    <select wire:model.live="statusFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="absent">Absent</option>
                        <option value="unmarked">Unmarked</option>
                    </select>
                </div>

                {{-- Search Input --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text" wire:model.live="search" placeholder="Search by name or ID..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Student Cards Grid --}}
        @if(empty($roster))
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">No students found. Try selecting a camp or adjusting your search.</p>
            </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-24">
            @foreach($roster as $row)
                @php
                    $student = $row['profile'];
                    $currentStatus = $attendance[$student->id] ?? '';
                    if ($statusFilter === 'unmarked' && $currentStatus !== '') continue;
                    if ($statusFilter && $statusFilter !== 'unmarked' && $currentStatus !== $statusFilter) continue;
                @endphp
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all" 
                     wire:key="student-{{ $student->id }}">
                    {{-- Student Photo --}}
                    <div class="flex items-center mb-4">
                        <x-user-avatar :user="$student->user" :name="$student->full_name" size="md" rounded="full" />
                        <div class="ml-3 flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $student->full_name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }}</p>
                            @if($student->class_grade)
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->class_grade }}</p>
                            @endif
                            @php
                                $categoryLabel = match($student->student_category ?? 'codecamp') {
                                    'school_club' => 'School Club',
                                    'ict_school' => 'ICT School',
                                    default => 'Codecamp',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 text-[10px] rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 mt-1">
                                {{ $categoryLabel }}
                            </span>
                            @if($row['source'])
                                <span class="inline-flex px-2 py-0.5 text-[10px] rounded bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 mt-1">
                                    {{ ucfirst(str_replace('_', ' ', $row['source'])) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Status Picker --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button"
                                    wire:click="$set('attendance.{{ $student->id }}', 'present')"
                                    class="px-3 py-2 text-xs font-medium rounded-lg transition-all
                                        {{ $currentStatus === 'present'
                                            ? 'bg-green-600 text-white shadow-md'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                Present
                            </button>

                            <button type="button"
                                    wire:click="$set('attendance.{{ $student->id }}', 'late')"
                                    class="px-3 py-2 text-xs font-medium rounded-lg transition-all
                                        {{ $currentStatus === 'late'
                                            ? 'bg-amber-600 text-white shadow-md'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                Late
                            </button>

                            <button type="button"
                                    wire:click="$set('attendance.{{ $student->id }}', 'absent')"
                                    class="px-3 py-2 text-xs font-medium rounded-lg transition-all
                                        {{ $currentStatus === 'absent'
                                            ? 'bg-red-600 text-white shadow-md'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                Absent
                            </button>
                        </div>

                        @if($currentStatus === '')
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Not marked yet</p>
                        @endif

                        {{-- Clock In/Out Times (shown for present/late) --}}
                        @if(in_array($currentStatus, ['present', 'late'], true))
                            <div class="mt-3 space-y-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                            </svg>
                                            Clock In
                                        </span>
                                    </label>
                                    <input type="time" 
                                           wire:model.live="clockIn.{{ $student->id }}" 
                                           class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Clock Out
                                        </span>
                                    </label>
                                    <input type="time" 
                                           wire:model.live="clockOut.{{ $student->id }}" 
                                           class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                </div>
                                @php
                                    $clockInTime = $clockIn[$student->id] ?? null;
                                    $clockOutTime = $clockOut[$student->id] ?? null;
                                    $totalHours = 0;
                                    if ($clockInTime && $clockOutTime) {
                                        try {
                                            $start = \Carbon\Carbon::parse($clockInTime);
                                            $end = \Carbon\Carbon::parse($clockOutTime);
                                            $totalHours = $start->diffInHours($end, true);
                                        } catch (\Exception $e) {
                                            $totalHours = 0;
                                        }
                                    }
                                @endphp
                                @if($totalHours > 0)
                                    @php
                                        $hours = floor($totalHours);
                                        $minutes = round(($totalHours - $hours) * 60);
                                    @endphp
                                    <div class="text-xs text-center py-2 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 text-blue-700 dark:text-blue-300 rounded-lg font-semibold border border-blue-200 dark:border-blue-700">
                                        ⏱️ Total: {{ $hours }}h {{ $minutes }}m
                                    </div>
                                @elseif($clockInTime && $clockOutTime)
                                    <div class="text-xs text-center py-2 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 rounded-lg">
                                        ⚠️ Check times
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Reason Input (shown for absent) --}}
                        @if($currentStatus === 'absent')
                            <div class="mt-3">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                                <input type="text" 
                                       wire:model="reasons.{{ $student->id }}" 
                                       placeholder="Enter reason for absence..." 
                                       class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500">
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Sticky Bottom Bar --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg z-50">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Marked</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $markedCount }}/{{ count($roster) }}</p>
                        </div>
                        
                        <div class="flex items-center space-x-4 text-sm">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-green-600 rounded-full mr-2"></span>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ collect($attendance)->filter(fn($s) => $s === 'present')->count() }} Present
                                </span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-amber-500 rounded-full mr-2"></span>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ collect($attendance)->filter(fn($s) => $s === 'late')->count() }} Late
                                </span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-red-600 rounded-full mr-2"></span>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ collect($attendance)->filter(fn($s) => $s === 'absent')->count() }} Absent
                                </span>
                            </div>
                            @php
                                $totalHoursSum = 0;
                                foreach ($attendance as $studentId => $status) {
                                    if (in_array($status, ['present', 'late'], true)) {
                                        $clockInTime = $clockIn[$studentId] ?? null;
                                        $clockOutTime = $clockOut[$studentId] ?? null;
                                        if ($clockInTime && $clockOutTime) {
                                            try {
                                                $start = \Carbon\Carbon::parse($clockInTime);
                                                $end = \Carbon\Carbon::parse($clockOutTime);
                                                $totalHoursSum += $start->diffInHours($end, true);
                                            } catch (\Exception $e) {}
                                        }
                                    }
                                }
                            @endphp
                            @if($totalHoursSum > 0)
                                <div class="flex items-center">
                                    <span class="w-3 h-3 bg-blue-600 rounded-full mr-2"></span>
                                    <span class="text-gray-700 dark:text-gray-300">
                                        {{ number_format($totalHoursSum, 1) }} Total Hours
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <button wire:click="saveAttendance"
                            @if($isLocked) disabled @endif
                            class="px-8 py-3 font-semibold rounded-lg transition-colors shadow-lg
                                {{ $isLocked ? 'bg-gray-400 text-gray-200 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 text-white hover:shadow-xl' }}">
                        Submit Attendance
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
