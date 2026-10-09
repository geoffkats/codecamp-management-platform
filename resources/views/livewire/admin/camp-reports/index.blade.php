@php
    use App\Models\CampReport;
    $input = 'rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent';
@endphp

<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Camp Reports</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">End-of-camp reports for management: attendance, instructor reports, learning outcomes and recommendations. Download any report as a PDF.</p>
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search camps…" class="{{ $input }} sm:w-72">
        <select wire:model.live="status" class="{{ $input }} sm:w-48">
            <option value="">All statuses</option>
            @foreach(['completed' => 'Completed', 'active' => 'Running now', 'upcoming' => 'Upcoming'] as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Camp</th>
                        <th class="px-5 py-3 text-right">Students</th>
                        <th class="px-5 py-3 text-right">Daily reports</th>
                        <th class="px-5 py-3">Report</th>
                        <th class="px-5 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($camps as $camp)
                        @php $report = $reports->get($camp->id); @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.camps.report', $camp) }}" wire:navigate class="font-semibold text-gray-900 dark:text-white hover:text-orange-600">{{ $camp->name }}</a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $camp->date_range }} · {{ ucfirst($camp->status) }}</p>
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums text-gray-700 dark:text-gray-200">{{ $camp->enrollments_count }}</td>
                            <td class="px-5 py-4 text-right tabular-nums text-gray-700 dark:text-gray-200">{{ $camp->daily_reports_count }}</td>
                            <td class="px-5 py-4 text-xs">
                                @if($report)
                                    <span class="text-gray-700 dark:text-gray-200">{{ CampReport::SOURCES[$report->source] ?? ucfirst($report->source) }}</span>
                                    <p class="text-gray-500 dark:text-gray-400">{{ ($report->updated_at ?? $report->generated_at)?->format('j M Y') }}@if($report->source === 'manual' && $report->updatedBy) · {{ $report->updatedBy->name }}@endif</p>
                                @elseif($camp->status === 'completed')
                                    <span class="font-semibold text-orange-600">Ready to open</span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">In progress — figures so far</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.camps.report', $camp) }}" wire:navigate
                                       class="rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-orange-700">Open report</a>
                                    <a href="{{ route('admin.camps.report.pdf', $camp) }}"
                                       class="rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">No camps found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $camps->links() }}
</div>
