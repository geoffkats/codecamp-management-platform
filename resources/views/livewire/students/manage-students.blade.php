@php
    $hasFilters = $search || $filterClass || $filterEnrollment || $filterEnrollmentCourseId || $filterCategory || $filterReadiness || $filterModuleId || ($showCampFilters && $filterCamp !== 'all') || (($showClubFilters ?? false) && $filterClub !== 'all');
    $activeCamps = $campOptions->where('status', 'active');
    $codeClubView = $isCodeClubView ?? false;
    $unassignedKey = \App\Livewire\Students\ManageStudents::CLASS_FILTER_UNASSIGNED;
    $selectedCount = count($selected);
    $addUrl = $isIct ? route('students.create-ict') : ($codeClubView ? route('students.create-codeclub') : route('students.create'));
    $title = $isIct ? 'ICT Students' : ($codeClubView ? 'Code Club Students' : 'Students');
    $subtitle = $isIct
        ? 'Your school’s ICT learners, module progress and ICDL exam readiness.'
        : ($codeClubView ? 'Club members, classes, enrolments and contact details.' : 'Every learner across camps, courses and programs — in one place.');
    $avatarColors = ['bg-orange-500', 'bg-sky-500', 'bg-emerald-500', 'bg-violet-500', 'bg-rose-500', 'bg-amber-500', 'bg-teal-500', 'bg-indigo-500'];
    $programBadge = [
        'codecamp' => ['Codecamp', 'bg-orange-50 text-orange-700 ring-orange-100 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/20'],
        'ict' => ['ICT', 'bg-sky-50 text-sky-700 ring-sky-100 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20'],
        'codeclub' => ['Code Club', 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20'],
    ];
    $showProgramColumn = $showProgramTabs && $filterProgram === 'all';
    $chip = 'inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold ring-1 transition';
    $chipOn = 'bg-gray-900 text-white ring-gray-900 dark:bg-white dark:text-gray-900 dark:ring-white';
    $chipOff = 'bg-white text-gray-600 ring-gray-200 hover:ring-gray-300 hover:text-gray-900 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700';
    $th = 'px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400';
@endphp

<div class="min-h-screen bg-gray-50/70 pb-28 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6">

        {{-- Hero --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-500 to-orange-600 p-6 text-white shadow-lg shadow-orange-500/20 sm:p-7">
            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-24 right-40 h-56 w-56 rounded-full bg-white/5"></div>

            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-xl">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-orange-100">People</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $title }}</h1>
                    <p class="mt-1 text-sm text-orange-50/90">{{ $subtitle }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($showCodeClubImport ?? false)
                        <button type="button" wire:click="toggleImportPanel"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 px-3.5 py-2 text-sm font-semibold text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white/25">
                            <flux:icon name="arrow-up-tray" variant="mini" class="size-4" />
                            {{ $showImportPanel ? 'Hide import' : 'Bulk import' }}
                        </button>
                    @endif
                    <a href="{{ $addUrl }}" wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-sm font-bold text-orange-700 shadow-sm transition hover:bg-orange-50">
                        <flux:icon name="user-plus" variant="mini" class="size-4" />
                        Add student
                    </a>
                </div>
            </div>

            <div class="relative mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @php
                    $pills = [['Total students', $stats['total'], 'users']];
                    if ($codeClubView) {
                        $pills[] = ['Showing now', $stats['matching'], 'funnel'];
                        $pills[] = ['Classes', ($classes ?? collect())->count(), 'rectangle-group'];
                        $pills[] = ['No class yet', $stats['unassigned'] ?? 0, 'question-mark-circle'];
                    } elseif ($isIct) {
                        $pills[] = ['Showing now', $stats['matching'], 'funnel'];
                        $pills[] = ['Modules', $courses->count(), 'book-open'];
                        $pills[] = ['No class yet', $stats['unassigned'] ?? 0, 'question-mark-circle'];
                    } else {
                        $pills[] = ['Showing now', $stats['matching'], 'funnel'];
                        $pills[] = $showCampFilters ? ['In an active camp', $stats['in_camp'], 'flag'] : ['Codecamp', $stats['codecamp'], 'code-bracket'];
                        $pills[] = ['No class yet', $stats['unassigned'] ?? 0, 'question-mark-circle'];
                    }
                @endphp
                @foreach($pills as [$label, $value, $icon])
                    <div class="flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/20">
                            <flux:icon :name="$icon" variant="outline" class="size-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xl font-extrabold leading-none">{{ number_format($value) }}</p>
                            <p class="mt-1 truncate text-[11px] font-semibold uppercase tracking-wide text-orange-50/90">{{ $label }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Flash --}}
        @if(session()->has('message'))
            <div class="flex items-start gap-3 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20">
                <flux:icon name="check-circle" variant="solid" class="mt-0.5 size-5 shrink-0 text-emerald-500" />
                <p>{{ session('message') }}</p>
            </div>
        @endif

        {{-- Code Club import --}}
        @if(($showCodeClubImport ?? false) && $showImportPanel)
            <section class="rounded-2xl bg-white p-5 ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Bulk import Code Club students</h2>
                        <p class="mt-0.5 text-sm text-gray-500">Upload a CSV (save Excel as CSV). Each row becomes a student with a login.</p>
                    </div>
                    <button type="button" wire:click="downloadCodeClubImportTemplate" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold text-orange-600 ring-1 ring-orange-200 hover:bg-orange-50 dark:ring-orange-500/30 dark:hover:bg-orange-500/10">
                        <flux:icon name="arrow-down-tray" variant="mini" class="size-4" /> Template
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    @if(($clubOptions ?? collect())->count() > 1)
                        <flux:select wire:model="importClubId" label="Import into club (required)">
                            <option value="">Select club…</option>
                            @foreach($clubOptions as $clubOpt)
                                <option value="{{ $clubOpt->id }}">{{ $clubOpt->name }}</option>
                            @endforeach
                        </flux:select>
                    @elseif(($clubOptions ?? collect())->count() === 1)
                        <div class="rounded-xl bg-orange-50 px-3 py-2 text-sm text-orange-900 dark:bg-orange-500/10 dark:text-orange-100">
                            Importing into <strong>{{ $clubOptions->first()->name }}</strong>
                        </div>
                    @endif

                    <x-codeclub.bulk-import-uploader wire:model="importCsv" :file="$importCsv" />
                    <x-codeclub.import-default-class />

                    @error('importCsv')
                        <div class="rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700 ring-1 ring-red-100 dark:bg-red-500/10 dark:text-red-300">{{ $message }}</div>
                    @enderror

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="importCodeClubStudents" wire:loading.attr="disabled" wire:target="importCsv,importCodeClubStudents"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <span wire:loading wire:target="importCodeClubStudents">Importing…</span>
                            <span wire:loading.remove wire:target="importCodeClubStudents">Run import</span>
                        </button>
                        <button type="button" wire:click="toggleImportPanel" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50 dark:text-zinc-300 dark:ring-zinc-700 dark:hover:bg-zinc-800">Cancel</button>
                    </div>

                    @if($importReport)
                        <div class="rounded-xl bg-gray-50 p-3 ring-1 ring-gray-100 dark:bg-zinc-800/50 dark:ring-zinc-700">
                            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $importReport['imported'] }} imported · {{ $importReport['skipped'] }} skipped</p>
                            @if(!empty($importReport['errors']))
                                <ul class="mt-2 max-h-32 list-disc space-y-1 overflow-y-auto pl-5 text-xs text-red-700 dark:text-red-300">
                                    @foreach($importReport['errors'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- Program tabs --}}
        @if($showProgramTabs)
            <div class="flex flex-wrap gap-1 rounded-2xl bg-white p-1.5 ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
                @foreach(array_filter([
                    'all' => ['All programs', $stats['total']],
                    'codecamp' => ['Codecamp', $stats['codecamp']],
                    'ict' => ['ICT', $stats['ict']],
                    'codeclub' => config('features.code_club', false) ? ['Code Club', $stats['codeclub']] : null,
                ]) as $key => [$label, $count])
                    <button type="button" wire:click="$set('filterProgram', '{{ $key }}')"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition {{ $filterProgram === $key ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                        {{ $label }}
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $filterProgram === $key ? 'bg-white/20' : 'bg-gray-100 text-gray-500 dark:bg-zinc-800' }}">{{ number_format($count) }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Filters --}}
        <section class="rounded-2xl bg-white p-4 ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <flux:icon name="magnifying-glass" variant="mini" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                    <input type="search" wire:model.live.debounce.300ms="search"
                        placeholder="{{ $isIct ? 'Search name, student ID or ICDL number…' : 'Search name, student ID or parent contact…' }}"
                        class="w-full rounded-xl border-0 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 ring-1 ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500/50 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700">
                </div>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center">
                    @php $select = 'rounded-xl border-0 bg-gray-50 py-2.5 pl-3 pr-8 text-sm font-medium text-gray-700 ring-1 ring-gray-200 focus:ring-2 focus:ring-orange-500/50 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700'; @endphp
                    @unless($codeClubView)
                        <select wire:model.live="filterClass" class="{{ $select }}" aria-label="Class">
                            <option value="">All classes</option>
                            <option value="{{ $unassignedKey }}">No class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class }}">{{ $class }}</option>
                            @endforeach
                        </select>
                    @endunless

                    @if($isIct)
                        <select wire:model.live="filterModuleId" class="{{ $select }}" aria-label="Module">
                            <option value="">All modules</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterReadiness" class="{{ $select }}" aria-label="Readiness">
                            <option value="">Any readiness</option>
                            <option value="not_ready">Not ready</option>
                            <option value="student_requested">Requested exam</option>
                            <option value="teacher_approved">Exam ready</option>
                            <option value="needs_practice">Needs practice</option>
                            <option value="exam_completed">Exam completed</option>
                        </select>
                    @else
                        @if($showCampFilters && $campOptions->count() > 3)
                            <select wire:model.live="filterCamp" class="{{ $select }}" aria-label="Camp">
                                <option value="all">All camps</option>
                                @foreach($campOptions as $campOpt)
                                    <option value="{{ $campOpt->id }}">{{ $campOpt->name }} ({{ $campOpt->status }})</option>
                                @endforeach
                            </select>
                        @endif
                        @if(($showClubFilters ?? false) && ($clubOptions ?? collect())->isNotEmpty())
                            <select wire:model.live="filterClub" class="{{ $select }}" aria-label="Code Club">
                                <option value="all">All clubs</option>
                                @foreach($clubOptions as $clubOpt)
                                    <option value="{{ $clubOpt->id }}">{{ $clubOpt->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <select wire:model.live="filterEnrollment" class="{{ $select }}" aria-label="Enrolment">
                            <option value="">Any enrolment</option>
                            <option value="enrolled">Enrolled</option>
                            <option value="not_enrolled">Not enrolled</option>
                        </select>
                        <select wire:model.live="filterEnrollmentCourseId" class="{{ $select }}" aria-label="Course">
                            <option value="">Any course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    @endif

                    @if($hasFilters)
                        <button type="button" wire:click="clearFilters" class="inline-flex items-center justify-center gap-1 rounded-xl px-3 py-2.5 text-sm font-semibold text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-500/10">
                            <flux:icon name="x-mark" variant="mini" class="size-4" /> Clear
                        </button>
                    @endif
                </div>
            </div>

            @if(($showCampFilters && $activeCamps->isNotEmpty()) || ($codeClubView && ($classes ?? collect())->isNotEmpty()))
                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3 dark:border-zinc-800">
                    @if($showCampFilters && $activeCamps->isNotEmpty())
                        <span class="mr-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">Active camps</span>
                        <button type="button" wire:click="selectCamp('all')" class="{{ $chip }} {{ $filterCamp === 'all' ? $chipOn : $chipOff }}">All</button>
                        @foreach($activeCamps as $camp)
                            <button type="button" wire:click="selectCamp('{{ $camp->id }}')" class="{{ $chip }} {{ (string) $filterCamp === (string) $camp->id ? $chipOn : $chipOff }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ $camp->name }}
                            </button>
                        @endforeach
                    @else
                        <span class="mr-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">Class</span>
                        <button type="button" wire:click="selectClass('')" class="{{ $chip }} {{ $filterClass === '' ? $chipOn : $chipOff }}">All</button>
                        @if(($stats['unassigned'] ?? 0) > 0)
                            <button type="button" wire:click="selectClass('{{ $unassignedKey }}')" class="{{ $chip }} {{ $filterClass === $unassignedKey ? $chipOn : $chipOff }}">
                                No class <span class="opacity-60">{{ $stats['unassigned'] }}</span>
                            </button>
                        @endif
                        @foreach($classes as $classOption)
                            <button type="button" wire:click="selectClass('{{ $classOption }}')" class="{{ $chip }} {{ $filterClass === $classOption ? $chipOn : $chipOff }}">{{ $classOption }}</button>
                        @endforeach
                    @endif
                </div>
            @endif
        </section>

        {{-- List --}}
        <section class="overflow-hidden rounded-2xl bg-white ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-zinc-800">
                <p class="text-sm text-gray-500">
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($stats['matching']) }}</span>
                    {{ \Illuminate\Support\Str::plural('student', $stats['matching']) }}{{ $hasFilters ? ' match your filters' : '' }}
                </p>
                <div wire:loading.flex wire:target="search,filterClass,filterProgram,filterCamp,filterCampStatus,filterClub,filterEnrollment,filterEnrollmentCourseId,filterCategory,filterReadiness,filterModuleId,selectCamp,selectClass,clearFilters" class="items-center gap-2 text-xs font-medium text-orange-600">
                    <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Updating…
                </div>
            </div>

            <div class="overflow-x-auto"
                 wire:loading.class="opacity-60"
                 wire:target="search,filterClass,filterProgram,filterCamp,filterCampStatus,filterClub,filterEnrollment,filterEnrollmentCourseId,filterCategory,filterReadiness,filterModuleId,selectCamp,selectClass,clearFilters">
                <table class="min-w-full">
                    <thead class="bg-gray-50/80 dark:bg-zinc-800/50">
                        <tr>
                            <th class="w-10 py-3 pl-4 pr-2">
                                <input type="checkbox" wire:model.live="selectAll" aria-label="Select all on this page"
                                    class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500 dark:border-zinc-600 dark:bg-zinc-800">
                            </th>
                            <th class="{{ $th }}">Student</th>
                            <th class="{{ $th }}">Class</th>
                            @if($isIct)
                                <th class="{{ $th }}">Modules</th>
                                <th class="{{ $th }}">Readiness</th>
                                <th class="{{ $th }}">Exam</th>
                            @else
                                <th class="{{ $th }}">{{ $codeClubView ? 'Club' : 'Camp' }}</th>
                                <th class="{{ $th }}">Courses</th>
                                <th class="{{ $th }}">Parent contact</th>
                                @unless($codeClubView)
                                    <th class="{{ $th }}">Uniform</th>
                                @endunless
                            @endif
                            <th class="{{ $th }} text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                        @forelse($students as $student)
                            @php
                                $enrollments = $student->user?->enrollments ?? collect();
                                $enrollmentCount = $enrollments->count();
                                $avgProgress = $enrollmentCount > 0 ? (int) round($enrollments->avg('progress_percentage')) : 0;
                                $moduleTitles = $enrollments->pluck('course.title')->filter()->values();
                                $currentCamp = $student->user?->currentCampEnrollment?->camp;
                                $activeClub = $student->user?->activeCodeClubMembership?->club;
                                $isSelected = in_array((string) $student->id, array_map('strval', $selected), true);
                                $initials = collect(preg_split('/\s+/', trim((string) $student->full_name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                                $avatar = $avatarColors[$student->id % count($avatarColors)];
                                [$readinessLabel, $readinessCls] = match($student->exam_readiness_status ?? 'not_ready') {
                                    'student_requested' => ['Requested', 'bg-sky-50 text-sky-700 ring-sky-100'],
                                    'teacher_approved' => ['Exam ready', 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
                                    'needs_practice' => ['Needs practice', 'bg-amber-50 text-amber-700 ring-amber-100'],
                                    'exam_completed' => ['Completed', 'bg-violet-50 text-violet-700 ring-violet-100'],
                                    default => ['Not ready', 'bg-gray-100 text-gray-600 ring-gray-200'],
                                };
                                $editUrl = $isIct
                                    ? route('students.edit-ict', $student->id)
                                    : ($student->program_type === 'codeclub' && config('features.code_club', false) ? route('students.edit-codeclub', $student->id) : route('students.edit', $student->id));
                            @endphp
                            <tr wire:key="student-{{ $student->id }}" class="group transition-colors {{ $isSelected ? 'bg-orange-50/60 dark:bg-orange-500/5' : 'hover:bg-gray-50/80 dark:hover:bg-zinc-800/40' }}">
                                <td class="py-3 pl-4 pr-2">
                                    <input type="checkbox" value="{{ $student->id }}" wire:model.live="selected" aria-label="Select {{ $student->full_name }}"
                                        class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500 dark:border-zinc-600 dark:bg-zinc-800">
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('students.show', $student->id) }}" wire:navigate class="flex items-center gap-3">
                                        <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $avatar }} text-sm font-bold text-white">
                                            {{ $initials ?: '?' }}
                                            @if(! $student->is_active)
                                                <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-gray-400 ring-2 ring-white dark:ring-zinc-900" title="Inactive"></span>
                                            @endif
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-bold text-gray-900 group-hover:text-orange-600 dark:text-white">{{ $student->full_name }}</span>
                                            <span class="mt-0.5 flex items-center gap-1.5">
                                                <span class="font-mono text-[11px] text-gray-400">{{ $student->student_id }}</span>
                                                @if($showProgramColumn && isset($programBadge[$student->program_type]))
                                                    <span class="rounded-full px-1.5 py-px text-[10px] font-bold ring-1 {{ $programBadge[$student->program_type][1] }}">{{ $programBadge[$student->program_type][0] }}</span>
                                                @endif
                                            </span>
                                        </span>
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    @if($student->class_grade)
                                        <span class="rounded-lg bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $student->class_grade }}</span>
                                    @else
                                        <span class="text-xs text-gray-300 dark:text-zinc-600">—</span>
                                    @endif
                                </td>

                                @if($isIct)
                                    <td class="px-4 py-3">
                                        @if($moduleTitles->isNotEmpty())
                                            <p class="max-w-[180px] truncate text-sm font-medium text-gray-700 dark:text-zinc-300" title="{{ $moduleTitles->implode(', ') }}">
                                                {{ $moduleTitles->first() }}@if($moduleTitles->count() > 1)<span class="text-gray-400"> +{{ $moduleTitles->count() - 1 }}</span>@endif
                                            </p>
                                            <div class="mt-1.5 flex items-center gap-2">
                                                <div class="h-1.5 w-24 overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-800"><div class="h-full rounded-full bg-sky-500" style="width: {{ $avgProgress }}%"></div></div>
                                                <span class="text-[11px] font-semibold text-gray-500">{{ $avgProgress }}%</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Not enrolled</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ring-1 {{ $readinessCls }}">{{ $readinessLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        <p><span class="text-gray-400">Request:</span> {{ ucfirst(str_replace('_', ' ', $student->exam_request_status ?? 'not requested')) }}</p>
                                        <p class="mt-0.5"><span class="text-gray-400">Payment:</span> {{ ucfirst(str_replace('_', ' ', $student->exam_payment_status ?? 'not submitted')) }}</p>
                                    </td>
                                @else
                                    <td class="px-4 py-3">
                                        @if($codeClubView)
                                            @if($activeClub)
                                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ $activeClub->name }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-300 dark:text-zinc-600">—</span>
                                            @endif
                                        @elseif($currentCamp)
                                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-semibold {{ $currentCamp->status === 'active' ? 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300' : 'bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $currentCamp->status === 'active' ? 'bg-sky-500' : 'bg-gray-400' }}"></span>{{ $currentCamp->name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-300 dark:text-zinc-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($enrollmentCount > 0)
                                            <p class="text-sm font-semibold text-gray-800 dark:text-zinc-200">{{ $enrollmentCount }} {{ \Illuminate\Support\Str::plural('course', $enrollmentCount) }}</p>
                                            <div class="mt-1.5 flex items-center gap-2" title="{{ $moduleTitles->implode(', ') }}">
                                                <div class="h-1.5 w-24 overflow-hidden rounded-full bg-gray-100 dark:bg-zinc-800"><div class="h-full rounded-full {{ $avgProgress >= 80 ? 'bg-emerald-500' : 'bg-orange-500' }}" style="width: {{ $avgProgress }}%"></div></div>
                                                <span class="text-[11px] font-semibold text-gray-500">{{ $avgProgress }}%</span>
                                            </div>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-500 dark:bg-zinc-800">Not enrolled</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($student->parent_guardian_contact)
                                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $student->parent_guardian_contact) }}" class="inline-flex items-center gap-1.5 text-gray-600 hover:text-orange-600 dark:text-zinc-300">
                                                <flux:icon name="phone" variant="micro" class="size-3.5 text-gray-400" />{{ $student->parent_guardian_contact }}
                                            </a>
                                            @if($student->parent_guardian_name)
                                                <p class="mt-0.5 truncate text-[11px] text-gray-400">{{ $student->parent_guardian_name }}</p>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-300 dark:text-zinc-600">—</span>
                                        @endif
                                    </td>
                                    @unless($codeClubView)
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold ring-1 {{ $student->uniform_paid ? 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20' : 'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20' }}">
                                                {{ $student->uniform_paid ? 'Paid' : 'Pending' }}
                                            </span>
                                            @if(($student->uniforms_count ?? 0) > 1)
                                                <span class="mt-0.5 block text-[11px] text-gray-500 dark:text-zinc-400">{{ $student->paid_uniforms_count }}/{{ $student->uniforms_count }} uniforms paid</span>
                                            @endif
                                        </td>
                                    @endunless
                                @endif

                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('students.show', $student->id) }}" wire:navigate title="View profile"
                                           class="rounded-lg p-2 text-gray-400 transition hover:bg-orange-50 hover:text-orange-600 dark:hover:bg-orange-500/10">
                                            <flux:icon name="eye" variant="mini" class="size-4" />
                                        </a>
                                        <a href="{{ $editUrl }}" wire:navigate title="Edit"
                                           class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
                                            <flux:icon name="pencil-square" variant="mini" class="size-4" />
                                        </a>
                                        @if($isIct)
                                            <div x-data="{ open: false }" class="inline-block">
                                                <button type="button" @click="open = true" title="More actions"
                                                    class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-zinc-800">
                                                    <flux:icon name="ellipsis-horizontal" variant="mini" class="size-4" />
                                                </button>
                                                <div x-show="open" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="open = false">
                                                    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="open = false"></div>
                                                    <div class="absolute inset-0 flex items-center justify-center p-4">
                                                        <div class="w-full max-w-xs overflow-hidden rounded-2xl bg-white text-left shadow-2xl ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
                                                            <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-3 dark:border-zinc-800">
                                                                <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $avatar }} text-xs font-bold text-white">{{ $initials ?: '?' }}</span>
                                                                <p class="min-w-0 flex-1 truncate text-sm font-bold text-gray-900 dark:text-white">{{ $student->full_name }}</p>
                                                                <button type="button" @click="open = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"><flux:icon name="x-mark" variant="mini" class="size-4" /></button>
                                                            </div>
                                                            @php $menuItem = 'flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm font-medium transition'; @endphp
                                                            <div class="space-y-0.5 p-2">
                                                                <a href="{{ route('students.print-credentials', $student->id) }}" target="_blank" class="{{ $menuItem }} text-gray-700 hover:bg-gray-50 dark:text-zinc-300 dark:hover:bg-zinc-800"><flux:icon name="printer" variant="mini" class="size-4 text-gray-400" />Print credentials</a>
                                                                <button type="button" @click="open = false" wire:click="markExamReady({{ $student->id }})" wire:confirm="Mark student as ICDL Test Ready?" class="{{ $menuItem }} text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-500/10"><flux:icon name="check-badge" variant="mini" class="size-4" />Mark exam ready</button>
                                                                <button type="button" @click="open = false" wire:click="markNeedsPractice({{ $student->id }})" class="{{ $menuItem }} text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-500/10"><flux:icon name="arrow-path" variant="mini" class="size-4" />Needs practice</button>
                                                                <button type="button" @click="open = false" wire:click="requestExamSession({{ $student->id }})" wire:confirm="Request exam session?" class="{{ $menuItem }} text-sky-700 hover:bg-sky-50 dark:text-sky-400 dark:hover:bg-sky-500/10"><flux:icon name="calendar-days" variant="mini" class="size-4" />Request exam</button>
                                                                <button type="button" @click="open = false" wire:click="submitExamPayment({{ $student->id }})" wire:confirm="Submit exam payment?" class="{{ $menuItem }} text-teal-700 hover:bg-teal-50 dark:text-teal-400 dark:hover:bg-teal-500/10"><flux:icon name="banknotes" variant="mini" class="size-4" />Submit payment</button>
                                                                <div class="my-1 border-t border-gray-100 dark:border-zinc-800"></div>
                                                                <button type="button" @click="open = false" wire:click="removeStudent({{ $student->id }})" wire:confirm="Remove this student from the active list?" class="{{ $menuItem }} text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"><flux:icon name="user-minus" variant="mini" class="size-4" />Remove from list</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-16">
                                    <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-500 dark:bg-orange-500/10">
                                            <flux:icon name="user-group" variant="outline" class="size-7" />
                                        </div>
                                        <p class="mt-4 text-base font-bold text-gray-900 dark:text-white">{{ $hasFilters ? 'No students match these filters' : 'No students yet' }}</p>
                                        <p class="mt-1 text-sm text-gray-500">{{ $hasFilters ? 'Try a different search or clear the filters.' : 'Add your first student to get started.' }}</p>
                                        <div class="mt-4">
                                            @if($hasFilters)
                                                <button type="button" wire:click="clearFilters" class="rounded-xl px-4 py-2 text-sm font-semibold text-orange-600 ring-1 ring-orange-200 hover:bg-orange-50">Clear filters</button>
                                            @else
                                                <a href="{{ $addUrl }}" wire:navigate class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-bold text-white hover:bg-orange-600">Add student</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
                <div class="border-t border-gray-100 px-4 py-3 dark:border-zinc-800">{{ $students->links() }}</div>
            @endif
        </section>
    </div>

    {{-- Floating bulk bar --}}
    @if($selectedCount > 0)
        @php
            $canDeactivate = auth()->user()->isAdmin() || auth()->user()->isSupervisor() || auth()->user()->isOperationsManager();
            $canDelete = auth()->user()->isAdmin() || auth()->user()->isSupervisor();
            $barBtn = 'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition';
        @endphp
        <div class="fixed inset-x-0 bottom-5 z-40 flex justify-center px-4">
            <div class="flex max-w-full flex-wrap items-center gap-2 rounded-2xl bg-cau-deep px-3 py-2.5 text-white shadow-2xl shadow-gray-900/30 ring-1 ring-white/10">
                <span class="flex items-center gap-2 pl-1 pr-2 text-sm font-semibold">
                    <span class="flex h-7 min-w-7 items-center justify-center rounded-lg bg-orange-500 px-1.5 text-xs font-extrabold">{{ $selectedCount }}</span>
                    selected
                </span>
                <span class="hidden h-6 w-px bg-white/15 sm:block"></span>
                <button type="button" wire:click="openAssignModal" class="{{ $barBtn }} bg-orange-500 hover:bg-orange-600">
                    <flux:icon name="academic-cap" variant="mini" class="size-4" />{{ $isIct ? 'Enrol in module' : 'Assign course' }}
                </button>
                @if($codeClubView)
                    <button type="button" wire:click="openBulkClassModal" class="{{ $barBtn }} hover:bg-white/10"><flux:icon name="rectangle-group" variant="mini" class="size-4" />Set class</button>
                @endif
                <button type="button" wire:click="exportSelectedCsv" class="{{ $barBtn }} hover:bg-white/10"><flux:icon name="arrow-down-tray" variant="mini" class="size-4" />Export CSV</button>
                <button type="button" wire:click="printSelectedCredentials" class="{{ $barBtn }} hover:bg-white/10"><flux:icon name="printer" variant="mini" class="size-4" />Print logins</button>

                @if($codeClubView || ($canBulkAdminActions && ($canDeactivate || $canDelete)))
                    <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="{{ $barBtn }} hover:bg-white/10">
                            More <flux:icon name="chevron-up" variant="micro" class="size-3.5 transition" ::class="open ? '' : 'rotate-180'" />
                        </button>
                        <div x-show="open" x-cloak x-transition.origin.bottom
                             class="absolute bottom-full right-0 mb-2 w-64 overflow-hidden rounded-2xl bg-white p-1.5 text-gray-700 shadow-2xl ring-1 ring-gray-100 dark:bg-zinc-900 dark:text-zinc-200 dark:ring-zinc-800">
                            @if($codeClubView)
                                <button type="button" @click="open = false" wire:click="bulkRemoveFromClub" wire:confirm="Remove selected students from the club? They will stay in the system."
                                    class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm font-medium hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-500/10">
                                    <flux:icon name="arrow-right-start-on-rectangle" variant="mini" class="size-4 text-amber-500" />Remove from club
                                </button>
                            @endif
                            @if($canBulkAdminActions)
                                <p class="px-3 pb-1 pt-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">Danger zone · export a backup first</p>
                                @if($canDeactivate)
                                    <button type="button" @click="open = false" wire:click="bulkDeactivateStudents" wire:confirm="Deactivate selected students? They will no longer be able to sign in."
                                        class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm font-medium hover:bg-gray-50 dark:hover:bg-zinc-800">
                                        <flux:icon name="no-symbol" variant="mini" class="size-4 text-gray-400" />Deactivate accounts
                                    </button>
                                @endif
                                @if($canDelete)
                                    <button type="button" @click="open = false" wire:click="bulkDeleteStudents" wire:confirm="Permanently remove selected students from the system? Profiles will be deleted, login accounts deactivated, and this action will appear in Audit Logs. Export a CSV backup first if needed."
                                        class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                        <flux:icon name="trash" variant="mini" class="size-4" />Delete from system
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                <button type="button" wire:click="clearSelection" title="Clear selection" class="rounded-xl p-2 text-white/60 transition hover:bg-white/10 hover:text-white">
                    <flux:icon name="x-mark" variant="mini" class="size-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Assign to course --}}
    @if($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" wire:keydown.escape="closeAssignModal">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
                <div class="flex items-start gap-3 px-6 pt-6">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/10"><flux:icon name="academic-cap" variant="outline" class="size-5" /></div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ $isIct ? 'Enrol in module' : 'Assign to course' }}</h2>
                        <p class="mt-0.5 text-sm text-gray-500">{{ $selectedCount }} {{ \Illuminate\Support\Str::plural('student', $selectedCount) }} selected. Existing enrolments are kept.</p>
                    </div>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <flux:select wire:model.live="selectedCourseId" label="{{ $isIct ? 'Module' : 'Course' }}">
                        <option value="">Select {{ $isIct ? 'a module' : 'a course' }}</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </flux:select>
                    @error('selectedCourseId') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    @error('selected') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <label class="flex items-center gap-2.5 rounded-xl bg-gray-50 px-3 py-2.5 text-sm text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">
                        <input type="checkbox" wire:model.live="notifyStudents" class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                        Notify students about the new course
                    </label>
                </div>
                <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <button type="button" wire:click="closeAssignModal" class="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-zinc-300 dark:hover:bg-zinc-800">Cancel</button>
                    <button type="button" wire:click="assignSelectedToCourse" wire:loading.attr="disabled" class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-orange-600 disabled:opacity-60">Assign</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Set class --}}
    @if($showBulkClassModal ?? false)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" wire:keydown.escape="closeBulkClassModal">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
                <div class="flex items-start gap-3 px-6 pt-6">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10"><flux:icon name="rectangle-group" variant="outline" class="size-5" /></div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Set class</h2>
                        <p class="mt-0.5 text-sm text-gray-500">Applies to {{ $selectedCount }} selected {{ \Illuminate\Support\Str::plural('student', $selectedCount) }}.</p>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-zinc-300">Class / grade</label>
                    <input type="text" wire:model="bulkClassGrade" placeholder="e.g. P.3"
                        class="mt-1.5 w-full rounded-xl border-0 bg-gray-50 px-3 py-2.5 text-sm ring-1 ring-gray-200 focus:bg-white focus:ring-2 focus:ring-orange-500/50 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700" />
                    @error('bulkClassGrade') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <button type="button" wire:click="closeBulkClassModal" class="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-zinc-300 dark:hover:bg-zinc-800">Cancel</button>
                    <button type="button" wire:click="applyBulkClassGrade" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700">Apply</button>
                </div>
            </div>
        </div>
    @endif
</div>
