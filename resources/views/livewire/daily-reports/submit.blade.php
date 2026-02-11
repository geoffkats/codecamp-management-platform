<div class="max-w-5xl mx-auto py-10 px-6">
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/20 p-4 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Daily Class Report</h1>
            <p class="text-gray-600 dark:text-gray-400">Submit today's summary, attendance, and issues.</p>
        </div>
        <button wire:click="save"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Submit Report
        </button>
    </div>

    <div class="space-y-6 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Date</label>
                <input type="date" wire:model="reportDate" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                @error('reportDate') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Course</label>
                <select wire:model="courseId" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </select>
                @error('courseId') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Summary</label>
            <textarea wire:model="summary" rows="3" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
            @error('summary') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Challenges</label>
                <textarea wire:model="challenges" rows="3" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Issues</label>
                <textarea wire:model="issuesText" rows="3" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" wire:model="followUpRequired" id="follow_up" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 focus:ring-blue-500" />
            <label for="follow_up" class="text-sm text-gray-700 dark:text-gray-300 font-semibold">Follow-up required</label>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Attendance</h2>
                <button type="button" wire:click="addAttendanceRow" class="text-blue-600 dark:text-blue-400 hover:underline">+ Add Row</button>
            </div>
            <div class="space-y-3">
                @foreach($attendance as $index => $row)
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg">
                        <div class="md:col-span-2">
                            <label class="block text-sm text-gray-700 dark:text-gray-300">Student</label>
                            <select wire:model="attendance.{{ $index }}.student_id" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">Status</label>
                            <select wire:model="attendance.{{ $index }}.status" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">Reason</label>
                            <input type="text" wire:model="attendance.{{ $index }}.reason" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <button type="button" wire:click="removeAttendanceRow({{ $index }})" class="text-sm text-red-600 dark:text-red-400 hover:underline">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Mentions</h2>
                <button type="button" wire:click="addMentionRow" class="text-blue-600 dark:text-blue-400 hover:underline">+ Add Mention</button>
            </div>
            <div class="space-y-3">
                @foreach($mentions as $index => $mention)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg">
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">User ID to mention</label>
                            <input type="number" wire:model="mentions.{{ $index }}.mentionable_id" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">Role/Context</label>
                            <input type="text" wire:model="mentions.{{ $index }}.role" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">Note</label>
                            <input type="text" wire:model="mentions.{{ $index }}.note" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div class="md:col-span-3 flex justify-end">
                            <button type="button" wire:click="removeMentionRow({{ $index }})" class="text-sm text-red-600 dark:text-red-400 hover:underline">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Issues</h2>
                <button type="button" wire:click="addIssueRow" class="text-blue-600 dark:text-blue-400 hover:underline">+ Add Issue</button>
            </div>
            <div class="space-y-3">
                @foreach($issues as $index => $issue)
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg">
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">Title</label>
                            <input type="text" wire:model="issues.{{ $index }}.title" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">Description</label>
                            <input type="text" wire:model="issues.{{ $index }}.description" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">Severity</label>
                            <select wire:model="issues.{{ $index }}.severity" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300">Assign to (user ID)</label>
                            <input type="number" wire:model="issues.{{ $index }}.assigned_to" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <button type="button" wire:click="removeIssueRow({{ $index }})" class="text-sm text-red-600 dark:text-red-400 hover:underline">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Attachments</label>
            <input type="file" wire:model="attachments" multiple class="w-full text-gray-900 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400 dark:hover:file:bg-blue-900/50" />
            @error('attachments.*') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
