<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'discussion',
    'showSubject' => true,
    'showReactions' => true,
    'compact' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'discussion',
    'showSubject' => true,
    'showReactions' => true,
    'compact' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all duration-300 overflow-hidden">
    <div class="p-6">
        <div class="flex items-start gap-4">
            
            <!--[if BLOCK]><![endif]--><?php if($showSubject && $discussion->subject_tag): ?>
                <?php if (isset($component)) { $__componentOriginal859850f09dad0cc259392b89fc4cb4d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal859850f09dad0cc259392b89fc4cb4d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.subject-icon','data' => ['subject' => $discussion->subject_tag,'size' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('subject-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['subject' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($discussion->subject_tag),'size' => 'md']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal859850f09dad0cc259392b89fc4cb4d5)): ?>
<?php $attributes = $__attributesOriginal859850f09dad0cc259392b89fc4cb4d5; ?>
<?php unset($__attributesOriginal859850f09dad0cc259392b89fc4cb4d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal859850f09dad0cc259392b89fc4cb4d5)): ?>
<?php $component = $__componentOriginal859850f09dad0cc259392b89fc4cb4d5; ?>
<?php unset($__componentOriginal859850f09dad0cc259392b89fc4cb4d5); ?>
<?php endif; ?>
            <?php else: ?>
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                    <?php echo e(substr($discussion->user->name, 0, 1)); ?>

                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <div class="flex-1 min-w-0">
                
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                <?php echo e($discussion->title); ?>

                            </h3>
                            
                            
                            <!--[if BLOCK]><![endif]--><?php if($discussion->is_pinned): ?>
                                <span class="inline-flex items-center px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 rounded-full text-xs font-semibold">
                                    📌 Pinned
                                </span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            
                            <!--[if BLOCK]><![endif]--><?php if($discussion->has_best_answer): ?>
                                <span class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-full text-xs font-semibold">
                                    ✓ Solved
                                </span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            
                            <!--[if BLOCK]><![endif]--><?php if($discussion->lesson): ?>
                                <span class="inline-flex items-center px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded-full text-xs font-semibold">
                                    📚 <?php echo e(Str::limit($discussion->lesson->title, 30)); ?>

                                </span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        
                        
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 flex-wrap">
                            <span class="font-medium"><?php echo e($discussion->user->name); ?></span>
                            <span>•</span>
                            <span><?php echo e($discussion->created_at->diffForHumans()); ?></span>
                            
                            <!--[if BLOCK]><![endif]--><?php if($discussion->course): ?>
                                <span>•</span>
                                <span><?php echo e($discussion->course->title); ?></span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>

                
                <!--[if BLOCK]><![endif]--><?php if(!$compact && $discussion->content): ?>
                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                        <?php echo e(Str::limit(strip_tags($discussion->content), 120)); ?>

                    </p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                
                <!--[if BLOCK]><![endif]--><?php if($discussion->scratch_project_id && !$compact): ?>
                    <div class="my-3 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                        <div class="flex items-center gap-2 text-sm text-orange-800 dark:text-orange-200">
                            <span class="text-lg">🟦</span>
                            <span class="font-semibold">Scratch Project Attached</span>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                
                <!--[if BLOCK]><![endif]--><?php if(!empty($discussion->code_snippets) && !$compact): ?>
                    <div class="my-3 p-3 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <span class="text-lg">💻</span>
                            <span class="font-semibold">Code Snippet Included</span>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                
                <!--[if BLOCK]><![endif]--><?php if($showReactions && $discussion->upvotes > 0): ?>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
                            <span class="text-base">👍</span>
                            <span class="font-semibold"><?php echo e($discussion->upvotes); ?></span>
                        </span>
                        <!--[if BLOCK]><![endif]--><?php if($discussion->helpful_count > 0): ?>
                            <span class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
                                <span class="text-base">💡</span>
                                <span class="font-semibold"><?php echo e($discussion->helpful_count); ?></span>
                            </span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                
                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span class="font-medium"><?php echo e($discussion->replies_count ?? 0); ?></span>
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span class="font-medium"><?php echo e($discussion->views_count ?? 0); ?></span>
                    </span>
                </div>
            </div>

            
            <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\User\Downloads\public_html\resources\views/components/discussion-card.blade.php ENDPATH**/ ?>