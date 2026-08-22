@props([
    'path' => null,
    'alt' => 'Image',
    'class' => 'max-w-full rounded-lg border border-gray-200 dark:border-gray-700',
])

@php
    $url = \App\Support\RichContent::storageUrl($path);
@endphp

@if($url)
    <img src="{{ $url }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => $class]) }} loading="lazy">
@endif
