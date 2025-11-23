<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Instructor Attendance</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ now()->format('l, F j, Y') }}</p>
    </div>

    <div class="p-6">
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        {{-- Filters Row --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Date Picker --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                    <input type="date" wire:model.live="attendanceDate" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Search Input --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text" wire:model.live="search" placeholder="Search by name..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Instructor Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-24">
            @foreach($instructors as $instructor)
                @php
                    $currentStatus = $attendance[$instructor->id] ?? 'present';
                    $instructorRoles = $instructor->roles->pluck('name')->toArray();
                    $roleDisplay = in_array('admin', $instructorRoles) ? 'Administrator' : 'Instructor';
                @endphp
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-all" 
                     wire:key="instructor-{{ $instructor->id }}">
                    {{-- Instructor Info --}}
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center text-white font-bold text-xl">
                            {{ strtoupper(substr($instructor->name, 0, 1)) }}
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $instructor->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $roleDisplay }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $instructor->email }}</p>
                        </div>
                    </div>

                    {{-- Status Selector --}}
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" 
                                    wire:click="$set('attendance.{{ $instructor->id }}', 'present')"
                                    class="px-4 py-3 text-sm font-medium rounded-lg transition-all
                                        {{ $currentStatus === 'present' 
                                            ? 'bg-green-600 text-white shadow-md' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                ✓ Present
                            </button>
                            
                            <button type="button" 
                                    wire:click="$set('attendance.{{ $instructor->id }}', 'absent')"
                                    class="px-4 py-3 text-sm font-medium rounded-lg transition-all
                                        {{ $currentStatus === 'absent' 
                                            ? 'bg-red-600 text-white shadow-md' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                ✗ Off
                            </button>
                            
                            <button type="button" 
                                    wire:click="$set('attendance.{{ $instructor->id }}', 'late')"
                                    class="px-4 py-3 text-sm font-medium rounded-lg transition-all
                                        {{ $currentStatus === 'late' 
                                            ? 'bg-yellow-600 text-white shadow-md' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                ⏰ Late
                            </button>
                            
                            <button type="button" 
                                    wire:click="$set('attendance.{{ $instructor->id }}', 'excused')"
                                    class="px-4 py-3 text-sm font-medium rounded-lg transition-all
                                        {{ $currentStatus === 'excused' 
                                            ? 'bg-blue-600 text-white shadow-md' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                📋 Excused
                            </button>
                        </div>

                        {{-- Reason Input --}}
                        @if(in_array($currentStatus, ['absent', 'late', 'excused']))
                            <div class="mt-3">
                                <input type="text" 
                                       wire:model="reasons.{{ $instructor->id }}" 
                                       placeholder="Enter reason..." 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Sticky Bottom Bar --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg z-50">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Instructors</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $instructors->count() }}</p>
                        </div>
                        
                        <div class="flex items-center space-x-4 text-sm">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-green-600 rounded-full mr-2"></span>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ collect($attendance)->filter(fn($s) => $s === 'present')->count() }} Present
                                </span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-red-600 rounded-full mr-2"></span>
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ collect($attendance)->filter(fn($s) => $s === 'absent')->count() }} Off
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <button wire:click="saveAttendance" 
                            class="px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-lg hover:shadow-xl">
                        Submit Attendance
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
