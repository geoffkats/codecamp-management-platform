{{-- Sits on the course-tree edge, not the browser's left edge (avoids a gap next to the app nav). --}}
<div class="absolute top-4 z-30 transition-[left] duration-200 ease-out {{ $sidebarCollapsed ? 'left-0' : 'left-72' }}">
    <button wire:click="toggleSidebar"
            type="button"
            class="group relative rounded-r-full border border-gray-200 bg-white px-3 py-2 shadow-md transition-all duration-150 hover:bg-gray-50 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 {{ $sidebarCollapsed ? '' : '-translate-x-1/2 border-l-0' }}"
            aria-label="{{ $sidebarCollapsed ? 'Show course outline' : 'Hide course outline' }}"
            title="{{ $sidebarCollapsed ? 'Show course outline (Ctrl+B)' : 'Hide course outline (Ctrl+B)' }}">
        <span class="sr-only">{{ $sidebarCollapsed ? 'Show course outline' : 'Hide course outline' }}</span>

        @if($sidebarCollapsed)
            <svg class="h-5 w-5 text-gray-500 transition-colors group-hover:text-orange-600 dark:text-gray-400 dark:group-hover:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
        @else
            <svg class="h-5 w-5 text-gray-500 transition-colors group-hover:text-orange-600 dark:text-gray-400 dark:group-hover:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M19 19l-7-7 7-7" />
            </svg>
        @endif
    </button>
</div>
