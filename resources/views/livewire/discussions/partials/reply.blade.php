@php
    $isOwnReply = $reply->user_id === auth()->id();
    $canEdit = $isOwnReply || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']);
    $canDelete = $isOwnReply || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']);
    $isEditing = ($editingReplyId ?? null) === $reply->id;
@endphp

<div class="reply-item {{ $level > 0 ? 'ml-8 border-l-2 border-indigo-200 dark:border-indigo-800 pl-6' : '' }}" wire:key="reply-{{ $reply->id }}">
    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 {{ $reply->is_solution ? 'border-2 border-green-500 dark:border-green-600' : 'border border-gray-200 dark:border-gray-700' }}">
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
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold shadow-lg">
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
                        {!! nl2br(e($reply->content)) !!}
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

