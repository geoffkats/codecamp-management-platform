<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 space-y-6">

    {{-- Back + actions bar --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.daily-reports.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            All Reports
        </a>
        <button onclick="window.print()"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
    </div>

    {{-- Report header card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="h-1.5 bg-orange-500"></div>
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Daily Class Report</p>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">
                        {{ $report->course?->title ?? 'Unknown Course' }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Instructor: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $report->instructor?->name ?? 'N/A' }}</span>
                    </p>
                    @if($report->camp)
                        <span class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-xs font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            {{ $report->camp->name }}
                        </span>
                    @endif
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $report->report_date->format('d M Y') }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        Submitted {{ $report->submitted_at ? $report->submitted_at->format('H:i') : '—' }}
                    </p>
                    <span class="mt-1.5 inline-block px-2.5 py-0.5 rounded-full text-xs font-bold
                        {{ $report->status === 'submitted' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                        {{ ucfirst($report->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Follow-up banner --}}
    @if($report->follow_up_required)
    <div class="flex items-center gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">This report requires follow-up action.</p>
    </div>
    @endif

    {{-- Summary + Challenges --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300">Class Summary</h2>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                {{ $report->summary ?: '—' }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300">Challenges</h2>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                {{ $report->challenges ?: '—' }}
            </p>
        </div>
    </div>

    @if($report->issues)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300">General Notes</h2>
        </div>
        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $report->issues }}</p>
    </div>
    @endif

    {{-- Attendance --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 p-5 border-b border-gray-100 dark:border-gray-700">
            <div class="w-7 h-7 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300">Attendance</h2>
            @php
                $presentCount = $report->attendance->where('status', 'present')->count();
                $absentCount  = $report->attendance->where('status', 'absent')->count();
                $lateCount    = $report->attendance->where('status', 'late')->count();
                $total        = $report->attendance->count();
            @endphp
            @if($total > 0)
                <div class="ml-auto flex items-center gap-3 text-xs">
                    <span class="px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 font-bold">{{ $presentCount }} present</span>
                    @if($absentCount) <span class="px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 font-bold">{{ $absentCount }} absent</span> @endif
                    @if($lateCount)   <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 font-bold">{{ $lateCount }} late</span> @endif
                </div>
            @endif
        </div>
        @forelse($report->attendance as $row)
            <div class="flex items-center gap-4 px-5 py-3 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400">
                        {{ strtoupper(substr($row->student?->name ?? '?', 0, 1)) }}
                    </span>
                </div>
                <span class="flex-1 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $row->student?->name ?? 'Unknown student' }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                    {{ $row->status === 'present' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                        : ($row->status === 'absent' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'
                        : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300') }}">
                    {{ ucfirst($row->status) }}
                </span>
                @if($row->reason)
                    <span class="text-xs text-gray-400 dark:text-gray-500 max-w-xs truncate">{{ $row->reason }}</span>
                @endif
            </div>
        @empty
            <p class="px-5 py-4 text-sm text-gray-400 dark:text-gray-500">No attendance recorded.</p>
        @endforelse
    </div>

    {{-- Student Highlights (Mentions) --}}
    @if($report->mentions->count())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 p-5 border-b border-gray-100 dark:border-gray-700">
            <div class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300">Student Highlights</h2>
        </div>
        @foreach($report->mentions as $mention)
            @php
                $roleLabel = match($mention->role) {
                    'recognition'  => ['label' => 'Recognition',   'class' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'],
                    'improvement'  => ['label' => 'Improvement',   'class' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'],
                    'concern'      => ['label' => 'Concern',       'class' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'],
                    'absent_reason'=> ['label' => 'Absence Note',  'class' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'],
                    default        => ['label' => ucfirst($mention->role ?? 'Note'), 'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'],
                };
                $mentionedName = $mention->mentionable?->name ?? "User #{$mention->mentionable_id}";
            @endphp
            <div class="flex items-start gap-4 px-5 py-3 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400">{{ strtoupper(substr($mentionedName, 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $mentionedName }}</span>
                    @if($mention->note)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $mention->note }}</p>
                    @endif
                </div>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $roleLabel['class'] }}">{{ $roleLabel['label'] }}</span>
            </div>
        @endforeach
    </div>
    @endif

    {{-- Flagged Issues --}}
    @if($report->reportIssues->count())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2 p-5 border-b border-gray-100 dark:border-gray-700">
            <div class="w-7 h-7 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300">Flagged Issues</h2>
        </div>
        @foreach($report->reportIssues as $issue)
            <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                <div class="flex items-start justify-between gap-3 mb-1">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $issue->title }}</h3>
                    <span class="flex-shrink-0 text-xs font-bold px-2.5 py-0.5 rounded-full
                        {{ $issue->severity === 'critical' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'
                            : ($issue->severity === 'high' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400') }}">
                        {{ ucfirst($issue->severity) }}
                    </span>
                </div>
                @if($issue->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $issue->description }}</p>
                @endif
                @if($issue->assignee)
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">
                        Assigned to: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ $issue->assignee->name }}</span>
                    </p>
                @endif
            </div>
        @endforeach
    </div>
    @endif

    {{-- Attachments --}}
    @if($report->attachments->count())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300">Attachments</h2>
        </div>
        <div class="space-y-2">
            @foreach($report->attachments as $attachment)
                <a href="{{ Storage::url($attachment->path) }}" target="_blank"
                   class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $attachment->name ?? basename($attachment->path) }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
