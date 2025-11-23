@props(['projectId', 'autostart' => false, 'title' => 'Scratch Project'])

@php
    // Check if this is a placeholder/example ID
    $isPlaceholder = in_array($projectId, ['1234567890', '123456789', '12345']);
@endphp

@if($isPlaceholder)
    {{-- Show helpful message for placeholder IDs --}}
    <div class="bg-gradient-to-br from-orange-50 to-pink-50 dark:from-orange-900/20 dark:to-pink-900/20 rounded-xl shadow-lg overflow-hidden border-2 border-orange-300 dark:border-orange-700 p-6">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-orange-100 dark:bg-orange-900/50 rounded-lg">
                <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Scratch Project Not Configured</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                    The teacher hasn't added a real Scratch project yet. This is just a placeholder.
                </p>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-orange-200 dark:border-orange-800">
                    <p class="text-xs font-semibold text-gray-900 dark:text-white mb-2">📝 For Teachers:</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        To add a real Scratch project:
                    </p>
                    <ol class="text-xs text-gray-600 dark:text-gray-400 mt-2 ml-4 space-y-1">
                        <li>1. Go to your Scratch project on scratch.mit.edu</li>
                        <li>2. Copy the project ID from the URL (e.g., 987654321)</li>
                        <li>3. Edit this lesson in the curriculum builder</li>
                        <li>4. Paste the real project ID in the "Scratch Project Embed" field</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="scratch-embed-container bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border-4 border-orange-400">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-orange-500 to-pink-500 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2 text-white">
            <svg class="w-6 h-6" viewBox="0 0 100 100" fill="currentColor">
                <circle cx="50" cy="50" r="40"/>
                <circle cx="40" cy="45" r="5" fill="#000"/>
                <circle cx="60" cy="45" r="5" fill="#000"/>
            </svg>
            <span class="font-bold">{{ $title }}</span>
        </div>
        <a href="https://scratch.mit.edu/projects/{{ $projectId }}" 
           target="_blank" 
           class="text-white hover:text-gray-200 text-sm flex items-center gap-1">
            <span>Open in Scratch</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
    </div>

    {{-- Scratch Project Iframe --}}
    <div class="relative" style="padding-bottom: 75%; height: 0;">
        <iframe 
            src="https://scratch.mit.edu/projects/{{ $projectId }}/embed{{ $autostart ? '?autostart=true' : '' }}" 
            allowtransparency="true" 
            frameborder="0" 
            scrolling="no" 
            allowfullscreen
            class="absolute top-0 left-0 w-full h-full">
        </iframe>
    </div>

    {{-- Action Buttons --}}
    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 flex gap-2">
        <button onclick="document.querySelector('.scratch-embed-container iframe').contentWindow.postMessage({type: 'greenFlag'}, '*')"
                class="flex-1 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition-colors flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
            </svg>
            Run
        </button>
        <button onclick="document.querySelector('.scratch-embed-container iframe').contentWindow.postMessage({type: 'stopAll'}, '*')"
                class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold transition-colors flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"/>
            </svg>
            Stop
        </button>
        <a href="https://scratch.mit.edu/projects/{{ $projectId }}/remix" 
           target="_blank"
           class="flex-1 px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-semibold transition-colors flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Remix
        </a>
    </div>
</div>
@endif
