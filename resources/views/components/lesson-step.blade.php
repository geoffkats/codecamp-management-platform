@props(['number', 'title', 'image' => null, 'tryItUrl' => null, 'time' => null, 'hint' => null, 'solution' => null])

<div x-data="{ open: {{ $number == 1 ? 'true' : 'false' }}, hintOpen: false, solutionOpen: false }" 
    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-3">
    
    {{-- Step Header (Clickable) --}}
    <button @click="open = !open" 
            class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
        <div class="flex items-center gap-3">
            {{-- Step Number Badge --}}
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-sm font-bold shadow-md">
                {{ $number }}
            </div>
            
            {{-- Title --}}
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white text-left">
                    {{ $title }}
                </h3>
                @if($time)
                    <span class="text-[11px] px-2 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $time }}</span>
                @endif
            </div>
        </div>

        {{-- Expand Icon --}}
        <svg x-show="!open" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
        <svg x-show="open" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
        </svg>
    </button>

    {{-- Step Content (Collapsible) --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="px-4 pb-4">
        
        {{-- Content --}}
        <div class="prose prose-sm dark:prose-invert max-w-none">
            {{ $slot }}
        </div>

        {{-- Hint / Solution --}}
        @if($hint || $solution)
            <div class="mt-3 space-y-2">
                @if($hint)
                    <button @click="hintOpen = !hintOpen" class="text-xs font-semibold text-purple-700 dark:text-purple-200 inline-flex items-center gap-1">
                        <span x-show="!hintOpen">Need a hint?</span>
                        <span x-show="hintOpen">Hide hint</span>
                    </button>
                    <div x-show="hintOpen" x-transition.opacity.duration.150ms class="text-xs text-gray-700 dark:text-gray-200 bg-purple-50 dark:bg-gray-700/50 border border-purple-100 dark:border-gray-700 rounded-md p-3">
                        {{ $hint }}
                    </div>
                @endif
                @if($solution)
                    <button @click="solutionOpen = !solutionOpen" class="text-xs font-semibold text-emerald-700 dark:text-emerald-200 inline-flex items-center gap-1">
                        <span x-show="!solutionOpen">Show answer</span>
                        <span x-show="solutionOpen">Hide answer</span>
                    </button>
                    <div x-show="solutionOpen" x-transition.opacity.duration.150ms class="text-xs text-gray-800 dark:text-gray-100 bg-emerald-50 dark:bg-gray-700/50 border border-emerald-100 dark:border-gray-700 rounded-md p-3">
                        {{ $solution }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Image (if provided) --}}
        @if($image)
            <div class="mt-3 rounded-lg overflow-hidden border-2 border-purple-200 dark:border-purple-800">
                <img src="{{ $image }}" alt="{{ $title }}" class="w-full" loading="lazy" decoding="async">
            </div>
        @endif

        {{-- Try It Button --}}
        @if($tryItUrl)
            <div class="mt-3">
                <a href="{{ $tryItUrl }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white text-sm font-semibold rounded-lg transition-all transform hover:scale-105 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Try It Yourself!
                </a>
            </div>
        @endif
    </div>
</div>
