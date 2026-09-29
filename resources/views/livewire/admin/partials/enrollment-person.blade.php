@if ($person)
    <div class="flex items-center gap-3">
        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-blue-900 text-xs font-bold text-white">{{ $person->initials() }}</span>
        <div class="min-w-0">
            <p class="truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $person->name }}</p>
            @if (! empty($sub))
                <p class="truncate text-xs text-zinc-500">{{ $sub }}</p>
            @endif
        </div>
    </div>
@else
    <span class="text-sm italic text-zinc-400">Deleted user</span>
@endif
