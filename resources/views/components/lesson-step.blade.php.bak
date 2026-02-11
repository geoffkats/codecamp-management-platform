@props(['number', 'title', 'image' => null, 'tryItUrl' => null])

<div x-data="{ open: {{ $number == 1 ? 'true' : 'false' }} }" 
     class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden mb-4">
    
    {{-- Step Header (Clickable) --}}
    <button @click="open = !open" 
            class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
        <div class="flex items-center gap-4">
            {{-- Step Number Badge --}}
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold shadow-lg">
                {{ $number }}
            </div>
            
            {{-- Title --}}
            <h3 class="text-lg font-bold text-gray-900 dark:text-white text-left">
                {{ $title }}
            </h3>
        </div>

        {{-- Expand Icon --}}
        <svg x-show="!open" class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
        <svg x-show="open" class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
        </svg>
    </button>

    {{-- Step Content (Collapsible) --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="px-6 pb-6">
        
        {{-- Content --}}
        <div class="prose dark:prose-invert max-w-none">
            {{ $slot }}
        </div>

        {{-- Image (if provided) --}}
        @if($image)
            <div class="mt-4 rounded-lg overflow-hidden border-2 border-purple-200 dark:border-purple-800">
                <img src="{{ $image }}" alt="{{ $title }}" class="w-full">
            </div>
        @endif

        {{-- Try It Button --}}
        @if($tryItUrl)
            <div class="mt-4">
                <a href="{{ $tryItUrl }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-semibold rounded-lg transition-all transform hover:scale-105 shadow-lg">
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

{{-- Alpine.js for interactivity --}}
@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @endpush
@endonce
