<div class="max-w-7xl mx-auto py-8 px-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Daily Reports</h1>
            <p class="text-gray-600">Review instructor submissions and attendance summaries.</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-sm text-gray-700">Date</label>
            <input type="date" wire:model.live="date" class="w-full border-gray-300 rounded" />
        </div>
        <div>
            <label class="block text-sm text-gray-700">Course</label>
            <select wire:model.live="courseId" class="w-full border-gray-300 rounded">
                <option value="">All</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-700">Instructor</label>
            <select wire:model.live="instructorId" class="w-full border-gray-300 rounded">
                <option value="">All</option>
                @foreach($instructors as $instructor)
                    <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-700">Status</label>
            <select wire:model.live="status" class="w-full border-gray-300 rounded">
                <option value="">All</option>
                <option value="submitted">Submitted</option>
            </select>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Course</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Instructor</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Submitted</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($reports as $report)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $report->report_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $report->course?->title ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $report->instructor?->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm">
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs">{{ ucfirst($report->status) }}</span>
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ optional($report->submitted_at)->format('H:i') ?? '—' }}</td>
                        <td class="px-4 py-2 text-sm text-right">
                            <a href="{{ route('admin.daily-reports.show', $report) }}" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">No reports found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $reports->links() }}
    </div>
</div>
