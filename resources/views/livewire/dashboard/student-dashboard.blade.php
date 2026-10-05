@php
    use Carbon\Carbon;

    $readable = fn (?string $text) => $text !== null && $text === mb_strtoupper($text) && preg_match('/\p{L}{4,}/u', $text)
        ? \Illuminate\Support\Str::title(mb_strtolower($text))
        : $text;

    $heatColors = [
        0 => 'bg-gray-100 dark:bg-zinc-800',
        1 => 'bg-orange-200 dark:bg-orange-900/60',
        2 => 'bg-orange-300 dark:bg-orange-700',
        3 => 'bg-orange-500 dark:bg-orange-600',
        4 => 'bg-orange-600 dark:bg-orange-500',
    ];

    $dailyXpPct = min(100, (int) round(($dailyXp / max(1, $dailyXpGoal)) * 100));
    $streakEmoji = $streak['current'] >= 7 ? '🔥' : ($streak['current'] >= 3 ? '⚡' : '✨');
    $featured = $activeEnrollments->first(fn ($e) => ! $e->completed_at) ?? $activeEnrollments->first();
    $others = $activeEnrollments->reject(fn ($e) => $featured && $e->id === $featured->id)->take(3);
    $deadlines = collect($upcomingDeadlines['assignments'] ?? []);
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5 px-4 py-5 sm:px-6">
    <livewire:attendance.morning-check-in-prompt />

    {{-- Hero --}}
    <section class="overflow-hidden rounded-3xl bg-orange-500 text-white shadow-sm">
        <div class="relative px-5 pt-5 pb-5 sm:px-7 sm:pt-6">
            <div class="pointer-events-none absolute -right-10 -top-16 size-56 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute right-24 -bottom-20 size-40 rounded-full bg-white/10"></div>

            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-100">{{ now()->format('l, j F') }}</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">Hey {{ \Illuminate\Support\Str::of($user->name)->before(' ') }}! {{ $streakEmoji }}</h1>
                    <p class="mt-1 text-sm text-orange-50">
                        @if($dailyXpPct >= 100)
                            You smashed today's goal. Amazing work!
                        @elseif($streak['current'] > 0)
                            {{ $streak['current'] }}-day streak. Finish a lesson today to keep it going.
                        @else
                            Finish a lesson today to start a streak.
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 ring-1 ring-white/20">
                    <span class="flex size-12 items-center justify-center rounded-xl bg-white text-xl font-black text-orange-600">{{ $levelInfo['level'] }}</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-orange-100">Level {{ $levelInfo['level'] }}</p>
                        <p class="text-lg font-extrabold leading-tight">{{ $levelInfo['name'] }}</p>
                    </div>
                </div>
            </div>

            <div class="relative mt-5">
                <div class="mb-1.5 flex items-center justify-between text-xs text-orange-50">
                    <span class="font-semibold">{{ number_format($levelInfo['xp']) }} XP</span>
                    @if(! $levelInfo['isMax'])
                        <span>{{ number_format(max(0, $levelInfo['xpNeeded'] - $levelInfo['xpInLevel'])) }} XP to {{ $levelInfo['nextName'] }}</span>
                    @else
                        <span>Top rank reached!</span>
                    @endif
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-white/25">
                    <div class="h-full rounded-full bg-white transition-all duration-700" style="width: {{ $levelInfo['progress'] }}%"></div>
                </div>
            </div>

            <div class="relative mt-5 grid grid-cols-3 gap-2 sm:gap-3">
                <div class="rounded-2xl bg-white/15 px-3 py-3 sm:px-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-orange-100">Streak</p>
                    <p class="text-2xl font-extrabold leading-tight">{{ $streak['current'] }}<span class="ml-1 text-sm font-semibold">{{ \Illuminate\Support\Str::plural('day', $streak['current']) }}</span></p>
                    <p class="text-[11px] text-orange-100">Best {{ $streak['longest'] }}</p>
                </div>
                <div class="rounded-2xl bg-white/15 px-3 py-3 sm:px-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-orange-100">Today</p>
                    <p class="text-2xl font-extrabold leading-tight">{{ $dailyXp }}<span class="ml-1 text-sm font-semibold">/ {{ $dailyXpGoal }} XP</span></p>
                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-white/25">
                        <div class="h-full rounded-full {{ $dailyXpPct >= 100 ? 'bg-emerald-300' : 'bg-white' }}" style="width: {{ $dailyXpPct }}%"></div>
                    </div>
                </div>
                <a href="{{ route('leaderboards.index', ['period' => 'weekly']) }}" wire:navigate class="rounded-2xl bg-white/15 px-3 py-3 transition hover:bg-white/25 sm:px-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-orange-100">This week</p>
                    <p class="text-2xl font-extrabold leading-tight">#{{ $leaderboardPosition['rank'] }}</p>
                    <p class="text-[11px] text-orange-100">of {{ $leaderboardPosition['total'] }} in class</p>
                </a>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            {{-- Continue learning --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900 dark:text-white">Continue learning</h2>
                    @if($activeEnrollments->total() > 4)
                        <a href="{{ route('enrollments.index') }}" wire:navigate class="text-sm font-medium text-orange-600 hover:underline dark:text-orange-400">All my courses</a>
                    @endif
                </div>

                @if($featured)
                    @php $pct = (int) round($featured->progress_percentage ?? 0); @endphp
                    <a href="{{ route('courses.learn', $featured->course) }}" wire:navigate
                       class="group flex items-center gap-4 rounded-2xl bg-orange-50 p-4 ring-1 ring-orange-100 transition hover:ring-orange-300 dark:bg-orange-900/15 dark:ring-orange-900">
                        <div class="relative size-16 shrink-0">
                            <svg class="size-16 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke-width="3.5" class="stroke-orange-100 dark:stroke-zinc-700" />
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke-width="3.5" stroke-linecap="round" class="stroke-orange-500" stroke-dasharray="{{ $pct }} 100" />
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-sm font-extrabold text-orange-600 dark:text-orange-400">{{ $pct }}%</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-orange-600 dark:text-orange-400">{{ $featured->completed_at ? 'Finished' : 'Up next' }}</p>
                            <p class="truncate text-lg font-bold text-gray-900 dark:text-white">{{ $readable($featured->course->title) }}</p>
                            @if($featured->course->instructor)
                                <p class="truncate text-sm text-gray-500 dark:text-zinc-400">with {{ $featured->course->instructor->name }}</p>
                            @endif
                        </div>
                        <span class="hidden shrink-0 items-center gap-1.5 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition group-hover:bg-orange-600 sm:inline-flex">
                            <flux:icon.play variant="solid" class="size-4" /> {{ $featured->completed_at ? 'Review' : 'Continue' }}
                        </span>
                        <flux:icon.chevron-right class="size-5 shrink-0 text-orange-400 sm:hidden" />
                    </a>

                    @if($others->isNotEmpty())
                        <ul class="mt-3 divide-y divide-gray-100 dark:divide-zinc-800">
                            @foreach($others as $enrollment)
                                @php $p = (int) round($enrollment->progress_percentage ?? 0); @endphp
                                <li>
                                    <a href="{{ route('courses.learn', $enrollment->course) }}" wire:navigate class="flex items-center gap-3 py-3 hover:text-orange-600">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-sm font-bold text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">{{ strtoupper(mb_substr($enrollment->course->title, 0, 1)) }}</span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $readable($enrollment->course->title) }}</span>
                                            <span class="mt-1 block h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-800">
                                                <span class="block h-full rounded-full {{ $enrollment->completed_at ? 'bg-emerald-500' : 'bg-orange-500' }}" style="width: {{ $p }}%"></span>
                                            </span>
                                        </span>
                                        <span class="w-10 shrink-0 text-right text-xs font-semibold text-gray-500 dark:text-zinc-400">{{ $enrollment->completed_at ? '✓' : $p.'%' }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <div class="rounded-2xl border border-dashed border-gray-200 p-8 text-center dark:border-zinc-700">
                        <p class="text-3xl">📚</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No courses yet</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">Your trainer will add you to a course soon.</p>
                    </div>
                @endif
            </section>

            {{-- Due soon --}}
            @if($deadlines->isNotEmpty())
                <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 font-bold text-gray-900 dark:text-white">Due soon</h2>
                    <ul class="space-y-2">
                        @foreach($deadlines as $a)
                            @php $daysLeft = (int) now()->startOfDay()->diffInDays($a->due_date->copy()->startOfDay(), false); @endphp
                            <li class="flex items-center gap-3 rounded-xl border border-gray-100 px-4 py-3 dark:border-zinc-800">
                                <span class="flex size-11 shrink-0 flex-col items-center justify-center rounded-xl {{ $daysLeft <= 1 ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                    <span class="text-[10px] font-semibold uppercase leading-none">{{ $a->due_date->format('M') }}</span>
                                    <span class="text-base font-extrabold leading-tight">{{ $a->due_date->format('j') }}</span>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $a->title }}</p>
                                    <p class="truncate text-xs text-gray-500 dark:text-zinc-400">{{ $readable($a->course->title ?? '') }}</p>
                                </div>
                                <span class="shrink-0 text-xs font-semibold {{ $daysLeft <= 1 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-zinc-400' }}">
                                    {{ $daysLeft <= 0 ? 'Today' : ($daysLeft === 1 ? 'Tomorrow' : 'In '.$daysLeft.' days') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Competition --}}
            @if($activeCompetition)
                @php
                    $compAttempt = $activeCompetition->attempts->first();
                    $compDone = $compAttempt && $compAttempt->is_completed;
                @endphp
                <section class="flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-900/15">
                    <span class="text-3xl">🏆</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-wide text-amber-700 dark:text-amber-400">Weekly competition</p>
                        <p class="truncate font-bold text-gray-900 dark:text-white">{{ $activeCompetition->title }}</p>
                        <p class="text-xs text-amber-700 dark:text-amber-400">+{{ $activeCompetition->reward_points }} XP{{ $activeCompetition->competition_ends_at ? ' · ends '.$activeCompetition->competition_ends_at->diffForHumans() : '' }}</p>
                    </div>
                    @if($compDone)
                        <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Done ✓</span>
                    @else
                        <a href="{{ route('daily-challenges.show', $activeCompetition) }}" wire:navigate class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-sm font-bold text-white hover:bg-amber-600">Compete</a>
                    @endif
                </section>
            @endif

            {{-- Challenges --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900 dark:text-white">Today's challenges</h2>
                    <a href="{{ route('daily-challenges.index') }}" wire:navigate class="text-sm font-medium text-orange-600 hover:underline dark:text-orange-400">See all</a>
                </div>
                @if($dailyChallenges->count() > 0)
                    <ul class="grid gap-2 sm:grid-cols-3">
                        @foreach($dailyChallenges as $ch)
                            @php
                                $done = $ch->is_completed;
                                $dlc = strtolower($ch->difficulty_level ?? 'medium');
                                $dlColor = match ($dlc) {
                                    'easy' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                    'hard' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                    default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                };
                            @endphp
                            <li>
                                <a href="{{ route('daily-challenges.show', $ch) }}" wire:navigate
                                   class="flex h-full flex-col rounded-2xl border p-4 transition {{ $done ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-900/15' : 'border-gray-100 hover:border-orange-300 hover:bg-orange-50/50 dark:border-zinc-800 dark:hover:bg-orange-900/10' }}">
                                    <div class="flex items-center justify-between">
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $dlColor }}">{{ ucfirst($dlc) }}</span>
                                        <span class="text-xs font-bold text-orange-600 dark:text-orange-400">+{{ $ch->reward_points }} XP</span>
                                    </div>
                                    <p class="mt-3 line-clamp-2 flex-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $ch->title }}</p>
                                    <p class="mt-3 text-xs font-semibold {{ $done ? 'text-emerald-700 dark:text-emerald-300' : 'text-orange-600 dark:text-orange-400' }}">{{ $done ? 'Completed ✓' : 'Start →' }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="py-4 text-center text-sm text-gray-500 dark:text-zinc-400">No challenges today. Check back tomorrow!</p>
                @endif
            </section>

            {{-- Activity --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900 dark:text-white">Your last 30 days</h2>
                    <span class="text-xs text-gray-500 dark:text-zinc-400">{{ $streak['activeDays'] }} {{ \Illuminate\Support\Str::plural('day', $streak['activeDays']) }} with a finished lesson</span>
                </div>
                <div class="grid grid-cols-10 gap-1.5 sm:grid-cols-15" style="grid-template-columns: repeat(15, minmax(0, 1fr));">
                    @foreach($heatmap as $day)
                        <div title="{{ Carbon::parse($day['date'])->format('D j M') }}: {{ $day['count'] }} {{ \Illuminate\Support\Str::plural('lesson', $day['count']) }}"
                             class="aspect-square rounded-md {{ $heatColors[$day['level']] }} {{ $day['date'] === now()->toDateString() ? 'ring-2 ring-orange-400 ring-offset-1 dark:ring-offset-zinc-900' : '' }}"></div>
                    @endforeach
                </div>
                <div class="mt-2 flex items-center justify-end gap-1.5 text-[11px] text-gray-400">
                    <span>Less</span>
                    @foreach($heatColors as $c)
                        <span class="size-2.5 rounded-sm {{ $c }}"></span>
                    @endforeach
                    <span>More</span>
                </div>
            </section>
        </div>

        <aside class="space-y-5">
            {{-- Class board --}}
            @if(! empty($campLeaderboard['top']))
                <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="font-bold text-gray-900 dark:text-white">Class board</h2>
                        <a href="{{ route('leaderboards.index', array_filter(['period' => 'weekly', 'campId' => $campLeaderboard['campId'] ?? null])) }}" wire:navigate class="text-sm font-medium text-orange-600 hover:underline dark:text-orange-400">Full board</a>
                    </div>
                    <p class="-mt-2 mb-3 text-xs text-gray-500 dark:text-zinc-400">{{ $campLeaderboard['campName'] }} · XP this week</p>
                    <ol class="space-y-1">
                        @foreach($campLeaderboard['top'] as $i => $member)
                            @php $isMe = $member['user_id'] === auth()->id(); @endphp
                            <li class="flex items-center gap-3 rounded-xl px-2 py-1.5 {{ $isMe ? 'bg-orange-50 ring-1 ring-orange-200 dark:bg-orange-900/20 dark:ring-orange-900' : '' }}">
                                <span class="w-6 text-center text-sm font-bold text-gray-400">{{ [0 => '🥇', 1 => '🥈', 2 => '🥉'][$i] ?? $i + 1 }}</span>
                                <span class="size-7 shrink-0"><x-user-avatar :user="$member['user']" size="xs" rounded="full" /></span>
                                <span class="flex-1 truncate text-sm {{ $isMe ? 'font-bold text-orange-700 dark:text-orange-300' : 'text-gray-700 dark:text-zinc-300' }}">{{ $isMe ? 'You' : $member['name'] }}</span>
                                <span class="text-xs font-semibold text-gray-500 dark:text-zinc-400">{{ number_format($member['xp']) }}</span>
                            </li>
                        @endforeach
                    </ol>
                    @if(($campLeaderboard['myRank'] ?? null) && $campLeaderboard['myRank'] > 5)
                        <p class="mt-3 rounded-xl bg-gray-50 px-3 py-2 text-xs text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">You're <span class="font-bold">#{{ $campLeaderboard['myRank'] }}</span>. A lesson or two could move you up!</p>
                    @endif
                </section>
            @endif

            {{-- Results --}}
            @if($recentSubmissions->isNotEmpty())
                <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 font-bold text-gray-900 dark:text-white">My work</h2>
                    <ul class="space-y-2">
                        @foreach($recentSubmissions as $item)
                            <li class="flex items-center gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $item->title }}</p>
                                    <p class="truncate text-xs text-gray-500 dark:text-zinc-400">{{ $readable($item->course->title ?? '') }}</p>
                                </div>
                                @if($item->status === 'graded' && $item->score !== null)
                                    <span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ rtrim(rtrim(number_format((float) $item->score, 1), '0'), '.') }}/{{ rtrim(rtrim(number_format((float) $item->max_score, 1), '0'), '.') }}</span>
                                @else
                                    <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">Being marked</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Badges --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900 dark:text-white">Badges</h2>
                    <span class="text-xs text-gray-500 dark:text-zinc-400">{{ $stats['totalBadges'] }} earned</span>
                </div>
                @if($recentBadges->isEmpty())
                    <div class="py-4 text-center">
                        <p class="text-3xl">🔒</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">Finish lessons to unlock your first badge!</p>
                    </div>
                @else
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($recentBadges->take(6) as $badge)
                            @php
                                $badgeColor = $badge->color ?: '#F97316';
                                $badgeIsEmoji = $badge->icon && preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $badge->icon);
                            @endphp
                            <div class="flex flex-col items-center gap-1 text-center" title="{{ $badge->name }}: {{ $badge->description }}">
                                <span class="flex size-12 items-center justify-center rounded-2xl text-2xl"
                                      style="color: {{ $badgeColor }}; background-color: {{ $badgeColor }}1f; box-shadow: inset 0 0 0 2px {{ $badgeColor }}40">
                                    @if($badgeIsEmoji)
                                        {{ $badge->icon }}
                                    @else
                                        <x-badge-icon :icon="$badge->icon ?: 'trophy'" class="size-6" />
                                    @endif
                                </span>
                                <span class="line-clamp-2 text-[11px] leading-tight text-gray-600 dark:text-zinc-400">{{ $badge->name }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if($stats['totalBadges'] > 6)
                        <p class="mt-3 text-center text-xs text-orange-600 dark:text-orange-400">+{{ $stats['totalBadges'] - 6 }} more on your profile</p>
                    @endif
                @endif
            </section>

            {{-- Certificates --}}
            @if($recentCertificates->count() > 0)
                <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-3 font-bold text-gray-900 dark:text-white">Certificates</h2>
                    <ul class="space-y-2">
                        @foreach($recentCertificates as $cert)
                            <li>
                                <a href="{{ route('certificates.show', $cert) }}" wire:navigate class="flex items-center gap-3 rounded-xl px-1 py-1 hover:bg-gray-50 dark:hover:bg-zinc-800">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-lg dark:bg-amber-900/30">🎓</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $readable($cert->course->title ?? 'Course') }}</span>
                                        <span class="block text-xs text-gray-500 dark:text-zinc-400">{{ Carbon::parse($cert->issued_at)->format('M Y') }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</div>
