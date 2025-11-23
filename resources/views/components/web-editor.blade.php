@props([
    'html' => '<!DOCTYPE html>\n<html>\n<head>\n  <title>My Page</title>\n</head>\n<body>\n  <h1>Hello World!</h1>\n</body>\n</html>',
    'css' => 'body {\n  font-family: Arial, sans-serif;\n  padding: 20px;\n}\n\nh1 {\n  color: #4F46E5;\n}',
    'javascript' => '// Your JavaScript code here\nconsole.log("Hello from JavaScript!");',
    'editable' => true,
    'title' => 'Web Development Editor',
])

@php
$editorId = 'web-editor-' . uniqid();
$previewId = 'preview-' . uniqid();
@endphp

<div x-data="{ activeTab: 'html', layout: 'horizontal' }" class="web-editor-container bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-green-500 to-blue-500 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2 text-white">
            <span class="text-2xl">🌐</span>
            <span class="font-bold">{{ $title }}</span>
        </div>
        <div class="flex items-center gap-2">
            {{-- Layout Toggle --}}
            <button 
                @click="layout = layout === 'horizontal' ? 'vertical' : 'horizontal'"
                class="px-3 py-1 bg-white/20 hover:bg-white/30 text-white rounded-lg text-sm font-semibold transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span x-text="layout === 'horizontal' ? 'Vertical' : 'Horizontal'"></span>
            </button>
            
            {{-- Run Button --}}
            <button 
                onclick="runWebCode{{ $editorId }}()"
                class="px-4 py-1 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition-colors flex items-center gap-2 shadow-lg">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                </svg>
                Run
            </button>
        </div>
    </div>

    {{-- Editor and Preview Container --}}
    <div class="flex" :class="layout === 'horizontal' ? 'flex-row' : 'flex-col'">
        {{-- Code Editors --}}
        <div class="flex-1 border-r border-gray-200 dark:border-gray-700" :class="layout === 'vertical' ? 'border-r-0 border-b' : ''">
            {{-- Tabs --}}
            <div class="flex border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <button 
                    @click="activeTab = 'html'"
                    :class="activeTab === 'html' ? 'bg-white dark:bg-gray-800 border-b-2 border-orange-500' : 'hover:bg-gray-100 dark:hover:bg-gray-800'"
                    class="px-4 py-2 text-sm font-semibold transition-colors flex items-center gap-2">
                    <span class="text-orange-500">📄</span>
                    HTML
                </button>
                <button 
                    @click="activeTab = 'css'"
                    :class="activeTab === 'css' ? 'bg-white dark:bg-gray-800 border-b-2 border-blue-500' : 'hover:bg-gray-100 dark:hover:bg-gray-800'"
                    class="px-4 py-2 text-sm font-semibold transition-colors flex items-center gap-2">
                    <span class="text-blue-500">🎨</span>
                    CSS
                </button>
                <button 
                    @click="activeTab = 'javascript'"
                    :class="activeTab === 'javascript' ? 'bg-white dark:bg-gray-800 border-b-2 border-yellow-500' : 'hover:bg-gray-100 dark:hover:bg-gray-800'"
                    class="px-4 py-2 text-sm font-semibold transition-colors flex items-center gap-2">
                    <span class="text-yellow-500">⚡</span>
                    JavaScript
                </button>
            </div>

            {{-- HTML Editor --}}
            <div x-show="activeTab === 'html'" class="relative">
                <textarea 
                    id="{{ $editorId }}-html"
                    class="w-full p-4 font-mono text-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-0 focus:ring-0 resize-none"
                    style="height: 400px; tab-size: 2;"
                    @if(!$editable) readonly @endif
                    spellcheck="false"
                >{{ $html }}</textarea>
            </div>

            {{-- CSS Editor --}}
            <div x-show="activeTab === 'css'" class="relative">
                <textarea 
                    id="{{ $editorId }}-css"
                    class="w-full p-4 font-mono text-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-0 focus:ring-0 resize-none"
                    style="height: 400px; tab-size: 2;"
                    @if(!$editable) readonly @endif
                    spellcheck="false"
                >{{ $css }}</textarea>
            </div>

            {{-- JavaScript Editor --}}
            <div x-show="activeTab === 'javascript'" class="relative">
                <textarea 
                    id="{{ $editorId }}-javascript"
                    class="w-full p-4 font-mono text-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-0 focus:ring-0 resize-none"
                    style="height: 400px; tab-size: 2;"
                    @if(!$editable) readonly @endif
                    spellcheck="false"
                >{{ $javascript }}</textarea>
            </div>
        </div>

        {{-- Live Preview --}}
        <div class="flex-1 bg-white">
            <div class="bg-gray-100 dark:bg-gray-900 px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Live Preview</span>
                <button 
                    onclick="refreshPreview{{ $editorId }}()"
                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
            </div>
            <iframe 
                id="{{ $previewId }}"
                class="w-full bg-white"
                style="height: 400px; border: none;"
                sandbox="allow-scripts allow-same-origin"
            ></iframe>
        </div>
    </div>
</div>

<script>
    // Auto-run on load
    document.addEventListener('DOMContentLoaded', function() {
        runWebCode{{ $editorId }}();
    });
    
    // Run web code
    function runWebCode{{ $editorId }}() {
        const html = document.getElementById('{{ $editorId }}-html').value;
        const css = document.getElementById('{{ $editorId }}-css').value;
        const js = document.getElementById('{{ $editorId }}-javascript').value;
        
        const preview = document.getElementById('{{ $previewId }}');
        
        // Combine HTML, CSS, and JS
        const fullCode = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>${css}</style>
            </head>
            <body>
                ${html}
                <script>${js}<\/script>
            </body>
            </html>
        `;
        
        // Update preview
        preview.srcdoc = fullCode;
    }
    
    // Refresh preview
    function refreshPreview{{ $editorId }}() {
        runWebCode{{ $editorId }}();
    }
    
    // Auto-update on typing (debounced)
    let timeout{{ $editorId }};
    ['{{ $editorId }}-html', '{{ $editorId }}-css', '{{ $editorId }}-javascript'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            clearTimeout(timeout{{ $editorId }});
            timeout{{ $editorId }} = setTimeout(() => runWebCode{{ $editorId }}(), 1000);
        });
    });
</script>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @endpush
@endonce
