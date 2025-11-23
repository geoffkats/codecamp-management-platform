<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900 p-6">
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            @if($discussion->is_pinned)
                                <flux:badge color="yellow">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                    </svg>
                                    Pinned
                                </flux:badge>
                            @endif
                            @if($discussion->status === 'closed')
                                <flux:badge color="gray">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Closed
                                </flux:badge>
                            @elseif($discussion->status === 'archived')
                                <flux:badge color="purple">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                    Archived
                                </flux:badge>
                            @endif
                            @if($discussion->is_locked)
                                <flux:badge color="red">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Locked
                                </flux:badge>
                            @endif
                        </div>
                        <h1 class="text-3xl font-bold mb-2">{{ $discussion->title }}</h1>
                        <div class="flex items-center gap-4 text-blue-100 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-sm font-bold">
                                    {{ substr($discussion->user->name, 0, 1) }}
                                </div>
                                <span>{{ $discussion->user->name }}</span>
                            </div>
                            <span>•</span>
                            <span>{{ $discussion->created_at->diffForHumans() }}</span>
                            @if($discussion->course)
                                <span>•</span>
                                <a href="{{ route('courses.show', $discussion->course) }}" class="hover:underline">
                                    {{ $discussion->course->title }}
                                </a>
                            @endif
                        </div>
                    </div>
                    @if(auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                        <div class="flex items-center gap-2">
                            <flux:button wire:click="togglePin" variant="ghost" size="sm" class="bg-white/20 hover:bg-white/30 text-white border-white/30" title="Pin/Unpin">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </flux:button>
                            <flux:button wire:click="toggleLock" variant="ghost" size="sm" class="bg-white/20 hover:bg-white/30 text-white border-white/30" title="Lock/Unlock">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </flux:button>
                            @if($discussion->status === 'closed')
                                <flux:button wire:click="reopenDiscussion" variant="ghost" size="sm" class="bg-green-500/20 hover:bg-green-500/30 text-white border-green-300/30" title="Reopen Discussion">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </flux:button>
                            @elseif($discussion->status === 'active')
                                <flux:button wire:click="closeDiscussion" variant="ghost" size="sm" class="bg-orange-500/20 hover:bg-orange-500/30 text-white border-orange-300/30" title="Close Discussion">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </flux:button>
                                <flux:button wire:click="archiveDiscussion" variant="ghost" size="sm" class="bg-purple-500/20 hover:bg-purple-500/30 text-white border-purple-300/30" title="Archive Discussion">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                </flux:button>
                            @elseif($discussion->status === 'archived')
                                <flux:button wire:click="reopenDiscussion" variant="ghost" size="sm" class="bg-green-500/20 hover:bg-green-500/30 text-white border-green-300/30" title="Restore from Archive">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </flux:button>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>
            
            <div class="p-6">
                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-2 mb-4">
                    @if($discussion->user_id === auth()->id() || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                        <flux:button href="{{ route('discussions.edit', $discussion) }}" variant="ghost" icon="pencil" wire:navigate>
                            Edit
                        </flux:button>
                    @endif
                </div>
                
                <div class="prose dark:prose-invert max-w-none mb-6">
                    {!! nl2br(e($discussion->content)) !!}
                </div>

                {{-- Scratch Project Embed --}}
                @if($discussion->scratch_project_id)
                    <div class="mb-6">
                        <x-scratch-embed 
                            :projectId="$discussion->scratch_project_id"
                            :title="$discussion->title"
                        />
                    </div>
                @endif

                {{-- Code Snippets --}}
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

                {{-- Attached Images --}}
                @if(!empty($discussion->attachments) && is_array($discussion->attachments))
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Attached Images
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($discussion->attachments as $image)
                                @php
                                    $imagePath = is_string($image) ? $image : (is_array($image) && isset($image['path']) ? $image['path'] : null);
                                    $imageUrl = $imagePath ? (str_starts_with($imagePath, 'http') ? $imagePath : asset('storage/' . $imagePath)) : null;
                                @endphp
                                @if($imageUrl)
                                    <div class="relative group rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 transition-all shadow-md hover:shadow-xl">
                                        <img 
                                            src="{{ $imageUrl }}" 
                                            alt="Discussion attachment" 
                                            class="w-full h-48 object-cover"
                                            loading="lazy"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center p-4">
                                            <a 
                                                href="{{ $imageUrl }}" 
                                                target="_blank"
                                                class="px-4 py-2 bg-white text-gray-900 rounded-lg font-semibold hover:bg-indigo-500 hover:text-white transition-colors shadow-lg flex items-center gap-2"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                                View Full Size
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Reaction Buttons --}}
                <div class="mb-6">
                    <x-reaction-buttons 
                        :discussionId="$discussion->id"
                        :reactions="$discussion->reactions ?? []"
                        :userReactions="$discussion->user_reaction_types ?? []"
                    />
                </div>
                
                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ $discussion->views_count }} views
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        {{ $totalReplies }} {{ Str::plural('reply', $totalReplies) }}
                    </div>
                    @if($discussion->last_reply_at)
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Last reply {{ $discussion->last_reply_at->diffForHumans() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Replies Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Replies ({{ $totalReplies }})
            </h2>

            @if($topLevelReplies->count() > 0)
                <div class="space-y-6 mb-6">
                    @foreach($topLevelReplies as $reply)
                        @include('livewire.discussions.partials.reply', [
                            'reply' => $reply, 
                            'level' => 0,
                            'editingReplyId' => $editingReplyId ?? null,
                            'discussion' => $discussion
                        ])
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $topLevelReplies->links() }}
                </div>
            @else
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="text-lg font-semibold mb-2">No replies yet</p>
                    <p>Be the first to reply to this discussion!</p>
                </div>
            @endif
        </div>

        {{-- Reply Form --}}
        @if($discussion->status === 'closed')
            <div class="bg-gray-100 dark:bg-gray-800/50 rounded-xl border-2 border-gray-300 dark:border-gray-700 p-8 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Discussion Closed</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        This discussion has been closed by a staff member. No new replies can be added.
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">
                        You can still view all existing replies and continue reading the conversation.
                    </p>
                    @if(auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                        <div class="mt-6">
                            <flux:button wire:click="reopenDiscussion" icon="arrow-path" variant="ghost">
                                Reopen Discussion
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($discussion->status === 'archived')
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl border-2 border-purple-300 dark:border-purple-700 p-8 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-purple-200 dark:bg-purple-800 flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Discussion Archived</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        This discussion has been archived and is no longer active. No new replies can be added.
                    </p>
                    @if(auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                        <div class="mt-6">
                            <flux:button wire:click="reopenDiscussion" icon="arrow-path" variant="ghost">
                                Restore Discussion
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        @elseif(!$discussion->is_locked || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
            <div id="reply-form" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                @if($parentReplyId)
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            <span class="text-sm text-blue-700 dark:text-blue-300">Replying to: <strong>{{ \App\Models\DiscussionReply::find($parentReplyId)->user->name ?? 'User' }}</strong></span>
                        </div>
                        <flux:button wire:click="cancelReply" variant="ghost" size="sm" color="red">Cancel</flux:button>
                    </div>
                @endif

                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Your Reply</h3>
                <div class="space-y-4">
                    <flux:field>
                        <flux:textarea 
                            wire:model="replyContent" 
                            placeholder="Write your reply here..."
                            rows="6"
                            class="font-mono text-sm"
                        />
                        <flux:error name="replyContent" />
                    </flux:field>

                    {{-- Image Upload for Reply --}}
                    <div>
                        <flux:label value="Attach Screenshots (Optional)" class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Add Images
                        </flux:label>
                        <x-image-uploader 
                            wireModel="replyImages"
                            :maxFiles="5"
                            :maxSize="5120"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">📸 Share screenshots, error messages, or your work (max 5 images, 5MB each)</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            💡 Markdown supported. Use **bold**, *italic*, `code`, and more.
                        </p>
                        <flux:button wire:click="reply" icon="paper-airplane" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="reply">Post Reply</span>
                            <span wire:loading wire:target="reply">Posting...</span>
                        </flux:button>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-6 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <p class="text-red-700 dark:text-red-300 font-semibold">This discussion is locked</p>
                <p class="text-sm text-red-600 dark:text-red-400 mt-1">New replies are not allowed.</p>
            </div>
        @endif

        @if (session()->has('message'))
            <div class="fixed bottom-4 right-4 z-50">
                <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-xl flex items-center gap-3 max-w-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Success</p>
                        <p class="text-sm">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="fixed bottom-4 right-4 z-50">
                <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-xl flex items-center gap-3 max-w-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Error</p>
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
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
