{{-- Sidebar Toggle Button --}}
<div class="fixed left-0 top-4 z-50 {{ $sidebarCollapsed ? 'translate-x-0' : 'translate-x-72' }}" style="transition: transform 200ms ease-out;">
    <button wire:click.debounce.100ms="toggleSidebar"
            class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-r-full shadow-md px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow-lg transition-all duration-150 relative"
            x-data="{ showTooltip: false }"
            @mouseenter="showTooltip = true"
            @mouseleave="showTooltip = false"
            aria-label="{{ $sidebarCollapsed ? 'Show sidebar' : 'Hide sidebar' }}">
        <span class="sr-only">{{ $sidebarCollapsed ? 'Show sidebar' : 'Hide sidebar' }}</span>

        @if($sidebarCollapsed)
            {{-- Panel open: two vertical lines + right arrow --}}
            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
        @else
            {{-- Panel close: left arrow + bar --}}
            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M19 19l-7-7 7-7" />
            </svg>
        @endif

        {{-- Tooltip --}}
        <div x-show="showTooltip"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="pointer-events-none absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2.5 py-1.5 bg-gray-900 dark:bg-gray-700 text-white text-xs font-medium rounded-lg shadow-lg whitespace-nowrap">
            {{ $sidebarCollapsed ? 'Show sidebar' : 'Hide sidebar' }}
            <span class="text-gray-400 ml-1.5">Ctrl+B</span>
            <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900 dark:border-r-gray-700"></div>
        </div>
    </button>
</div>
