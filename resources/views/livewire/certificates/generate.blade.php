@php
    $hasSelection = $selectedUserId && $candidateName;
    $readyCount = $bulkStudents->where('is_ready')->where('has_certificate', false)->count();
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">

    {{-- Header --}}
    <div class="border-b-4 border-blue-600 bg-[#1a3a8f]">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Certificate Generator</h1>
                    <p class="mt-1 text-sm text-blue-100">
                        Pick a course and student — name, ID, and completed modules fill in automatically.
                    </p>
                </div>
                <div class="flex rounded-xl bg-white/10 p-1 backdrop-blur">
                    <button type="button" wire:click="$set('mode', 'single')"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $mode === 'single' ? 'bg-white text-[#1a3a8f] shadow' : 'text-blue-100 hover:text-white' }}">
                        Single Student
                    </button>
                    <button type="button" wire:click="$set('mode', 'bulk')"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $mode === 'bulk' ? 'bg-white text-[#1a3a8f] shadow' : 'text-blue-100 hover:text-white' }}">
                        Bulk Issue
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6">

        @if($statusMessage)
            <div class="mb-4 rounded-lg border px-4 py-3 text-sm {{ $statusType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' }}">
                {{ $statusMessage }}
            </div>
        @endif

        @if($mode === 'single')
            <div class="grid gap-6 xl:grid-cols-12">

                {{-- Left: selection workflow --}}
                <div class="space-y-5 xl:col-span-5">

                    {{-- Step 1: Course --}}
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mb-3 flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#1a3a8f] text-xs font-bold text-white">1</span>
                            <h2 class="font-semibold text-slate-900 dark:text-white">Select course</h2>
                        </div>
                        <flux:select wire:model.live="selectedCourseId" placeholder="Choose a course…">
                            <option value="">— Choose a course —</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    @if($selectedCourseId)
                        {{-- Step 2: Student --}}
                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#1a3a8f] text-xs font-bold text-white">2</span>
                                    <h2 class="font-semibold text-slate-900 dark:text-white">Select student</h2>
                                </div>
                                <flux:select wire:model.live="eligibilityFilter" class="!w-auto text-xs">
                                    <option value="all">All enrolled</option>
                                    <option value="ready">Ready for cert</option>
                                    <option value="not_issued">Ready & not issued</option>
                                </flux:select>
                            </div>

                            <flux:input wire:model.live.debounce.300ms="studentSearch" placeholder="Search name, email, or student ID…" icon="magnifying-glass" />

                            <div class="mt-3 max-h-72 space-y-2 overflow-y-auto">
                                @forelse($studentResults as $student)
                                    <button type="button" wire:click="selectStudent({{ $student['user_id'] }})"
                                        class="w-full rounded-lg border px-3 py-3 text-left transition {{ (int) $selectedUserId === (int) $student['user_id'] ? 'border-[#1a3a8f] bg-blue-50 ring-1 ring-[#1a3a8f]/30 dark:bg-blue-950/30' : 'border-slate-200 hover:border-slate-300 dark:border-zinc-700' }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <p class="font-medium text-slate-900 dark:text-white">{{ $student['name'] }}</p>
                                                <p class="text-xs text-slate-500">{{ $student['student_id'] }} · {{ $student['email'] }}</p>
                                            </div>
                                            <div class="flex flex-shrink-0 flex-col items-end gap-1">
                                                @if($student['has_certificate'])
                                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-emerald-700">Issued</span>
                                                @elseif($student['is_ready'])
                                                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-blue-700">Ready</span>
                                                @else
                                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-600">In progress</span>
                                                @endif
                                                <span class="text-xs text-slate-500">{{ $student['progress'] }}% · {{ $student['completed_modules'] }}/{{ max($student['total_modules'], 1) }} modules</span>
                                            </div>
                                        </div>
                                    </button>
                                @empty
                                    <p class="rounded-lg bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:bg-zinc-800">No students match this filter.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    @if($hasSelection)
                        {{-- Step 3: Auto-filled summary --}}
                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="mb-4 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#1a3a8f] text-xs font-bold text-white">3</span>
                                    <h2 class="font-semibold text-slate-900 dark:text-white">Certificate details</h2>
                                </div>
                                <button type="button" wire:click="clearSelection" class="text-xs font-medium text-slate-500 hover:text-slate-800">Clear</button>
                            </div>

                            @if($existingCertificate)
                                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                    Certificate already on file ({{ $existingCertificate->issued_at?->format('j M Y') }}).
                                    Issuing again will update the record.
                                </div>
                            @endif

                            @if($selectedStudent)
                                <div class="mb-4 grid grid-cols-3 gap-2">
                                    <div class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-zinc-800">
                                        <p class="text-[10px] font-semibold uppercase text-slate-500">Progress</p>
                                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $selectedStudent['progress'] }}%</p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-zinc-800">
                                        <p class="text-[10px] font-semibold uppercase text-slate-500">Modules</p>
                                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $selectedStudent['completed_modules'] }}/{{ max($selectedStudent['total_modules'], 1) }}</p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-zinc-800">
                                        <p class="text-[10px] font-semibold uppercase text-slate-500">Completed</p>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $selectedStudent['completed_at'] ?? '—' }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Signatory & signature --}}
                            <div class="mt-4 space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <p class="text-xs font-semibold uppercase text-slate-500">Signature &amp; signatory</p>
                                <flux:select wire:model.live="signatoryProfile" label="Signatory profile">
                                    <option value="auto">Auto (from student program)</option>
                                    @foreach($signatoryProfiles as $key => $profile)
                                        <option value="{{ $key }}">{{ $profile['label'] }}{{ $profile['has_signature'] ? '' : ' (no signature uploaded)' }}</option>
                                    @endforeach
                                </flux:select>
                                @if($signatoryProfile === 'auto')
                                    <p class="text-xs text-slate-500">Resolved: <strong>{{ $signatoryProfiles[$resolvedSignatoryKey]['label'] ?? $resolvedSignatoryKey }}</strong></p>
                                @endif
                                <flux:input wire:model.live.debounce.400ms="customSignatoryOverride"
                                    label="Override signatory line (optional)"
                                    placeholder="{{ $signatoryProfiles[$resolvedSignatoryKey]['signatory_line'] ?? '' }}" />
                                @if($auditTrail)
                                    <p class="text-xs text-slate-500">
                                        Last issued by {{ $auditTrail['name'] ?? 'staff' }}
                                        @if(!empty($auditTrail['issued_at']))
                                            · {{ \Carbon\Carbon::parse($auditTrail['issued_at'])->format('j M Y g:i A') }}
                                        @endif
                                    </p>
                                @endif
                            </div>

                            @if(!$manualEdit)
                                <dl class="space-y-3 text-sm">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-slate-500">Candidate</dt>
                                        <dd class="font-medium text-slate-900 dark:text-white">{{ $candidateName }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-slate-500">Student ID</dt>
                                        <dd class="font-medium text-slate-900 dark:text-white">{{ $candidateNo }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-slate-500">Issue date</dt>
                                        <dd class="font-medium text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($signatureDate)->format('jS M Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="mb-2 text-xs font-semibold uppercase text-slate-500">Modules on certificate</dt>
                                        <dd class="space-y-1">
                                            @foreach($modules as $module)
                                                @if($module['name'])
                                                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-zinc-800">
                                                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $module['name'] }}</span>
                                                        <span class="text-xs text-slate-500">v{{ $module['version'] }} · {{ \Carbon\Carbon::parse($module['date'])->format('j M Y') }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </dd>
                                    </div>
                                </dl>
                                <button type="button" wire:click="$set('manualEdit', true)" class="mt-4 text-xs font-medium text-blue-600 hover:underline">
                                    Edit manually (advanced)
                                </button>
                            @else
                                <div class="space-y-4">
                                    <flux:input wire:model.live="candidateName" label="Candidate name" />
                                    <flux:input wire:model.live="candidateNo" label="Student ID" />
                                    <flux:input wire:model.live="signatureDate" type="date" label="Issue date" />

                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Modules</p>
                                            <button type="button" wire:click="addModule" class="text-xs font-semibold text-emerald-600">+ Add</button>
                                        </div>
                                        @foreach($modules as $index => $module)
                                            <div class="grid gap-2 rounded-lg bg-slate-50 p-2 dark:bg-zinc-800">
                                                <flux:input wire:model.live="modules.{{ $index }}.name" placeholder="Module name" />
                                                <div class="grid grid-cols-2 gap-2">
                                                    <flux:input wire:model.live="modules.{{ $index }}.version" placeholder="Version" />
                                                    <flux:input wire:model.live="modules.{{ $index }}.date" type="date" />
                                                </div>
                                                @if(count($modules) > 1)
                                                    <button type="button" wire:click="removeModule({{ $index }})" class="text-xs text-red-600">Remove</button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" wire:click="$set('manualEdit', false)" class="text-xs font-medium text-slate-500 hover:underline">
                                        Back to auto-filled view
                                    </button>
                                </div>
                            @endif

                            @error('candidateName') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            @error('modules') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                            <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-zinc-800">
                                <flux:button variant="primary" wire:click="issueAndDownload" wire:loading.attr="disabled" icon="check-badge">
                                    <span wire:loading.remove wire:target="issueAndDownload">Issue &amp; Download PDF</span>
                                    <span wire:loading wire:target="issueAndDownload">Generating…</span>
                                </flux:button>
                                <flux:button wire:click="generate" wire:loading.attr="disabled" icon="arrow-down-tray">
                                    <span wire:loading.remove wire:target="generate">Download only</span>
                                    <span wire:loading wire:target="generate">Generating…</span>
                                </flux:button>
                            </div>
                        </div>
                    @elseif($selectedCourseId)
                        <div class="rounded-xl border border-dashed border-slate-300 bg-white/50 p-8 text-center dark:border-zinc-600 dark:bg-zinc-900/50">
                            <flux:icon.user-group class="mx-auto size-10 text-slate-300" />
                            <p class="mt-3 text-sm text-slate-500">Select a student above to auto-fill their certificate.</p>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-300 bg-white/50 p-8 text-center dark:border-zinc-600 dark:bg-zinc-900/50">
                            <flux:icon.academic-cap class="mx-auto size-10 text-slate-300" />
                            <p class="mt-3 text-sm text-slate-500">Start by choosing a course.</p>
                        </div>
                    @endif
                </div>

                {{-- Right: live preview --}}
                <div class="xl:col-span-7">
                    <div class="sticky top-4 rounded-xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-zinc-800">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Live preview</h2>
                            @if($previewUrl)
                                <a href="{{ $previewUrl }}" target="_blank" class="text-xs font-medium text-blue-600 hover:underline">Open full size</a>
                            @endif
                        </div>

                        @if($previewUrl)
                            <div class="overflow-auto bg-slate-100 p-3 dark:bg-zinc-950" style="max-height: calc(100vh - 8rem);">
                                <iframe
                                    src="{{ $previewUrl }}"
                                    wire:key="preview-{{ $selectedUserId }}-{{ md5(json_encode($modules)) }}"
                                    class="mx-auto w-full border-0 bg-white shadow-lg"
                                    style="height: 1100px; min-width: 820px; max-width: 820px;"
                                    title="Certificate preview"
                                ></iframe>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center px-6 py-24 text-center">
                                <div class="rounded-full bg-slate-100 p-4 dark:bg-zinc-800">
                                    <flux:icon.document-text class="size-8 text-slate-400" />
                                </div>
                                <p class="mt-4 text-sm text-slate-500">Preview appears here once you select a student.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Bulk mode --}}
        @if($mode === 'bulk')
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-5">
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <h2 class="mb-4 font-semibold text-slate-900 dark:text-white">Issue certificates for a course</h2>
                        <p class="mb-4 text-sm text-slate-500">Select ready students — data is pulled from their course progress automatically.</p>

                        <flux:select wire:model.live="selectedCourseId" label="Course" placeholder="Choose a course…">
                            <option value="">— Choose a course —</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </flux:select>

                        @if($selectedCourseId)
                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <flux:input wire:model.live.debounce.300ms="studentSearch" placeholder="Filter students…" class="flex-1" />
                                <flux:button size="sm" wire:click="selectAllBulkReady">Select all ready</flux:button>
                                <flux:button size="sm" variant="ghost" wire:click="clearBulkSelection">Clear</flux:button>
                            </div>

                            <p class="mt-3 text-xs text-slate-500">{{ $readyCount }} ready · {{ count($bulkSelectedUserIds) }} selected</p>

                            <div class="mt-3 max-h-96 space-y-2 overflow-y-auto">
                                @forelse($bulkStudents as $student)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-3 transition {{ in_array($student['user_id'], $bulkSelectedUserIds) ? 'border-[#1a3a8f] bg-blue-50 dark:bg-blue-950/20' : 'border-slate-200 dark:border-zinc-700' }} {{ !$student['is_ready'] ? 'opacity-50' : '' }}">
                                        <input type="checkbox" class="rounded border-slate-300"
                                            @checked(in_array($student['user_id'], $bulkSelectedUserIds))
                                            wire:click="toggleBulkStudent({{ $student['user_id'] }})"
                                            @disabled(!$student['is_ready'])>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate font-medium text-slate-900 dark:text-white">{{ $student['name'] }}</p>
                                            <p class="text-xs text-slate-500">{{ $student['student_id'] }} · {{ $student['completed_modules'] }} modules · {{ $student['progress'] }}%</p>
                                        </div>
                                        @if($student['has_certificate'])
                                            <span class="text-xs font-medium text-emerald-600">Issued</span>
                                        @endif
                                    </label>
                                @empty
                                    <p class="py-8 text-center text-sm text-slate-500">No ready students in this course.</p>
                                @endforelse
                            </div>

                            @if($bulkError)
                                <p class="mt-3 text-sm text-red-600">{{ $bulkError }}</p>
                            @endif

                            <flux:button class="mt-4 w-full" variant="primary" wire:click="generateBulkFromCourse" wire:loading.attr="disabled" icon="archive-box-arrow-down">
                                <span wire:loading.remove wire:target="generateBulkFromCourse">Issue {{ count($bulkSelectedUserIds) }} &amp; Download ZIP</span>
                                <span wire:loading wire:target="generateBulkFromCourse">Packaging certificates…</span>
                            </flux:button>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="mb-2 font-semibold text-slate-900 dark:text-white">CSV import (optional)</h2>
                    <p class="mb-4 text-sm text-slate-500">For external records or one-off batches outside enrolled students.</p>

                    <label class="flex cursor-pointer flex-col items-center rounded-xl border-2 border-dashed border-slate-300 px-6 py-8 text-center transition hover:border-blue-400 dark:border-zinc-600">
                        <flux:icon.arrow-up-tray class="size-8 text-slate-400" />
                        <span class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-300">Upload CSV</span>
                        <span class="mt-1 text-xs text-slate-500">candidate_name, candidate_no, module_name, …</span>
                        <input type="file" wire:model="csvFile" accept=".csv" class="hidden">
                    </label>

                    @if($csvFile)
                        <p class="mt-2 text-sm text-emerald-600">{{ $csvFile->getClientOriginalName() }}</p>
                    @endif

                    @if(count($bulkCandidates) > 0)
                        <p class="mt-4 text-sm font-medium text-slate-700">{{ count($bulkCandidates) }} candidate(s) loaded</p>
                        <flux:button class="mt-3 w-full" wire:click="generateBulk" wire:loading.attr="disabled">
                            Download CSV batch ZIP
                        </flux:button>
                    @endif

                    <a href="{{ route('certificates.sample-csv') }}" class="mt-4 inline-block text-sm text-blue-600 hover:underline" download>Download sample CSV</a>
                </div>
            </div>
        @endif
    </div>
</div>
