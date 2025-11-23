<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'subject' => 'general', // scratch, python, web, video, interactive, general
    'size' => 'md', // sm, md, lg
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
    'subject' => 'general', // scratch, python, web, video, interactive, general
    'size' => 'md', // sm, md, lg
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$subjects = [
    'scratch' => ['icon' => '🟦', 'bg' => 'bg-orange-100 dark:bg-orange-900/30', 'text' => 'text-orange-600 dark:text-orange-400', 'name' => 'Scratch'],
    'python' => ['icon' => '🐍', 'bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-600 dark:text-blue-400', 'name' => 'Python'],
    'web' => ['icon' => '🌐', 'bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-600 dark:text-green-400', 'name' => 'Web Dev'],
    'video' => ['icon' => '🎥', 'bg' => 'bg-purple-100 dark:bg-purple-900/30', 'text' => 'text-purple-600 dark:text-purple-400', 'name' => 'Video'],
    'interactive' => ['icon' => '⚡', 'bg' => 'bg-yellow-100 dark:bg-yellow-900/30', 'text' => 'text-yellow-600 dark:text-yellow-400', 'name' => 'Interactive'],
    'general' => ['icon' => '📚', 'bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-600 dark:text-gray-400', 'name' => 'Lesson'],
];

$sizes = [
    'sm' => ['container' => 'w-10 h-10', 'icon' => 'text-xl'],
    'md' => ['container' => 'w-16 h-16', 'icon' => 'text-3xl'],
    'lg' => ['container' => 'w-24 h-24', 'icon' => 'text-5xl'],
];

$subjectData = $subjects[$subject] ?? $subjects['general'];
$sizeClasses = $sizes[$size] ?? $sizes['md'];
?>

<div <?php echo e($attributes->merge(['class' => 'flex items-center justify-center rounded-xl shadow-md ' . $sizeClasses['container'] . ' ' . $subjectData['bg']])); ?>>
    <span class="<?php echo e($sizeClasses['icon']); ?>"><?php echo e($subjectData['icon']); ?></span>
</div>
<?php /**PATH C:\Users\User\Downloads\public_html\resources\views/components/subject-icon.blade.php ENDPATH**/ ?>