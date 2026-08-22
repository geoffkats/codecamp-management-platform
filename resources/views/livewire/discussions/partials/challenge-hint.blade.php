@if($challenges->isNotEmpty())
    <div class="rounded-lg border border-amber-200 bg-amber-50/90 p-4 dark:border-amber-900 dark:bg-amber-950/30">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-sm font-bold text-amber-900 dark:text-amber-200">Daily challenge: forum participation</h2>
                <p class="mt-1 text-sm text-amber-800 dark:text-amber-300/90">
                    Quality posts and replies here count toward today's challenges. Short or off-topic messages do not count.
                </p>
                <ul class="mt-2 space-y-2">
                    @foreach($challenges as $challenge)
                        @php
                            $status = $progress[$challenge->id] ?? null;
                            $current = $status['current'] ?? 0;
                            $required = $status['required'] ?? ($challenge->requirements['posts'] ?? 1);
                            $met = $status['met'] ?? false;
                        @endphp
                        <li class="text-sm text-amber-900 dark:text-amber-100">
                            <span class="font-medium">{{ $challenge->title }}</span>
                            — {{ $current }}/{{ $required }} verified
                            @if($met)
                                <span class="font-semibold text-green-700 dark:text-green-400">(ready to claim)</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="flex shrink-0 flex-col gap-2">
                <flux:button href="{{ route('daily-challenges.index') }}" variant="ghost" size="sm" wire:navigate>
                    View challenges
                </flux:button>
            </div>
        </div>
    </div>
@endif
