<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Assignments</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                @if($isTeacher) View and grade student assignment submissions.
                @else Assignments from your enrolled courses.
                @endif
            </p>
        </div>
        @if($isTeacher)
            <a href="{{ route('assessments.create') }}?type=assignment" wire:navigate
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                New Assignment
            </a>
        @endif
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-48">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search assignments…"
                   class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
        </div>
        @if(!$isTeacher)
            <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-xl p-1">
                @foreach(['all' => 'All', 'pending' => 'To Do', 'submitted' => 'Submitted'] as $val => $lbl)
                    <button type="button" wire:click="$set('filter', '{{ $val }}')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors
                                {{ $filter === $val
                                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- List --}}
    @if($assignments->count() > 0)
        <div class="space-y-3">
            @foreach($assignments as $assignment)
                @php
                    $attempt = $assignment->myAttempt ?? null;
                    $isSubmitted = $attempt && $attempt->completed_at;
                    $isPending   = $isSubmitted && $attempt->score === null;
                    $isGraded    = $isSubmitted && $attempt->score !== null;
                    $maxScore    = $attempt?->maxScore() ?? ($assignment->max_points ?? 100);
                    $pct         = $isGraded ? $attempt?->scorePercentage() : null;
                @endphp
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md hover:border-orange-200 dark:hover:border-orange-800 transition-all">
                    <div class="flex items-center gap-4 p-4">

                        {{-- Status dot --}}
                        <div class="w-2.5 h-2.5 rounded-full flex-shrink-0
                            {{ $isTeacher ? ($assignment->pendingCount > 0 ? 'bg-amber-400' : 'bg-green-400') :
                               ($isGraded ? 'bg-green-400' : ($isPending ? 'bg-amber-400 animate-pulse' : 'bg-gray-300 dark:bg-gray-600')) }}">
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white leading-tight">{{ $assignment->title }}</p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                <span>{{ $assignment->course->title }}</span>
                                @if($assignment->lesson)
                                    <span>· {{ $assignment->lesson->title }}</span>
                                @endif
                                @if($assignment->is_required)
                                    <span class="text-red-500 font-semibold">Required</span>
                                @endif
                            </div>
                        </div>

                        {{-- Teacher view: submission count --}}
                        @if($isTeacher)
                            <div class="text-center flex-shrink-0">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $assignment->submissionCount }}</p>
                                <p class="text-xs text-gray-400">submitted</p>
                            </div>
                            @if($assignment->pendingCount > 0)
                                <span class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    {{ $assignment->pendingCount }} to grade
                                </span>
                            @endif
                        @else
                            {{-- Student view: status badge --}}
                            @if($isGraded)
                                <div class="text-center flex-shrink-0">
                                    <p class="text-xl font-bold {{ $attempt->is_passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $pct }}%</p>
                                    <p class="text-xs {{ $attempt->is_passed ? 'text-green-500' : 'text-red-500' }}">{{ $attempt->is_passed ? 'Passed' : 'Failed' }}</p>
                                </div>
                            @elseif($isPending)
                                <span class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Awaiting grade
                                </span>
                            @else
                                <span class="flex-shrink-0 inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                    Not submitted
                                </span>
                            @endif
                        @endif

                        {{-- Action button --}}
                        @if($isTeacher)
                            <a href="{{ route('assessments.show', $assignment) }}" wire:navigate
                               class="flex-shrink-0 px-4 py-2 text-sm font-bold border border-orange-300 dark:border-orange-700 text-orange-600 dark:text-orange-400 rounded-xl hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors">
                                View Submissions
                            </a>
                        @elseif($isGraded || $isPending)
                            <a href="{{ route('assessments.results', [$assignment, $attempt]) }}" wire:navigate
                               class="flex-shrink-0 px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                View
                            </a>
                        @else
                            <a href="{{ route('assessments.take', $assignment) }}" wire:navigate
                               class="flex-shrink-0 px-4 py-2 text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition-colors shadow-sm">
                                Submit
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($assignments->hasPages())
            <div>{{ $assignments->links() }}</div>
        @endif

    @else
        <div class="text-center py-16 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800">
            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="font-semibold text-gray-500 dark:text-gray-400">
                @if($search) No assignments match "{{ $search }}"
                @elseif($filter === 'submitted') You haven't submitted any assignments yet.
                @elseif($filter === 'pending') All assignments are done — great work!
                @else No assignments found.
                @endif
            </p>
            @if($search || $filter !== 'all')
                <button type="button" wire:click="$set('search', ''); $set('filter', 'all')"
                        class="mt-3 text-sm text-orange-600 dark:text-orange-400 font-semibold hover:underline">
                    Clear filters
                </button>
            @endif
        </div>
    @endif

</div>
