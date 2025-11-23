<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'discussionId' => null,
    'replyId' => null,
    'reactions' => [],
    'userReactions' => [],
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
    'discussionId' => null,
    'replyId' => null,
    'reactions' => [],
    'userReactions' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$reactionTypes = [
    'upvote' => ['emoji' => '👍', 'label' => 'Upvote', 'color' => 'blue'],
    'helpful' => ['emoji' => '💡', 'label' => 'Helpful', 'color' => 'yellow'],
    'love' => ['emoji' => '❤️', 'label' => 'Love', 'color' => 'red'],
    'celebrate' => ['emoji' => '🎉', 'label' => 'Celebrate', 'color' => 'purple'],
];

$counts = collect($reactions)->groupBy('reaction_type')->map->count();
?>

<div class="flex items-center gap-2 flex-wrap">
    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $reactionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $count = $counts->get($type, 0);
            $hasReacted = in_array($type, $userReactions);
        ?>
        <button 
            wire:click="toggleReaction('<?php echo e($type); ?>', <?php echo e($discussionId); ?>, <?php echo e($replyId); ?>)"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-all
                <?php echo e($hasReacted 
                    ? 'bg-' . $config['color'] . '-100 dark:bg-' . $config['color'] . '-900/30 text-' . $config['color'] . '-700 dark:text-' . $config['color'] . '-300 ring-2 ring-' . $config['color'] . '-500' 
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'); ?>">
            <span class="text-base"><?php echo e($config['emoji']); ?></span>
            <!--[if BLOCK]><![endif]--><?php if($count > 0): ?>
                <span class="font-bold"><?php echo e($count); ?></span>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </button>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\Users\User\Downloads\public_html\resources\views/components/reaction-buttons.blade.php ENDPATH**/ ?>