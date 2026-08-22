<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 space-y-6">
    @if(session('message'))
        <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-sm font-semibold text-green-800 dark:text-green-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Code Clubs</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Manage school-based Code Club programs, facilitators, and members.</p>
        </div>
        @if($canCreateClubs)
            <button wire:click="toggleCreateForm" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold">
                {{ $showCreateForm ? 'Cancel' : 'New Club' }}
            </button>
        @endif
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Total Clubs', 'value' => $stats['total']],
            ['label' => 'Active', 'value' => $stats['active']],
            ['label' => 'Members', 'value' => $stats['students']],
            ['label' => 'Inactive', 'value' => $stats['inactive']],
        ] as $stat)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase">{{ $stat['label'] }}</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    @if($canCreateClubs && $showCreateForm)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-blue-200 dark:border-blue-800 p-6 space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">New Club</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Club Name *</label>
                    <input type="text" wire:model="name" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">School *</label>
                    <select wire:model="schoolId" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                        <option value="">Select school</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                    @error('schoolId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Day</label>
                    <input type="text" wire:model="dayOfWeek" placeholder="e.g. wednesday" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                    <p class="text-xs text-gray-500 mt-1">Optional initial day. Add Mon–Fri or custom days on the club Schedule tab after creation.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Max Capacity</label>
                    <input type="number" wire:model="maxCapacity" min="1" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Start Time</label>
                    <input type="time" wire:model="sessionStart" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">End Time</label>
                    <input type="time" wire:model="sessionEnd" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Description</label>
                    <textarea wire:model="description" rows="2" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
                </div>
            </div>
            <button wire:click="createClub" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">Create Club</button>
        </div>
    @endif

    <div class="flex gap-2">
        <select wire:model.live="filterStatus" class="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($clubs as $club)
            <a href="{{ route('admin.code-clubs.show', $club) }}" wire:navigate class="block bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 hover:border-blue-400 transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ $club->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $club->school?->name }}</p>
                        <p class="text-xs text-gray-400 mt-2">{{ $club->schedule_label }}</p>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $club->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($club->status) }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-3">{{ $club->activeMemberships->count() }} active members</p>
                @php $ret = $clubRetention[$club->id] ?? null; @endphp
                @if($ret)
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        @if($ret['retention_pct'] !== null)
                            <span class="px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200 font-semibold">{{ $ret['retention_pct'] }}% active</span>
                            <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-semibold">{{ $ret['dropped_pct'] }}% dropped</span>
                        @endif
                        @if($ret['attendance_rate'] !== null)
                            <span class="px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 font-semibold">{{ $ret['attendance_rate'] }}% attendance this month</span>
                        @else
                            <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 font-semibold">No attendance this month</span>
                        @endif
                    </div>
                @endif
            </a>
        @empty
            <p class="text-sm text-gray-500 col-span-full py-8 text-center">No Code Clubs yet.</p>
        @endforelse
    </div>

    {{ $clubs->links() }}
</div>
