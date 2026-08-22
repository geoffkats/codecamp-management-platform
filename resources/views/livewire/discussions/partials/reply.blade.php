@php
    $isOwnReply = $reply->user_id === auth()->id();
    $canEdit = $isOwnReply || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']);
    $canDelete = $isOwnReply || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']);
    $isEditing = ($editingReplyId ?? null) === $reply->id;
@endphp

<div class="reply-item {{ $level > 0 ? 'ml-6 border-l-2 border-slate-200 pl-4 dark:border-slate-600' : '' }}" wire:key="reply-{{ $reply->id }}">
    <div class="rounded-lg border p-4 {{ $reply->is_solution ? 'border-green-500 bg-green-50/50 dark:border-green-700 dark:bg-green-950/20' : 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50' }}">
        @if($reply->is_solution)
            <div class="flex items-center gap-2 mb-3 p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-semibold text-green-700 dark:text-green-300">Marked as Solution</span>
            </div>
        @endif

        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#1a3a8f] text-sm font-bold text-white">
                    {{ substr($reply->user->name, 0, 1) }}
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                        @if($reply->user->hasRole('teacher') || $reply->user->hasRole('admin'))
                            <flux:badge size="sm" color="blue">Staff</flux:badge>
                        @endif
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                        @if($reply->updated_at != $reply->created_at)
                            <span class="text-xs text-gray-400 dark:text-gray-500">(edited)</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @if($discussion->user_id == auth()->id() || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                            @if(!$reply->is_solution)
                                <button 
                                    wire:click="markAsSolution({{ $reply->id }})" 
                                    class="text-xs text-gray-500 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors"
                                    title="Mark as solution"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            @endif
                        @endif

                        <button 
                            wire:click="likeReply({{ $reply->id }})" 
                            class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span>{{ $reply->likes_count }}</span>
                        </button>

                        @if(!$discussion->is_locked || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']))
                            <button 
                                wire:click="setReplyTo({{ $reply->id }})" 
                                class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors"
                                title="Reply to this"
                            >
                                Reply
                            </button>
                        @endif

                        @if($canEdit)
                            <button 
                                wire:click="startEdit({{ $reply->id }})" 
                                class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                            >
                                Edit
                            </button>
                        @endif

                        @if($canDelete)
                            <button 
                                wire:click="deleteReply({{ $reply->id }})" 
                                wire:confirm="Are you sure you want to delete this reply?"
                                class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors"
                            >
                                Delete
                            </button>
                        @endif
                    </div>
                </div>

                @if(isset($editingReplyId) && $editingReplyId === $reply->id)
                    <div class="mb-3">
                        <flux:textarea wire:model="editContent" rows="4" class="mb-2" />
                        <div class="flex items-center gap-2">
                            <flux:button wire:click="updateReply" size="sm" color="green">Save</flux:button>
                            <flux:button wire:click="cancelEdit" size="sm" variant="ghost">Cancel</flux:button>
                        </div>
                    </div>
                @else
                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 mb-3">
                        <x-discussion-text :text="$reply->content" />
                    </div>

                    {{-- Attached Images --}}
                    @if(!empty($reply->attachments) && is_array($reply->attachments))
                        <div class="mb-3">
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach($reply->attachments as $image)
                                    @php
                                        $imagePath = is_string($image) ? $image : (is_array($image) && isset($image['path']) ? $image['path'] : null);
                                        $imageUrl = $imagePath ? (str_starts_with($imagePath, 'http') ? $imagePath : asset('storage/' . $imagePath)) : null;
                                    @endphp
                                    @if($imageUrl)
                                        <div class="relative group rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 transition-all">
                                            <img 
                                                src="{{ $imageUrl }}" 
                                                alt="Reply attachment" 
                                                class="w-full h-32 object-cover cursor-pointer"
                                                loading="lazy"
                                                onclick="window.open('{{ $imageUrl }}', '_blank')"
                                            />
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Reaction Buttons for Reply --}}
                    <div class="mb-3">
                        <x-reaction-buttons 
                            :discussionId="$discussion->id"
                            :replyId="$reply->id"
                            :reactions="$reply->reactions ?? []"
                            :userReactions="$reply->user_reaction_types ?? []"
                        />
                    </div>
                @endif

                {{-- Nested Replies --}}
                @if($reply->replies && $reply->replies->count() > 0)
                    <div class="mt-4 space-y-4">
                        @foreach($reply->replies as $nestedReply)
                            @include('livewire.discussions.partials.reply', [
                                'reply' => $nestedReply, 
                                'level' => $level + 1,
                                'editingReplyId' => $editingReplyId ?? null,
                                'discussion' => $discussion
                            ])
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

