@props([
    'content' => '',
    'ignore' => false,
])

@if(filled($content))
    <div @if($ignore) wire:ignore @endif {{ $attributes->merge(['class' => 'prose prose-sm sm:prose-base dark:prose-invert max-w-none break-words']) }}>
        {!! \App\Support\RichContent::render($content) !!}
    </div>
@endif
