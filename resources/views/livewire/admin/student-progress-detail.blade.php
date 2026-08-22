<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">

    {{-- Header --}}
    <div class="border-b-4 border-blue-600 bg-orange-600">
        <div class="mx-auto max-w-6xl px-4 py-5 sm:px-6">
            <flux:button href="{{ route('admin.student-progress.index') }}" wire:navigate variant="ghost" size="sm"
                class="!text-orange-100 hover:!bg-orange-700 hover:!text-white">
                ← Back to report
            </flux:button>
            <h1 class="mt-2 text-xl font-bold text-white">
                {{ $detail['profile']->full_name ?: $detail['profile']->user?->name }}
            </h1>
            <p class="mt-0.5 text-sm text-orange-100">
                ID {{ $detail['profile']->student_id ?: $detail['profile']->user?->student_id ?: 'N/A' }}
                · {{ $detail['profile']->class_grade ?: 'Unassigned' }}
                · {{ strtoupper($detail['profile']->program_type ?: 'N/A') }}
                @if($detail['profile']->school)
                    · {{ $detail['profile']->school->name }}
                @endif
            </p>
        </div>
    </div>

    <div class="mx-auto max-w-6xl space-y-4 px-4 py-5 sm:px-6">

        @if(session()->has('message'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
                {{ session('message') }}
            </div>
        @endif

        {{-- Summary --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach([
                ['label' => 'Courses', 'value' => $detail['summary']['courses_enrolled']],
                ['label' => 'Lessons Done', 'value' => $detail['summary']['lessons_completed']],
                ['label' => 'Completion', 'value' => number_format($detail['summary']['completion_rate'], 1) . '%'],
                ['label' => 'Assessments', 'value' => $detail['summary']['assessments_attempted']],
                ['label' => 'Avg Score', 'value' => $detail['summary']['assessments_attempted'] > 0 ? number_format($detail['summary']['avg_assessment_score'], 1) . '%' : '—'],
                ['label' => 'Badges', 'value' => $detail['summary']['badges_earned']],
            ] as $stat)
                <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        @if($detail['summary']['last_activity_at'])
            <p class="text-xs text-slate-500">
                Last activity {{ \Illuminate\Support\Carbon::parse($detail['summary']['last_activity_at'])->diffForHumans() }}
            </p>
        @endif

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

            {{-- Course progress --}}
            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-slate-200 px-4 py-2.5 dark:border-zinc-700">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Course Progress</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse($detail['courseProgress'] as $enrollment)
                        @php
                            $progress = (float) ($enrollment->completed_at ? 100 : ($enrollment->progress_percentage ?? 0));
                        @endphp
                        <div class="px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $enrollment->course?->title ?: 'Course unavailable' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ (int) ($enrollment->lessons_completed ?? 0) }} lessons
                                        · {{ (int) ($enrollment->assessments_completed ?? 0) }} assessments
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ number_format($progress, 0) }}%</p>
                                    @if($enrollment->completed_at)
                                        <p class="text-xs text-emerald-600">Completed</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full {{ $progress >= 70 ? 'bg-emerald-500' : ($progress >= 40 ? 'bg-amber-500' : 'bg-orange-500') }}"
                                    style="width: {{ min(100, max(0, $progress)) }}%"></div>
                            </div>
                            @if(! $enrollment->completed_at)
                                <div class="mt-2 flex justify-end">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        x-data
                                        @click="if (confirm('Mark completed and award 50% completion XP (50 XP)?')) { $wire.markCourseCompletedHalfXp({{ $enrollment->id }}) }"
                                    >
                                        Mark completed (50% XP)
                                    </flux:button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="px-4 py-8 text-center text-sm text-slate-500">No enrollments yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Assessment breakdown --}}
            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-slate-200 px-4 py-2.5 dark:border-zinc-700">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Assessment Breakdown</h2>
                </div>
                <div class="max-h-96 divide-y divide-slate-100 overflow-y-auto dark:divide-zinc-800">
                    @forelse($detail['assessmentBreakdown'] as $row)
                        <div class="px-4 py-2.5">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $row->assessment_title ?: ('Assessment #' . $row->assessment_id) }}</p>
                            <p class="text-xs text-slate-500">
                                {{ ucfirst(str_replace('_', ' ', (string) $row->assessment_type)) }}
                                · {{ $row->course_title ?: 'Unlinked' }}
                            </p>
                            <div class="mt-1 flex gap-4 text-xs text-slate-600 dark:text-slate-400">
                                <span>{{ (int) $row->attempts }} attempts</span>
                                <span>Best {{ number_format((float) $row->best_score, 1) }}%</span>
                                <span>Avg {{ number_format((float) $row->average_score, 1) }}%</span>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-8 text-center text-sm text-slate-500">No completed assessments yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Badges --}}
            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-slate-200 px-4 py-2.5 dark:border-zinc-700">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Badges</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse($detail['badges'] as $badge)
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $badge->name }}</p>
                                <p class="text-xs text-slate-500">{{ $badge->slug }}</p>
                            </div>
                            <p class="text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($badge->earned_at)->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="px-4 py-8 text-center text-sm text-slate-500">No badges earned.</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent activity --}}
            <div class="rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-slate-200 px-4 py-2.5 dark:border-zinc-700">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Recent Activity</h2>
                </div>
                <div class="max-h-96 divide-y divide-slate-100 overflow-y-auto dark:divide-zinc-800">
                    @forelse($detail['recentActivity'] as $activity)
                        <div class="px-4 py-2.5">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $activity->course?->title ?: 'General' }}
                                @if($activity->lesson)
                                    · {{ $activity->lesson->title }}
                                @endif
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="px-4 py-8 text-center text-sm text-slate-500">No activity recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
