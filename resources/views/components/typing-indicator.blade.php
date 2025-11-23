@props([
    'userName' => null,
    'show' => false,
])

<div 
    x-data="{ show: @entangle('show') }"
    x-show="show"
    x-transition
    class="flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg text-sm text-blue-800 dark:text-blue-200">
    
    <div class="flex space-x-1">
        <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
        <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
        <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
    </div>
    
    <span class="font-medium">
        {{ $userName ?? 'Someone' }} is typing...
    </span>
</div>
