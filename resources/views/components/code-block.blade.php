@props([
    'code' => '',
    'language' => 'python',
    'showLineNumbers' => true,
    'title' => null,
])

@php
$languages = [
    'python' => ['name' => 'Python', 'icon' => '🐍', 'color' => 'blue'],
    'javascript' => ['name' => 'JavaScript', 'icon' => '⚡', 'color' => 'yellow'],
    'html' => ['name' => 'HTML', 'icon' => '🌐', 'color' => 'orange'],
    'css' => ['name' => 'CSS', 'icon' => '🎨', 'color' => 'purple'],
    'scratch' => ['name' => 'Scratch', 'icon' => '🟦', 'color' => 'orange'],
];

$lang = $languages[$language] ?? $languages['python'];
$codeId = 'code-' . uniqid();
@endphp

<div class="code-block-container bg-gray-900 rounded-xl shadow-lg overflow-hidden my-4">
    {{-- Header --}}
    <div class="bg-gray-800 px-4 py-2 flex items-center justify-between border-b border-gray-700">
        <div class="flex items-center gap-2 text-gray-300">
            <span class="text-lg">{{ $lang['icon'] }}</span>
            <span class="text-sm font-semibold">{{ $title ?? $lang['name'] }}</span>
        </div>
        <button 
            onclick="copyCode{{ $codeId }}()"
            class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-sm font-medium transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span id="copy-text-{{ $codeId }}">Copy</span>
        </button>
    </div>

    {{-- Code Content --}}
    <div class="relative">
        @if($showLineNumbers)
            <div class="flex">
                <div class="select-none text-gray-500 text-right pr-4 pl-4 py-4 bg-gray-800/50 font-mono text-sm leading-relaxed">
                    @foreach(explode("\n", $code) as $index => $line)
                        <div>{{ $index + 1 }}</div>
                    @endforeach
                </div>
                <pre class="flex-1 p-4 overflow-x-auto"><code id="{{ $codeId }}" class="language-{{ $language }} text-sm leading-relaxed">{{ $code }}</code></pre>
            </div>
        @else
            <pre class="p-4 overflow-x-auto"><code id="{{ $codeId }}" class="language-{{ $language }} text-sm leading-relaxed">{{ $code }}</code></pre>
        @endif
    </div>
</div>

<script>
function copyCode{{ $codeId }}() {
    const code = document.getElementById('{{ $codeId }}').textContent;
    const copyText = document.getElementById('copy-text-{{ $codeId }}');
    
    navigator.clipboard.writeText(code).then(() => {
        copyText.textContent = 'Copied!';
        setTimeout(() => {
            copyText.textContent = 'Copy';
        }, 2000);
    });
}
</script>

<style>
/* Basic syntax highlighting */
.language-python .keyword { color: #ff79c6; }
.language-python .string { color: #f1fa8c; }
.language-python .comment { color: #6272a4; font-style: italic; }
.language-python .function { color: #50fa7b; }

.language-javascript .keyword { color: #ff79c6; }
.language-javascript .string { color: #f1fa8c; }
.language-javascript .comment { color: #6272a4; font-style: italic; }

code {
    color: #f8f8f2;
    font-family: 'Courier New', Courier, monospace;
}
</style>
