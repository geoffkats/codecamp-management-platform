@props([
    'discussion',
    'showSubject' => false,
    'showReactions' => false,
    'compact' => false,
])

@php
    $category = $discussion->category ?? 'general';
    $categoryClasses = match ($category) {
        'question' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
        'help' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
        'announcement' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200',
        'project' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
        'feedback' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
        default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
    };
@endphp

<div @class(['px-5 py-4', 'py-3' => $compact])>
    <div class="flex items-start gap-4">
        <div class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#1a3a8f] text-sm font-bold text-white sm:flex">
            {{ strtoupper(substr($discussion->user->name, 0, 1)) }}
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                @if($discussion->is_pinned)
                    <span class="rounded px-2 py-0.5 text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200">Pinned</span>
                @endif
                <span class="rounded px-2 py-0.5 text-xs font-semibold {{ $categoryClasses }}">
                    {{ \App\Models\Discussion::categoryLabel($category) }}
                </span>
                @if($discussion->has_best_answer)
                    <span class="rounded px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">Solved</span>
                @endif
            </div>

            <h3 class="mt-1 text-base font-semibold text-slate-900 dark:text-white sm:text-lg">
                {{ $discussion->title }}
            </h3>

            @if(!$compact && $discussion->content)
                <p class="mt-1 line-clamp-2 text-sm text-slate-600 dark:text-slate-400">
                    {{ Str::limit(strip_tags($discussion->content), 140) }}
                </p>
            @endif

            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $discussion->user->name }}</span>
                <span>{{ $discussion->created_at->diffForHumans() }}</span>
                @if($discussion->course)
                    <span class="truncate">{{ $discussion->course->title }}</span>
                @endif
                @if($discussion->lesson)
                    <span class="truncate">{{ Str::limit($discussion->lesson->title, 40) }}</span>
                @endif
                @if($showSubject && $discussion->subject_tag)
                    <span class="capitalize">{{ $discussion->subject_tag }}</span>
                @endif
            </div>
        </div>

        <div class="shrink-0 text-right text-xs text-slate-500 dark:text-slate-400">
            <div class="font-semibold text-slate-900 dark:text-white">{{ $discussion->replies_count ?? 0 }}</div>
            <div>replies</div>
            <div class="mt-2 font-medium">{{ $discussion->views_count ?? 0 }} views</div>
        </div>
    </div>
</div>
