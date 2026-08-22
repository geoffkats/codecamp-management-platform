<div class="mx-auto max-w-5xl space-y-6 p-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Course discussions</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Ask questions, share work, and help classmates — one topic per post.</p>
        </div>
        <flux:button href="{{ route('discussions.create', array_filter(['course' => $courseId, 'lesson' => $lessonId])) }}" icon="plus" variant="primary" wire:navigate>
            New post
        </flux:button>
    </div>

    @include('livewire.discussions.partials.guidelines')

    @if(isset($forumChallenges) && $forumChallenges->isNotEmpty())
        @include('livewire.discussions.partials.challenge-hint', [
            'challenges' => $forumChallenges,
            'progress' => $forumChallengeProgress ?? collect(),
        ])
    @endif

    {{-- Toolbar --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by title or content..." />
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button wire:click="$set('filter', 'all')" variant="{{ $filter === 'all' ? 'primary' : 'ghost' }}" size="sm">All posts</flux:button>
                <flux:button wire:click="$set('filter', 'my_discussions')" variant="{{ $filter === 'my_discussions' ? 'primary' : 'ghost' }}" size="sm">My posts</flux:button>
            </div>
        </div>

        <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-700">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Category</p>
            <div class="flex flex-wrap gap-2">
                @foreach(['all' => 'All', 'question' => 'Questions', 'help' => 'Help', 'project' => 'Projects', 'feedback' => 'Feedback', 'general' => 'General', 'announcement' => 'Announcements'] as $value => $label)
                    <flux:button
                        wire:click="$set('categoryFilter', '{{ $value }}')"
                        variant="{{ ($categoryFilter ?? 'all') === $value ? 'primary' : 'ghost' }}"
                        size="sm"
                    >{{ $label }}</flux:button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="flex flex-wrap gap-4 text-sm text-slate-600 dark:text-slate-400">
        <span><strong class="font-semibold text-slate-900 dark:text-white">{{ $stats['total'] }}</strong> total</span>
        <span><strong class="font-semibold text-slate-900 dark:text-white">{{ $stats['my_discussions'] }}</strong> yours</span>
        <span><strong class="font-semibold text-slate-900 dark:text-white">{{ $stats['recent'] }}</strong> this week</span>
    </div>

    {{-- List --}}
    @if($discussions->count() > 0)
        <div class="divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-200 bg-white dark:divide-slate-700 dark:border-slate-700 dark:bg-slate-800">
            @foreach($discussions as $discussion)
                <a href="{{ route('discussions.show', $discussion) }}" class="block transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/80" wire:navigate>
                    <x-discussion-card :discussion="$discussion" :showSubject="false" :showReactions="false" />
                </a>
            @endforeach
        </div>

        <div>{{ $discussions->links() }}</div>
    @else
        <div class="rounded-lg border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-600 dark:bg-slate-800">
            <p class="text-slate-600 dark:text-slate-400">No discussions match your filters.</p>
            <flux:button href="{{ route('discussions.create', array_filter(['course' => $courseId, 'lesson' => $lessonId])) }}" variant="primary" class="mt-4" wire:navigate>
                Start a post
            </flux:button>
        </div>
    @endif
</div>
