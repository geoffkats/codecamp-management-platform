<div class="flex flex-col gap-6 p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Discussions</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Join course discussions and help fellow learners</p>
        </div>
        <flux:button href="{{ route('discussions.create') }}" icon="plus" variant="primary" wire:navigate>
            New Discussion
        </flux:button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">My Discussions</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['my_discussions'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Recent (7 days)</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['recent'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search discussions..." />
            <div class="flex gap-2">
                <flux:button wire:click="$set('filter', 'all')" variant="{{ $filter === 'all' ? 'primary' : 'ghost' }}" size="sm">All</flux:button>
                <flux:button wire:click="$set('filter', 'my_discussions')" variant="{{ $filter === 'my_discussions' ? 'primary' : 'ghost' }}" size="sm">My Discussions</flux:button>
            </div>
        </div>
    </div>

    {{-- Subject Filter Tabs --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 mr-2">Filter by Subject:</span>
            <flux:button wire:click="$set('subjectFilter', 'all')" variant="{{ ($subjectFilter ?? 'all') === 'all' ? 'primary' : 'ghost' }}" size="sm">
                All
            </flux:button>
            <flux:button wire:click="$set('subjectFilter', 'scratch')" variant="{{ ($subjectFilter ?? '') === 'scratch' ? 'primary' : 'ghost' }}" size="sm">
                🟦 Scratch
            </flux:button>
            <flux:button wire:click="$set('subjectFilter', 'python')" variant="{{ ($subjectFilter ?? '') === 'python' ? 'primary' : 'ghost' }}" size="sm">
                🐍 Python
            </flux:button>
            <flux:button wire:click="$set('subjectFilter', 'web')" variant="{{ ($subjectFilter ?? '') === 'web' ? 'primary' : 'ghost' }}" size="sm">
                🌐 Web Dev
            </flux:button>
            <flux:button wire:click="$set('subjectFilter', 'javascript')" variant="{{ ($subjectFilter ?? '') === 'javascript' ? 'primary' : 'ghost' }}" size="sm">
                ⚡ JavaScript
            </flux:button>
        </div>
    </div>

    {{-- Discussions List --}}
    @if($discussions->count() > 0)
        <div class="space-y-4">
            @foreach($discussions as $index => $discussion)
                <a href="{{ route('discussions.show', $discussion) }}" class="block" {{ $index > 2 ? 'loading="lazy"' : '' }}>
                    <x-discussion-card :discussion="$discussion" :showSubject="true" :showReactions="true" />
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $discussions->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p class="text-gray-600 dark:text-gray-400">No discussions found</p>
            <flux:button href="{{ route('discussions.create') }}" variant="primary" class="mt-4" wire:navigate>
                Start a Discussion
            </flux:button>
        </div>
    @endif
</div>
