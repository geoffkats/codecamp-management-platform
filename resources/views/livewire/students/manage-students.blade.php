@php
    $hasFilters = $search || $filterClass || $filterEnrollment || $filterEnrollmentCourseId || $filterCategory || $filterReadiness || $filterModuleId || ($showCampFilters && $filterCamp !== 'all') || (($showClubFilters ?? false) && $filterClub !== 'all');
    $activeCamps = $campOptions->where('status', 'active');
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">

    {{-- Header --}}
    <div class="border-b-4 border-blue-600 bg-orange-600">
        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-white">{{ $isIct ? 'ICT Students' : (($isCodeClubView ?? false) ? 'Code Club Students' : 'Students') }}</h1>
                    <p class="mt-0.5 text-sm text-orange-100">
                        @if($isIct)
                            ICT student list for your school
                        @elseif($isCodeClubView ?? false)
                            Manage Code Club members, enrolments, and contact details
                        @else
                            Manage student information, enrolments, and camps
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button"
                        wire:click="openAssignModal"
                        @if(count($selected) === 0) disabled @endif
                        class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 transition">
                        {{ $isIct ? 'Enroll in Module' : 'Assign to Course' }}
                    </button>
                    @if($isIct || ($isCodeClubView ?? false))
                        <button type="button"
                            wire:click="printSelectedCredentials"
                            @if(count($selected) === 0) disabled @endif
                            class="inline-flex items-center rounded-lg border border-white/30 px-3 py-1.5 text-sm font-semibold text-white hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-50 transition">
                            Print (PDF)
                        </button>
                        <button type="button"
                            wire:click="exportSelectedCsv"
                            @if(count($selected) === 0) disabled @endif
                            class="inline-flex items-center rounded-lg border border-white/30 px-3 py-1.5 text-sm font-semibold text-white hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-50 transition">
                            Export (CSV)
                        </button>
                    @endif
                    <a href="{{ $isIct ? route('students.create-ict') : (($isCodeClubView ?? false) ? route('students.create-codeclub') : route('students.create')) }}"
                       wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-sm font-semibold text-orange-700 hover:bg-orange-50 transition">
                        + Add Student
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-5 sm:px-6">

        {{-- Flash --}}
        @if(session()->has('message'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
                {{ session('message') }}
            </div>
        @endif

        {{-- Program tabs (admin / supervisor only) --}}
        @if($showProgramTabs)
            <div class="flex flex-wrap gap-2">
                @foreach(array_filter([
                    'all' => 'All Programs',
                    'codecamp' => 'Codecamp',
                    'ict' => 'ICT',
                    'codeclub' => config('features.code_club', false) ? 'Code Club' : null,
                ]) as $key => $label)
                    <button type="button" wire:click="$set('filterProgram', '{{ $key }}')"
                        class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm font-medium transition
                            {{ $filterProgram === $key
                                ? 'border-orange-600 bg-orange-600 text-white'
                                : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        @endif

        @if($showCodeClubImport ?? false)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Code Club bulk import</p>
                        <p class="text-xs text-amber-800/80 dark:text-amber-200/70 mt-0.5">Upload a CSV (save Excel as CSV). Select a Code Club filter below, or import from Admin → Code Clubs → your club.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="downloadCodeClubImportTemplate" class="text-xs font-semibold text-blue-600 hover:underline">Template</button>
                        <button type="button" wire:click="toggleImportPanel" class="inline-flex items-center rounded-lg bg-amber-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-amber-700">
                            {{ $showImportPanel ? 'Hide Import' : '↑ Bulk Import Students' }}
                        </button>
                    </div>
                </div>
                @if($showImportPanel)
                    <div class="mt-4 space-y-4 rounded-xl border border-amber-300/60 bg-white/70 p-4 dark:border-amber-800 dark:bg-zinc-900/40">
                        @if(($clubOptions ?? collect())->count() > 1)
                            <flux:select wire:model="importClubId" label="Import into club (required)">
                                <option value="">Select club…</option>
                                @foreach($clubOptions as $clubOpt)
                                    <option value="{{ $clubOpt->id }}">{{ $clubOpt->name }}</option>
                                @endforeach
                            </flux:select>
                        @elseif(($clubOptions ?? collect())->count() === 1)
                            <div class="rounded-lg bg-amber-100/80 px-3 py-2 text-sm text-amber-900 dark:bg-amber-900/30 dark:text-amber-100">
                                Importing into: <strong>{{ $clubOptions->first()->name }}</strong>
                            </div>
                        @endif

                        <x-codeclub.bulk-import-uploader wire:model="importCsv" :file="$importCsv" />

                        <x-codeclub.import-default-class />

                        @error('importCsv')
                            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <button type="button" wire:click="importCodeClubStudents" wire:loading.attr="disabled" wire:target="importCsv,importCodeClubStudents"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                                <svg wire:loading.remove wire:target="importCodeClubStudents" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span wire:loading wire:target="importCodeClubStudents">Importing…</span>
                                <span wire:loading.remove wire:target="importCodeClubStudents">Run Import</span>
                            </button>
                            <button type="button" wire:click="toggleImportPanel" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:text-slate-300 dark:hover:bg-zinc-800">
                                Cancel
                            </button>
                        </div>

                        @if($importReport)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $importReport['imported'] }} imported · {{ $importReport['skipped'] }} skipped</p>
                                @if(!empty($importReport['errors']))
                                    <ul class="mt-2 max-h-32 list-disc space-y-1 pl-5 text-xs text-red-700 dark:text-red-300 overflow-y-auto">
                                        @foreach($importReport['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if(($isCodeClubView ?? false) && count($selected) > 0)
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/50 dark:bg-blue-950/30">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">
                        {{ count($selected) }} student{{ count($selected) === 1 ? '' : 's' }} selected
                    </p>
                    <div class="flex flex-1 flex-wrap gap-2">
                        <button type="button" wire:click="openAssignModal" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">Assign Course</button>
                        <button type="button" wire:click="openBulkClassModal" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Set Class</button>
                        <button type="button" wire:click="exportSelectedCsv" class="rounded-lg border border-blue-300 bg-white px-3 py-1.5 text-xs font-semibold text-blue-800 hover:bg-blue-100 dark:border-blue-800 dark:bg-zinc-900 dark:text-blue-200">Export CSV</button>
                        <button type="button" wire:click="printSelectedCredentials" class="rounded-lg border border-blue-300 bg-white px-3 py-1.5 text-xs font-semibold text-blue-800 hover:bg-blue-100 dark:border-blue-800 dark:bg-zinc-900 dark:text-blue-200">Print Credentials</button>
                        <button type="button" wire:click="bulkRemoveFromClub" wire:confirm="Remove selected students from the club? They will stay in the system." class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">Remove from Club</button>
                        <button type="button" wire:click="clearSelection" class="rounded-lg border border-blue-300 px-3 py-1.5 text-xs font-semibold text-blue-800 hover:bg-blue-100 dark:border-blue-800 dark:text-blue-200">Clear</button>
                    </div>
                </div>

                @if($canBulkAdminActions ?? false)
                    <x-students.bulk-advanced-panel
                        :can-deactivate="auth()->user()->isAdmin() || auth()->user()->isSupervisor() || auth()->user()->isOperationsManager()"
                        :can-delete="auth()->user()->isAdmin() || auth()->user()->isSupervisor()"
                        export-action="exportSelectedCsv"
                    />
                @endif
            </div>
        @endif

        {{-- Camp quick-pills (codecamp only) --}}
        @if($showCampFilters && $activeCamps->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Camp:</span>
                <button type="button" wire:click="selectCamp('all')"
                    class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-medium transition
                        {{ $filterCamp === 'all'
                            ? 'border-blue-600 bg-blue-600 text-white'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300' }}">
                    All
                </button>
                @foreach($activeCamps as $camp)
                    <button type="button" wire:click="selectCamp('{{ $camp->id }}')"
                        class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-medium transition
                            {{ (string) $filterCamp === (string) $camp->id
                                ? 'border-blue-600 bg-blue-600 text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300' }}">
                        {{ $camp->name }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Class quick-pills (Code Club) --}}
        @if(($isCodeClubView ?? false) && ($classes ?? collect())->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Class:</span>
                <button type="button" wire:click="selectClass('')"
                    class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-medium transition
                        {{ $filterClass === ''
                            ? 'border-emerald-600 bg-emerald-600 text-white'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300' }}">
                    All
                </button>
                @if(($stats['unassigned'] ?? 0) > 0)
                    <button type="button" wire:click="selectClass('{{ \App\Livewire\Students\ManageStudents::CLASS_FILTER_UNASSIGNED }}')"
                        class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-medium transition
                            {{ $filterClass === \App\Livewire\Students\ManageStudents::CLASS_FILTER_UNASSIGNED
                                ? 'border-emerald-600 bg-emerald-600 text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300' }}">
                        Unassigned
                    </button>
                @endif
                @foreach($classes as $classOption)
                    <button type="button" wire:click="selectClass('{{ $classOption }}')"
                        class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-medium transition
                            {{ $filterClass === $classOption
                                ? 'border-emerald-600 bg-emerald-600 text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300' }}">
                        {{ $classOption }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total</p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            </div>
            @if($isCodeClubView ?? false)
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Showing</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format($stats['matching']) }}</p>
                </div>
            @elseif(!$isIct && !$isCodecampOnly && $showProgramTabs)
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Codecamp</p>
                    <p class="mt-1 text-2xl font-bold text-orange-600">{{ number_format($stats['codecamp']) }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">ICT</p>
                    <p class="mt-1 text-2xl font-bold text-blue-600">{{ number_format($stats['ict']) }}</p>
                </div>
                @if(config('features.code_club', false))
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Code Club</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format($stats['codeclub']) }}</p>
                </div>
                @endif
            @else
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Matching</p>
                    <p class="mt-1 text-2xl font-bold text-blue-600">{{ number_format($stats['matching']) }}</p>
                </div>
            @endif
            @if($showCampFilters)
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">In Active Camp</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format($stats['in_camp']) }}</p>
                </div>
            @else
                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Showing</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['matching']) }}</p>
                </div>
            @endif
        </div>

        {{-- Filters --}}
        <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-{{ $isIct ? '4' : '5' }} lg:items-end">
                <div class="sm:col-span-2 lg:col-span-1">
                    <flux:input wire:model.live.debounce.300ms="search"
                        placeholder="{{ $isIct ? 'Name, ID, or ICDL #' : 'Name, ID, or contact…' }}" />
                </div>

                <div>
                    <flux:select wire:model.live="filterClass" label="Class / grade">
                        <option value="">All classes</option>
                        <option value="{{ \App\Livewire\Students\ManageStudents::CLASS_FILTER_UNASSIGNED }}">Unassigned</option>
                        @foreach($classes as $class)
                            <option value="{{ $class }}">{{ $class }}</option>
                        @endforeach
                    </flux:select>
                </div>

                @if($isIct)
                    <div>
                        <flux:select wire:model.live="filterModuleId" label="Module">
                            <option value="">All modules</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model.live="filterReadiness" label="Readiness">
                            <option value="">All readiness</option>
                            <option value="not_ready">Not Ready</option>
                            <option value="student_requested">Requested Exam</option>
                            <option value="teacher_approved">Exam Ready</option>
                            <option value="needs_practice">Needs Practice</option>
                            <option value="exam_completed">Exam Completed</option>
                        </flux:select>
                    </div>
                @else
                    @if($showCampFilters && $campOptions->count() > 3)
                        <div>
                            <flux:select wire:model.live="filterCamp" label="Camp">
                                <option value="all">All camps</option>
                                @foreach($campOptions as $campOpt)
                                    <option value="{{ $campOpt->id }}">{{ $campOpt->name }} ({{ $campOpt->status }})</option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif
                    @if(($showClubFilters ?? false) && ($clubOptions ?? collect())->isNotEmpty())
                        <div>
                            <flux:select wire:model.live="filterClub" label="Code Club">
                                <option value="all">All clubs</option>
                                @foreach($clubOptions as $clubOpt)
                                    <option value="{{ $clubOpt->id }}">{{ $clubOpt->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif
                    <div>
                        <flux:select wire:model.live="filterEnrollment" label="Enrolment">
                            <option value="">Any status</option>
                            <option value="enrolled">Enrolled</option>
                            <option value="not_enrolled">Not enrolled</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:select wire:model.live="filterEnrollmentCourseId" label="Course">
                            <option value="">Any course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                <div class="flex items-end gap-2">
                    @if($hasFilters)
                        <flux:button variant="ghost" size="sm" wire:click="clearFilters">Clear</flux:button>
                    @endif
                    @if(count($selected) > 0)
                        <span class="text-xs text-slate-500">{{ count($selected) }} selected</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto"
                 wire:loading.class="opacity-60"
                 wire:target="search,filterClass,filterProgram,filterCamp,filterCampStatus,filterClub,filterEnrollment,filterEnrollmentCourseId,filterCategory,filterReadiness,filterModuleId">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-700">
                    <thead class="bg-slate-50 dark:bg-zinc-800/80">
                        <tr>
                            <th class="px-3 py-2.5">
                                <input type="checkbox" wire:model.live="selectAll"
                                    class="rounded border-slate-300 text-blue-600 shadow-sm">
                            </th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Student</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Class</th>
                            @if($isIct)
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Module</th>
                                <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Progress</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Readiness</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Exam Request</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Payment</th>
                            @else
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">{{ ($isCodeClubView ?? false) ? 'Club' : 'Camp' }}</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Courses</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Contact</th>
                                @if(!($isCodeClubView ?? false))
                                    <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Uniform</th>
                                @endif
                            @endif
                            <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        @forelse($students as $student)
                            @php
                                $enrollments = $student->user?->enrollments ?? collect();
                                $enrollmentCount = $enrollments->count();
                                $avgProgress = $enrollmentCount > 0 ? round($enrollments->avg('progress_percentage'), 1) : 0;
                                $moduleTitles = $enrollments->pluck('course.title')->filter()->values();
                                $currentCamp = $student->user?->currentCampEnrollment?->camp;
                                $activeClub = $student->user?->activeCodeClubMembership?->club;
                                $readinessLabel = match($student->exam_readiness_status ?? 'not_ready') {
                                    'student_requested' => 'Requested',
                                    'teacher_approved' => 'Exam Ready',
                                    'needs_practice' => 'Needs Practice',
                                    'exam_completed' => 'Completed',
                                    default => 'Not Ready',
                                };
                                $readinessCls = match($student->exam_readiness_status ?? 'not_ready') {
                                    'teacher_approved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
                                    'needs_practice' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
                                    'student_requested' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200',
                                    'exam_completed' => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-200',
                                    default => 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-300',
                                };
                            @endphp
                            <tr class="hover:bg-orange-50/40 dark:hover:bg-orange-950/10">
                                <td class="px-3 py-2.5">
                                    <input type="checkbox" value="{{ $student->id }}" wire:model.live="selected"
                                        class="rounded border-slate-300 text-blue-600 shadow-sm">
                                </td>
                                <td class="px-3 py-2.5">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $student->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $student->student_id }}</p>
                                    @if(!$isIct && $enrollmentCount > 0)
                                        <span class="mt-0.5 inline-flex rounded-full bg-emerald-100 px-1.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                                            {{ $enrollmentCount }} enrolled
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-sm text-slate-600 dark:text-slate-400">{{ $student->class_grade ?: '—' }}</td>

                                @if($isIct)
                                    <td class="px-3 py-2.5 text-sm text-slate-600 dark:text-slate-400">
                                        @if($moduleTitles->isNotEmpty())
                                            <span class="inline-block max-w-[140px] truncate rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-zinc-800"
                                                  title="{{ $moduleTitles->implode(', ') }}">
                                                {{ $moduleTitles->first() }}
                                            </span>
                                            @if($moduleTitles->count() > 1)
                                                <span class="text-xs text-slate-400">+{{ $moduleTitles->count() - 1 }}</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400">Not enrolled</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-right text-sm font-semibold text-slate-900 dark:text-white">{{ $avgProgress }}%</td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $readinessCls }}">
                                            {{ $readinessLabel }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-slate-600 dark:text-slate-400">
                                        {{ ucfirst(str_replace('_', ' ', $student->exam_request_status ?? 'not requested')) }}
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-slate-600 dark:text-slate-400">
                                        {{ ucfirst(str_replace('_', ' ', $student->exam_payment_status ?? 'not submitted')) }}
                                    </td>
                                @else
                                    <td class="px-3 py-2.5 text-sm text-slate-600 dark:text-slate-400">
                                        @if($isCodeClubView ?? false)
                                            @if($activeClub)
                                                <span class="inline-flex items-center gap-1 rounded bg-emerald-100 px-1.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                                                    {{ $activeClub->name }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400">—</span>
                                            @endif
                                        @elseif($currentCamp)
                                            <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-xs font-medium
                                                {{ $currentCamp->status === 'active' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200' : 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-slate-300' }}">
                                                {{ $currentCamp->name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-sm text-slate-600 dark:text-slate-400">
                                        @if($enrollmentCount > 0)
                                            <span class="text-xs text-slate-700 dark:text-slate-300">{{ $enrollmentCount }} course{{ $enrollmentCount !== 1 ? 's' : '' }}</span>
                                            <span class="block text-xs text-slate-400">avg {{ $avgProgress }}%</span>
                                        @else
                                            <span class="text-xs text-slate-400">None</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-slate-600 dark:text-slate-400">
                                        {{ $student->parent_guardian_contact ?: '—' }}
                                    </td>
                                    @if(!($isCodeClubView ?? false))
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                            {{ $student->uniform_paid ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' }}">
                                            {{ $student->uniform_paid ? 'Paid' : 'Pending' }}
                                        </span>
                                    </td>
                                    @endif
                                @endif

                                <td class="px-3 py-2.5 text-right">
                                    @if($isIct)
                                        <div x-data="{ open: false }" class="inline-block">
                                            <button type="button" @click="open = true"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-300">
                                                Actions
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                            <div x-show="open" x-cloak class="fixed inset-0 z-50">
                                                <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
                                                <div class="absolute inset-0 flex items-center justify-center p-4">
                                                    <div class="w-full max-w-xs rounded-xl border border-slate-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                                                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-zinc-700">
                                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $student->full_name }}</p>
                                                            <button @click="open = false" class="text-slate-400 hover:text-slate-600">✕</button>
                                                        </div>
                                                        <div class="space-y-1 p-3">
                                                            <a href="{{ route('students.show', $student->id) }}" wire:navigate class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-zinc-800">View Profile</a>
                                                            <a href="{{ route('students.print-credentials', $student->id) }}" target="_blank" class="block rounded-lg px-3 py-2 text-sm text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/30">Print Credentials</a>
                                                            <a href="{{ route('students.edit-ict', $student->id) }}" wire:navigate class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-zinc-800">Edit</a>
                                                            <button type="button" @click="open = false" wire:click="markExamReady({{ $student->id }})"
                                                                onclick="return confirm('Mark student as ICDL Test Ready?')"
                                                                class="block w-full rounded-lg px-3 py-2 text-left text-sm text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/30">Mark Ready</button>
                                                            <button type="button" @click="open = false" wire:click="markNeedsPractice({{ $student->id }})"
                                                                class="block w-full rounded-lg px-3 py-2 text-left text-sm text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/30">Needs Practice</button>
                                                            <button type="button" @click="open = false" wire:click="requestExamSession({{ $student->id }})"
                                                                onclick="return confirm('Request exam session?')"
                                                                class="block w-full rounded-lg px-3 py-2 text-left text-sm text-teal-700 hover:bg-teal-50 dark:text-teal-400 dark:hover:bg-teal-950/30">Request Exam</button>
                                                            <button type="button" @click="open = false" wire:click="submitExamPayment({{ $student->id }})"
                                                                onclick="return confirm('Submit exam payment?')"
                                                                class="block w-full rounded-lg px-3 py-2 text-left text-sm text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/30">Submit Payment</button>
                                                            <button type="button" @click="open = false" wire:click="removeStudent({{ $student->id }})"
                                                                onclick="return confirm('Remove this student from active list?')"
                                                                class="block w-full rounded-lg px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30">Remove</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('students.show', $student->id) }}" wire:navigate
                                                class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">View</a>
                                            <a href="{{ $student->program_type === 'codeclub' && config('features.code_club', false) ? route('students.edit-codeclub', $student->id) : route('students.edit', $student->id) }}" wire:navigate
                                                class="text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400">Edit</a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isIct ? 10 : (($isCodeClubView ?? false) ? 8 : 9) }}" class="px-4 py-10 text-center text-sm text-slate-500">
                                    No students found.
                                    @if($hasFilters)
                                        <button wire:click="clearFilters" class="ml-2 text-blue-600 hover:underline">Clear filters</button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($students->hasPages())
            <div>{{ $students->links() }}</div>
        @endif
    </div>

    {{-- Assign to Course modal --}}
    @if($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-zinc-700">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ $isIct ? 'Enroll in Module' : 'Assign to Course' }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500">{{ count($selected) }} student(s) selected</p>
                </div>
                <div class="space-y-4 p-5">
                    <div>
                        <flux:select wire:model.live="selectedCourseId" label="{{ $isIct ? 'Module' : 'Course' }}">
                            <option value="">Select {{ $isIct ? 'a module' : 'a course' }}</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </flux:select>
                        @error('selectedCourseId')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input type="checkbox" wire:model.live="notifyStudents"
                            class="rounded border-slate-300 text-blue-600 shadow-sm">
                        Send notification to students
                    </label>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 px-5 py-4 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="closeAssignModal">Cancel</flux:button>
                    <flux:button class="!bg-blue-600 !text-white hover:!bg-blue-700" wire:click="assignSelectedToCourse">Assign</flux:button>
                </div>
            </div>
        </div>
    @endif

    @if($showBulkClassModal ?? false)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-zinc-700">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Set class for {{ count($selected) }} student(s)</h2>
                </div>
                <div class="p-5">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Class / Grade</label>
                    <input type="text" wire:model="bulkClassGrade" placeholder="e.g. P.3, P.2"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" />
                    @error('bulkClassGrade') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 px-5 py-4 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="closeBulkClassModal">Cancel</flux:button>
                    <flux:button class="!bg-emerald-600 !text-white hover:!bg-emerald-700" wire:click="applyBulkClassGrade">Apply to Selected</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
