@props([
    'count' => 0,
    'viewers' => [],
    'showNames' => false,
])

<div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-full text-sm font-medium">
    <span class="relative flex h-3 w-3">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
    </span>
    
    <span class="font-semibold">{{ $count }}</span>
    <span>{{ $count === 1 ? 'person' : 'people' }} viewing</span>
    
    @if($showNames && count($viewers) > 0)
        <div class="flex -space-x-2 ml-2">
            @foreach(array_slice($viewers, 0, 3) as $viewer)
                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold border-2 border-white dark:border-gray-800"
                     title="{{ $viewer->name }}">
                    {{ substr($viewer->name, 0, 1) }}
                </div>
            @endforeach
            @if(count($viewers) > 3)
                <div class="w-6 h-6 rounded-full bg-gray-400 flex items-center justify-center text-white text-xs font-bold border-2 border-white dark:border-gray-800">
                    +{{ count($viewers) - 3 }}
                </div>
            @endif
        </div>
    @endif
</div>
