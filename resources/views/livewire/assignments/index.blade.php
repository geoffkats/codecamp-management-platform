<div class="flex flex-col gap-6 p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Assignments</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage and complete your assignments</p>
        </div>
        @can('create', \App\Models\Assignment::class)
            <flux:button href="{{ route('assignments.create') }}" icon="plus" variant="primary" wire:navigate>
                Create Assignment
            </flux:button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search assignments..." />
            <flux:select wire:model.live="filter" label="Filter">
                <option value="all">All Assignments</option>
                <option value="pending">Pending</option>
                <option value="submitted">Submitted</option>
                <option value="graded">Graded</option>
            </flux:select>
        </div>
    </div>

    {{-- Assignments List --}}
    @if($assignments->count() > 0)
        <div class="space-y-4">
            @foreach($assignments as $assignment)
                <a href="{{ route('assignments.show', $assignment) }}" class="block">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $assignment->title }}</h3>
                                        @if($assignment->due_date)
                                            @if($assignment->due_date < now())
                                                <flux:badge variant="danger" size="sm">Overdue</flux:badge>
                                            @elseif($assignment->due_date < now()->addDays(2))
                                                <flux:badge variant="warning" size="sm">Due Soon</flux:badge>
                                            @endif
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ Str::limit($assignment->description, 150) }}</p>
                                    
                                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            <span>{{ $assignment->course->title }}</span>
                                        </div>
                                        @if($assignment->due_date)
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Due: {{ $assignment->due_date->format('M d, Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2 ml-4">
                                    @if($assignment->submission)
                                        @if($assignment->submission->graded_at)
                                            <flux:badge variant="success">Graded</flux:badge>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $assignment->submission->grade ?? 'N/A' }}%
                                            </span>
                                        @else
                                            <flux:badge variant="primary">Submitted</flux:badge>
                                        @endif
                                    @else
                                        <flux:badge variant="warning">Not Submitted</flux:badge>
                                    @endif
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $assignments->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-gray-600 dark:text-gray-400">No assignments found</p>
        </div>
    @endif
</div>
