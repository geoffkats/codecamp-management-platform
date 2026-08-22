@php
use Carbon\Carbon;

$typeIcons = [
    'lesson_completion'    => ['emoji' => '📚', 'label' => 'Lesson'],
    'quiz_score'           => ['emoji' => '🎯', 'label' => 'Quiz'],
    'study_time'           => ['emoji' => '⏱️', 'label' => 'Study'],
    'course_progress'      => ['emoji' => '📈', 'label' => 'Progress'],
    'forum_participation'  => ['emoji' => '💬', 'label' => 'Discussion'],
    'assignment_submission'=> ['emoji' => '📝', 'label' => 'Assignment'],
];

$completionRate = $stats['total'] > 0
    ? round(($stats['completed'] / $stats['total']) * 100)
    : 0;
@endphp

<div class="max-w-7xl mx-auto px-4 py-5 space-y-5">

    {{-- ── Orange/Blue branded header ─────────────────────────────────── --}}
    <div class="rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 text-white shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Daily Challenges</h1>
                <p class="text-orange-100 text-sm mt-0.5">Complete challenges, earn XP, climb the leaderboard.</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Personal progress ring --}}
                <div class="hidden sm:flex flex-col items-center bg-white/15 rounded-xl px-4 py-2 text-center min-w-[80px]">
                    <span class="text-2xl font-extrabold leading-none">{{ $completionRate }}%</span>
                    <span class="text-[11px] text-orange-100 mt-0.5">done</span>
                </div>
                @can('manage_challenges')
                <a href="{{ route('daily-challenges.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-orange-600 hover:bg-orange-50 text-sm font-bold transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Challenge
                </a>
                @endcan
            </div>
        </div>

        {{-- Compact 4-stat strip inside header --}}
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['label'=>'Total',        'value'=> $stats['total'],                'sub'=>'available'],
                ['label'=>'Completed',    'value'=> $stats['completed'],            'sub'=>'by you'],
                ['label'=>'Active today', 'value'=> $stats['active'],               'sub'=>'right now'],
                ['label'=>'XP earned',    'value'=> number_format($stats['totalPoints']), 'sub'=>'points'],
            ] as $s)
            <div class="bg-white/15 rounded-xl px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-orange-100">{{ $s['label'] }}</p>
                <p class="text-2xl font-extrabold leading-tight">{{ $s['value'] }}</p>
                <p class="text-[11px] text-orange-200">{{ $s['sub'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Today's challenges banner ───────────────────────────────────── --}}
    @if($todayChallenges->count() > 0)
    <div class="rounded-2xl border-2 border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-3 border-b border-orange-200 dark:border-orange-800">
            <span class="text-lg">🔥</span>
            <div class="flex-1">
                <span class="font-bold text-orange-700 dark:text-orange-300 text-sm">Today's Challenges</span>
                <span class="text-xs text-orange-500 dark:text-orange-400 ml-2">{{ $todayChallenges->count() }} available now</span>
            </div>
        </div>
        <div class="flex gap-3 overflow-x-auto p-4 scrollbar-none">
            @foreach($todayChallenges as $ch)
            @php
                $ta   = $ch->attempts->first();
                $done = $ta && $ta->is_completed;
                $ti   = $typeIcons[$ch->type] ?? ['emoji'=>'⚡','label'=>'Challenge'];
                $dl   = strtolower($ch->difficulty_level ?? 'medium');
                $dlColor = match($dl) { 'easy'=>'text-green-600 bg-green-100 dark:bg-green-900/40 dark:text-green-400', 'hard'=>'text-red-600 bg-red-100 dark:bg-red-900/40 dark:text-red-400', default=>'text-yellow-700 bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-300' };
            @endphp
            <div class="flex-shrink-0 w-60 rounded-xl {{ $done ? 'bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800' : 'bg-white dark:bg-gray-800 border border-orange-200 dark:border-orange-700' }} p-4 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-xl">{{ $ti['emoji'] }}</span>
                    @if($done)
                        <span class="flex items-center gap-1 text-[11px] font-bold text-green-600 dark:text-green-400">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Done
                        </span>
                    @else
                        <span class="text-[11px] font-bold text-orange-600 dark:text-orange-400">{{ $ch->reward_points }} XP</span>
                    @endif
                </div>
                <p class="text-sm font-bold text-gray-900 dark:text-white leading-snug line-clamp-2">{{ $ch->title }}</p>
                <div class="flex items-center gap-1.5 mt-auto">
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $dlColor }}">{{ ucfirst($dl) }}</span>
                    @if($ch->course)
                        <span class="text-[10px] text-gray-400 truncate max-w-[80px]">{{ $ch->course->title }}</span>
                    @endif
                </div>
                @if(!$done)
                <a href="{{ route('daily-challenges.show', $ch) }}" wire:navigate
                   class="mt-1 block text-center px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold transition">
                    Start →
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Active Competition Banner ──────────────────────────────────── --}}
    @if($activeCompetition)
    <div class="rounded-2xl border-2 border-amber-300 dark:border-amber-700 bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 overflow-hidden shadow-md">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-amber-200 dark:border-amber-700">
            <span class="text-2xl">🏆</span>
            <div class="flex-1">
                <h3 class="font-bold text-amber-800 dark:text-amber-300">{{ $activeCompetition->title }}</h3>
                <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                    Weekly Competition · {{ $activeCompetition->reward_points }} XP
                    @if($activeCompetition->competition_ends_at)
                    · Ends {{ $activeCompetition->competition_ends_at->diffForHumans() }}
                    @endif
                </p>
            </div>
            @if($myCompetitionAttempt?->is_completed)
            <div class="px-3 py-1.5 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-400 text-sm font-bold rounded-full">
                ✅ Completed!
            </div>
            @else
            <a href="{{ route('daily-challenges.show', $activeCompetition) }}" wire:navigate
               class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl transition shadow-sm">
                Compete Now →
            </a>
            @endif
        </div>
        @if($competitionLeaderboard->isNotEmpty())
        <div class="px-5 py-4">
            <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wide mb-3">Leaderboard — Fastest Completions</p>
            <div class="space-y-2">
                @foreach($competitionLeaderboard as $entry)
                @php
                    $medals = ['🥇','🥈','🥉'];
                    $medal  = $medals[$entry['rank'] - 1] ?? '#'.$entry['rank'];
                    $isMe   = auth()->check() && $entry['user']?->id === auth()->id();
                @endphp
                <div class="flex items-center gap-3 {{ $isMe ? 'bg-amber-100 dark:bg-amber-900/40 rounded-lg px-2 py-1' : '' }}">
                    <span class="text-base w-6 text-center">{{ $medal }}</span>
                    <span class="flex-1 text-sm font-medium {{ $isMe ? 'text-amber-800 dark:text-amber-300 font-bold' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $entry['user']?->name ?? 'Unknown' }}
                        @if($isMe) <span class="text-[10px] text-amber-600">(you)</span> @endif
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        {{ $entry['completed_at'] ? Carbon::parse($entry['completed_at'])->format('M j, g:ia') : '' }}
                    </span>
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400">{{ $entry['points'] }} XP</span>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="px-5 py-4 text-center text-sm text-amber-600 dark:text-amber-400">
            No completions yet — be the first to finish and grab 🥇!
        </div>
        @endif
    </div>
    @endif

    {{-- ── Filter bar ──────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3">

        {{-- Search --}}
        <div class="relative flex-1 min-w-[180px] max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search"
                   type="text" placeholder="Search challenges…"
                   class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"/>
        </div>

        {{-- Status pills --}}
        <div class="flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-1.5">
            @foreach(['active'=>'Active','available'=>'Upcoming','all'=>'All'] as $s => $l)
            <button wire:click="filterByStatus('{{ $s }}')"
                    class="px-3 py-1 rounded-lg text-xs font-semibold transition
                           {{ $filterStatus === $s ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                {{ $l }}
            </button>
            @endforeach
        </div>

        {{-- Difficulty pills --}}
        <div class="flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-2 py-1.5">
            @foreach(['all'=>'All','easy'=>'Easy','medium'=>'Medium','hard'=>'Hard'] as $d => $l)
            @php
                $active = $filterDifficulty === $d;
                $dColor = match($d) {
                    'easy'   => $active ? 'bg-green-500 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-green-600',
                    'medium' => $active ? 'bg-yellow-500 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-yellow-600',
                    'hard'   => $active ? 'bg-red-500 text-white shadow-sm'   : 'text-gray-500 dark:text-gray-400 hover:text-red-600',
                    default  => $active ? 'bg-gray-600 text-white shadow-sm'  : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200',
                };
            @endphp
            <button wire:click="filterByDifficulty('{{ $d }}')"
                    class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ $dColor }}">
                {{ $l }}
            </button>
            @endforeach
        </div>

        @if($search || $filterStatus !== 'active' || $filterDifficulty !== 'all')
        <button wire:click="$set('search',''); $set('filterStatus','active'); $set('filterDifficulty','all')"
                class="text-xs text-gray-400 hover:text-red-500 transition underline">
            Reset
        </button>
        @endif

        <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">{{ $challenges->total() }} result{{ $challenges->total() !== 1 ? 's' : '' }}</span>
    </div>

    {{-- ── Challenges grid ─────────────────────────────────────────────── --}}
    @if($challenges->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($challenges as $challenge)
        @php
            $attempt     = $userAttempts[$challenge->id] ?? null;
            $isCompleted = $attempt && $attempt->is_completed;
            $inProgress  = $attempt && !$attempt->is_completed;
            $isAvailable = !$challenge->date || $challenge->date <= now()->toDateString();
            $isUpcoming  = $challenge->date && $challenge->date > now()->toDateString();
            $ti          = $typeIcons[$challenge->type] ?? ['emoji'=>'⚡','label'=>ucfirst(str_replace('_',' ',$challenge->type))];
            $dl          = strtolower($challenge->difficulty_level ?? 'medium');
            $xp          = $challenge->reward_points ?? 100;
        @endphp

        <div class="group relative flex flex-col bg-white dark:bg-gray-800 rounded-2xl border
                    {{ $challenge->is_competition ? 'border-amber-300 dark:border-amber-700' : ($isCompleted ? 'border-green-300 dark:border-green-700' : ($isUpcoming ? 'border-gray-200 dark:border-gray-700 opacity-75' : 'border-gray-200 dark:border-gray-700 hover:border-orange-300 dark:hover:border-orange-600')) }}
                    shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">

            {{-- Coloured top accent bar --}}
            <div class="h-1.5 w-full {{ $challenge->is_competition ? 'bg-gradient-to-r from-amber-400 to-yellow-500' : ($isCompleted ? 'bg-green-400' : ($isUpcoming ? 'bg-gray-300 dark:bg-gray-600' : 'bg-gradient-to-r from-orange-400 to-orange-500')) }}"></div>
            @if($challenge->is_competition)
            <div class="flex items-center gap-1.5 px-4 pt-2 text-xs font-bold text-amber-600 dark:text-amber-400">
                🏆 Weekly Competition
                @if($challenge->competition_ends_at)
                <span class="font-normal text-amber-500">· ends {{ $challenge->competition_ends_at->diffForHumans() }}</span>
                @endif
            </div>
            @endif

            <div class="flex flex-col flex-1 p-5 gap-3">

                {{-- Top row: type + XP + edit --}}
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xl leading-none" title="{{ $ti['label'] }}">{{ $ti['emoji'] }}</span>
                        <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $ti['label'] }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- XP badge --}}
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold {{ $isCompleted ? 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/40' : 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30' }} px-2 py-0.5 rounded-full">
                            ⚡ {{ $xp }} XP
                        </span>
                        @if(auth()->check() && $challenge->canBeEditedBy(auth()->user()))
                        <a href="{{ route('daily-challenges.edit', $challenge) }}" wire:navigate
                           class="opacity-0 group-hover:opacity-100 p-1 rounded-lg text-gray-400 hover:text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/30 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Title --}}
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-[15px] leading-snug line-clamp-2">{{ $challenge->title }}</h3>
                    @if($challenge->date)
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                        {{ Carbon::parse($challenge->date)->isToday() ? 'Today' : Carbon::parse($challenge->date)->format('M j') }}
                    </p>
                    @else
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Evergreen</p>
                    @endif
                </div>

                {{-- Description --}}
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed flex-1">{{ $challenge->description }}</p>

                {{-- Tags row --}}
                <div class="flex items-center gap-1.5 flex-wrap">
                    @php
                        $dlClr = match($dl) {
                            'easy'  => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                            'hard'  => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                            default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
                        };
                    @endphp
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $dlClr }}">{{ ucfirst($dl) }}</span>
                    @if($challenge->course)
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-medium truncate max-w-[100px]">{{ $challenge->course->title }}</span>
                    @endif
                    @if($challenge->category)
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">{{ $challenge->category }}</span>
                    @endif
                </div>

                {{-- In-progress indicator --}}
                @if($inProgress)
                <div class="flex items-center gap-1.5 text-[11px] text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg px-2.5 py-1.5">
                    <svg class="w-3 h-3 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="6"/>
                    </svg>
                    In progress · started {{ $attempt->attempted_at->diffForHumans() }}
                </div>
                @endif

                {{-- CTA button --}}
                <div class="mt-auto pt-1">
                    @if($isCompleted)
                        <div class="flex items-center justify-center gap-2 py-2 rounded-xl bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm font-bold">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Completed
                        </div>
                    @elseif($isUpcoming)
                        <div class="flex items-center justify-center gap-1.5 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Available {{ Carbon::parse($challenge->date)->diffForHumans() }}
                        </div>
                    @else
                        <a href="{{ route('daily-challenges.show', $challenge) }}" wire:navigate
                           class="flex items-center justify-center gap-2 py-2 rounded-xl
                                  {{ $inProgress ? 'bg-blue-600 hover:bg-blue-700' : 'bg-orange-500 hover:bg-orange-600' }}
                                  text-white text-sm font-bold transition-colors shadow-sm">
                            {{ $inProgress ? 'Continue' : 'Start Challenge' }}
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div>{{ $challenges->links() }}</div>

    @else
    {{-- Empty state --}}
    <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 p-12 text-center">
        <div class="text-5xl mb-4">🎯</div>
        <h3 class="text-base font-bold text-gray-800 dark:text-white">No challenges found</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Try adjusting your filters or check back tomorrow.</p>
        @if($search || $filterStatus !== 'active' || $filterDifficulty !== 'all')
        <button wire:click="$set('search',''); $set('filterStatus','active'); $set('filterDifficulty','all')"
                class="mt-4 text-sm text-orange-600 dark:text-orange-400 hover:underline font-semibold">
            Clear filters
        </button>
        @endif
        @can('manage_challenges')
        <div class="mt-4">
            <a href="{{ route('daily-challenges.create') }}" wire:navigate
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold transition">
                Create the first challenge
            </a>
        </div>
        @endcan
    </div>
    @endif

</div>
