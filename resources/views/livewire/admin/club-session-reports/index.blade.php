<div class="max-w-6xl mx-auto py-8 px-4 space-y-6">
    @if(session('message'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-sm font-semibold text-green-800">{{ session('message') }}</div>
    @endif

    <div>
        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Club Session Reports</h1>
        <p class="text-sm text-gray-500 mt-1">Review facilitator session reports and retention metrics.</p>
        @if($avgRetention !== null)
            <p class="text-sm text-blue-600 mt-2">Average retention (filtered): {{ $avgRetention }}%</p>
        @endif
    </div>

    <div class="flex flex-wrap gap-3">
        <input type="date" wire:model.live="dateFrom" class="rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm" placeholder="From" />
        <input type="date" wire:model.live="dateTo" class="rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm" placeholder="To" />
        <select wire:model.live="schoolId" class="rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm">
            <option value="">All schools</option>
            @foreach($schools as $school)
                <option value="{{ $school->id }}">{{ $school->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="clubId" class="rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm">
            <option value="">All clubs</option>
            @foreach($clubs as $club)
                <option value="{{ $club->id }}">{{ $club->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <option value="submitted">Submitted</option>
            <option value="reviewed">Reviewed</option>
        </select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Club</th>
                    <th class="px-4 py-3">Facilitator</th>
                    <th class="px-4 py-3">Attendance</th>
                    <th class="px-4 py-3">Topics</th>
                    <th class="px-4 py-3">Teamwork</th>
                    <th class="px-4 py-3">Collaboration</th>
                    <th class="px-4 py-3">Retention</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($reports as $report)
                    <tr class="{{ $report->follow_up_required ? 'bg-amber-50/60 dark:bg-amber-900/10' : '' }}">
                        <td class="px-4 py-3">{{ $report->session_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $report->club?->name }}</td>
                        <td class="px-4 py-3">{{ $report->facilitator?->name }}</td>
                        <td class="px-4 py-3">{{ $report->attendance_count }}/{{ $report->enrolled_count }}</td>
                        <td class="px-4 py-3 max-w-xs">
                            <p class="truncate text-xs text-gray-600 dark:text-gray-300" title="{{ $report->topics_covered }}">{{ $report->topics_covered ?: '—' }}</p>
                            @if($report->new_techniques)
                                <p class="truncate text-xs text-blue-600 dark:text-blue-400 mt-0.5" title="{{ $report->new_techniques }}">New: {{ Str::limit($report->new_techniques, 40) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($report->teamwork_rating)
                                <span title="{{ $report->teamwork_rating }}/5">{{ str_repeat('★', $report->teamwork_rating) }}{{ str_repeat('☆', 5 - $report->teamwork_rating) }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($report->collaboration_rating)
                                <span title="{{ $report->collaboration_rating }}/5">{{ str_repeat('★', $report->collaboration_rating) }}{{ str_repeat('☆', 5 - $report->collaboration_rating) }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $report->retentionRate() !== null ? $report->retentionRate().'%' : '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1">
                                {{ ucfirst($report->status) }}
                                @if($report->follow_up_required)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-200 text-amber-900">Follow-up</span>
                                @endif
                            </span>
                            @if($report->admin_notes)
                                <p class="text-xs text-gray-500 mt-1 max-w-xs truncate" title="{{ $report->admin_notes }}">Note: {{ Str::limit($report->admin_notes, 50) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($report->status === 'submitted' && (auth()->user()->isAdmin() || auth()->user()->isSupervisor()))
                                <button wire:click="openReview({{ $report->id }})" class="text-xs font-bold text-blue-600">Mark reviewed</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-gray-500">No reports found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $reports->links() }}

    @if($reviewingReportId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Mark report reviewed</h2>
                <textarea wire:model="reviewNotes" rows="4" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm" placeholder="Admin notes (optional)"></textarea>
                @error('reviewNotes') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('reviewingReportId', null)" class="px-4 py-2 rounded-xl border text-sm">Cancel</button>
                    <button wire:click="markReviewed" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">Confirm</button>
                </div>
            </div>
        </div>
    @endif
</div>
