<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Daily Reports</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Review instructor submissions and attendance logs.</p>
        </div>
    </div>

    {{-- Filter card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Date</label>
                <input type="date" wire:model.live="date"
                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Camp</label>
                <select wire:model.live="campId"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    <option value="">All camps</option>
                    @foreach($camps as $camp)
                        <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Course</label>
                <select wire:model.live="courseId"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    <option value="">All courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Instructor</label>
                <select wire:model.live="instructorId"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    <option value="">All instructors</option>
                    @foreach($instructors as $instructor)
                        <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Status</label>
                <select wire:model.live="status"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    <option value="">All statuses</option>
                    <option value="submitted">Submitted</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Reports list --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        @forelse($reports as $report)
            @php
                $attendanceCount = $report->attendance_count ?? $report->attendance?->count() ?? 0;
            @endphp
            <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 dark:border-gray-700/60 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">

                {{-- Date block --}}
                <div class="flex-shrink-0 w-14 text-center">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase">{{ $report->report_date->format('M') }}</p>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">{{ $report->report_date->format('d') }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $report->report_date->format('Y') }}</p>
                </div>

                {{-- Divider --}}
                <div class="w-px h-10 bg-gray-200 dark:bg-gray-600 flex-shrink-0"></div>

                {{-- Main info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                        {{ $report->course?->title ?? 'Unknown course' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $report->instructor?->name ?? 'Unknown instructor' }}
                        @if($report->submitted_at)
                            &middot; Submitted at {{ $report->submitted_at->format('H:i') }}
                        @endif
                    </p>
                </div>

                {{-- Badges --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($report->follow_up_required)
                        <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs font-bold">Follow-up</span>
                    @endif
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                        {{ $report->status === 'submitted' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                        {{ ucfirst($report->status) }}
                    </span>
                </div>

                {{-- View link --}}
                <a href="{{ route('admin.daily-reports.show', $report) }}"
                   class="flex-shrink-0 inline-flex items-center gap-1 text-sm font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 transition-colors">
                    View
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">No reports found</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Try adjusting the filters above.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($reports->hasPages())
        <div>{{ $reports->links() }}</div>
    @endif

</div>
