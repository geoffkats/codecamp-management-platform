@props([
    'contributors' => [],
    'period' => 'week', // week, month, all-time
])

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span class="text-2xl">🏆</span>
            Top Helpers This {{ ucfirst($period) }}
        </h3>
    </div>

    @if(count($contributors) > 0)
        <div class="space-y-3">
            @foreach($contributors as $index => $contributor)
                <div class="flex items-center gap-3 p-3 rounded-lg {{ $index === 0 ? 'bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border-2 border-yellow-300 dark:border-yellow-700' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                    {{-- Rank --}}
                    <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $index === 0 ? 'bg-gradient-to-br from-yellow-400 to-orange-500' : ($index === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-500' : ($index === 2 ? 'bg-gradient-to-br from-orange-400 to-orange-600' : 'bg-gray-400')) }} flex items-center justify-center text-white font-bold text-sm">
                        {{ $index + 1 }}
                    </div>

                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                        {{ substr($contributor->name ?? 'U', 0, 1) }}
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-900 dark:text-white truncate">
                            {{ $contributor->name ?? 'Unknown' }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $contributor->helpful_answers ?? 0 }} helpful answers
                        </div>
                    </div>

                    {{-- XP --}}
                    <x-xp-display :points="$contributor->weekly_xp ?? 0" size="sm" />
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p class="text-sm">No contributors yet this {{ $period }}</p>
            <p class="text-xs mt-1">Be the first to help others!</p>
        </div>
    @endif
</div>
