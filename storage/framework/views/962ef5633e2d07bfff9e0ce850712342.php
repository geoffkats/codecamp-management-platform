

<?php
$classes = Flux::classes()
    ->add('rounded-xl border bg-white dark:bg-zinc-900 dark:border-zinc-800 shadow-xs')
    ;
?>

<div <?php echo e($attributes->class($classes)); ?> data-flux-card>
    <?php echo e($slot); ?>

</div>

<?php /**PATH C:\Users\User\Downloads\public_html\resources\views/flux/card.blade.php ENDPATH**/ ?>