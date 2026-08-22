@php
    $archivedCount = max(($stats['total'] ?? 0) - ($stats['active'] ?? 0) - ($stats['completed'] ?? 0), 0);
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My Courses</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $stats['total'] ?? 0 }} Courses • {{ $stats['completed'] ?? 0 }} Completed • Avg Progress: {{ number_format($stats['average_progress'] ?? 0, 0) }}%
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                wire:click="$set('filterStatus','active')"
                class="rounded-full px-4 py-2 text-xs font-semibold transition {{ $filterStatus === 'active' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 dark:bg-zinc-900 dark:text-gray-300 dark:border-zinc-800 dark:hover:bg-zinc-800' }}">
                Active ({{ $stats['active'] ?? 0 }})
            </button>
            <button
                wire:click="$set('filterStatus','completed')"
                class="rounded-full px-4 py-2 text-xs font-semibold transition {{ $filterStatus === 'completed' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 dark:bg-zinc-900 dark:text-gray-300 dark:border-zinc-800 dark:hover:bg-zinc-800' }}">
                Completed ({{ $stats['completed'] ?? 0 }})
            </button>
            <button
                wire:click="$set('filterStatus','archived')"
                class="rounded-full px-4 py-2 text-xs font-semibold transition {{ $filterStatus === 'archived' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 dark:bg-zinc-900 dark:text-gray-300 dark:border-zinc-800 dark:hover:bg-zinc-800' }}">
                Archived ({{ $archivedCount }})
            </button>
        </div>

        @if(($showCampFilter ?? false) && $camps->count() > 0)
            <div class="max-w-xs">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Filter by Camp</label>
                <select wire:model.live="filterCampId"
                        class="w-full rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sm text-gray-900 dark:text-white px-3 py-2">
                    <option value="">All camps</option>
                    @foreach($camps as $camp)
                        <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($enrollments->count() > 0)
            <div class="space-y-4">
                @foreach($enrollments as $enrollment)
                    @php
                        $progress = number_format($enrollment->progress_percentage ?? 0, 0);
                        $statusLabel = 'Not started';
                        if ($enrollment->completed_at) {
                            $statusLabel = 'Completed';
                        } elseif (($enrollment->progress_percentage ?? 0) > 0) {
                            $statusLabel = 'In progress';
                        }
                    @endphp
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="space-y-1">
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $enrollment->course->title }}</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Instructor: {{ $enrollment->course->instructor->name }}</p>
                                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span>Progress: {{ $progress }}%</span>
                                    <span>Status: {{ $statusLabel }}</span>
                                </div>
                            </div>
                            <flux:button href="{{ route('courses.learn', $enrollment->course) }}" variant="primary" size="sm" wire:navigate>
                                Continue
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-2">
                {{ $enrollments->links() }}
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-600 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:text-gray-400">
                No enrollments found for this view.
            </div>
        @endif
    </div>
</div>
