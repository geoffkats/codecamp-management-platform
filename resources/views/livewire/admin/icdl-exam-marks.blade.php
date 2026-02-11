<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">ICDL Exam Marks Review</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Approve, lock, or unlock exam marks with a complete audit trail.</p>
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="statusFilter" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="pending_review">Pending Admin Review</option>
                    <option value="approved">Approved & Locked</option>
                    <option value="all">All Records</option>
                </select>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        @if (session()->has('message'))
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-sm text-blue-800 dark:text-blue-200">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        @if($statusFilter === 'approved')
                            ICDL Marks Approved & Locked
                        @elseif($statusFilter === 'all')
                            ICDL Marks (All Records)
                        @else
                            ICDL Marks Pending Review
                        @endif
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Module</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Result</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($marks as $mark)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        <div class="font-medium">{{ $mark->student?->full_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $mark->student?->school?->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $mark->module?->title }}
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $mark->module?->course?->title }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $mark->exam_session }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ number_format($mark->score, 1) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex px-2.5 py-1 text-xs rounded-full {{ $mark->result === 'pass' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                            {{ $mark->result === 'pass' ? 'Pass' : 'Fail' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($mark->status === 'approved')
                                            <span class="inline-flex px-2.5 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Approved & Locked</span>
                                        @else
                                            <span class="inline-flex px-2.5 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">Pending Admin Review</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <button type="button" wire:click="selectMark({{ $mark->id }})" class="text-blue-600 hover:text-blue-700">Review</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No ICDL exam marks found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $marks->links() }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Review Panel</h2>
                </div>
                <div class="p-6 space-y-4">
                    @if(!$selectedMark)
                        <p class="text-sm text-gray-500 dark:text-gray-400">Select a record to review, approve, or unlock.</p>
                    @else
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $selectedMark->student?->full_name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedMark->student?->school?->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Module: {{ $selectedMark->module?->title }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Exam Session</label>
                            <input type="text" wire:model="exam_session" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('exam_session') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Score</label>
                                <input type="number" step="0.01" wire:model="score" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @error('score') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Exam Date</label>
                                <input type="date" wire:model="exam_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @error('exam_date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Teacher Comment</label>
                            <textarea wire:model="teacher_comment" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                            @error('teacher_comment') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span>Status:</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $selectedMark->status === 'approved' ? 'Approved & Locked' : 'Pending Admin Review' }}
                            </span>
                            <span>•</span>
                            <span>{{ $selectedMark->is_locked ? 'Locked' : 'Unlocked by Admin' }}</span>
                        </div>

                        <div class="space-y-3">
                            @if(!($selectedMark->status === 'approved' && $selectedMark->is_locked))
                                <button type="button" wire:click="approveAndLock" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Approve & Lock</button>
                            @endif

                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-3">
                                <div class="text-xs font-semibold text-gray-600 dark:text-gray-300">Unlock (Admin Only)</div>
                                <textarea wire:model="unlock_reason" rows="2" placeholder="Reason for unlocking" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                                @error('unlock_reason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                <button type="button" wire:click="unlock" class="w-full px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg">Unlock</button>
                            </div>

                            <button type="button" wire:click="saveCorrection" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save Correction</button>

                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-3">
                                <div class="text-xs font-semibold text-gray-600 dark:text-gray-300">Reject to Teacher</div>
                                <textarea wire:model="reject_reason" rows="2" placeholder="Reason for rejection" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                                @error('reject_reason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                <button type="button" wire:click="reject" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">Reject</button>
                            </div>

                            <button type="button" wire:click="clearSelection" class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg">Clear</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
