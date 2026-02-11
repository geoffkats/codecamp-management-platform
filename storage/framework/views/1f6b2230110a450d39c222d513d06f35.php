<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<?php
    $appName = cache()->remember('app_name', 86400, fn() => \App\Models\SystemSetting::get('app_name', config('app.name')));
    $favicon = cache()->remember('favicon_path', 86400, fn() => \App\Models\SystemSetting::get('favicon'));
?>

<title><?php echo e($title ?? $appName); ?></title>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($favicon): ?>
    <link rel="icon" href="<?php echo e(asset('storage/' . $favicon)); ?>" type="image/x-icon">
<?php else: ?>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<link rel="apple-touch-icon" href="/apple-touch-icon.png">


<style>
    body{margin:0;min-height:100vh}
    .dark{color-scheme:dark}
</style>


<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link rel="dns-prefetch" href="https://code.jquery.com">
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">


<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />


<?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
<?php echo app('flux')->fluxAppearance(); ?>



<?php echo $__env->yieldPushContent('editor-scripts'); ?>

<?php echo $__env->yieldPushContent('styles'); ?>
<?php /**PATH C:\wamp64\www\codecamp-system\resources\views/partials/head.blade.php ENDPATH**/ ?>