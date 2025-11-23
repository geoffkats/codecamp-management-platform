@props([
    'subject' => 'general', // scratch, python, web, video, interactive, general
    'size' => 'md', // sm, md, lg
])

@php
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
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-center rounded-xl shadow-md ' . $sizeClasses['container'] . ' ' . $subjectData['bg']]) }}>
    <span class="{{ $sizeClasses['icon'] }}">{{ $subjectData['icon'] }}</span>
</div>
