<div class="min-h-screen bg-gray-50 dark:bg-gray-950" wire:key="leaderboards-root">
@php
use App\Livewire\Leaderboards\Index as LeaderboardIndex;

$authUser = Auth::user();
$authPoints = $authUser?->points;
$authLevelGoal = $authPoints->points_to_next_level ?? 1000;
$authLevelPoints = $authPoints ? ($authPoints->total_points ?? 0) % $authLevelGoal : 0;
$authLevelProgress = $authLevelGoal > 0 ? min(($authLevelPoints / $authLevelGoal) * 100, 100) : 0;

$podiumOrder = [2, 1, 3];
$podiumStyle = [
    1 => ['h' => 'h-44', 'avatar' => 'w-20 h-20 text-2xl', 'ring' => 'ring-orange-600', 'bar' => 'bg-orange-600', 'badge' => 'bg-orange-600', 'lift' => '-translate-y-4'],
    2 => ['h' => 'h-32', 'avatar' => 'w-16 h-16 text-lg', 'ring' => 'ring-slate-400', 'bar' => 'bg-slate-400', 'badge' => 'bg-slate-500', 'lift' => ''],
    3 => ['h' => 'h-24', 'avatar' => 'w-16 h-16 text-lg', 'ring' => 'ring-blue-600', 'bar' => 'bg-blue-600', 'badge' => 'bg-blue-600', 'lift' => ''],
];
@endphp

    {{-- Hero — solid orange (700 = dark but clearly orange) --}}
    <div class="bg-orange-700 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-blue-700 text-xs font-bold uppercase tracking-widest mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        XP Rankings
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight">Leaderboard</h1>
                    <p class="mt-2 text-orange-100 max-w-xl">
                        @if($isStaff)
                            This Week and This Month rank XP earned in the selected camp or course. All Time is career XP.
                        @else
                            This Week and This Month count only XP from your current class and camp. Going back to a finished course does not count. All Time is career XP.
                        @endif
                    </p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full lg:w-auto">
                    @foreach([
                        ['label' => 'Students', 'value' => number_format($stats['participants']), 'bg' => 'bg-blue-700', 'icon' => 'users'],
                        ['label' => 'Total XP', 'value' => number_format($stats['totalXp']), 'bg' => 'bg-blue-600', 'icon' => 'bolt'],
                        ['label' => 'Average', 'value' => number_format($stats['avgXp']), 'bg' => 'bg-blue-700', 'icon' => 'chart'],
                        ['label' => 'Top Score', 'value' => number_format($stats['topXp']), 'bg' => 'bg-blue-600', 'icon' => 'trophy'],
                    ] as $stat)
                    <div class="rounded-xl {{ $stat['bg'] }} px-4 py-3 text-center">
                        <div class="flex justify-center mb-1">
                            @if($stat['icon'] === 'users')
                            <svg class="w-5 h-5 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            @elseif($stat['icon'] === 'bolt')
                            <svg class="w-5 h-5 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            @elseif($stat['icon'] === 'chart')
                            <svg class="w-5 h-5 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            @else
                            <svg class="w-5 h-5 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            @endif
                        </div>
                        <div class="text-xl font-black leading-tight">{{ $stat['value'] }}</div>
                        <div class="text-[10px] uppercase tracking-wide text-blue-100">{{ $stat['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="h-1 bg-blue-700"></div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-6">

        @if($currentUserRank)
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="h-1 bg-orange-600"></div>
            <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-orange-600 flex items-center justify-center text-2xl font-black text-white">
                        #{{ $currentUserRank['rank'] }}
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Your Rank</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ number_format($currentUserRank['points']) }} {{ $pointsLabel }} XP
                            <span class="text-sm font-normal text-gray-500">· Level {{ $currentUserRank['level'] ?? 1 }} · {{ $currentUserRank['levelName'] ?? 'Beginner' }}</span>
                        </p>
                        <p class="text-xs text-gray-500">of {{ number_format($totalActiveUsers) }} students</p>
                    </div>
                </div>
                <div class="flex-1 sm:max-w-xs sm:ml-auto">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Next level</span>
                        <span>{{ number_format($authLevelPoints) }} / {{ number_format($authLevelGoal) }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-orange-600 transition-all" style="width: {{ $authLevelProgress }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Filters --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 sm:p-5 shadow-sm space-y-4">
            @if($isStaff)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Search student</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name…"
                               class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 pl-9 pr-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-600 outline-none"/>
                    </div>
                </div>
                @if($camps->isNotEmpty())
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Code Camp</label>
                    <select wire:model.live="campId"
                            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-600 outline-none">
                        <option value="">All camps</option>
                        @foreach($camps as $camp)
                        <option value="{{ $camp->id }}">{{ $camp->name }} ({{ $camp->status }})</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($clubs->isNotEmpty())
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Code Club</label>
                    <select wire:model.live="clubId"
                            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-600 outline-none">
                        <option value="">All clubs</option>
                        @foreach($clubs as $club)
                        <option value="{{ $club->id }}">{{ $club->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Course</label>
                    <select wire:model.live="courseId"
                            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-600 outline-none">
                        <option value="">All courses</option>
                        @forelse($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @empty
                        <option value="" disabled>No courses available</option>
                        @endforelse
                    </select>
                </div>
            </div>
            @if(! $campId && ! $courseId && ! $clubId)
            <p class="text-xs text-amber-700 dark:text-amber-300">
                Pick a camp and/or course above to open that class leaderboard.
            </p>
            @endif
            @endif

            @unless($isStaff)
            <div class="rounded-lg border border-orange-200 dark:border-orange-900/50 bg-orange-50 dark:bg-orange-950/20 px-4 py-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-orange-700 dark:text-orange-300 mb-1">Your class board</p>
                @if($selectedCourseTitle)
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $selectedCourseTitle }}</p>
                @elseif($courses->isNotEmpty())
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $courses->first()->title }}</p>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-300">No active class enrollment found.</p>
                @endif
                @if($courses->count() > 1)
                <div class="mt-3">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Switch course</label>
                    <select wire:model.live="courseId"
                            class="w-full sm:max-w-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-600 outline-none">
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            @endunless

            <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                <div class="flex flex-wrap gap-2">
                    <button wire:click="filterByType('overall')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold transition {{ $leaderboardType === 'overall' ? 'bg-orange-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-orange-50 dark:hover:bg-orange-950/30' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        XP
                    </button>
                    <button wire:click="filterByType('level')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold transition {{ $leaderboardType === 'level' ? 'bg-orange-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-orange-50 dark:hover:bg-orange-950/30' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        Level
                    </button>
                </div>
                <div class="flex flex-wrap gap-2 lg:ml-auto">
                    @foreach(['all' => 'All Time (career)', 'monthly' => 'This Month', 'weekly' => 'This Week'] as $val => $label)
                    <button wire:click="filterByPeriod('{{ $val }}')"
                            class="px-4 py-2 rounded-lg text-xs font-bold transition {{ $period === $val ? 'bg-blue-700 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-950/30' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
                @if($search || $campId || $clubId || ($isStaff && $courseId) || $period !== 'all' || $leaderboardType !== 'overall')
                <button wire:click="clearFilters" class="text-xs text-gray-400 hover:text-red-500 underline lg:ml-2">Reset</button>
                @endif
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @if($scopeCaption)
                    <span class="inline-flex items-center gap-1.5 font-semibold text-gray-700 dark:text-gray-200">{{ $scopeCaption }}</span>
                    <span class="mx-1">·</span>
                @endif
                @if($usesScopedXp)
                    Rankings use <strong>{{ $pointsLabel }} XP</strong> from the class they are in now.
                    Finishing an old course or going back to a previous one does not count here.
                    Career totals still appear as a smaller number on each row.
                @else
                    All Time is career XP across every course.
                    @unless($isStaff)
                        This Week and This Month stay inside your current camp and class.
                    @endunless
                @endif
            </p>
        </div>

        {{-- Podium --}}
        @if($topThree->count() >= 1 && ($topThree->first()['points'] ?? 0) > 0)
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-2 mb-6">
                <span class="w-1 h-6 bg-orange-600 rounded-full"></span>
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                <h2 class="text-lg font-black text-gray-900 dark:text-white">Top Performers</h2>
            </div>

            <div class="hidden md:flex items-end justify-center gap-4 lg:gap-8 min-h-[20rem] pb-2">
                @foreach($podiumOrder as $place)
                    @php
                        $entry = $topThree->firstWhere('rank', $place);
                        $style = $podiumStyle[$place];
                    @endphp
                    @if($entry)
                    <div class="flex flex-col items-center w-40 lg:w-48 {{ $style['lift'] }}">
                        <div class="mb-2 w-8 h-8 rounded-full {{ $style['badge'] }} text-white text-sm font-black flex items-center justify-center">
                            {{ $place }}
                        </div>
                        <x-user-avatar :user="$entry['user']" size="xl" class="ring-4 {{ $style['ring'] }}" />
                        <p class="mt-3 text-sm font-bold text-gray-900 dark:text-white text-center truncate w-full px-1">
                            {{ $entry['user']->name ?? 'Student' }}
                        </p>
                        <p class="text-xs font-semibold" style="color: {{ $entry['levelColor'] }}">
                            Lv {{ $entry['level'] }} · {{ $entry['levelName'] }}
                        </p>
                        <p class="text-sm font-black text-orange-600 dark:text-orange-400 mt-0.5">{{ number_format($entry['points']) }} {{ $pointsLabel }} XP</p>
                        @if($entry['badgeCount'] > 0)
                        <span class="mt-1 inline-flex items-center gap-1 text-[10px] text-gray-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            {{ $entry['badgeCount'] }} badges
                        </span>
                        @endif
                        <div class="mt-4 w-full {{ $style['h'] }} rounded-t-xl {{ $style['bar'] }} flex items-end justify-center pb-3">
                            <span class="text-3xl font-black text-white/90">{{ $place }}</span>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            <div class="md:hidden space-y-3">
                @foreach($topThree as $entry)
                @php $style = $podiumStyle[$entry['rank']] ?? $podiumStyle[3]; @endphp
                <div class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                    <div class="w-8 h-8 rounded-full {{ $style['badge'] }} text-white text-sm font-black flex items-center justify-center flex-shrink-0">
                        {{ $entry['rank'] }}
                    </div>
                    <x-user-avatar :user="$entry['user']" size="md" class="ring-2 {{ $style['ring'] }}" />
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 dark:text-white truncate">{{ $entry['user']->name }}</p>
                        <p class="text-xs text-gray-500">Lv {{ $entry['level'] }} · {{ $entry['levelName'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-black text-orange-600 dark:text-orange-400">{{ number_format($entry['points']) }}</p>
                        <p class="text-[10px] text-gray-400">{{ $pointsLabel }} XP</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Rankings list --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50">
                <h2 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                    <span class="w-1 h-4 bg-blue-700 rounded-full"></span>
                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    All Rankings
                </h2>
                <span class="text-xs text-gray-400">{{ $leaderboard->total() }} students</span>
            </div>

            @if($leaderboard->isEmpty())
            <div class="py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-sm">No students match these filters yet.</p>
            </div>
            @else
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($leaderboard as $entry)
                @php
                    $userPoint = $entry->userPoint;
                    $isMe = Auth::id() === $userPoint->user_id;
                    $isTop3 = $entry->rank <= 3;
                    $rankColors = [1 => 'bg-orange-600', 2 => 'bg-slate-400', 3 => 'bg-blue-600'];
                @endphp
                <div class="flex items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3.5 transition {{ $isMe ? 'bg-orange-50 dark:bg-orange-950/30 border-l-4 border-orange-600' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                    <div class="w-10 flex-shrink-0 flex justify-center">
                        @if($isTop3)
                        <div class="w-7 h-7 rounded-full {{ $rankColors[$entry->rank] }} text-white text-xs font-black flex items-center justify-center">
                            {{ $entry->rank }}
                        </div>
                        @else
                        <span class="text-sm font-black text-gray-400">#{{ $entry->rank }}</span>
                        @endif
                    </div>

                    <x-user-avatar :user="$userPoint->user" size="sm" />

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-bold text-sm text-gray-900 dark:text-white truncate">
                                {{ $userPoint->user->name ?? 'Student' }}
                            </p>
                            @if($isMe)
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-orange-600 text-white">YOU</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded text-white" style="background-color: {{ $entry->levelColor }}">
                                Lv {{ $entry->level }} · {{ $entry->levelName }}
                            </span>
                            @if($entry->badgeCount > 0)
                            <span class="inline-flex items-center gap-0.5 text-[10px] text-gray-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                {{ $entry->badgeCount }}
                            </span>
                            @endif
                        </div>
                        <div class="mt-1.5 h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 max-w-[160px] overflow-hidden">
                            <div class="h-full rounded-full bg-orange-600" style="width: {{ $entry->levelProgress }}%"></div>
                        </div>
                    </div>

                    <div class="text-right flex-shrink-0">
                        <p class="text-base font-black text-orange-600 dark:text-orange-400">{{ number_format($entry->points) }}</p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">{{ $pointsLabel }} XP</p>
                        @if($usesScopedXp && ($entry->careerPoints ?? 0) !== $entry->points)
                            <p class="text-[10px] text-gray-400">Career {{ number_format($entry->careerPoints) }}</p>
                        @endif
                    </div>

                    @if($isStaff && $entry->profileId)
                    <a href="{{ route('students.show', $entry->profileId) }}" wire:navigate
                       class="hidden sm:inline-flex flex-shrink-0 items-center gap-1 px-3 py-1.5 rounded-lg text-[11px] font-bold text-white bg-blue-700 hover:bg-blue-600 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if($leaderboard->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800">
                {{ $leaderboard->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
