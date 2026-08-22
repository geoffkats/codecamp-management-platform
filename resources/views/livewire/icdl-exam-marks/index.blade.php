<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">ICDL Exam Result Submission</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    School: {{ $lockedStudent?->school?->name ?? auth()->user()->teacherProfile?->school?->name ?? auth()->user()->ictSchoolId() }}
                </p>
            </div>
            <a href="{{ route('students.index') }}" wire:navigate class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg">Back</a>
        </div>
    </div>

    <div class="p-6 space-y-6">
        @if (session()->has('message'))
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-sm text-blue-800 dark:text-blue-200">
                {{ session('message') }}
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-sm text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if($unlockedRecords->isNotEmpty())
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-amber-100 dark:bg-amber-900/40 border-b border-amber-200 dark:border-amber-800">
                    <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-200">Pending Corrections (Unlocked by Admin)</h2>
                    <p class="text-sm text-amber-800 dark:text-amber-300 mt-1">Admin has unlocked the following records for correction. Review and re-submit them.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-amber-50 dark:bg-amber-900/20">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-amber-900 dark:text-amber-300 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-amber-900 dark:text-amber-300 uppercase">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-amber-900 dark:text-amber-300 uppercase">Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-amber-900 dark:text-amber-300 uppercase">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-amber-900 dark:text-amber-300 uppercase">Result</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-amber-900 dark:text-amber-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-200 dark:divide-amber-800">
                            @foreach($unlockedRecords as $record)
                                <tr class="hover:bg-amber-100/50 dark:hover:bg-amber-900/10">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $record->student?->full_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $record->module?->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $record->exam_session }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ number_format($record->score, 1) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex px-2.5 py-1 text-xs rounded-full {{ $record->result === 'pass' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                            {{ $record->result === 'pass' ? 'Pass' : 'Fail' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($editingId !== $record->id)
                                            <button wire:click="editUnlockedRecord({{ $record->id }})" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white text-xs rounded">
                                                Edit & Correct
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @if($editingId === $record->id)
                                    <tr class="bg-amber-50 dark:bg-amber-900/20">
                                        <td colspan="6" class="px-6 py-4">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Corrected Score *</label>
                                                    <input type="number" step="0.01" wire:model="editingScore" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                    @error('editingScore') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Exam Notes / Irregularities (optional)</label>
                                                    <textarea wire:model="editingComment" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                                                </div>
                                            </div>
                                            <div class="mt-4 flex gap-3">
                                                <button wire:click="saveEditedRecord" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">
                                                    Submit Correction
                                                </button>
                                                <button wire:click="cancelEdit" class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm rounded-lg">
                                                    Cancel
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-indigo-50 dark:from-slate-900/50 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">ICDL Exam Result Record</h2>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Record official ICDL exam results; submissions are locked and sent for admin verification.</span>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Student *</label>
                    <select wire:model="student_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->student_id }})</option>
                        @endforeach
                    </select>
                    @error('student_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Course *</label>
                    <select wire:model="course_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Select Course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    @error('course_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Exam Attempt *</label>
                    <input type="text" wire:model="exam_session" placeholder="ICDL Module Exam 1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('exam_session') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Score (0–100) *</label>
                    <input type="number" step="0.01" wire:model="score" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" title="Score as issued by the ICDL testing system">
                    @error('score') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Exam Date *</label>
                    <input type="date" wire:model="exam_date" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('exam_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Result</label>
                    <div class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                        {{ is_numeric($score) ? ((float)$score >= 75 ? 'Pass' : 'Fail') : '—' }}
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Exam Notes / Irregularities (optional)</label>
                    <textarea wire:model="teacher_comment" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                </div>
            </div>
            <div class="px-6 pb-6 flex flex-wrap gap-3">
                <button type="button" wire:click="save" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Submit for Admin Verification</button>
                <button type="button" wire:click="saveAndContinue" class="px-5 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg">Submit & Continue</button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Submitted ICDL Exam Marks</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Session</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Result</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($history as $mark)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $mark->student?->full_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $mark->module?->course?->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $mark->exam_session }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ number_format($mark->score, 1) }}</td>
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
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $mark->exam_date?->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No ICDL exam marks submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $history->links() }}
            </div>
        </div>
    </div>
</div>
