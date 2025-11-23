@props([
    'discussionId' => null,
    'replyId' => null,
    'reactions' => [],
    'userReactions' => [],
])

@php
$reactionTypes = [
    'upvote' => ['emoji' => '👍', 'label' => 'Upvote', 'color' => 'blue'],
    'helpful' => ['emoji' => '💡', 'label' => 'Helpful', 'color' => 'yellow'],
    'love' => ['emoji' => '❤️', 'label' => 'Love', 'color' => 'red'],
    'celebrate' => ['emoji' => '🎉', 'label' => 'Celebrate', 'color' => 'purple'],
];

$counts = collect($reactions)->groupBy('reaction_type')->map->count();
@endphp

<div class="flex items-center gap-2 flex-wrap">
    @foreach($reactionTypes as $type => $config)
        @php
            $count = $counts->get($type, 0);
            $hasReacted = in_array($type, $userReactions);
        @endphp
        <button 
            wire:click="toggleReaction('{{ $type }}', {{ $discussionId }}, {{ $replyId }})"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-all
                {{ $hasReacted 
                    ? 'bg-' . $config['color'] . '-100 dark:bg-' . $config['color'] . '-900/30 text-' . $config['color'] . '-700 dark:text-' . $config['color'] . '-300 ring-2 ring-' . $config['color'] . '-500' 
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
            <span class="text-base">{{ $config['emoji'] }}</span>
            @if($count > 0)
                <span class="font-bold">{{ $count }}</span>
            @endif
        </button>
    @endforeach
</div>
