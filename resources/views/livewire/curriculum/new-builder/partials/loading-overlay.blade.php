{{-- Loading Overlay — no delay so clicks like Add Quiz feel responsive --}}
<div wire:loading class="absolute inset-0 bg-gradient-to-br from-white/95 via-blue-50/90 to-white/95 dark:from-gray-900/95 dark:via-blue-900/50 dark:to-gray-900/95 backdrop-blur-md flex items-center justify-center z-50">
    <div class="text-center">
        <div class="relative w-16 h-16 mx-auto mb-6">
            <div class="absolute inset-0 rounded-full border-4 border-blue-100 dark:border-blue-900/30"></div>
            <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-blue-600 dark:border-t-blue-400 border-r-blue-500 dark:border-r-blue-300 animate-spin"></div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Updating Content</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Please wait while we refresh your curriculum...</p>

        <div class="flex justify-center gap-1.5 mt-4">
            <div class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400 animate-bounce" style="animation-delay: 0s;"></div>
            <div class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400 animate-bounce" style="animation-delay: 0.2s;"></div>
            <div class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400 animate-bounce" style="animation-delay: 0.4s;"></div>
        </div>

        <div class="mt-6 space-y-3">
            <div class="h-4 w-64 mx-auto rounded bg-gray-200/80 dark:bg-gray-700/60 animate-pulse"></div>
            <div class="h-4 w-56 mx-auto rounded bg-gray-200/80 dark:bg-gray-700/60 animate-pulse"></div>
            <div class="h-4 w-48 mx-auto rounded bg-gray-200/80 dark:bg-gray-700/60 animate-pulse"></div>
        </div>
    </div>
</div>
