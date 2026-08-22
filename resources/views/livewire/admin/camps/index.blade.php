<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 space-y-6">

    {{-- Flash --}}
    @if(session('message'))
        <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-semibold text-green-800 dark:text-green-200">{{ session('message') }}</p>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Code Camps</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Create and manage CodeCamp programs across the year.</p>
        </div>
        @if($canCreateCamps)
        <button wire:click="toggleCreateForm"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold transition-colors">
            @if($showCreateForm)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Cancel
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Camp
            @endif
        </button>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Total Camps',    'value' => $stats['total'],           'color' => 'blue'],
            ['label' => 'Active Camps',   'value' => $stats['active'],          'color' => 'green'],
            ['label' => 'Students Enrolled', 'value' => $stats['total_students'], 'color' => 'orange'],
            ['label' => 'Completed',      'value' => $stats['completed'],       'color' => 'gray'],
        ] as $stat)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $stat['label'] }}</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Create form (collapsible) --}}
    @if($canCreateCamps && $showCreateForm)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-orange-200 dark:border-orange-800 shadow-sm overflow-hidden">
            <div class="h-1 bg-orange-500"></div>
            <div class="p-6">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-5">New Camp Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Camp Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="e.g. January 2026 Code Camp"
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" />
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="startDate"
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" />
                        @error('startDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">End Date</label>
                        <input type="date" wire:model="endDate"
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" />
                        @error('endDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Max Capacity</label>
                        <input type="number" wire:model="maxCapacity" min="1" placeholder="Leave blank for unlimited"
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Description</label>
                        <textarea wire:model="description" rows="2" placeholder="Optional description..."
                                  class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition resize-none"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-5">
                    <button wire:click="toggleCreateForm"
                            class="px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="createCamp"
                            class="px-5 py-2 text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition-colors">
                        Create Camp
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Filter --}}
    <div class="flex items-center gap-3">
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Filter:</span>
        @foreach(['' => 'All', 'upcoming' => 'Upcoming', 'active' => 'Active', 'completed' => 'Completed', 'archived' => 'Archived'] as $val => $label)
            <button wire:click="$set('filterStatus', '{{ $val }}')"
                    class="px-3 py-1 rounded-full text-xs font-bold transition-colors
                        {{ $filterStatus === $val
                            ? 'bg-orange-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Camp list --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        @forelse($camps as $camp)
            @php
                $statusColors = [
                    'upcoming'  => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
                    'active'    => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
                    'completed' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                    'archived'  => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                ];
            @endphp
            <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 dark:border-gray-700/60 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                {{-- Status dot --}}
                <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $camp->status === 'active' ? 'bg-green-500' : ($camp->status === 'upcoming' ? 'bg-blue-500' : 'bg-gray-400') }}"></div>

                {{-- Name + dates --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $camp->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $camp->date_range }}
                        @if($camp->max_capacity)
                            &middot; Max {{ $camp->max_capacity }} students
                        @endif
                    </p>
                </div>

                {{-- Student count --}}
                <div class="flex-shrink-0 text-right">
                    <p class="text-lg font-extrabold text-gray-900 dark:text-white">{{ $camp->total_students }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">students</p>
                </div>

                {{-- Status badge --}}
                <span class="flex-shrink-0 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusColors[$camp->status] ?? '' }}">
                    {{ ucfirst($camp->status) }}
                </span>

                {{-- View link --}}
                <a href="{{ route('admin.camps.show', $camp) }}"
                   class="flex-shrink-0 inline-flex items-center gap-1 text-sm font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 transition-colors">
                    Manage
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">No camps yet</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Click "New Camp" to create your first CodeCamp.</p>
            </div>
        @endforelse
    </div>

    @if($camps->hasPages())
        <div>{{ $camps->links() }}</div>
    @endif

</div>
