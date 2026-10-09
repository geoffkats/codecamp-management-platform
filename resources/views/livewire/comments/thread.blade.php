<section class="rounded-2xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 print:hidden">
    <header class="flex items-center gap-2 border-b border-gray-100 px-5 py-4 dark:border-zinc-800">
        <flux:icon.chat-bubble-left-right class="size-5 text-orange-500" />
        <h2 class="text-sm font-bold text-gray-900 dark:text-white">{{ $title }}</h2>
        <span class="rounded-full bg-gray-100 px-2 text-xs font-medium text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $comments->count() }}</span>
    </header>

    @if($comments->isNotEmpty())
        <ul class="divide-y divide-gray-100 dark:divide-zinc-800">
            @foreach($comments as $comment)
                @php $isMine = (int) $comment->user_id === (int) auth()->id(); @endphp
                <li wire:key="comment-{{ $comment->id }}" class="group flex gap-3 px-5 py-4">
                    <x-user-avatar :user="$comment->user" size="xs" rounded="full" class="mt-0.5" />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline gap-x-2">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $comment->user?->name ?? 'Removed user' }}</span>
                            @if((int) $comment->user_id === (int) $commentable->commentOwner()?->id)
                                <span class="rounded bg-orange-50 px-1.5 text-[11px] font-medium text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">Author</span>
                            @endif
                            <span class="text-xs text-gray-400" title="{{ $comment->created_at->format('d M Y H:i') }}">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-gray-700 dark:text-zinc-300">{{ $comment->body }}</p>
                    </div>
                    @if($isMine || auth()->user()->isAdmin())
                        <button type="button" wire:click="delete({{ $comment->id }})" wire:confirm="Delete this comment?"
                                class="self-start text-gray-300 opacity-0 transition hover:text-red-500 group-hover:opacity-100 dark:text-zinc-600"
                                aria-label="Delete comment">
                            <flux:icon.trash class="size-4" />
                        </button>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <p class="px-5 py-4 text-sm text-gray-500 dark:text-zinc-400">No comments yet. Anyone reviewing this can leave one, and the author is notified.</p>
    @endif

    <form wire:submit="post" class="border-t border-gray-100 p-4 dark:border-zinc-800">
        <textarea wire:model="body" rows="3" placeholder="{{ $placeholder }}"
                  class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm focus:border-orange-400 focus:ring-orange-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"></textarea>
        @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <div class="mt-2 flex justify-end">
            <button type="submit" wire:loading.attr="disabled" wire:target="post"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600 disabled:opacity-60">
                <flux:icon.paper-airplane class="size-4" /> Post comment
            </button>
        </div>
    </form>
</section>
