<div class="max-w-6xl mx-auto py-8 px-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">{{ $report->report_date->format('Y-m-d') }}</p>
            <h1 class="text-3xl font-bold text-gray-900">{{ $report->course?->title ?? 'Course' }}</h1>
            <p class="text-gray-600">Instructor: {{ $report->instructor?->name ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('admin.daily-reports.index') }}" class="text-blue-600 hover:underline">← Back</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-2">Summary</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $report->summary ?: '—' }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h2 class="text-lg font-semibold mb-2">Challenges</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $report->challenges ?: '—' }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-2">Issues</h2>
        <p class="text-gray-700 whitespace-pre-line">{{ $report->issues ?: '—' }}</p>
        @if($report->reportIssues->count())
            <div class="mt-3 space-y-2">
                @foreach($report->reportIssues as $issue)
                    <div class="border rounded p-3">
                        <div class="flex items-center justify-between">
                            <div class="font-semibold">{{ $issue->title }}</div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $issue->severity === 'critical' ? 'bg-red-100 text-red-800' : ($issue->severity === 'high' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($issue->severity) }}</span>
                        </div>
                        <p class="text-gray-700 text-sm mt-1">{{ $issue->description }}</p>
                        <p class="text-gray-600 text-xs mt-1">Assigned: {{ $issue->assignee?->name ?? 'N/A' }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-semibold">Attendance</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Student</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Reason</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($report->attendance as $row)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $row->student?->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ ucfirst($row->status) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $row->reason ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-center text-gray-500">No attendance recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-2">Mentions</h2>
        @if($report->mentions->count())
            <ul class="space-y-1">
                @foreach($report->mentions as $mention)
                    <li class="text-sm text-gray-800">{{ $mention->mentionable_type }} #{{ $mention->mentionable_id }} — {{ $mention->role ?? 'Mentioned' }} ({{ $mention->note ?? 'No note' }})</li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-600">No mentions.</p>
        @endif
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-2">Attachments</h2>
        @if($report->attachments->count())
            <ul class="space-y-1">
                @foreach($report->attachments as $attachment)
                    <li>
                        <a class="text-blue-600 hover:underline" href="{{ Storage::url($attachment->path) }}" target="_blank">{{ $attachment->name ?? basename($attachment->path) }}</a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-600">No attachments.</p>
        @endif
    </div>
</div>
