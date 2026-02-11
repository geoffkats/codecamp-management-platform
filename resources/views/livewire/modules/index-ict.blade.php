<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="flex flex-col gap-6 p-6">
        {{-- Header Section --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Modules</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Modules available for your school
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            label="Search Modules"
                            placeholder="Search by module or course..."
                        />
                    </div>
                    <flux:field>
                        <flux:label>Status</flux:label>
                        <flux:select wire:model.live.debounce.150ms="filterStatus">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </flux:select>
                    </flux:field>
                </div>
            </div>
        </div>

        {{-- Modules Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($modules->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Module</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Students</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Avg Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lessons</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($modules as $module)
                                @php
                                    $avgProgress = $module->course?->enrollments_avg_progress_percentage;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $module->title }}</div>
                                        @if($module->overview)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">
                                                {{ strip_tags($module->overview) }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $module->course?->title ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $module->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $module->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $module->course?->enrollments_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $avgProgress !== null ? number_format($avgProgress, 1) . '%' : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $module->lessons_count }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <flux:button href="{{ route('modules.show', $module) }}" variant="ghost" size="sm" wire:navigate>
                                            View
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $modules->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No modules found</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Try adjusting your filters to find more modules.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
