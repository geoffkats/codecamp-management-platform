@php
    $programLabels = ['codecamp' => 'Code Camp', 'ict' => 'ICT', 'codeclub' => 'Code Club'];
    if (! config('features.code_club', false)) {
        unset($programLabels['codeclub']);
    }
    $tabs = [
        'enrollments' => ['label' => 'Enrollments', 'count' => $stats['active_enrollments']],
        'students' => ['label' => 'Students', 'count' => $stats['total_students']],
        'instructors' => ['label' => 'Instructors', 'count' => $stats['unassigned_courses'] ? $stats['unassigned_courses'] . ' unassigned' : null],
        'requests' => ['label' => 'Requests', 'count' => $stats['pending_requests'] ?: null, 'alert' => $stats['pending_requests'] > 0],
        'invitations' => ['label' => 'Invitations', 'count' => $stats['active_invitations'] ?: null],
    ];
    $flash = session('enrollment_flash');
    $flashStyles = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-200',
        'error' => 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950/50 dark:text-red-200',
        'info' => 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-950/50 dark:text-blue-200',
    ];
    $input = 'w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/30 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100';
    $btnPrimary = 'inline-flex items-center justify-center gap-2 rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500/40 disabled:cursor-not-allowed disabled:opacity-60';
    $btnSecondary = 'inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800';
    $btnDangerSoft = 'inline-flex items-center justify-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40';
    $btnLinkSoft = 'inline-flex items-center justify-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-blue-800 transition hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-950/40';
    $filterSelect = 'w-auto rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/30 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100';
    $th = 'px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400';
    $td = 'px-4 py-3 align-middle text-sm text-zinc-700 dark:text-zinc-300';
@endphp

<div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
    {{-- Branded header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0f1f4d] via-[#1e3a8a] to-[#1d4ed8] p-6 text-white shadow-lg sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-orange-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-sky-400/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white p-1.5 shadow-md">
                    @if ($brandLogo)
                        <img src="{{ asset('storage/' . $brandLogo) }}" alt="{{ $brandName }}" class="h-full w-full object-contain">
                    @else
                        <span class="text-lg font-black text-orange-600">CA</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-orange-300">{{ $brandName }}</p>
                    <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Enrollment Management</h1>
                    <p class="mt-1 text-sm text-blue-100/80">Admit students, assign courses and manage the instructors who teach them.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($tab === 'enrollments')
                    <button type="button" wire:click="exportEnrollments" class="inline-flex items-center gap-2 rounded-lg border border-white/25 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20">
                        <flux:icon.arrow-down-tray class="size-4" />
                        Export CSV
                    </button>
                @endif
                <button type="button" wire:click="openEnrollPanel" class="{{ $btnPrimary }} shadow-orange-900/30">
                    <flux:icon.user-plus class="size-4" />
                    Enroll students
                </button>
            </div>
        </div>

        {{-- Stats --}}
        <div class="relative mt-8 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            @foreach ([
                ['label' => 'Active enrollments', 'value' => $stats['active_enrollments'], 'icon' => 'academic-cap'],
                ['label' => 'Students learning', 'value' => $stats['enrolled_students'], 'icon' => 'users'],
                ['label' => 'Registered students', 'value' => $stats['total_students'], 'icon' => 'identification'],
                ['label' => 'Courses without lead', 'value' => $stats['unassigned_courses'], 'icon' => 'exclamation-triangle', 'warn' => $stats['unassigned_courses'] > 0],
                ['label' => 'Pending requests', 'value' => $stats['pending_requests'], 'icon' => 'inbox-arrow-down', 'warn' => $stats['pending_requests'] > 0],
                ['label' => 'Open invitations', 'value' => $stats['active_invitations'], 'icon' => 'envelope'],
            ] as $stat)
                <div class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-blue-100/80">{{ $stat['label'] }}</p>
                        <flux:icon :name="$stat['icon']" class="size-4 {{ ! empty($stat['warn']) ? 'text-orange-300' : 'text-blue-200/70' }}" />
                    </div>
                    <p class="mt-2 text-2xl font-bold {{ ! empty($stat['warn']) ? 'text-orange-300' : 'text-white' }}">{{ number_format($stat['value']) }}</p>
                </div>
            @endforeach
        </div>
    </header>

    @if ($flash)
        <div wire:key="flash-{{ md5($flash['message'] . microtime()) }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
             class="flex items-start justify-between gap-4 rounded-xl border px-4 py-3 text-sm {{ $flashStyles[$flash['type']] ?? $flashStyles['info'] }}" role="status">
            <span>{{ $flash['message'] }}</span>
            <button type="button" x-on:click="show = false" class="opacity-70 hover:opacity-100" aria-label="Dismiss">
                <flux:icon.x-mark class="size-4" />
            </button>
        </div>
    @endif

    {{-- Main panel --}}
    <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        {{-- Tabs --}}
        <nav class="flex gap-1 overflow-x-auto border-b border-zinc-200 px-2 dark:border-zinc-800" aria-label="Enrollment sections">
            @foreach ($tabs as $key => $meta)
                <button type="button" wire:click="setTab('{{ $key }}')"
                        class="relative flex shrink-0 items-center gap-2 px-4 py-3.5 text-sm font-semibold transition {{ $tab === $key ? 'text-blue-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
                    {{ $meta['label'] }}
                    @if ($meta['count'] !== null)
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ ! empty($meta['alert']) ? 'bg-orange-600 text-white' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }}">{{ is_numeric($meta['count']) ? number_format($meta['count']) : $meta['count'] }}</span>
                    @endif
                    @if ($tab === $key)
                        <span class="absolute inset-x-3 -bottom-px h-0.5 rounded-full bg-orange-600"></span>
                    @endif
                </button>
            @endforeach
        </nav>

        {{-- Filters --}}
        <div class="flex flex-col gap-3 border-b border-zinc-200 bg-zinc-50/60 p-4 dark:border-zinc-800 dark:bg-zinc-900/60 lg:flex-row lg:items-center">
            <div class="relative flex-1 lg:min-w-64">
                <flux:icon.magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                <input type="search" wire:model.live.debounce.350ms="search" class="{{ $input }} pl-9"
                       placeholder="{{ $tab === 'instructors' ? 'Search courses or instructors…' : 'Search by name, email or student ID…' }}">
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($tab !== 'instructors')
                    <select wire:model.live="courseFilter" class="{{ $filterSelect }} min-w-44 max-w-64">
                        <option value="">All courses</option>
                        @foreach ($allCourses as $c)
                            <option value="{{ $c->id }}">{{ $c->title }}{{ $c->is_published ? '' : ' (draft)' }}</option>
                        @endforeach
                    </select>
                @endif

                @if (in_array($tab, ['enrollments', 'students'], true))
                    <select wire:model.live="programFilter" class="{{ $filterSelect }}">
                        <option value="all">All programs</option>
                        @foreach ($programLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @endif

                @if ($tab === 'enrollments')
                    @if ($camps->isNotEmpty() && in_array($programFilter, ['all', 'codecamp'], true))
                        <select wire:model.live="campFilter" class="{{ $filterSelect }}">
                            <option value="">All camps</option>
                            @foreach ($camps as $camp)
                                <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    @if ($clubs->isNotEmpty() && in_array($programFilter, ['all', 'codeclub'], true))
                        <select wire:model.live="clubFilter" class="{{ $filterSelect }}">
                            <option value="">All clubs</option>
                            @foreach ($clubs as $club)
                                <option value="{{ $club->id }}">{{ $club->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select wire:model.live="enrollmentStatus" class="{{ $filterSelect }}">
                        <option value="active">In progress</option>
                        <option value="completed">Completed</option>
                        <option value="all">All statuses</option>
                    </select>
                @elseif ($tab === 'instructors')
                    <select wire:model.live="instructorFilter" class="{{ $filterSelect }}">
                        <option value="all">All courses</option>
                        <option value="unassigned">Without lead instructor</option>
                        <option value="published">Published only</option>
                    </select>
                @elseif ($tab === 'requests')
                    <select wire:model.live="requestStatus" class="{{ $filterSelect }}">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="all">All</option>
                    </select>
                @elseif ($tab === 'invitations')
                    <select wire:model.live="invitationStatus" class="{{ $filterSelect }}">
                        <option value="active">Open</option>
                        <option value="accepted">Accepted</option>
                        <option value="declined">Declined</option>
                        <option value="expired">Expired / cancelled</option>
                        <option value="all">All</option>
                    </select>
                @endif

                @if ($search !== '' || $courseFilter || $programFilter !== 'all' || $campFilter || $clubFilter || $instructorFilter !== 'all')
                    <button type="button" wire:click="clearFilters" class="{{ $btnSecondary }}">
                        <flux:icon.x-mark class="size-4" /> Clear
                    </button>
                @endif
            </div>
        </div>

        <div class="relative">
            <div wire:loading.delay.flex wire:target="setTab,search,courseFilter,programFilter,campFilter,clubFilter,enrollmentStatus,requestStatus,invitationStatus,instructorFilter,clearFilters,gotoPage,nextPage,previousPage"
                 class="absolute inset-0 z-10 hidden items-start justify-center bg-white/60 pt-16 dark:bg-zinc-900/60">
                <div class="flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-zinc-600 shadow dark:bg-zinc-800 dark:text-zinc-300">
                    <flux:icon.arrow-path class="size-4 animate-spin text-orange-600" /> Loading…
                </div>
            </div>

            {{-- ============ ENROLLMENTS ============ --}}
            @if ($tab === 'enrollments')
                @php $pageIds = $enrollments->pluck('id')->map(fn ($id) => (string) $id)->values()->all(); @endphp

                @if (count($selectedEnrollments) > 0)
                    <div class="flex items-center justify-between gap-3 border-b border-orange-200 bg-orange-50 px-4 py-2.5 text-sm dark:border-orange-900 dark:bg-orange-950/40">
                        <span class="font-medium text-orange-900 dark:text-orange-200">{{ count($selectedEnrollments) }} selected</span>
                        <div class="flex gap-2">
                            <button type="button" wire:click="$set('selectedEnrollments', [])" class="{{ $btnSecondary }} py-1.5">Clear</button>
                            <button type="button" wire:click="removeSelectedEnrollments"
                                    wire:confirm="Remove {{ count($selectedEnrollments) }} enrollment(s)? Students lose access to these courses."
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700">
                                <flux:icon.trash class="size-4" /> Remove selected
                            </button>
                        </div>
                    </div>
                @endif

                @if ($enrollments->isEmpty())
                    @include('livewire.admin.partials.enrollment-empty', ['title' => 'No enrollments found', 'text' => 'Try changing the filters, or enroll students into a course.', 'action' => true])
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                            <thead class="bg-zinc-50 dark:bg-zinc-900/80">
                                <tr>
                                    <th class="w-10 px-4 py-3">
                                        <input type="checkbox" class="rounded border-zinc-300 text-orange-600 focus:ring-orange-500 dark:border-zinc-600 dark:bg-zinc-800"
                                               @checked(count($pageIds) > 0 && count(array_diff($pageIds, $selectedEnrollments)) === 0)
                                               x-on:change="$wire.set('selectedEnrollments', $event.target.checked ? @js($pageIds) : [])"
                                               aria-label="Select all on this page">
                                    </th>
                                    <th class="{{ $th }}">Student</th>
                                    <th class="{{ $th }}">Course</th>
                                    <th class="{{ $th }}">Program</th>
                                    <th class="{{ $th }}">Progress</th>
                                    <th class="{{ $th }}">Enrolled</th>
                                    <th class="{{ $th }} text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($enrollments as $enrollment)
                                    @php
                                        $student = $enrollment->user;
                                        $profile = $student?->studentProfile;
                                        $progress = (int) round($enrollment->progress_percentage ?? 0);
                                        $enrolledAt = $enrollment->enrolled_at ?? $enrollment->created_at;
                                    @endphp
                                    <tr wire:key="enrollment-{{ $enrollment->id }}" class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" value="{{ $enrollment->id }}" wire:model.live="selectedEnrollments"
                                                   class="rounded border-zinc-300 text-orange-600 focus:ring-orange-500 dark:border-zinc-600 dark:bg-zinc-800">
                                        </td>
                                        <td class="{{ $td }}">
                                            @include('livewire.admin.partials.enrollment-person', ['person' => $student, 'sub' => $profile?->student_id ?: $student?->email])
                                        </td>
                                        <td class="{{ $td }} font-medium text-zinc-900 dark:text-zinc-100">{{ $enrollment->course?->title ?? '—' }}</td>
                                        <td class="{{ $td }}">
                                            <div class="flex flex-col gap-1">
                                                @if ($profile?->program_type)
                                                    <span class="inline-flex w-fit rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-950/50 dark:text-blue-300">{{ $programLabels[$profile->program_type] ?? ucfirst($profile->program_type) }}</span>
                                                @endif
                                                @if ($enrollment->camp || $enrollment->club)
                                                    <span class="text-xs text-zinc-500">{{ $enrollment->camp?->name ?? $enrollment->club?->name }}</span>
                                                @endif
                                                @if (! $profile?->program_type && ! $enrollment->camp && ! $enrollment->club)
                                                    <span class="text-xs text-zinc-400">—</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="{{ $td }} min-w-36">
                                            @if ($enrollment->completed_at)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                                    <flux:icon.check-circle class="size-3.5" /> Completed
                                                </span>
                                            @else
                                                <div class="flex items-center gap-2">
                                                    <div class="h-1.5 w-24 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                                        <div class="h-full rounded-full bg-orange-500" style="width: {{ max(0, min(100, $progress)) }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-medium tabular-nums text-zinc-500">{{ $progress }}%</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="{{ $td }} whitespace-nowrap text-xs text-zinc-500">
                                            @if ($enrolledAt)
                                                <span title="{{ $enrolledAt->format('d M Y, H:i') }}">{{ $enrolledAt->format('d M Y') }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="{{ $td }} text-right">
                                            <div class="flex justify-end gap-1">
                                                @if ($student)
                                                    <button type="button" wire:click="manageStudent({{ $student->id }})" class="{{ $btnLinkSoft }}">Courses</button>
                                                @endif
                                                <button type="button" wire:click="removeEnrollment({{ $enrollment->id }})"
                                                        wire:confirm="Remove {{ $student?->name ?? 'this student' }} from {{ $enrollment->course?->title ?? 'this course' }}?"
                                                        class="{{ $btnDangerSoft }}">
                                                    <flux:icon.user-minus class="size-3.5" /> Remove
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800">{{ $enrollments->links() }}</div>
                @endif
            @endif

            {{-- ============ STUDENTS ============ --}}
            @if ($tab === 'students')
                @if ($students->isEmpty())
                    @include('livewire.admin.partials.enrollment-empty', ['title' => 'No students found', 'text' => 'Student accounts are created from User Management. Adjust the filters to see more.', 'action' => false])
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                            <thead class="bg-zinc-50 dark:bg-zinc-900/80">
                                <tr>
                                    <th class="{{ $th }}">Student</th>
                                    <th class="{{ $th }}">Program</th>
                                    <th class="{{ $th }}">Courses</th>
                                    <th class="{{ $th }} text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($students as $student)
                                    @php $profile = $student->studentProfile; @endphp
                                    <tr wire:key="student-{{ $student->id }}" class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                        <td class="{{ $td }}">
                                            @include('livewire.admin.partials.enrollment-person', ['person' => $student, 'sub' => trim(($profile?->student_id ? $profile->student_id . ' · ' : '') . $student->email)])
                                        </td>
                                        <td class="{{ $td }}">
                                            @if ($profile?->program_type)
                                                <span class="inline-flex rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-950/50 dark:text-blue-300">{{ $programLabels[$profile->program_type] ?? ucfirst($profile->program_type) }}</span>
                                                @if ($profile->class_grade)
                                                    <span class="ml-1 text-xs text-zinc-500">{{ $profile->class_grade }}</span>
                                                @endif
                                            @else
                                                <span class="text-xs text-zinc-400">No profile</span>
                                            @endif
                                        </td>
                                        <td class="{{ $td }}">
                                            @if ($student->enrollments->isEmpty())
                                                <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">Not enrolled</span>
                                            @else
                                                <div class="flex max-w-md flex-wrap gap-1">
                                                    @foreach ($student->enrollments->take(3) as $e)
                                                        <span class="inline-flex max-w-48 truncate rounded-md px-2 py-0.5 text-xs font-medium {{ $e->completed_at ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}" title="{{ $e->course?->title }}">{{ $e->course?->title }}</span>
                                                    @endforeach
                                                    @if ($student->enrollments->count() > 3)
                                                        <span class="text-xs font-medium text-zinc-500">+{{ $student->enrollments->count() - 3 }} more</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="{{ $td }} text-right">
                                            <button type="button" wire:click="manageStudent({{ $student->id }})" class="{{ $btnSecondary }} py-1.5 text-xs">
                                                <flux:icon.book-open class="size-4" /> Manage courses
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800">{{ $students->links() }}</div>
                @endif
            @endif

            {{-- ============ INSTRUCTORS ============ --}}
            @if ($tab === 'instructors')
                @if ($courses->isEmpty())
                    @include('livewire.admin.partials.enrollment-empty', ['title' => 'No courses found', 'text' => 'Adjust the search or filter to see courses.', 'action' => false])
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                            <thead class="bg-zinc-50 dark:bg-zinc-900/80">
                                <tr>
                                    <th class="{{ $th }}">Course</th>
                                    <th class="{{ $th }}">Lead instructor</th>
                                    <th class="{{ $th }}">Co-instructors</th>
                                    <th class="{{ $th }}">Students</th>
                                    <th class="{{ $th }} text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($courses as $course)
                                    <tr wire:key="course-{{ $course->id }}" class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                        <td class="{{ $td }}">
                                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $course->title }}</p>
                                            <span class="mt-0.5 inline-flex rounded px-1.5 py-0.5 text-[11px] font-semibold {{ $course->is_published ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">{{ $course->is_published ? 'Published' : 'Draft' }}</span>
                                        </td>
                                        <td class="{{ $td }}">
                                            @if ($course->instructor)
                                                @include('livewire.admin.partials.enrollment-person', ['person' => $course->instructor, 'sub' => $course->instructor->email])
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-xs font-semibold text-orange-700 dark:bg-orange-950/50 dark:text-orange-300">
                                                    <flux:icon.exclamation-triangle class="size-3.5" /> Unassigned
                                                </span>
                                            @endif
                                        </td>
                                        <td class="{{ $td }}">
                                            @if ($course->collaboratorUsers->isEmpty())
                                                <span class="text-xs text-zinc-400">None</span>
                                            @else
                                                <div class="flex -space-x-2">
                                                    @foreach ($course->collaboratorUsers->take(4) as $co)
                                                        <span class="flex size-8 items-center justify-center rounded-full border-2 border-white bg-blue-900 text-[11px] font-bold text-white dark:border-zinc-900" title="{{ $co->name }} ({{ $co->pivot->role }})">{{ $co->initials() }}</span>
                                                    @endforeach
                                                    @if ($course->collaboratorUsers->count() > 4)
                                                        <span class="flex size-8 items-center justify-center rounded-full border-2 border-white bg-zinc-200 text-[11px] font-bold text-zinc-700 dark:border-zinc-900 dark:bg-zinc-700 dark:text-zinc-200">+{{ $course->collaboratorUsers->count() - 4 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="{{ $td }} tabular-nums">{{ number_format($course->students_count) }}</td>
                                        <td class="{{ $td }} text-right">
                                            <div class="flex justify-end gap-1">
                                                <button type="button" wire:click="openEnrollPanel({{ $course->id }})" class="{{ $btnLinkSoft }}">
                                                    <flux:icon.user-plus class="size-3.5" /> Enroll
                                                </button>
                                                <button type="button" wire:click="manageCourse({{ $course->id }})" class="{{ $btnSecondary }} py-1.5 text-xs">
                                                    <flux:icon.user-group class="size-4" /> Manage team
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800">{{ $courses->links() }}</div>
                @endif
            @endif

            {{-- ============ REQUESTS ============ --}}
            @if ($tab === 'requests')
                @if ($requests->isEmpty())
                    @include('livewire.admin.partials.enrollment-empty', ['title' => $requestStatus === 'pending' ? 'No pending requests' : 'No requests found', 'text' => 'Requests appear here when students ask to join a course.', 'action' => false])
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                            <thead class="bg-zinc-50 dark:bg-zinc-900/80">
                                <tr>
                                    <th class="{{ $th }}">Student</th>
                                    <th class="{{ $th }}">Course</th>
                                    <th class="{{ $th }}">Message</th>
                                    <th class="{{ $th }}">Requested</th>
                                    <th class="{{ $th }}">Status</th>
                                    <th class="{{ $th }} text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($requests as $req)
                                    @php
                                        $requestedAt = $req->requested_at ?? $req->created_at;
                                        $statusStyle = match ($req->status) {
                                            'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
                                            'rejected' => 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300',
                                            default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300',
                                        };
                                    @endphp
                                    <tr wire:key="request-{{ $req->id }}" class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                        <td class="{{ $td }}">
                                            @include('livewire.admin.partials.enrollment-person', ['person' => $req->user, 'sub' => $req->user?->studentProfile?->student_id ?: $req->user?->email])
                                        </td>
                                        <td class="{{ $td }} font-medium text-zinc-900 dark:text-zinc-100">{{ $req->course?->title ?? 'Deleted course' }}</td>
                                        <td class="{{ $td }} max-w-xs">
                                            @if ($req->message)
                                                <p class="line-clamp-2 text-xs italic text-zinc-500" title="{{ $req->message }}">“{{ $req->message }}”</p>
                                            @else
                                                <span class="text-xs text-zinc-400">—</span>
                                            @endif
                                            @if ($req->status === 'rejected' && $req->rejection_reason)
                                                <p class="mt-1 line-clamp-2 text-xs text-red-600 dark:text-red-400" title="{{ $req->rejection_reason }}">Reason: {{ $req->rejection_reason }}</p>
                                            @endif
                                        </td>
                                        <td class="{{ $td }} whitespace-nowrap text-xs text-zinc-500">{{ $requestedAt?->format('d M Y') ?? '—' }}</td>
                                        <td class="{{ $td }}">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold capitalize {{ $statusStyle }}">{{ $req->status }}</span>
                                            @if ($req->reviewer && $req->status !== 'pending')
                                                <p class="mt-0.5 text-[11px] text-zinc-400">by {{ $req->reviewer->name }}</p>
                                            @endif
                                        </td>
                                        <td class="{{ $td }} text-right">
                                            @if ($req->status === 'pending')
                                                <div class="flex justify-end gap-1">
                                                    <button type="button" wire:click="approveRequest({{ $req->id }})" wire:loading.attr="disabled" wire:target="approveRequest({{ $req->id }})"
                                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">
                                                        <flux:icon.check class="size-3.5" /> Approve
                                                    </button>
                                                    <button type="button" wire:click="openReject({{ $req->id }})" class="{{ $btnDangerSoft }}">Reject</button>
                                                </div>
                                            @else
                                                <span class="text-xs text-zinc-400">{{ $req->reviewed_at?->format('d M Y') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800">{{ $requests->links() }}</div>
                @endif
            @endif

            {{-- ============ INVITATIONS ============ --}}
            @if ($tab === 'invitations')
                @if ($invitations->isEmpty())
                    @include('livewire.admin.partials.enrollment-empty', ['title' => 'No invitations found', 'text' => 'Use “Enroll students” and choose “Send invitation” to invite students.', 'action' => true])
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                            <thead class="bg-zinc-50 dark:bg-zinc-900/80">
                                <tr>
                                    <th class="{{ $th }}">Student</th>
                                    <th class="{{ $th }}">Course</th>
                                    <th class="{{ $th }}">Invited by</th>
                                    <th class="{{ $th }}">Expires</th>
                                    <th class="{{ $th }}">Status</th>
                                    <th class="{{ $th }} text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($invitations as $inv)
                                    @php
                                        $status = $inv->effectiveStatus();
                                        $statusStyle = match ($status) {
                                            'accepted' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
                                            'declined' => 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300',
                                            'expired' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
                                            default => 'bg-blue-50 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300',
                                        };
                                    @endphp
                                    <tr wire:key="invitation-{{ $inv->id }}" class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                        <td class="{{ $td }}">
                                            @include('livewire.admin.partials.enrollment-person', ['person' => $inv->user, 'sub' => $inv->user?->email])
                                        </td>
                                        <td class="{{ $td }} font-medium text-zinc-900 dark:text-zinc-100">{{ $inv->course?->title ?? 'Deleted course' }}</td>
                                        <td class="{{ $td }} text-xs">
                                            {{ $inv->inviter?->name ?? '—' }}
                                            <p class="text-zinc-400">{{ ($inv->invited_at ?? $inv->created_at)?->format('d M Y') }}</p>
                                        </td>
                                        <td class="{{ $td }} whitespace-nowrap text-xs text-zinc-500">
                                            @if ($inv->expires_at)
                                                <span title="{{ $inv->expires_at->format('d M Y, H:i') }}">{{ $inv->expires_at->isPast() ? 'Expired ' : 'In ' }}{{ $inv->expires_at->diffForHumans(null, true) }}{{ $inv->expires_at->isPast() ? ' ago' : '' }}</span>
                                            @else
                                                Never
                                            @endif
                                        </td>
                                        <td class="{{ $td }}">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold capitalize {{ $statusStyle }}">{{ $status === 'pending' ? 'Open' : $status }}</span>
                                        </td>
                                        <td class="{{ $td }} text-right">
                                            <div class="flex justify-end gap-1">
                                                @if (in_array($status, ['pending', 'expired', 'declined'], true) && $inv->course && $inv->user)
                                                    <button type="button" wire:click="resendInvitation({{ $inv->id }})" class="{{ $btnLinkSoft }}">
                                                        <flux:icon.arrow-path class="size-3.5" /> {{ $status === 'pending' ? 'Resend' : 'Re-invite' }}
                                                    </button>
                                                @endif
                                                @if ($status === 'pending')
                                                    <button type="button" wire:click="cancelInvitation({{ $inv->id }})" wire:confirm="Cancel this invitation?" class="{{ $btnDangerSoft }}">Cancel</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800">{{ $invitations->links() }}</div>
                @endif
            @endif
        </div>
    </section>

    {{-- ============ ENROLL PANEL ============ --}}
    @if ($showEnrollPanel)
        @php
            $selectedCourseCount = count($enrollCourseIds);
            $selectedStudentCount = count($enrollStudentIds);
        @endphp
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-labelledby="enroll-title" wire:keydown.escape.window="closeEnrollPanel">
            <div class="absolute inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="closeEnrollPanel"></div>
            <div class="relative flex h-full w-full max-w-2xl flex-col bg-white shadow-2xl dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4 bg-gradient-to-r from-[#0f1f4d] to-[#1e3a8a] px-6 py-5 text-white">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-orange-300">{{ $brandName }}</p>
                        <h2 id="enroll-title" class="mt-1 text-xl font-bold">Enroll students</h2>
                        <p class="text-sm text-blue-100/80">Pick courses and students, then enroll directly or send invitations.</p>
                    </div>
                    <button type="button" wire:click="closeEnrollPanel" class="rounded-lg p-1.5 text-white/80 hover:bg-white/10 hover:text-white" aria-label="Close">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">
                    {{-- Mode --}}
                    <div class="grid grid-cols-2 gap-2 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                        @foreach (['direct' => ['Enroll now', 'Students get access immediately'], 'invite' => ['Send invitation', 'Students accept before joining']] as $mode => [$label, $hint])
                            <button type="button" wire:click="$set('enrollMode', '{{ $mode }}')"
                                    class="rounded-lg px-3 py-2 text-left transition {{ $enrollMode === $mode ? 'bg-white shadow-sm dark:bg-zinc-900' : 'hover:bg-white/50 dark:hover:bg-zinc-900/50' }}">
                                <span class="block text-sm font-semibold {{ $enrollMode === $mode ? 'text-blue-900 dark:text-white' : 'text-zinc-600 dark:text-zinc-300' }}">{{ $label }}</span>
                                <span class="block text-xs text-zinc-500">{{ $hint }}</span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Courses --}}
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">1. Courses</label>
                            <span class="text-xs text-zinc-500">{{ $selectedCourseCount }} selected</span>
                        </div>
                        <div class="max-h-48 space-y-1 overflow-y-auto rounded-xl border border-zinc-200 p-2 dark:border-zinc-700">
                            @forelse ($allCourses as $c)
                                <label wire:key="enroll-course-{{ $c->id }}" class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                    <input type="checkbox" value="{{ $c->id }}" wire:model.live="enrollCourseIds" class="rounded border-zinc-300 text-orange-600 focus:ring-orange-500 dark:border-zinc-600 dark:bg-zinc-800">
                                    <span class="flex-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $c->title }}</span>
                                    @unless ($c->is_published)
                                        <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[11px] font-semibold text-zinc-500 dark:bg-zinc-800">Draft</span>
                                    @endunless
                                </label>
                            @empty
                                <p class="px-2 py-3 text-sm text-zinc-500">No courses exist yet.</p>
                            @endforelse
                        </div>
                        @error('enrollCourseIds') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Students --}}
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">2. Students</label>
                            <span class="text-xs text-zinc-500">{{ $selectedStudentCount }} selected</span>
                        </div>

                        @if ($selectedEnrollStudents->isNotEmpty())
                            <div class="mb-2 flex flex-wrap gap-1.5">
                                @foreach ($selectedEnrollStudents as $s)
                                    <span wire:key="chip-{{ $s->id }}" class="inline-flex items-center gap-1 rounded-full bg-orange-50 py-0.5 pl-2.5 pr-1 text-xs font-medium text-orange-800 dark:bg-orange-950/50 dark:text-orange-200">
                                        {{ $s->name }}
                                        <button type="button" wire:click="toggleEnrollStudent({{ $s->id }})" class="rounded-full p-0.5 hover:bg-orange-100 dark:hover:bg-orange-900" aria-label="Remove {{ $s->name }}">
                                            <flux:icon.x-mark class="size-3" />
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="relative mb-2">
                            <flux:icon.magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                            <input type="search" wire:model.live.debounce.300ms="enrollStudentSearch" class="{{ $input }} pl-9" placeholder="Search students by name, email or student ID…">
                        </div>

                        <div class="max-h-72 divide-y divide-zinc-100 overflow-y-auto rounded-xl border border-zinc-200 dark:divide-zinc-800 dark:border-zinc-700">
                            @forelse ($enrollCandidates as $candidate)
                                @php $isPicked = in_array((string) $candidate->id, $enrollStudentIds, true); @endphp
                                <label wire:key="candidate-{{ $candidate->id }}" class="flex cursor-pointer items-center gap-3 px-3 py-2 {{ $isPicked ? 'bg-orange-50/60 dark:bg-orange-950/20' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800' }}">
                                    <input type="checkbox" value="{{ $candidate->id }}" wire:model.live="enrollStudentIds" class="rounded border-zinc-300 text-orange-600 focus:ring-orange-500 dark:border-zinc-600 dark:bg-zinc-800">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-blue-900 text-xs font-bold text-white">{{ $candidate->initials() }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $candidate->name }}</span>
                                        <span class="block truncate text-xs text-zinc-500">{{ $candidate->studentProfile?->student_id ? $candidate->studentProfile->student_id . ' · ' : '' }}{{ $candidate->email }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs text-zinc-400">{{ $candidate->active_courses_count }} {{ \Illuminate\Support\Str::plural('course', $candidate->active_courses_count) }}</span>
                                </label>
                            @empty
                                <p class="px-3 py-6 text-center text-sm text-zinc-500">No students match your search.</p>
                            @endforelse
                        </div>
                        @if ($enrollCandidates->count() >= 40)
                            <p class="mt-1 text-xs text-zinc-500">Showing the first 40 matches. Search to narrow the list.</p>
                        @endif
                        @error('enrollStudentIds') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if ($enrollMode === 'invite')
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-semibold text-zinc-900 dark:text-zinc-100">Message <span class="font-normal text-zinc-400">(optional)</span></label>
                                <textarea wire:model="invitationMessage" rows="3" maxlength="500" class="{{ $input }}" placeholder="Welcome to the course! We start next Monday."></textarea>
                                @error('invitationMessage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-zinc-900 dark:text-zinc-100">Expires after</label>
                                <select wire:model="expiresInDays" class="{{ $input }}">
                                    @foreach ([3, 7, 14, 30, 60, 90] as $days)
                                        <option value="{{ $days }}">{{ $days }} days</option>
                                    @endforeach
                                </select>
                                @error('expiresInDays') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-xs text-zinc-500">
                        @if ($selectedCourseCount && $selectedStudentCount)
                            {{ $selectedStudentCount }} {{ \Illuminate\Support\Str::plural('student', $selectedStudentCount) }} × {{ $selectedCourseCount }} {{ \Illuminate\Support\Str::plural('course', $selectedCourseCount) }}
                        @else
                            Select at least one course and one student.
                        @endif
                    </p>
                    <div class="flex gap-2">
                        <button type="button" wire:click="closeEnrollPanel" class="{{ $btnSecondary }}">Cancel</button>
                        <button type="button" wire:click="enroll" wire:loading.attr="disabled" wire:target="enroll" class="{{ $btnPrimary }}" @disabled(! $selectedCourseCount || ! $selectedStudentCount)>
                            <flux:icon.arrow-path wire:loading wire:target="enroll" class="size-4 animate-spin" />
                            {{ $enrollMode === 'direct' ? 'Enroll students' : 'Send invitations' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ STUDENT COURSE MANAGER ============ --}}
    @if ($managedStudent)
        @php
            $enrolledCourseIds = $managedStudent->enrollments->pluck('course_id')->all();
            $assignable = $allCourses->reject(fn ($c) => in_array($c->id, $enrolledCourseIds, true));
            $mProfile = $managedStudent->studentProfile;
        @endphp
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" wire:keydown.escape.window="closeStudentManager">
            <div class="absolute inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="closeStudentManager"></div>
            <div class="relative flex h-full w-full max-w-lg flex-col bg-white shadow-2xl dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4 bg-gradient-to-r from-[#0f1f4d] to-[#1e3a8a] px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex size-12 items-center justify-center rounded-full bg-orange-500 text-base font-bold">{{ $managedStudent->initials() }}</span>
                        <div>
                            <h2 class="text-lg font-bold">{{ $managedStudent->name }}</h2>
                            <p class="text-xs text-blue-100/80">{{ $mProfile?->student_id ? $mProfile->student_id . ' · ' : '' }}{{ $managedStudent->email }}</p>
                            @if ($mProfile?->program_type)
                                <span class="mt-1 inline-flex rounded bg-white/15 px-1.5 py-0.5 text-[11px] font-semibold">{{ $programLabels[$mProfile->program_type] ?? ucfirst($mProfile->program_type) }}</span>
                            @endif
                        </div>
                    </div>
                    <button type="button" wire:click="closeStudentManager" class="rounded-lg p-1.5 text-white/80 hover:bg-white/10 hover:text-white" aria-label="Close">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-zinc-900 dark:text-zinc-100">Assign a course</label>
                        <div class="flex gap-2">
                            <select wire:model="addCourseId" class="{{ $input }}">
                                <option value="">Choose a course…</option>
                                @foreach ($assignable as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }}{{ $c->is_published ? '' : ' (draft)' }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="addCourseToStudent" wire:loading.attr="disabled" wire:target="addCourseToStudent" class="{{ $btnPrimary }} shrink-0">
                                <flux:icon.plus class="size-4" /> Assign
                            </button>
                        </div>
                        @error('addCourseId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <h3 class="mb-2 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Current courses ({{ $managedStudent->enrollments->count() }})</h3>
                        <div class="space-y-2">
                            @forelse ($managedStudent->enrollments as $e)
                                @php $p = (int) round($e->progress_percentage ?? 0); @endphp
                                <div wire:key="managed-enrollment-{{ $e->id }}" class="flex items-center gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $e->course?->title }}</p>
                                        <div class="mt-1.5 flex items-center gap-2">
                                            @if ($e->completed_at)
                                                <span class="text-xs font-semibold text-emerald-600">Completed {{ $e->completed_at->format('d M Y') }}</span>
                                            @else
                                                <div class="h-1.5 w-24 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                                    <div class="h-full rounded-full bg-orange-500" style="width: {{ max(0, min(100, $p)) }}%"></div>
                                                </div>
                                                <span class="text-xs tabular-nums text-zinc-500">{{ $p }}%</span>
                                            @endif
                                            @if ($e->camp || $e->club)
                                                <span class="text-xs text-zinc-400">· {{ $e->camp?->name ?? $e->club?->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" wire:click="removeCourseFromStudent({{ $e->id }})"
                                            wire:confirm="Remove {{ $managedStudent->name }} from {{ $e->course?->title ?? 'this course' }}? Their progress record for this course will be removed."
                                            class="{{ $btnDangerSoft }}">
                                        <flux:icon.trash class="size-3.5" /> Remove
                                    </button>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700">
                                    This student is not enrolled in any course yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ COURSE TEAM MANAGER ============ --}}
    @if ($managedCourse)
        @php
            $teamIds = $managedCourse->collaboratorUsers->pluck('id')->push($managedCourse->instructor_id)->filter()->all();
            $coOptions = $instructorOptions->reject(fn ($u) => in_array($u->id, $teamIds, true));
            $roleLabel = fn ($u) => $u->roles->pluck('name')->map(fn ($r) => ucwords(str_replace('_', ' ', $r)))->implode(', ');
        @endphp
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" wire:keydown.escape.window="closeCourseManager">
            <div class="absolute inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="closeCourseManager"></div>
            <div class="relative flex h-full w-full max-w-lg flex-col bg-white shadow-2xl dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4 bg-gradient-to-r from-[#0f1f4d] to-[#1e3a8a] px-6 py-5 text-white">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-orange-300">Course team</p>
                        <h2 class="mt-1 text-lg font-bold">{{ $managedCourse->title }}</h2>
                        <p class="text-xs text-blue-100/80">Choose who leads this course and who helps teach it.</p>
                    </div>
                    <button type="button" wire:click="closeCourseManager" class="rounded-lg p-1.5 text-white/80 hover:bg-white/10 hover:text-white" aria-label="Close">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">
                    {{-- Lead --}}
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Lead instructor</h3>
                        <p class="mb-3 text-xs text-zinc-500">The lead owns the course and appears as its instructor to students.</p>
                        <select wire:model="leadInstructorId" class="{{ $input }}">
                            <option value="">No lead instructor</option>
                            @foreach ($instructorOptions as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} — {{ $roleLabel($u) }}</option>
                            @endforeach
                        </select>
                        @error('leadInstructorId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @if ($managedCourse->instructor)
                            <label class="mt-3 flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                                <input type="checkbox" wire:model="keepPreviousLead" class="rounded border-zinc-300 text-orange-600 focus:ring-orange-500 dark:border-zinc-600 dark:bg-zinc-800">
                                Keep {{ $managedCourse->instructor->name }} as a co-instructor if replaced
                            </label>
                        @endif
                        <div class="mt-3 flex justify-end">
                            <button type="button" wire:click="saveLeadInstructor" wire:loading.attr="disabled" wire:target="saveLeadInstructor" class="{{ $btnPrimary }}">Save lead</button>
                        </div>
                    </div>

                    {{-- Co-instructors --}}
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Co-instructors</h3>
                        <p class="mb-3 text-xs text-zinc-500">Editors can change course content. Viewers can only see it and follow students.</p>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <select wire:model="coInstructorId" class="{{ $input }}">
                                <option value="">Choose a staff member…</option>
                                @foreach ($coOptions as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} — {{ $roleLabel($u) }}</option>
                                @endforeach
                            </select>
                            <select wire:model="coInstructorRole" class="{{ $input }} sm:w-32">
                                <option value="editor">Editor</option>
                                <option value="viewer">Viewer</option>
                            </select>
                            <button type="button" wire:click="addCoInstructor" wire:loading.attr="disabled" wire:target="addCoInstructor" class="{{ $btnPrimary }} shrink-0">
                                <flux:icon.plus class="size-4" /> Add
                            </button>
                        </div>
                        @error('coInstructorId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                        <div class="mt-4 space-y-2">
                            @forelse ($managedCourse->collaboratorUsers as $co)
                                <div wire:key="co-{{ $co->id }}" class="flex items-center gap-3 rounded-lg bg-zinc-50 p-2.5 dark:bg-zinc-800/60">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-blue-900 text-xs font-bold text-white">{{ $co->initials() }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $co->name }}</p>
                                        <p class="truncate text-xs text-zinc-500">{{ $co->email }}</p>
                                    </div>
                                    <select wire:change="updateCoInstructorRole({{ $co->id }}, $event.target.value)" class="rounded-md border border-zinc-300 bg-white px-2 py-1 text-xs dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-200">
                                        <option value="editor" @selected($co->pivot->role === 'editor')>Editor</option>
                                        <option value="viewer" @selected($co->pivot->role === 'viewer')>Viewer</option>
                                    </select>
                                    <button type="button" wire:click="removeCoInstructor({{ $co->id }})" wire:confirm="Remove {{ $co->name }} from this course team?" class="{{ $btnDangerSoft }}" aria-label="Remove {{ $co->name }}">
                                        <flux:icon.trash class="size-3.5" />
                                    </button>
                                </div>
                            @empty
                                <p class="rounded-lg border border-dashed border-zinc-300 p-4 text-center text-xs text-zinc-500 dark:border-zinc-700">No co-instructors yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ REJECT MODAL ============ --}}
    @if ($rejectingRequest)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" wire:keydown.escape.window="closeReject">
            <div class="absolute inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="closeReject"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Reject enrollment request</h2>
                <p class="mt-1 text-sm text-zinc-500">
                    {{ $rejectingRequest->user?->name ?? 'This student' }} asked to join
                    <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $rejectingRequest->course?->title ?? 'a course' }}</span>.
                    The reason is sent to the student.
                </p>
                <textarea wire:model="rejectionReason" rows="4" maxlength="500" class="{{ $input }} mt-4" placeholder="e.g. This course is full for this term. Please request again next term."></textarea>
                @error('rejectionReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="closeReject" class="{{ $btnSecondary }}">Cancel</button>
                    <button type="button" wire:click="confirmReject" wire:loading.attr="disabled" wire:target="confirmReject"
                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">Reject request</button>
                </div>
            </div>
        </div>
    @endif
</div>
