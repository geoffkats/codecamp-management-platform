@props([
    'language' => 'python', // python, javascript, html, css
    'code' => '',
    'editable' => true,
    'showOutput' => true,
    'height' => '400px',
    'title' => null,
])

@php
    $editorId = 'editor-' . uniqid();
    $outputId = 'output-' . uniqid();
    $jsKey = 'ce' . \Illuminate\Support\Str::random(8); // safe for function names

    $languageConfig = [
        'python' => ['name' => 'Python', 'icon' => '🐍', 'color' => 'blue', 'mode' => 'python'],
        'javascript' => ['name' => 'JavaScript', 'icon' => '⚡', 'color' => 'yellow', 'mode' => 'javascript'],
        'html' => ['name' => 'HTML', 'icon' => '🌐', 'color' => 'orange', 'mode' => 'html'],
        'css' => ['name' => 'CSS', 'icon' => '🎨', 'color' => 'purple', 'mode' => 'css'],
    ];

    $config = $languageConfig[$language] ?? $languageConfig['python'];
@endphp

<div class="code-editor-container bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-{{ $config['color'] }}-500 to-{{ $config['color'] }}-600 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2 text-white">
            <span class="text-2xl">{{ $config['icon'] }}</span>
            <span class="font-bold">{{ $title ?? $config['name'] . ' Editor' }}</span>
        </div>
        <div class="flex items-center gap-2">
            @if($editable)
                <button 
                    onclick="resetCode{{ $jsKey }}()"
                    class="px-3 py-1 bg-white/20 hover:bg-white/30 text-white rounded-lg text-sm font-semibold transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </button>
            @endif
            <button 
                onclick="runCode{{ $jsKey }}()"
                class="px-4 py-1 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition-colors flex items-center gap-2 shadow-lg">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                </svg>
                Run Code
            </button>
        </div>
    </div>

    {{-- Editor Area --}}
    <div class="relative">
        <textarea 
            id="{{ $editorId }}"
            class="w-full p-4 font-mono text-sm bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-0 focus:ring-0 resize-none"
            style="height: {{ $height }}; tab-size: 4;"
            @if(!$editable) readonly @endif
            spellcheck="false"
        >{{ $code }}</textarea>
        
        {{-- Line numbers (optional enhancement) --}}
        <div class="absolute top-0 left-0 p-4 pr-2 text-gray-400 dark:text-gray-600 font-mono text-sm select-none pointer-events-none">
            <div id="{{ $editorId }}-lines"></div>
        </div>
    </div>

    {{-- Output Area --}}
    @if($showOutput)
        <div class="border-t border-gray-200 dark:border-gray-700">
            <div class="bg-gray-100 dark:bg-gray-900 px-4 py-2 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Output</span>
                <button 
                    onclick="clearOutput{{ $jsKey }}()"
                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    Clear
                </button>
            </div>
            <div 
                id="{{ $outputId }}"
                class="p-4 font-mono text-sm bg-black text-green-400 min-h-[120px] max-h-[300px] overflow-auto"
            >
                <div class="text-gray-500">Click "Run Code" to see output...</div>
            </div>
        </div>
    @endif
</div>

<script>
    // Store original code for reset
    const originalCode{{ $jsKey }} = `{{ $code }}`;
    
    // Update line numbers
    function updateLineNumbers{{ $jsKey }}() {
        const editor = document.getElementById('{{ $editorId }}');
        const linesDiv = document.getElementById('{{ $editorId }}-lines');
        if (!editor || !linesDiv) return;
        const lineCount = editor.value.split('\n').length;
        linesDiv.innerHTML = Array.from({length: lineCount}, (_, i) => i + 1).join('\n');
    }
    
    // Initialize line numbers
    document.addEventListener('DOMContentLoaded', function() {
        updateLineNumbers{{ $jsKey }}();
        const editor = document.getElementById('{{ $editorId }}');
        if (editor) {
            editor.addEventListener('input', updateLineNumbers{{ $jsKey }});
        }
    });
    
    // Reset code
    function resetCode{{ $jsKey }}() {
        const editor = document.getElementById('{{ $editorId }}');
        if (editor) {
            editor.value = originalCode{{ $jsKey }};
        }
        updateLineNumbers{{ $jsKey }}();
        clearOutput{{ $jsKey }}();
    }
    
    // Clear output
    function clearOutput{{ $jsKey }}() {
        const out = document.getElementById('{{ $outputId }}');
        if (!out) return;
        out.innerHTML = '<div class="text-gray-500">Click "Run Code" to see output...</div>';
    }
    
    // Run code
    function runCode{{ $jsKey }}() {
        const editor = document.getElementById('{{ $editorId }}');
        const output = document.getElementById('{{ $outputId }}');
        if (!editor || !output) return;
        const code = editor.value;
        
        output.innerHTML = '<div class="text-yellow-400">⏳ Running code...</div>';
        
        @if($language === 'python')
            // Python execution (requires backend API)
            fetch('/api/execute/python', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ code: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    output.innerHTML = `<div class="text-green-400">${escapeHtml(data.output || 'Code executed successfully!')}</div>`;
                } else {
                    output.innerHTML = `<div class="text-red-400">❌ Error:\n${escapeHtml(data.error)}</div>`;
                }
            })
            .catch(error => {
                output.innerHTML = `<div class="text-red-400">❌ Error: ${escapeHtml(error.message)}</div>`;
            });
        @elseif($language === 'javascript')
            // JavaScript execution (client-side) with console capture and DOMContentLoaded safety
            (function() {
                const logs = [];
                const originalLog = console.log;
                const originalError = console.error;

                console.log = (...args) => {
                    logs.push(args.map(arg => typeof arg === 'object' ? JSON.stringify(arg, null, 2) : String(arg)).join(' '));
                    originalLog(...args);
                };

                console.error = (...args) => {
                    logs.push(args.map(arg => typeof arg === 'object' ? JSON.stringify(arg, null, 2) : String(arg)).join(' '));
                    originalError(...args);
                };

                try {
                    const wrapped = `(() => { if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', () => { ${code} }); } else { ${code} } })();`;
                    new Function(wrapped)();
                    const outputText = logs.join('\n') || 'Code executed successfully!';
                    output.innerHTML = `<div class="text-green-400">${escapeHtml(outputText)}</div>`;
                } catch (error) {
                    const outputText = logs.join('\n');
                    output.innerHTML = `<div class="text-red-400">❌ Error:\n${escapeHtml(error.message)}${outputText ? '\n' + escapeHtml(outputText) : ''}</div>`;
                } finally {
                    console.log = originalLog;
                    console.error = originalError;
                }
            })();
        @elseif($language === 'html')
            // HTML preview
            output.innerHTML = `<div class="text-blue-400">📄 HTML Preview:</div><div class="mt-2 bg-white p-4 rounded border border-gray-300">${code}</div>`;
        @elseif($language === 'css')
            // CSS preview
            output.innerHTML = `<div class="text-purple-400">🎨 CSS Code:</div><pre class="mt-2 text-gray-300">${escapeHtml(code)}</pre>`;
        @endif
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<style>
    #{{ $editorId }} {
        padding-left: 3.5rem;
    }
    
    #{{ $editorId }}-lines {
        width: 2.5rem;
        text-align: right;
        line-height: 1.5;
    }
</style>
