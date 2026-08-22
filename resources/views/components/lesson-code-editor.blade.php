@props([
    'type' => 'single',
    'language' => 'python',
    'code' => '',
    'title' => null,
    'html' => null,
    'css' => null,
    'javascript' => null,
])

<x-lazy-section
    placeholder-title="Loading editor..."
    placeholder-tone="emerald"
    class="mt-6"
>
    @if($type === 'web')
        <x-web-editor
            :html="$html"
            :css="$css"
            :javascript="$javascript"
            :title="$title ?? 'Web Development Playground'"
        />
    @else
        <x-code-editor
            :language="$language"
            :code="$code"
            :title="$title"
        />
    @endif
</x-lazy-section>
