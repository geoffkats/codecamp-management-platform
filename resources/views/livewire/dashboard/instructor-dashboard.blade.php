@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = \Illuminate\Support\Str::of($user->name)->before(' ');
    $readable = fn (?string $text) => $text !== null && $text === mb_strtoupper($text) && preg_match('/\p{L}{4,}/u', $text)
        ? \Illuminate\Support\Str::title(mb_strtolower($text))
        : $text;

    $can = fn (string $ability) => \Illuminate\Support\Facades\Gate::allows($ability);
    $quickActions = array_values(array_filter([
        $can('edit_courses') ? ['New assessment', 'clipboard-document-list', route('assessments.create')] : null,
        $can('edit_courses') ? ['Question bank', 'rectangle-stack', route('questions.index')] : null,
        ['Lesson locks', 'lock-closed', route('lessons.locks')],
        $can('view_teacher_code') ? ['Attendance code', 'qr-code', route('attendance.code')] : null,
        $can('award_course_xp') ? ['Award XP', 'sparkles', route('admin.xp-manager')] : null,
        ['Daily report', 'document-text', route('daily-reports.submit')],
        $can('manage_challenges') ? ['New challenge', 'bolt', route('daily-challenges.create')] : null,
        ['Leaderboard', 'trophy', route('leaderboards.index')],
    ]));
@endphp

<div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6">
    <livewire:daily-reports.optional-reminder-banner />

    {{-- Greeting --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ now()->format('l, j F') }}</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $greeting }}, {{ $firstName }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-zinc-400">
                @if($pendingGradingCount > 0)
                    You have <span class="font-semibold text-gray-900 dark:text-white">{{ $pendingGradingCount }} {{ \Illuminate\Support\Str::plural('submission', $pendingGradingCount) }}</span> to mark.
                @else
                    You're all caught up on marking.
                @endif
                @if($pendingApprovals->isNotEmpty())
                    {{ $pendingApprovals->count() }} {{ \Illuminate\Support\Str::plural('item', $pendingApprovals->count()) }} waiting for approval.
                @endif
            </p>
        </div>
        <a href="{{ route('submissions.index', ['filter' => 'pending']) }}" wire:navigate
           class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600 sm:self-auto">
            <flux:icon.pencil-square class="size-4" />
            Mark submissions
            @if($pendingGradingCount > 0)
                <span class="rounded-full bg-white/25 px-2 py-0.5 text-xs font-bold">{{ $pendingGradingCount }}</span>
            @endif
        </a>
    </header>

    {{-- Key numbers --}}
    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Summary">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-zinc-400">
                <span class="flex size-8 items-center justify-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400"><flux:icon.book-open class="size-4" /></span>
                My courses
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['totalCourses'] }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">{{ $stats['publishedCourses'] }} published</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-zinc-400">
                <span class="flex size-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400"><flux:icon.users class="size-4" /></span>
                Students
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['students']) }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">{{ $stats['activeLearners'] }} learning right now</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-zinc-400">
                <span class="flex size-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400"><flux:icon.chart-bar class="size-4" /></span>
                Average progress
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['averageProgress'] }}%</p>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-800">
                <div class="h-full rounded-full bg-violet-500" style="width: {{ min(100, $stats['averageProgress']) }}%"></div>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-zinc-400">
                <span class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"><flux:icon.academic-cap class="size-4" /></span>
                Finished a course
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['completionRate'] }}%</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">{{ $stats['completed'] }} {{ \Illuminate\Support\Str::plural('enrollment', $stats['completed']) }} completed</p>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- To mark --}}
            <section class="rounded-2xl border border-gray-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-zinc-800">
                    <div>
                        <h2 class="font-semibold text-gray-900 dark:text-white">To mark</h2>
                        <p class="text-xs text-gray-500 dark:text-zinc-400">Newest submissions first</p>
                    </div>
                    <a href="{{ route('submissions.index') }}" wire:navigate class="text-sm font-medium text-orange-600 hover:underline dark:text-orange-400">All submissions</a>
                </div>

                @if($recentSubmissions->isNotEmpty())
                    <ul class="divide-y divide-gray-100 dark:divide-zinc-800">
                        @foreach($recentSubmissions as $item)
                            <li class="flex items-center gap-3 px-5 py-3" wire:key="sub-{{ $item->type }}-{{ $item->id }}">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ strtoupper(mb_substr($item->studentName, 0, 1)) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $item->studentName }}
                                        <span class="font-normal text-gray-500 dark:text-zinc-400">· {{ $item->title }}</span>
                                    </p>
                                    <p class="truncate text-xs text-gray-500 dark:text-zinc-400">
                                        {{ $item->typeLabel }} · {{ $readable($item->courseTitle) }}@if($item->submittedAt) · {{ $item->submittedAt->diffForHumans() }}@endif
                                    </p>
                                </div>
                                @can('grade_submissions')
                                    <a href="{{ route('grades.grade', $item->submission) }}" wire:navigate
                                       class="shrink-0 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 hover:bg-orange-100 dark:bg-orange-900/30 dark:text-orange-300">Mark</a>
                                @else
                                    <a href="{{ route('submissions.show', ['submissionId' => $item->id, 'type' => $item->type]) }}" wire:navigate
                                       class="shrink-0 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:text-zinc-200">View</a>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                    @if($pendingGradingCount > $recentSubmissions->count())
                        <a href="{{ route('submissions.index', ['filter' => 'pending']) }}" wire:navigate
                           class="block border-t border-gray-100 px-5 py-3 text-center text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-800/60">
                            See all {{ $pendingGradingCount }} waiting
                        </a>
                    @endif
                @else
                    <div class="flex flex-col items-center px-5 py-10 text-center">
                        <span class="flex size-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"><flux:icon.check class="size-6" /></span>
                        <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">Nothing to mark</p>
                        <p class="mt-1 max-w-sm text-xs text-gray-500 dark:text-zinc-400">When students hand in assignments or written quiz answers, they'll appear here.</p>
                    </div>
                @endif
            </section>

            {{-- Courses --}}
            <section>
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900 dark:text-white">My courses</h2>
                    <a href="{{ route('courses.index') }}" wire:navigate class="text-sm font-medium text-orange-600 hover:underline dark:text-orange-400">View all</a>
                </div>

                @if($courses->isNotEmpty())
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($courses as $course)
                            @php $progress = (int) round($course->enrollments_avg_progress_percentage ?? 0); @endphp
                            <article wire:key="course-{{ $course->id }}" class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-4 transition hover:border-orange-200 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-orange-900">
                                <div class="flex items-start gap-3">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 font-bold text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                                        {{ strtoupper(mb_substr($course->title, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('courses.show', $course) }}" wire:navigate class="block truncate font-semibold text-gray-900 group-hover:text-orange-600 dark:text-white">{{ $readable($course->title) }}</a>
                                        <p class="text-xs text-gray-500 dark:text-zinc-400">
                                            {{ $course->modules_count }} {{ \Illuminate\Support\Str::plural('module', $course->modules_count) }} · {{ $course->lessons_count }} {{ \Illuminate\Support\Str::plural('lesson', $course->lessons_count) }} · {{ $course->enrollments_count }} {{ \Illuminate\Support\Str::plural('student', $course->enrollments_count) }}
                                        </p>
                                    </div>
                                    @if($course->approval_status === 'pending')
                                        <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">In review</span>
                                    @elseif(! $course->is_published)
                                        <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-zinc-800 dark:text-zinc-300">Draft</span>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-zinc-400">
                                        <span>Class progress</span>
                                        <span class="font-medium text-gray-700 dark:text-zinc-300">{{ $progress }}%</span>
                                    </div>
                                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-800">
                                        <div class="h-full rounded-full bg-orange-500" style="width: {{ min(100, $progress) }}%"></div>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-3 text-xs font-medium dark:border-zinc-800">
                                    <a href="{{ route('courses.show', $course) }}" wire:navigate class="rounded-lg px-2.5 py-1.5 text-gray-700 hover:bg-gray-100 dark:text-zinc-200 dark:hover:bg-zinc-800">Open</a>
                                    @can('edit_courses')
                                        <a href="{{ route('curriculum.builder', $course) }}" wire:navigate class="rounded-lg px-2.5 py-1.5 text-gray-700 hover:bg-gray-100 dark:text-zinc-200 dark:hover:bg-zinc-800">Builder</a>
                                    @endcan
                                    <a href="{{ route('courses.preview', $course) }}" class="ml-auto inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-orange-600 hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-900/20">
                                        <flux:icon.eye class="size-3.5" /> Preview
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if($courses->hasPages())
                        <div class="mt-4">{{ $courses->links() }}</div>
                    @endif
                @else
                    <div class="rounded-2xl border border-dashed border-gray-300 p-10 text-center dark:border-zinc-700">
                        <flux:icon.book-open class="mx-auto size-8 text-gray-400" />
                        <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No courses yet</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-zinc-400">Ask an admin to assign you a course, or create one.</p>
                        @can('create_courses')
                            <a href="{{ route('courses.create') }}" wire:navigate class="mt-4 inline-flex rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">Create course</a>
                        @endcan
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            {{-- Quick actions --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="px-1 font-semibold text-gray-900 dark:text-white">Quick actions</h2>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach($quickActions as [$label, $icon, $url])
                        <a href="{{ $url }}" wire:navigate
                           class="flex items-center gap-2 rounded-xl border border-gray-100 px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700 dark:border-zinc-800 dark:text-zinc-200 dark:hover:border-orange-900 dark:hover:bg-orange-900/20 dark:hover:text-orange-300">
                            <flux:icon :name="$icon" class="size-4 shrink-0 text-gray-400" />
                            <span class="truncate">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Waiting for approval --}}
            @if($pendingApprovals->isNotEmpty())
                <section class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="px-1 font-semibold text-gray-900 dark:text-white">Waiting for approval</h2>
                    <p class="px-1 text-xs text-gray-500 dark:text-zinc-400">A supervisor will review these</p>
                    <ul class="mt-3 space-y-1">
                        @foreach($pendingApprovals as $approval)
                            <li class="flex items-center gap-3 rounded-xl px-1 py-1.5">
                                <span class="size-2 shrink-0 rounded-full bg-amber-400"></span>
                                <div class="min-w-0 flex-1">
                                    @can('review_content')
                                        <a href="{{ route('content-approvals.review', $approval) }}" wire:navigate class="block truncate text-sm text-gray-800 hover:text-orange-600 dark:text-zinc-200">{{ $readable($approval->approvable?->title ?? 'Removed item') }}</a>
                                    @else
                                        <p class="truncate text-sm text-gray-800 dark:text-zinc-200">{{ $readable($approval->approvable?->title ?? 'Removed item') }}</p>
                                    @endcan
                                    <p class="text-xs text-gray-500 dark:text-zinc-400">{{ class_basename($approval->approvable_type) }}@if($approval->submitted_at) · {{ $approval->submitted_at->diffForHumans() }}@endif</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- New students --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="px-1 font-semibold text-gray-900 dark:text-white">New students</h2>
                @if($recentEnrollments->isNotEmpty())
                    <ul class="mt-3 space-y-1">
                        @foreach($recentEnrollments as $enrollment)
                            <li class="flex items-center gap-3 px-1 py-1.5">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-sky-50 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">
                                    {{ strtoupper(mb_substr($enrollment->user?->name ?? '?', 0, 1)) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm text-gray-800 dark:text-zinc-200">{{ $enrollment->user?->name ?? 'Student' }}</p>
                                    <p class="truncate text-xs text-gray-500 dark:text-zinc-400">{{ $readable($enrollment->course?->title) }}</p>
                                </div>
                                @if($enrollment->enrolled_at)
                                    <span class="shrink-0 text-[11px] text-gray-400">{{ $enrollment->enrolled_at->diffForHumans(null, true) }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 px-1 text-sm text-gray-500 dark:text-zinc-400">No enrollments yet.</p>
                @endif
            </section>
        </aside>
    </div>
</div>
