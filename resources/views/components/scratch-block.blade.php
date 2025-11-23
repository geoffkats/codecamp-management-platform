@props(['type' => 'motion', 'text'])

@php
$colors = [
    'motion' => 'from-blue-500 to-blue-600',
    'looks' => 'from-purple-500 to-purple-600',
    'sound' => 'from-pink-500 to-pink-600',
    'events' => 'from-yellow-500 to-yellow-600',
    'control' => 'from-orange-500 to-orange-600',
    'sensing' => 'from-cyan-500 to-cyan-600',
    'operators' => 'from-green-500 to-green-600',
    'variables' => 'from-red-500 to-red-600',
];

$color = $colors[$type] ?? 'from-gray-500 to-gray-600';
@endphp

<div class="inline-flex items-center my-2">
    <div class="relative">
        {{-- Block Shape --}}
        <div class="bg-gradient-to-r {{ $color }} text-white px-4 py-2 rounded-lg shadow-lg font-mono text-sm font-semibold flex items-center gap-2"
             style="clip-path: polygon(0 0, 100% 0, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0 100%, 0 0);">
            
            {{-- Icon based on type --}}
            @if($type === 'motion')
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                </svg>
            @elseif($type === 'looks')
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                </svg>
            @elseif($type === 'sound')
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd"/>
                </svg>
            @elseif($type === 'events')
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                </svg>
            @endif

            <span>{{ $text }}</span>
        </div>

        {{-- Notch at bottom for stacking effect --}}
        <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-6 h-2 bg-gradient-to-r {{ $color }} rounded-b"></div>
    </div>
</div>
