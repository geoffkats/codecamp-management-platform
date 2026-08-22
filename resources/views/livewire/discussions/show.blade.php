<div class="mx-auto max-w-4xl space-y-6 p-6">
    {{-- Back + actions --}}
    <div class="flex items-center justify-between">
        <flux:button href="{{ route('discussions.index') }}" icon="arrow-left" variant="ghost" wire:navigate>
            All discussions
        </flux:button>
        @if($discussion->user_id === auth()->id() || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
            <flux:button href="{{ route('discussions.edit', $discussion) }}" variant="ghost" icon="pencil" wire:navigate>
                Edit
            </flux:button>
        @endif
    </div>

    {{-- Main post --}}
    <article class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
        <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-700">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                @php $category = $discussion->category ?? 'general'; @endphp
                <span class="rounded px-2 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                    {{ \App\Models\Discussion::categoryLabel($category) }}
                </span>
                @if($discussion->is_pinned)
                    <flux:badge color="yellow">Pinned</flux:badge>
                @endif
                @if($discussion->status === 'closed')
                    <flux:badge color="gray">Closed</flux:badge>
                @elseif($discussion->status === 'archived')
                    <flux:badge color="purple">Archived</flux:badge>
                @endif
                @if($discussion->is_locked)
                    <flux:badge color="red">Locked</flux:badge>
                @endif
                @if($discussion->has_best_answer)
                    <flux:badge color="green">Solved</flux:badge>
                @endif
            </div>

            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $discussion->title }}</h1>

            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1a3a8f] text-xs font-bold text-white">
                        {{ substr($discussion->user->name, 0, 1) }}
                    </div>
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $discussion->user->name }}</span>
                </div>
                <span>{{ $discussion->created_at->diffForHumans() }}</span>
                @if($discussion->course)
                    <a href="{{ route('courses.show', $discussion->course) }}" class="text-[#1a3a8f] hover:underline dark:text-blue-300">
                        {{ $discussion->course->title }}
                    </a>
                @endif
                @if($discussion->lesson)
                    <span>{{ $discussion->lesson->title }}</span>
                @endif
            </div>

            @if(auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-slate-700">
                    <flux:button wire:click="togglePin" variant="ghost" size="sm">Pin / unpin</flux:button>
                    <flux:button wire:click="toggleLock" variant="ghost" size="sm">Lock / unlock</flux:button>
                    @if($discussion->status === 'closed' || $discussion->status === 'archived')
                        <flux:button wire:click="reopenDiscussion" variant="ghost" size="sm">Reopen</flux:button>
                    @elseif($discussion->status === 'active')
                        <flux:button wire:click="closeDiscussion" variant="ghost" size="sm">Close</flux:button>
                        <flux:button wire:click="archiveDiscussion" variant="ghost" size="sm">Archive</flux:button>
                    @endif
                </div>
            @endif
        </div>

        <div class="px-6 py-5">
            <div class="prose prose-slate dark:prose-invert max-w-none mb-6">
                <x-discussion-text :text="$discussion->content" />
            </div>

            @if($discussion->scratch_project_id)
                <div class="mb-6">
                    <x-scratch-embed :projectId="$discussion->scratch_project_id" :title="$discussion->title" />
                </div>
            @endif

            @if(!empty($discussion->code_snippets))
                <div class="mb-6 space-y-4">
                    @foreach($discussion->code_snippets as $snippet)
                        <x-code-block
                            :code="$snippet['code']"
                            :language="$snippet['language'] ?? 'python'"
                            :title="$snippet['title'] ?? null"
                        />
                    @endforeach
                </div>
            @endif

            @if(!empty($discussion->attachments) && is_array($discussion->attachments))
                <div class="mb-6">
                    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Attachments</h3>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($discussion->attachments as $image)
                            @php
                                $imagePath = is_string($image) ? $image : (is_array($image) && isset($image['path']) ? $image['path'] : null);
                                $imageUrl = $imagePath ? (str_starts_with($imagePath, 'http') ? $imagePath : asset('storage/' . $imagePath)) : null;
                            @endphp
                            @if($imageUrl)
                                <a href="{{ $imageUrl }}" target="_blank" class="block overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                                    <img src="{{ $imageUrl }}" alt="Attachment" class="h-40 w-full object-cover" loading="lazy" />
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-4">
                <x-reaction-buttons
                    :discussionId="$discussion->id"
                    :reactions="$discussion->reactions ?? []"
                    :userReactions="$discussion->user_reaction_types ?? []"
                />
            </div>

            <div class="flex flex-wrap gap-4 border-t border-slate-200 pt-4 text-sm text-slate-600 dark:border-slate-700 dark:text-slate-400">
                <span>{{ $discussion->views_count }} views</span>
                <span>{{ $totalReplies }} {{ Str::plural('reply', $totalReplies) }}</span>
                @if($discussion->last_reply_at)
                    <span>Last reply {{ $discussion->last_reply_at->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    </article>

    {{-- Replies --}}
    <section class="rounded-lg border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Replies ({{ $totalReplies }})</h2>

        @if($topLevelReplies->count() > 0)
            <div class="mt-6 space-y-4">
                @foreach($topLevelReplies as $reply)
                    @include('livewire.discussions.partials.reply', [
                        'reply' => $reply,
                        'level' => 0,
                        'editingReplyId' => $editingReplyId ?? null,
                        'discussion' => $discussion,
                    ])
                @endforeach
            </div>
            <div class="mt-6">{{ $topLevelReplies->links() }}</div>
        @else
            <p class="mt-6 py-8 text-center text-slate-500 dark:text-slate-400">No replies yet. Be the first to help or add context.</p>
        @endif
    </section>

    {{-- Reply form --}}
    @if($discussion->status === 'closed')
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-6 text-center dark:border-slate-700 dark:bg-slate-800/50">
            <p class="font-semibold text-slate-900 dark:text-white">This discussion is closed</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">New replies are not allowed.</p>
            @if(auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                <flux:button wire:click="reopenDiscussion" class="mt-4" variant="ghost">Reopen</flux:button>
            @endif
        </div>
    @elseif($discussion->status === 'archived')
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-6 text-center dark:border-slate-700 dark:bg-slate-800/50">
            <p class="font-semibold text-slate-900 dark:text-white">This discussion is archived</p>
            @if(auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                <flux:button wire:click="reopenDiscussion" class="mt-4" variant="ghost">Restore</flux:button>
            @endif
        </div>
    @elseif(!$discussion->is_locked || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
        <div id="reply-form" class="rounded-lg border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
            @if($parentReplyId)
                <div class="mb-4 flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm dark:border-blue-900 dark:bg-blue-950/40">
                    <span>Replying to <strong>{{ \App\Models\DiscussionReply::find($parentReplyId)->user->name ?? 'User' }}</strong></span>
                    <flux:button wire:click="cancelReply" variant="ghost" size="sm">Cancel</flux:button>
                </div>
            @endif

            <h3 class="mb-3 text-base font-semibold text-slate-900 dark:text-white">Write a reply</h3>
            <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Stay on topic. Be helpful and respectful.</p>

            <div class="space-y-4">
                <flux:field>
                    <flux:textarea wire:model="replyContent" placeholder="Your answer or follow-up..." rows="5" />
                    <flux:error name="replyContent" />
                </flux:field>

                <div>
                    <flux:label value="Screenshots (optional)" />
                    <x-image-uploader wireModel="replyImages" :maxFiles="5" :maxSize="5120" />
                </div>

                <div class="flex justify-end">
                    <flux:button wire:click="reply" icon="paper-airplane" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="reply">Post reply</span>
                        <span wire:loading wire:target="reply">Posting...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-lg border border-red-200 bg-red-50 p-6 text-center dark:border-red-900 dark:bg-red-950/30">
            <p class="font-semibold text-red-800 dark:text-red-200">This discussion is locked</p>
        </div>
    @endif

    @if (session()->has('message'))
        <div class="fixed bottom-4 right-4 z-50 rounded-lg bg-green-600 px-4 py-3 text-white shadow-lg">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 z-50 rounded-lg bg-red-600 px-4 py-3 text-white shadow-lg">{{ session('error') }}</div>
    @endif
</div>

@push('scripts')
<script>
    Livewire.on('reply-added', () => {
        document.getElementById('reply-form')?.scrollIntoView({ behavior: 'smooth' });
    });
    Livewire.on('scroll-to-reply-form', () => {
        document.getElementById('reply-form')?.scrollIntoView({ behavior: 'smooth' });
    });
</script>
@endpush
