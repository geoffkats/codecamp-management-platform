<div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Lesson discussion</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Ask questions and help classmates on this lesson.</p>
        </div>
        @can('enroll_courses')
            <a href="{{ route('discussions.create', $createParams) }}" wire:navigate
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New post
            </a>
        @endcan
    </div>

    @include('livewire.discussions.partials.guidelines')

    @if($discussions->count() > 0)
        <div class="divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-200 bg-white dark:divide-slate-700 dark:border-slate-700 dark:bg-slate-800">
            @foreach($discussions as $discussion)
                <a href="{{ route('discussions.show', $discussion) }}" wire:navigate
                   class="block transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/80">
                    <x-discussion-card :discussion="$discussion" :showSubject="false" :showReactions="false" :compact="true" />
                </a>
            @endforeach
        </div>

        <div>{{ $discussions->links() }}</div>

        @if($compact && $discussions->total() > $discussions->count())
            <div class="text-center">
                <a href="{{ route('discussions.index', $createParams) }}" wire:navigate
                   class="text-sm font-semibold text-orange-600 hover:text-orange-700 dark:text-orange-400">
                    View all {{ $discussions->total() }} posts →
                </a>
            </div>
        @endif
    @else
        <div class="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-600 dark:bg-slate-800">
            <p class="text-slate-600 dark:text-slate-400">No posts for this lesson yet. Start the conversation.</p>
            @can('enroll_courses')
                <a href="{{ route('discussions.create', $createParams) }}" wire:navigate
                   class="mt-4 inline-flex items-center justify-center rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">
                    Ask a question
                </a>
            @endcan
        </div>
    @endif
</div>
