<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 space-y-6">

    @if(session('message'))
        <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-green-800 dark:text-green-200 text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('message') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-800 dark:text-red-200 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Page header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Daily Class Report</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Submit today's class summary for admin review.</p>
        </div>
        <button wire:click="save"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Submit Report
        </button>
    </div>

    {{-- ── Section 1: Report Info ─────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-4">Report Info</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Date</label>
                <input type="date" wire:model.live="reportDate"
                       class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent" />
                @error('reportDate') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Camp</label>
                <select wire:model.live="campId"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="">No camp / General</option>
                    @foreach($camps as $camp)
                        <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Course <span class="text-red-500">*</span></label>
                <select wire:model.live="courseId"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="">Select a course…</option>
                    @forelse($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @empty
                    @endforelse
                </select>
                @if($courses->isEmpty())
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">No courses assigned yet. Ask an admin to give you course access from the Users menu.</p>
                @endif
                @error('courseId') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- ── Section 2: Class Summary ────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-5">
        <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Class Summary</h2>
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">What was covered today?</label>
            <textarea wire:model="summary" rows="4"
                      placeholder="Describe the lesson content, topics covered, learning activities…"
                      class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"></textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Challenges</label>
            <textarea wire:model="challenges" rows="3"
                      placeholder="Any difficulties, slow progress, or areas where students struggled…"
                      class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"></textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">General issues / notes</label>
            <textarea wire:model="issuesText" rows="2"
                      placeholder="Facility issues, equipment problems, or other general notes…"
                      class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"></textarea>
        </div>
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox" wire:model="followUpRequired"
                   class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-orange-600 focus:ring-orange-500" />
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">This report requires follow-up action</span>
        </label>
    </div>

    {{-- ── Pedagogical approaches ──────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Pedagogical approaches applied today</h2>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 mb-4">Tick each approach you used, then briefly say how.</p>

        <div class="space-y-3">
            @foreach($approachOptions as $key => $option)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/40 space-y-2">
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" wire:model.live="approaches.{{ $key }}.used"
                               class="mt-0.5 w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-orange-600 focus:ring-orange-500" />
                        <span>
                            <span class="block text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $option['label'] }}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $option['hint'] }}</span>
                        </span>
                    </label>
                    @if(!empty($approaches[$key]['used']))
                        <div class="pl-7">
                            <textarea wire:model="approaches.{{ $key }}.description" rows="2"
                                      placeholder="How did you use this today?"
                                      class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"></textarea>
                            @error("approaches.{$key}.description")
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Section 3: Attendance ───────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Attendance</h2>
                @if($courseId)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pre-filled from check-in records. Adjust any row before submitting.</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if($courseId)
                    @php
                        $present = collect($attendance)->where('status', 'present')->count();
                        $late = collect($attendance)->where('status', 'late')->count();
                        $absent = collect($attendance)->where('status', 'absent')->count();
                    @endphp
                    <span class="text-xs font-medium text-green-700 dark:text-green-400">{{ $present }} present</span>
                    <span class="text-xs font-medium text-amber-700 dark:text-amber-400">{{ $late }} late</span>
                    <span class="text-xs font-medium text-red-700 dark:text-red-400">{{ $absent }} absent</span>
                @endif
                <button type="button" wire:click="addAttendanceRow"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add student
                </button>
            </div>
        </div>

        @if(empty($attendance))
            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">Select a course above to auto-fill attendance.</p>
        @else
            <div class="space-y-2">
                @foreach($attendance as $index => $row)
                    <div class="grid grid-cols-12 gap-3 items-center p-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                        {{-- Student --}}
                        <div class="col-span-5">
                            @if(!empty($students) && count($students) > 0)
                                <select wire:model="attendance.{{ $index }}.student_id"
                                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <option value="">Select student…</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <select wire:model="attendance.{{ $index }}.student_id"
                                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <option value="">Select course first</option>
                                </select>
                            @endif
                        </div>
                        {{-- Status --}}
                        <div class="col-span-3">
                            <select wire:model="attendance.{{ $index }}.status"
                                    class="w-full border rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent
                                    @if(($row['status'] ?? '') === 'present') border-green-300 dark:border-green-700 dark:bg-gray-700 dark:text-white
                                    @elseif(($row['status'] ?? '') === 'late') border-amber-300 dark:border-amber-700 dark:bg-gray-700 dark:text-white
                                    @elseif(($row['status'] ?? '') === 'absent') border-red-300 dark:border-red-700 dark:bg-gray-700 dark:text-white
                                    @else border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white @endif">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                            </select>
                        </div>
                        {{-- Reason --}}
                        <div class="col-span-3">
                            <input type="text" wire:model="attendance.{{ $index }}.reason"
                                   placeholder="Reason (optional)"
                                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent" />
                        </div>
                        {{-- Remove --}}
                        <div class="col-span-1 flex justify-center">
                            <button type="button" wire:click="removeAttendanceRow({{ $index }})"
                                    class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Section 4: Student Highlights ──────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Student Highlights</h2>
            <button type="button" wire:click="addMentionRow"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add highlight
            </button>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Call out a student for recognition, improvement, or a concern.</p>

        <div class="space-y-2">
            @foreach($mentions as $index => $mention)
                <div class="grid grid-cols-12 gap-3 items-center p-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                    {{-- Student --}}
                    <div class="col-span-4">
                        <select wire:model="mentions.{{ $index }}.mentionable_id"
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            <option value="">Select student…</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Type --}}
                    <div class="col-span-3">
                        <select wire:model="mentions.{{ $index }}.role"
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            <option value="recognition">Recognition</option>
                            <option value="improvement">Improvement</option>
                            <option value="concern">Concern</option>
                            <option value="absent_reason">Absence Note</option>
                        </select>
                    </div>
                    {{-- Note --}}
                    <div class="col-span-4">
                        <input type="text" wire:model="mentions.{{ $index }}.note"
                               placeholder="Brief note…"
                               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent" />
                    </div>
                    {{-- Remove --}}
                    <div class="col-span-1 flex justify-center">
                        <button type="button" wire:click="removeMentionRow({{ $index }})"
                                class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Section 5: Flagged Issues ───────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Flagged Issues</h2>
            <button type="button" wire:click="addIssueRow"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Flag issue
            </button>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Report equipment, facility, or urgent concerns that need admin attention.</p>

        <div class="space-y-3">
            @foreach($issues as $index => $issue)
                <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Issue Title</label>
                            <input type="text" wire:model="issues.{{ $index }}.title"
                                   placeholder="e.g. Projector not working"
                                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Severity</label>
                            <select wire:model="issues.{{ $index }}.severity"
                                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Assign to</label>
                            <select wire:model="issues.{{ $index }}.assigned_to"
                                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <option value="">Unassigned</option>
                                @foreach($staff as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-3 items-start">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Description</label>
                            <input type="text" wire:model="issues.{{ $index }}.description"
                                   placeholder="Describe the issue in detail…"
                                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent" />
                        </div>
                        <button type="button" wire:click="removeIssueRow({{ $index }})"
                                class="mt-5 p-2 text-gray-400 hover:text-red-500 rounded-lg transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Section 6: Attachments ──────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-4">Attachments <span class="font-normal normal-case text-gray-400">(optional)</span></h2>
        <input type="file" wire:model="attachments" multiple
               class="w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-orange-50 dark:file:bg-orange-900/20 file:text-orange-700 dark:file:text-orange-400 hover:file:bg-orange-100 dark:hover:file:bg-orange-900/30 transition-colors" />
        @error('attachments.*') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    {{-- Bottom submit --}}
    <div class="flex justify-end pb-8">
        <button wire:click="save"
                class="inline-flex items-center gap-2 px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Submit Report
        </button>
    </div>
</div>
