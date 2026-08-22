@php
use Carbon\Carbon;

// Level color map
$lvlColors = [
    'gray'   => ['bg'=>'bg-gray-100 dark:bg-gray-800',   'text'=>'text-gray-600 dark:text-gray-300',   'bar'=>'bg-gray-400',     'ring'=>'ring-gray-400'],
    'blue'   => ['bg'=>'bg-blue-100 dark:bg-blue-900/40', 'text'=>'text-blue-700 dark:text-blue-300',   'bar'=>'bg-blue-500',     'ring'=>'ring-blue-400'],
    'teal'   => ['bg'=>'bg-teal-100 dark:bg-teal-900/40', 'text'=>'text-teal-700 dark:text-teal-300',   'bar'=>'bg-teal-500',     'ring'=>'ring-teal-400'],
    'green'  => ['bg'=>'bg-green-100 dark:bg-green-900/40','text'=>'text-green-700 dark:text-green-300','bar'=>'bg-green-500',   'ring'=>'ring-green-400'],
    'yellow' => ['bg'=>'bg-yellow-100 dark:bg-yellow-900/40','text'=>'text-yellow-700 dark:text-yellow-300','bar'=>'bg-yellow-500','ring'=>'ring-yellow-400'],
    'orange' => ['bg'=>'bg-orange-100 dark:bg-orange-900/40','text'=>'text-orange-700 dark:text-orange-300','bar'=>'bg-orange-500','ring'=>'ring-orange-400'],
    'red'    => ['bg'=>'bg-red-100 dark:bg-red-900/40',   'text'=>'text-red-700 dark:text-red-300',     'bar'=>'bg-red-500',     'ring'=>'ring-red-400'],
    'purple' => ['bg'=>'bg-purple-100 dark:bg-purple-900/40','text'=>'text-purple-700 dark:text-purple-300','bar'=>'bg-purple-500','ring'=>'ring-purple-400'],
    'gold'   => ['bg'=>'bg-amber-100 dark:bg-amber-900/40','text'=>'text-amber-700 dark:text-amber-300','bar'=>'bg-amber-500',   'ring'=>'ring-amber-400'],
];
$lc = $lvlColors[$levelInfo['color']] ?? $lvlColors['blue'];

// Heatmap intensity colours
$heatColors = [
    0 => 'bg-gray-100 dark:bg-gray-800',
    1 => 'bg-orange-200 dark:bg-orange-900/50',
    2 => 'bg-orange-400 dark:bg-orange-700',
    3 => 'bg-orange-500 dark:bg-orange-600',
    4 => 'bg-orange-600 dark:bg-orange-500',
];

$dailyXpPct = min(100, round(($dailyXp / $dailyXpGoal) * 100));
$streakFire = $streak['current'] >= 7 ? '🔥' : ($streak['current'] >= 3 ? '⚡' : '✨');
@endphp

<div class="max-w-7xl mx-auto px-4 py-5 space-y-5">

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- HERO: Level + Streak + Daily Goal                                  --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg overflow-hidden">
        <div class="px-6 pt-5 pb-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

                {{-- Greeting --}}
                <div>
                    <p class="text-orange-100 text-xs font-semibold uppercase tracking-widest mb-1">CodeCamp</p>
                    <h1 class="text-2xl font-bold leading-tight">Hey, {{ explode(' ', $user->name)[0] }}! {{ $streakFire }}</h1>
                    <p class="text-orange-100 text-sm mt-0.5">Keep your momentum — pick up where you left off.</p>
                </div>

                {{-- Level badge --}}
                <div class="flex-shrink-0 flex flex-col items-center bg-white/20 rounded-2xl px-5 py-3 min-w-[110px] text-center">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-orange-100">Level {{ $levelInfo['level'] }}</span>
                    <span class="text-xl font-extrabold leading-tight mt-0.5">{{ $levelInfo['name'] }}</span>
                    @if(!$levelInfo['isMax'])
                    <span class="text-[10px] text-orange-200 mt-1">→ {{ $levelInfo['nextName'] }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- XP progress bar --}}
        <div class="px-6 pb-4">
            <div class="flex items-center justify-between text-[11px] text-orange-100 mb-1.5">
                <span>{{ number_format($levelInfo['xp']) }} XP total</span>
                @if(!$levelInfo['isMax'])
                <span>{{ number_format($levelInfo['xpNeeded'] - $levelInfo['xpInLevel']) }} XP to {{ $levelInfo['nextName'] }}</span>
                @else
                <span>Max level reached!</span>
                @endif
            </div>
            <div class="h-2.5 bg-white/25 rounded-full overflow-hidden">
                <div class="h-full bg-white rounded-full transition-all duration-700"
                     style="width: {{ $levelInfo['progress'] }}%"></div>
            </div>
        </div>

        {{-- Stat pills row --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 px-6 pb-5">
            {{-- Streak --}}
            <div class="bg-white/15 rounded-xl px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-orange-100">Streak</p>
                <p class="text-2xl font-extrabold leading-tight">{{ $streak['current'] }} <span class="text-base">days</span></p>
                <p class="text-[10px] text-orange-200">Best: {{ $streak['longest'] }}d</p>
            </div>

            {{-- Daily XP goal --}}
            <div class="bg-white/15 rounded-xl px-4 py-3">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-orange-100">Today's Goal</p>
                    <span class="text-[10px] font-bold {{ $dailyXpPct >= 100 ? 'text-green-300' : 'text-orange-200' }}">{{ $dailyXpPct }}%</span>
                </div>
                <div class="h-1.5 bg-white/20 rounded-full overflow-hidden mb-1">
                    <div class="h-full {{ $dailyXpPct >= 100 ? 'bg-green-400' : 'bg-white' }} rounded-full transition-all"
                         style="width: {{ $dailyXpPct }}%"></div>
                </div>
                <p class="text-[10px] text-orange-200">{{ $dailyXp }} / {{ $dailyXpGoal }} XP</p>
            </div>

            {{-- Lessons done --}}
            <div class="bg-white/15 rounded-xl px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-orange-100">Lessons Done</p>
                <p class="text-2xl font-extrabold leading-tight">{{ $stats['completedLessons'] }}</p>
                <p class="text-[10px] text-orange-200">{{ $stats['totalBadges'] }} badge{{ $stats['totalBadges'] !== 1 ? 's' : '' }}</p>
            </div>

            {{-- Rank --}}
            <div class="bg-white/15 rounded-xl px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-orange-100">Global Rank</p>
                <p class="text-2xl font-extrabold leading-tight">#{{ $leaderboardPosition['rank'] }}</p>
                <p class="text-[10px] text-orange-200">of {{ $leaderboardPosition['total'] }} students</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MAIN GRID: left col (courses + challenges) | right col (gamification) --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── LEFT / MAIN (2 cols wide) ─────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Activity heatmap — 30 days --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white">📅 Activity — Last 30 Days</h2>
                    <div class="flex items-center gap-2 text-[10px] text-gray-400">
                        <span>{{ $streak['activeDays'] }} active</span>
                        <span>·</span>
                        <span>Less</span>
                        @foreach($heatColors as $c)
                        <div class="w-3 h-3 rounded-sm {{ $c }}"></div>
                        @endforeach
                        <span>More</span>
                    </div>
                </div>
                <div class="flex gap-1.5 overflow-x-auto pb-1">
                    @foreach($heatmap as $day)
                    <div class="flex flex-col items-center gap-1 flex-shrink-0">
                        <div
                            title="{{ $day['date'] }}: {{ $day['count'] }} lesson{{ $day['count'] !== 1 ? 's' : '' }}"
                            class="w-7 h-7 rounded-lg {{ $heatColors[$day['level']] }} transition-all cursor-default hover:scale-110 hover:ring-2 hover:ring-orange-300"
                        ></div>
                        @if(in_array(Carbon::parse($day['date'])->dayOfWeek, [0,6]) || Carbon::parse($day['date'])->day === 1)
                        <span class="text-[9px] text-gray-400 leading-none">{{ Carbon::parse($day['date'])->format('j') }}</span>
                        @else
                        <span class="text-[9px] text-transparent">·</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Continue Learning --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white">Continue Learning</h2>
                    @if($activeEnrollments->count() > 3)
                    <a href="{{ route('enrollments.index') }}" wire:navigate
                       class="text-xs text-orange-600 dark:text-orange-400 hover:underline">View all</a>
                    @endif
                </div>
                @if($activeEnrollments->count() > 0)
                <div class="space-y-3">
                    @foreach($activeEnrollments->take(4) as $enrollment)
                    @php $pct = $enrollment->progress_percentage ?? 0; @endphp
                    <div class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-orange-200 dark:hover:border-orange-700 transition group">
                        {{-- Progress ring --}}
                        <div class="flex-shrink-0 relative w-11 h-11">
                            <svg class="w-11 h-11 -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="15" fill="none" stroke="#e5e7eb" stroke-width="3" class="dark:stroke-gray-700"/>
                                <circle cx="18" cy="18" r="15" fill="none" stroke="#f97316" stroke-width="3"
                                        stroke-dasharray="{{ round($pct * 0.942) }} 100"
                                        stroke-linecap="round"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-orange-600 dark:text-orange-400">{{ $pct }}%</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $enrollment->course->title }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $enrollment->course->instructor->name ?? '' }}</p>
                        </div>
                        <a href="{{ route('courses.preview', $enrollment->course) }}" wire:navigate
                           class="flex-shrink-0 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold transition opacity-0 group-hover:opacity-100">
                            Continue →
                        </a>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-700 p-6 text-center">
                    <p class="text-sm text-gray-400">No active courses yet.</p>
                    <a href="{{ route('courses.index') }}" wire:navigate class="mt-2 inline-block text-xs text-orange-600 dark:text-orange-400 hover:underline font-semibold">Browse courses →</a>
                </div>
                @endif
            </div>

            {{-- Weekly Competition (home) --}}
            @if($activeCompetition)
            @php
                $compAttempt = $activeCompetition->attempts->first();
                $compDone = $compAttempt && $compAttempt->is_completed;
            @endphp
            <div class="rounded-2xl border-2 border-amber-300 dark:border-amber-700 bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">🏆</span>
                            <span class="text-xs font-bold uppercase tracking-wide text-amber-700 dark:text-amber-400">Weekly Competition</span>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $activeCompetition->title }}</h3>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                            +{{ $activeCompetition->reward_points }} XP
                            @if($activeCompetition->competition_ends_at)
                            · Ends {{ $activeCompetition->competition_ends_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                    @if($compDone)
                    <span class="flex-shrink-0 px-3 py-1.5 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400 text-xs font-bold rounded-full">✅ Done</span>
                    @else
                    <a href="{{ route('daily-challenges.show', $activeCompetition) }}" wire:navigate
                       class="flex-shrink-0 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition shadow-sm">
                        Compete →
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Daily Challenges --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white">🔥 Today's Challenges</h2>
                    <a href="{{ route('daily-challenges.index') }}" wire:navigate
                       class="text-xs text-orange-600 dark:text-orange-400 hover:underline">See all</a>
                </div>
                @if($dailyChallenges->count() > 0)
                <div class="space-y-3">
                    @foreach($dailyChallenges as $ch)
                    @php
                        $done = $ch->is_completed;
                        $dlc  = strtolower($ch->difficulty_level ?? 'medium');
                        $dlColor = match($dlc) { 'easy'=>'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400', 'hard'=>'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400', default=>'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400' };
                    @endphp
                    <div class="flex items-center gap-3 p-3 rounded-xl border {{ $done ? 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20' : 'border-gray-100 dark:border-gray-700 hover:border-orange-200 dark:hover:border-orange-800' }} transition">
                        <div class="flex-shrink-0 w-9 h-9 rounded-xl {{ $done ? 'bg-green-500' : 'bg-orange-100 dark:bg-orange-900/40' }} flex items-center justify-center">
                            @if($done)
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            @else
                            <span class="text-lg">⚡</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $ch->title }}</p>
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $dlColor }}">{{ ucfirst($dlc) }}</span>
                        </div>
                        <div class="flex-shrink-0 flex flex-col items-end gap-1">
                            <span class="text-xs font-bold text-orange-600 dark:text-orange-400">+{{ $ch->reward_points }} XP</span>
                            @if(!$done)
                            <a href="{{ route('daily-challenges.show', $ch) }}" wire:navigate
                               class="text-[10px] text-blue-600 dark:text-blue-400 hover:underline font-semibold">Start →</a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-4">No challenges today — check back tomorrow!</p>
                @endif
            </div>

            {{-- Upcoming deadlines --}}
            @if(count($upcomingDeadlines['assignments']) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4">⏰ Upcoming Deadlines</h2>
                <div class="space-y-2">
                    @foreach($upcomingDeadlines['assignments'] as $a)
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 dark:border-gray-700 px-4 py-2.5">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $a->title }}</p>
                            <p class="text-xs text-gray-400">{{ $a->course->title }}</p>
                        </div>
                        <span class="text-xs font-bold text-red-600 dark:text-red-400 flex-shrink-0">{{ $a->due_date->format('M j') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>{{-- end left col --}}

        {{-- ── RIGHT SIDEBAR (gamification) ──────────────────────────── --}}
        <div class="space-y-5">

            {{-- Streak card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 text-center">
                <p class="text-4xl mb-1">{{ $streak['current'] >= 7 ? '🔥' : ($streak['current'] >= 3 ? '⚡' : '✨') }}</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $streak['current'] }}</p>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">day streak</p>
                <div class="mt-3 flex justify-center gap-1.5">
                    @for($d = 6; $d >= 0; $d--)
                    @php
                        $dayStr = now()->subDays($d)->format('D');
                        $isActive = $d < $streak['current'];
                    @endphp
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-6 h-6 rounded-full {{ $isActive ? 'bg-orange-500' : 'bg-gray-100 dark:bg-gray-700' }} flex items-center justify-center">
                            @if($isActive)
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            @endif
                        </div>
                        <span class="text-[9px] text-gray-400">{{ substr($dayStr,0,1) }}</span>
                    </div>
                    @endfor
                </div>
                @if($streak['longest'] > $streak['current'])
                <p class="mt-3 text-[11px] text-gray-400">Best: {{ $streak['longest'] }} days</p>
                @elseif($streak['current'] > 0)
                <p class="mt-3 text-[11px] text-orange-500 font-semibold">Personal best!</p>
                @endif
            </div>

            {{-- Camp leaderboard --}}
            @if(!empty($campLeaderboard['top']))
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white">🏆 {{ $campLeaderboard['campName'] }}</h2>
                    <a href="{{ route('leaderboards.index') }}" wire:navigate class="text-xs text-orange-600 dark:text-orange-400 hover:underline">Full →</a>
                </div>
                <div class="space-y-2">
                    @foreach($campLeaderboard['top'] as $i => $member)
                    @php $isMe = $member['user_id'] === auth()->id(); @endphp
                    <div class="flex items-center gap-3 px-3 py-2 rounded-xl {{ $isMe ? 'bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800' : '' }}">
                        <span class="text-sm font-bold w-5 text-center {{ $i === 0 ? 'text-yellow-500' : ($i === 1 ? 'text-gray-400' : ($i === 2 ? 'text-amber-600' : 'text-gray-400')) }}">
                            {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i+1)) }}
                        </span>
                        <div class="w-7 h-7 flex-shrink-0">
                            <x-user-avatar :user="$member['user']" size="xs" rounded="full" />
                        </div>
                        <span class="flex-1 text-xs font-semibold {{ $isMe ? 'text-orange-700 dark:text-orange-300' : 'text-gray-700 dark:text-gray-300' }} truncate">
                            {{ $isMe ? 'You' : $member['name'] }}
                        </span>
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">{{ number_format($member['xp']) }} XP</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Badges --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white">🏅 Badges</h2>
                    <span class="text-xs text-gray-400">{{ $recentBadges->count() }} earned</span>
                </div>
                @if($recentBadges->isEmpty())
                <div class="text-center py-4 text-gray-400 dark:text-gray-500">
                    <div class="text-3xl mb-1">🔒</div>
                    <p class="text-[11px]">Complete lessons to unlock badges!</p>
                </div>
                @else
                <div class="grid grid-cols-3 gap-3">
                    @foreach($recentBadges->take(6) as $badge)
                    <div
                        class="flex flex-col items-center gap-1 text-center group cursor-default"
                        title="{{ $badge->name }}: {{ $badge->description }}"
                    >
                        <div
                            class="w-11 h-11 rounded-full flex items-center justify-center text-xl shadow-sm group-hover:scale-110 transition-transform"
                            style="background-color: {{ $badge->color ?? '#F97316' }}22; border: 2px solid {{ $badge->color ?? '#F97316' }}55"
                        >
                            {{ $badge->icon ?? '🏅' }}
                        </div>
                        <span class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight line-clamp-2">{{ $badge->name }}</span>
                    </div>
                    @endforeach
                </div>
                @if($recentBadges->count() > 6)
                <p class="mt-2 text-center text-xs text-orange-500 dark:text-orange-400">
                    +{{ $recentBadges->count() - 6 }} more on your profile
                </p>
                @endif
                @endif
            </div>

            {{-- Certificates --}}
            @if($recentCertificates->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-3">🎓 Certificates</h2>
                <div class="space-y-2">
                    @foreach($recentCertificates as $cert)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ $cert->course->title ?? 'Course' }}</p>
                            <p class="text-[10px] text-gray-400">{{ Carbon::parse($cert->issued_at)->format('M Y') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick links --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-3">Quick Links</h2>
                <div class="space-y-1.5">
                    @foreach([
                        ['label'=>'Daily Challenges','route'=>'daily-challenges.index','emoji'=>'⚡'],
                        ['label'=>'Leaderboard','route'=>'leaderboards.index','emoji'=>'🏆'],
                        ['label'=>'My Courses','route'=>'enrollments.index','emoji'=>'📚'],
                        ['label'=>'Assignments','route'=>'assignments.index','emoji'=>'📝'],
                    ] as $link)
                    <a href="{{ route($link['route']) }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 text-gray-700 dark:text-gray-300 hover:text-orange-700 dark:hover:text-orange-300 transition text-sm font-medium">
                        <span>{{ $link['emoji'] }}</span>
                        {{ $link['label'] }}
                        <svg class="w-3.5 h-3.5 ml-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endforeach
                </div>
            </div>

        </div>{{-- end right sidebar --}}
    </div>

</div>
