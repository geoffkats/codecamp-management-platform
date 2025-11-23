<?php
    $isOwnReply = $reply->user_id === auth()->id();
    $canEdit = $isOwnReply || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']);
    $canDelete = $isOwnReply || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor']);
    $isEditing = ($editingReplyId ?? null) === $reply->id;
?>

<div class="reply-item <?php echo e($level > 0 ? 'ml-8 border-l-2 border-indigo-200 dark:border-indigo-800 pl-6' : ''); ?>" wire:key="reply-<?php echo e($reply->id); ?>">
    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 <?php echo e($reply->is_solution ? 'border-2 border-green-500 dark:border-green-600' : 'border border-gray-200 dark:border-gray-700'); ?>">
        <!--[if BLOCK]><![endif]--><?php if($reply->is_solution): ?>
            <div class="flex items-center gap-2 mb-3 p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-semibold text-green-700 dark:text-green-300">Marked as Solution</span>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold shadow-lg">
                    <?php echo e(substr($reply->user->name, 0, 1)); ?>

                </div>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($reply->user->name); ?></span>
                        <!--[if BLOCK]><![endif]--><?php if($reply->user->hasRole('teacher') || $reply->user->hasRole('admin')): ?>
                            <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['size' => 'sm','color' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','color' => 'blue']); ?>Staff <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $attributes = $__attributesOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $component = $__componentOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__componentOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($reply->created_at->diffForHumans()); ?></span>
                        <!--[if BLOCK]><![endif]--><?php if($reply->updated_at != $reply->created_at): ?>
                            <span class="text-xs text-gray-400 dark:text-gray-500">(edited)</span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="flex items-center gap-2">
                        <!--[if BLOCK]><![endif]--><?php if($discussion->user_id == auth()->id() || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor'])): ?>
                            <!--[if BLOCK]><![endif]--><?php if(!$reply->is_solution): ?>
                                <button 
                                    wire:click="markAsSolution(<?php echo e($reply->id); ?>)" 
                                    class="text-xs text-gray-500 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors"
                                    title="Mark as solution"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <button 
                            wire:click="likeReply(<?php echo e($reply->id); ?>)" 
                            class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span><?php echo e($reply->likes_count); ?></span>
                        </button>

                        <!--[if BLOCK]><![endif]--><?php if(!$discussion->is_locked || auth()->user()->hasAnyRole(['admin', 'teacher', 'supervisor'])): ?>
                            <button 
                                wire:click="setReplyTo(<?php echo e($reply->id); ?>)" 
                                class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors"
                                title="Reply to this"
                            >
                                Reply
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <!--[if BLOCK]><![endif]--><?php if($canEdit): ?>
                            <button 
                                wire:click="startEdit(<?php echo e($reply->id); ?>)" 
                                class="text-xs text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                            >
                                Edit
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <!--[if BLOCK]><![endif]--><?php if($canDelete): ?>
                            <button 
                                wire:click="deleteReply(<?php echo e($reply->id); ?>)" 
                                wire:confirm="Are you sure you want to delete this reply?"
                                class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors"
                            >
                                Delete
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <!--[if BLOCK]><![endif]--><?php if(isset($editingReplyId) && $editingReplyId === $reply->id): ?>
                    <div class="mb-3">
                        <?php if (isset($component)) { $__componentOriginal0ee30026125d1a66523211147b00e4dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0ee30026125d1a66523211147b00e4dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::textarea','data' => ['wire:model' => 'editContent','rows' => '4','class' => 'mb-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'editContent','rows' => '4','class' => 'mb-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $attributes = $__attributesOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__attributesOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0ee30026125d1a66523211147b00e4dc)): ?>
<?php $component = $__componentOriginal0ee30026125d1a66523211147b00e4dc; ?>
<?php unset($__componentOriginal0ee30026125d1a66523211147b00e4dc); ?>
<?php endif; ?>
                        <div class="flex items-center gap-2">
                            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'updateReply','size' => 'sm','color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'updateReply','size' => 'sm','color' => 'green']); ?>Save <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['wire:click' => 'cancelEdit','size' => 'sm','variant' => 'ghost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'cancelEdit','size' => 'sm','variant' => 'ghost']); ?>Cancel <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 mb-3">
                        <?php echo nl2br(e($reply->content)); ?>

                    </div>

                    
                    <!--[if BLOCK]><![endif]--><?php if(!empty($reply->attachments) && is_array($reply->attachments)): ?>
                        <div class="mb-3">
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $reply->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $imagePath = is_string($image) ? $image : (is_array($image) && isset($image['path']) ? $image['path'] : null);
                                        $imageUrl = $imagePath ? (str_starts_with($imagePath, 'http') ? $imagePath : asset('storage/' . $imagePath)) : null;
                                    ?>
                                    <!--[if BLOCK]><![endif]--><?php if($imageUrl): ?>
                                        <div class="relative group rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 transition-all">
                                            <img 
                                                src="<?php echo e($imageUrl); ?>" 
                                                alt="Reply attachment" 
                                                class="w-full h-32 object-cover cursor-pointer"
                                                loading="lazy"
                                                onclick="window.open('<?php echo e($imageUrl); ?>', '_blank')"
                                            />
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                            </div>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    
                    <div class="mb-3">
                        <?php if (isset($component)) { $__componentOriginal7b8db765393db675d52fd0783d8ffd4f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b8db765393db675d52fd0783d8ffd4f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.reaction-buttons','data' => ['discussionId' => $discussion->id,'replyId' => $reply->id,'reactions' => $reply->reactions ?? [],'userReactions' => $reply->user_reaction_types ?? []]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('reaction-buttons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['discussionId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($discussion->id),'replyId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reply->id),'reactions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reply->reactions ?? []),'userReactions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reply->user_reaction_types ?? [])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b8db765393db675d52fd0783d8ffd4f)): ?>
<?php $attributes = $__attributesOriginal7b8db765393db675d52fd0783d8ffd4f; ?>
<?php unset($__attributesOriginal7b8db765393db675d52fd0783d8ffd4f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b8db765393db675d52fd0783d8ffd4f)): ?>
<?php $component = $__componentOriginal7b8db765393db675d52fd0783d8ffd4f; ?>
<?php unset($__componentOriginal7b8db765393db675d52fd0783d8ffd4f); ?>
<?php endif; ?>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                
                <!--[if BLOCK]><![endif]--><?php if($reply->replies && $reply->replies->count() > 0): ?>
                    <div class="mt-4 space-y-4">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $reply->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nestedReply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('livewire.discussions.partials.reply', [
                                'reply' => $nestedReply, 
                                'level' => $level + 1,
                                'editingReplyId' => $editingReplyId ?? null,
                                'discussion' => $discussion
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    </div>
</div>

<?php /**PATH C:\Users\User\Downloads\public_html\resources\views/livewire/discussions/partials/reply.blade.php ENDPATH**/ ?>