<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Modules</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Browse and manage course modules
                </p>
            </div>
            <a href="{{ route('modules.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                New module
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Module or course…"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select wire:model.live="filterStatus"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Sort</label>
                <select wire:model.live="sortBy"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="latest">Latest</option>
                    <option value="title">Title</option>
                    <option value="active">Active first</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($modules as $module)
                <a href="{{ route('modules.show', $module) }}"
                   class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-2 flex items-start justify-between gap-3">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $module->title }}</h2>
                        <span @class([
                            'rounded-full px-2 py-0.5 text-xs font-semibold',
                            'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $module->is_active,
                            'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' => ! $module->is_active,
                        ])>
                            {{ $module->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">
                        {{ $module->course?->title ?? 'No course' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500">
                        {{ $module->lessons_count }} lesson{{ $module->lessons_count === 1 ? '' : 's' }}
                    </p>
                </a>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    No modules found.
                </div>
            @endforelse
        </div>

        @if($modules->hasPages())
            <div>
                {{ $modules->links() }}
            </div>
        @endif
    </div>
</div>
