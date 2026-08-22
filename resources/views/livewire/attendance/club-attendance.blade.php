<div class="max-w-6xl mx-auto py-8 px-4 space-y-6">
    @if(session('message'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-sm font-semibold text-green-800">{{ session('message') }}</div>
    @endif

    <x-attendance.nav-tabs context="club" />

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">Code Club Attendance</h1>
            <p class="text-sm text-gray-500 mt-1">Mark session attendance for your club members.</p>
            @if($selectedClub)
                <p class="text-xs text-gray-500 mt-1">{{ $selectedClub->schedule_label }}</p>
            @endif
        </div>
        @if($todayCode)
            <div class="rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 px-4 py-3 text-center shrink-0">
                <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-300 uppercase tracking-wide">Today's Code</p>
                <p class="text-2xl font-mono font-bold text-indigo-900 dark:text-indigo-100 tracking-widest">{{ $todayCode }}</p>
                <a href="{{ route('attendance.code') }}" class="text-xs text-indigo-700 dark:text-indigo-300 underline mt-1 inline-block">Full screen →</a>
            </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs font-semibold text-gray-500">Session Date</label>
            <input type="date" wire:model.live="sessionDate" class="block mt-1 rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm dark:bg-gray-800" />
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500">Club</label>
            <select wire:model.live="clubId" class="block mt-1 rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm dark:bg-gray-800 min-w-[200px]">
                <option value="">Select club</option>
                @foreach($clubs as $club)
                    <option value="{{ $club->id }}">{{ $club->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="text-xs font-semibold text-gray-500">Search</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name or student ID…" class="block w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm dark:bg-gray-800" />
        </div>
    </div>

    @if($clubId && $selectedClub)
        @if(!$sessionMeetsToday)
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-sm text-amber-900 dark:text-amber-100">
                This date is not a scheduled session day for <strong>{{ $selectedClub->name }}</strong>. You can still mark attendance manually if needed.
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Check-in</th>
                        <th class="px-4 py-3">Check-out</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($roster as $row)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $row['profile']->full_name }}</p>
                                <p class="text-xs text-gray-500">{{ $row['profile']->student_id }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                {{ $row['record']?->formattedClockIn() ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                {{ $row['record']?->formattedClockOut() ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <select wire:model="attendanceStatuses.{{ $row['profile']->id }}" class="rounded-lg border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm dark:bg-gray-900">
                                    <option value="present">Present</option>
                                    <option value="late">Late</option>
                                    <option value="absent">Absent</option>
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No members found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-3 items-center">
            @if($roster->isNotEmpty())
                <button wire:click="saveAttendance" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">Save Attendance</button>
            @endif
            <a href="{{ route('admin.code-clubs.reports.bulk-download', $selectedClub) }}"
               class="px-4 py-2 rounded-xl bg-orange-600 text-white text-sm font-bold hover:bg-orange-700"
               title="Download end-of-term progress report PDFs for all active members">
                Download All Term Reports
            </a>
            @if($roster->isNotEmpty())
                <a href="{{ route('admin.code-clubs.reports.preview', ['club' => $selectedClub, 'student' => $roster->first()['profile']->user_id]) }}"
                   target="_blank"
                   class="px-4 py-2 rounded-xl border border-orange-300 dark:border-orange-700 text-orange-700 dark:text-orange-300 text-sm font-bold hover:bg-orange-50 dark:hover:bg-orange-900/20"
                   title="Preview a sample term report PDF">
                    Preview Sample
                </a>
            @endif
        </div>
    @elseif($clubs->isEmpty())
        <div class="p-6 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-sm text-amber-900 dark:text-amber-100 text-center">
            No clubs are assigned to your account. Ask an administrator to add you as a facilitator on a Code Club.
        </div>
    @else
        <div class="p-6 rounded-xl bg-gray-50 dark:bg-gray-900/50 text-sm text-gray-600 dark:text-gray-400 text-center">
            Select a club to view and mark attendance.
        </div>
    @endif
</div>
