<div class="flex flex-col gap-6 p-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">XP Manager</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Manage student experience points across all courses</p>
        </div>
        <div class="flex items-center gap-3">
            <flux:button wire:click="openResetModal('week')" variant="subtle" class="text-orange-600 hover:text-orange-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset Week
            </flux:button>
            <flux:button wire:click="openResetModal('all')" variant="danger">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Reset All XP
            </flux:button>
        </div>
    </div>

    @if (session('message'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3 text-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm font-medium text-blue-100">Total Students</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($totalStudents) }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm font-medium text-purple-100">Total XP Awarded</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($totalXp) }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6">
            <p class="text-sm font-medium text-green-100">Average XP</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($avgXp, 0) }}</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Student</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name or email..."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Course</label>
                <select wire:model.live="courseFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </select>
                @if($courseFilter)
                    <div class="mt-3">
                        <flux:button size="sm" variant="primary" wire:click="openCourseBulkModal({{ $courseFilter }})">
                            Award all students in this course
                        </flux:button>
                    </div>
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time Period</label>
                <select wire:model.live="timeFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
                <select wire:model.live="sortBy" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="total_points">Total XP</option>
                    <option value="level">Level</option>
                    <option value="name">Name</option>
                    <option value="period_xp">Period XP</option>
                    @if($courseFilter)
                        <option value="course_xp">Course XP</option>
                    @endif
                </select>
            </div>
        </div>
    </div>

    <!-- Bulk Operations -->
    @if(!empty($selectedStudents))
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex items-center justify-between gap-4">
                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                    {{ count($selectedStudents) }} student(s) selected
                </p>
                <div class="flex items-center gap-3">
                    <select wire:model="bulkOperation" class="px-3 py-2 border border-blue-300 dark:border-blue-700 rounded-lg text-sm dark:bg-blue-900/50 dark:text-white">
                        <option value="add">Add XP</option>
                        <option value="subtract">Subtract XP</option>
                        <option value="set">Set XP to</option>
                    </select>
                    <input type="number" wire:model="bulkPoints" placeholder="Points" min="0"
                        class="w-32 px-3 py-2 border border-blue-300 dark:border-blue-700 rounded-lg text-sm dark:bg-blue-900/50 dark:text-white">
                    <flux:button wire:click="bulkUpdateXp" variant="primary" size="sm">
                        Apply
                    </flux:button>
                    <flux:button wire:click="$set('selectedStudents', [])" variant="subtle" size="sm">
                        Clear
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    <!-- Students Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" 
                                wire:click="$toggle('selectAll')"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Student
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total_points')">
                            Total XP
                            @if($sortBy === 'total_points')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('level')">
                            Level
                            @if($sortBy === 'level')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        @if($timeFilter !== 'all')
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('period_xp')">
                                Period XP
                                @if($sortBy === 'period_xp')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                        @endif
                        @if($courseFilter)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('course_xp')">
                                Course XP
                                @if($sortBy === 'course_xp')
                                    <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Multiplier
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($students as $student)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <td class="px-4 py-3">
                                <input type="checkbox" 
                                    wire:model.live="selectedStudents" 
                                    value="{{ $student->id }}"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                        <span class="text-sm font-bold text-white">{{ $student->initials() }}</span>
                                    </div>
                                    <div>
                                        <button 
                                            wire:click="openDetailsModal({{ $student->id }})"
                                            class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition text-left">
                                            {{ $student->name }}
                                        </button>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 rounded-full text-sm font-semibold text-yellow-700 dark:text-yellow-300">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ number_format($student->points->total_points ?? 0) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 dark:bg-purple-900/30 rounded-full text-sm font-semibold text-purple-700 dark:text-purple-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Level {{ $student->points->level ?? 1 }}
                                </span>
                            </td>
                            @if($timeFilter !== 'all')
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        +{{ number_format($student->period_xp) }} XP
                                    </span>
                                </td>
                            @endif
                            @if($courseFilter)
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($student->course_xp) }} XP
                                    </span>
                                </td>
                            @endif
                            <td class="px-4 py-3">
                                @if($student->points?->xp_multiplier)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 rounded text-xs font-medium text-green-700 dark:text-green-300">
                                        {{ $student->points->xp_multiplier }}x
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:button wire:click="openEditModal({{ $student->id }})" variant="subtle" size="sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <p class="text-gray-500 dark:text-gray-400">No students found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $students->links() }}
            </div>
        @endif
    </div>

    <!-- Edit Modal -->
    @if($showEditModal && $editingUser)
        <flux:modal name="edit-xp" :show="$showEditModal" wire:model="showEditModal" class="!max-w-3xl">
            <form wire:submit.prevent="saveEdit">
                <div class="p-6 space-y-6">
                    <!-- Header with User Info -->
                    <div class="flex items-center gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                            <span class="text-2xl font-bold text-white">{{ $editingUser->initials() }}</span>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $editingUser->name }}</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $editingUser->email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Current Stats</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 rounded-full text-xs font-semibold text-yellow-700 dark:text-yellow-300">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ number_format($editingUser->points->total_points ?? 0) }} XP
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 dark:bg-purple-900/30 rounded-full text-xs font-semibold text-purple-700 dark:text-purple-300">
                                    Lv {{ $editingUser->points->level ?? 1 }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Core Stats Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Core Experience Points
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Total Points
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-normal block mt-0.5">Student's cumulative XP</span>
                                </label>
                                <div class="relative">
                                    <input type="number" min="0" wire:model.defer="editForm.total_points" 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <svg class="w-5 h-5 text-yellow-500 absolute left-3 top-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </div>
                                @error('editForm.total_points')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Level
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-normal block mt-0.5">Current progression level</span>
                                </label>
                                <div class="relative">
                                    <input type="number" min="1" wire:model.defer="editForm.level" 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <svg class="w-5 h-5 text-purple-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                @error('editForm.level')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Points to Next Level
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-normal block mt-0.5">XP needed for next level</span>
                                </label>
                                <div class="relative">
                                    <input type="number" min="0" wire:model.defer="editForm.points_to_next_level" 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <svg class="w-5 h-5 text-green-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                @error('editForm.points_to_next_level')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- Bonus Multiplier Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            XP Multiplier Bonus
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">(Optional)</span>
                        </h3>
                        <div class="bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800 rounded-lg p-4 mb-4">
                            <p class="text-sm text-orange-800 dark:text-orange-200">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                Grant temporary XP boost (e.g., 1.5x = 50% bonus, 2x = double XP)
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Multiplier Value
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-normal block mt-0.5">Leave empty for none</span>
                                </label>
                                <div class="relative">
                                    <input type="number" min="0" step="0.1" wire:model.defer="editForm.xp_multiplier" 
                                        placeholder="e.g., 1.5" 
                                        class="w-full pl-10 pr-12 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <svg class="w-5 h-5 text-orange-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="absolute right-3 top-3 text-sm text-gray-500">×</span>
                                </div>
                                @error('editForm.xp_multiplier')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Expires At
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-normal block mt-0.5">When boost ends</span>
                                </label>
                                <div class="relative">
                                    <input type="datetime-local" wire:model.defer="editForm.multiplier_expires_at" 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <svg class="w-5 h-5 text-orange-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                @error('editForm.multiplier_expires_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Reason/Note
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-normal block mt-0.5">Why this bonus?</span>
                                </label>
                                <div class="relative">
                                    <input type="text" wire:model.defer="editForm.multiplier_reason" 
                                        placeholder="e.g., Top performer" 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <svg class="w-5 h-5 text-orange-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                </div>
                                @error('editForm.multiplier_reason')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Changes will be logged and visible to the student immediately
                        </p>
                        <div class="flex items-center gap-3">
                            <flux:button type="button" wire:click="closeEditModal" variant="subtle">
                                Cancel
                            </flux:button>
                            <flux:button type="submit" variant="primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Save Changes
                            </flux:button>
                        </div>
                    </div>
                </div>
            </form>
        </flux:modal>
    @endif

    <!-- Student XP Details & Audit Modal -->
    @if($showDetailsModal && $detailsUser)
        <flux:modal name="xp-details" :show="$showDetailsModal" wire:model="showDetailsModal" class="!max-w-4xl">
            <div class="p-6 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                            <span class="text-2xl font-bold text-white">{{ $detailsUser->initials() }}</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $detailsUser->name }}</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $detailsUser->email }}</p>
                        </div>
                    </div>
                    <button wire:click="closeDetailsModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Current Stats Summary -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                        <p class="text-xs font-medium text-yellow-700 dark:text-yellow-300 uppercase tracking-wide">Total XP</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100 mt-1">
                            {{ number_format($detailsUser->points->total_points ?? 0) }}
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-800 rounded-xl p-4">
                        <p class="text-xs font-medium text-purple-700 dark:text-purple-300 uppercase tracking-wide">Level</p>
                        <p class="text-2xl font-bold text-purple-900 dark:text-purple-100 mt-1">
                            {{ $detailsUser->points->level ?? 1 }}
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                        <p class="text-xs font-medium text-green-700 dark:text-green-300 uppercase tracking-wide">XP Activities</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100 mt-1">
                            {{ count($xpHistory) }}
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                        <p class="text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wide">Enrollments</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1">
                            {{ $detailsUser->enrollments->count() }}
                        </p>
                    </div>
                </div>

                <!-- XP Breakdown by Type -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        XP Breakdown
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @php
                            $breakdown = collect($xpHistory)->groupBy('type')->map(fn($items) => $items->sum('points'));
                        @endphp
                        @foreach(['course_enrolled' => 'Enrollments', 'lesson_completed' => 'Lessons', 'course_completed' => 'Completions', 'quiz_completed' => 'Quizzes'] as $type => $label)
                            <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $label }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ number_format($breakdown->get($type, 0)) }} XP
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    {{ collect($xpHistory)->where('type', $type)->count() }} activities
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Complete XP History/Audit Trail -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Complete XP Audit Trail
                    </h3>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="max-h-96 overflow-y-auto">
                            @forelse($xpHistory as $activity)
                                <div class="flex items-start gap-4 p-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition">
                                    <!-- Icon based on type -->
                                    <div class="flex-shrink-0 mt-1">
                                        @if($activity['type'] === 'course_enrolled')
                                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                        @elseif($activity['type'] === 'lesson_completed')
                                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        @elseif($activity['type'] === 'course_completed')
                                            <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-900/30 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Activity Details -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-900 dark:text-white">
                                                    {{ ucwords(str_replace('_', ' ', $activity['type'])) }}
                                                </p>
                                                @if($activity['course'])
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                        </svg>
                                                        {{ $activity['course'] }}
                                                    </p>
                                                @endif
                                                @if($activity['lesson'])
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                        </svg>
                                                        {{ $activity['lesson'] }}
                                                    </p>
                                                @endif
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $activity['date']->format('M d, Y \a\t g:i A') }}
                                                    <span class="text-gray-400 mx-1">•</span>
                                                    {{ $activity['date']->diffForHumans() }}
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0 text-right">
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 dark:bg-green-900/30 rounded-full text-sm font-bold text-green-700 dark:text-green-300">
                                                    +{{ $activity['points'] }}
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-12 text-center">
                                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">No XP activity found for this student</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button wire:click="openEditModal({{ $detailsUser->id }})" variant="outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit XP
                    </flux:button>
                    <flux:button wire:click="closeDetailsModal" variant="subtle">
                        Close
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    @if($showCourseBulkModal)
        <flux:modal wire:model.live="showCourseBulkModal" max-width="lg">
            <div class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Award XP to Course</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Give points to every approved student in a course.</p>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Course</label>
                        <select wire:model.live="courseBulkCourseId" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select a course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Points to award</label>
                            <input type="number" min="1" max="10000" wire:model.live="courseBulkPoints" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="e.g. 50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason (optional)</label>
                            <input type="text" wire:model.live="courseBulkReason" maxlength="255" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Bonus, event, reward...">
                        </div>
                    </div>

                    @if($courseBulkCourseId)
                        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 p-4 text-sm text-blue-800 dark:text-blue-200">
                            Everyone enrolled (approved) in the selected course will receive the XP amount above. Progress will be logged as "bulk_award".
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3">
                    <flux:button wire:click="closeCourseBulkModal" variant="subtle">Cancel</flux:button>
                    <flux:button wire:click="awardCourseXp" variant="primary" :disabled="!$courseBulkCourseId || $courseBulkPoints <= 0">
                        Award XP
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    <!-- Reset Confirmation Modal -->
    @if($showResetModal)
        <flux:modal name="reset-xp" :show="$showResetModal" wire:model="showResetModal">
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2 text-center">Confirm Reset</h2>
                
                @if($resetType === 'all')
                    <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                        This will <strong class="text-red-600">reset ALL student XP to 0</strong> and delete all progress records. This action cannot be undone!
                    </p>
                @elseif($resetType === 'week')
                    <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                        This will remove all XP earned <strong>this week</strong> and delete the associated progress records.
                    </p>
                @elseif($resetType === 'course')
                    <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                        This will remove all XP earned from <strong>the selected course</strong> and delete the associated progress records.
                    </p>
                @endif

                <div class="flex items-center justify-center gap-3">
                    <flux:button wire:click="closeResetModal" variant="subtle">Cancel</flux:button>
                    <flux:button wire:click="confirmReset" variant="danger">
                        Yes, Reset XP
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
