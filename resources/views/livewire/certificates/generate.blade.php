@php
    $card = 'rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800';
    $input = 'w-full rounded-xl border-0 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-orange-500 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700';
    $label = 'mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-zinc-400';
    $avatarColors = ['bg-orange-500', 'bg-sky-500', 'bg-emerald-500', 'bg-violet-500', 'bg-rose-500', 'bg-amber-500', 'bg-teal-500', 'bg-indigo-500'];
    $avatar = fn (string $name) => $avatarColors[crc32($name) % count($avatarColors)];
    $initials = fn (string $name) => collect(explode(' ', trim($name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $statusMeta = [
        'new' => ['label' => 'No certificate yet', 'class' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-950/40 dark:text-sky-300'],
        'update' => ['label' => 'New course to add', 'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-300'],
        'issued' => ['label' => 'Up to date', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300'],
    ];
@endphp

<div class="mx-auto max-w-7xl space-y-5 p-4 sm:p-6">

    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-500 to-orange-600 px-6 py-6 text-white shadow-lg sm:px-8">
        <div class="pointer-events-none absolute -right-16 -top-24 size-72 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute right-48 -bottom-24 size-48 rounded-full bg-white/10"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-orange-100">Certificates</p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Certificate centre</h1>
                <p class="mt-1 max-w-2xl text-sm text-orange-50/90">Issue certificates for every ready student in one go. When a child finishes another course, its modules are added to the certificate they already have.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('certificates.template-preview') }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-bold ring-1 ring-white/30 transition hover:bg-white/25">
                    <flux:icon name="eye" variant="micro" class="size-4" /> Template preview
                </a>
                @if(Route::has('admin.settings'))
                    <a href="{{ route('admin.settings') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-bold ring-1 ring-white/30 transition hover:bg-white/25">
                        <flux:icon name="cog-6-tooth" variant="micro" class="size-4" /> Design &amp; signatures
                    </a>
                @endif
            </div>
        </div>
        <div class="relative mt-5 grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach([
                ['label' => 'Ready, no certificate', 'value' => $counts['new'], 'icon' => 'sparkles', 'tab' => 'issue', 'status' => 'new'],
                ['label' => 'New course to add', 'value' => $counts['update'], 'icon' => 'plus-circle', 'tab' => 'issue', 'status' => 'update'],
                ['label' => 'Up to date', 'value' => $counts['issued'], 'icon' => 'check-badge', 'tab' => 'issue', 'status' => 'issued'],
                ['label' => 'Certificates issued', 'value' => $counts['certificates'], 'icon' => 'document-check', 'tab' => 'issued', 'status' => null],
            ] as $pill)
                <button type="button" wire:click="{{ $pill['status'] ? "showStatus('{$pill['status']}')" : "setTab('issued')" }}"
                        class="flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 text-left ring-1 ring-white/20 backdrop-blur transition hover:bg-white/25">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white/20"><flux:icon :name="$pill['icon']" class="size-5" /></span>
                    <span class="min-w-0">
                        <span class="block text-2xl font-extrabold leading-none">{{ number_format($pill['value']) }}</span>
                        <span class="mt-1 block truncate text-[11px] font-semibold uppercase tracking-wide text-orange-100">{{ $pill['label'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- Tabs --}}
    <nav class="flex gap-1 overflow-x-auto rounded-2xl bg-white p-1.5 shadow-sm ring-1 ring-gray-100 dark:bg-zinc-900 dark:ring-zinc-800">
        @foreach(['issue' => ['Ready to issue', 'bolt'], 'issued' => ['Issued', 'document-check'], 'single' => ['One student', 'user'], 'csv' => ['CSV import', 'arrow-up-tray']] as $key => [$tabLabel, $tabIcon])
            <button type="button" wire:click="setTab('{{ $key }}')"
                    @class([
                        'inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition',
                        'bg-orange-500 text-white shadow-sm' => $tab === $key,
                        'text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:text-zinc-400 dark:hover:bg-zinc-800' => $tab !== $key,
                    ])>
                <flux:icon :name="$tabIcon" variant="micro" class="size-4" /> {{ $tabLabel }}
            </button>
        @endforeach
    </nav>

    @if($statusMessage)
        <div @class([
            'flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold ring-1',
            'bg-emerald-50 text-emerald-800 ring-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-200 dark:ring-emerald-900' => $statusType === 'success',
            'bg-rose-50 text-rose-800 ring-rose-200 dark:bg-rose-950/30 dark:text-rose-200 dark:ring-rose-900' => $statusType !== 'success',
        ])>
            <flux:icon :name="$statusType === 'success' ? 'check-circle' : 'exclamation-circle'" class="size-5 shrink-0" />
            <span class="flex-1">{{ $statusMessage }}</span>
            <button type="button" wire:click="$set('statusMessage', '')" class="opacity-60 hover:opacity-100"><flux:icon name="x-mark" variant="micro" class="size-4" /></button>
        </div>
    @endif

    {{-- ─────────────── Ready to issue ─────────────── --}}
    @if($tab === 'issue')
        @php
            $pageIds = $candidates->pluck('user_id')->implode(',');
            $pageAllSelected = $candidates->count() > 0 && $candidates->pluck('user_id')->diff($selected)->isEmpty();
        @endphp

        <section class="{{ $card }} p-4">
            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_14rem_auto]">
                <div class="relative">
                    <flux:icon name="magnifying-glass" variant="micro" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or student ID" class="{{ $input }} pl-10">
                </div>
                <select wire:model.live="courseFilter" class="{{ $input }}">
                    <option value="">All courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </select>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(['pending' => 'To issue', 'new' => 'New', 'update' => 'Add course', 'issued' => 'Up to date', 'all' => 'All'] as $value => $chip)
                        <button type="button" wire:click="$set('statusFilter', '{{ $value }}')"
                                @class([
                                    'rounded-full px-3 py-2 text-xs font-bold ring-1 transition',
                                    'bg-orange-500 text-white ring-orange-500' => $statusFilter === $value,
                                    'bg-white text-gray-600 ring-gray-200 hover:ring-orange-300 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700' => $statusFilter !== $value,
                                ])>{{ $chip }}</button>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="{{ $card }} overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-zinc-800">
                <label class="flex items-center gap-3 text-sm font-semibold text-gray-700 dark:text-zinc-200">
                    <input type="checkbox" class="size-4 rounded accent-orange-500" @checked($pageAllSelected) wire:click="togglePage('{{ $pageIds }}')" @disabled($candidates->isEmpty())>
                    {{ number_format($matchingCount) }} {{ Str::plural('student', $matchingCount) }}
                </label>
                @if($matchingCount > 0 && count($selected) < $matchingCount)
                    <button type="button" wire:click="selectAllMatching" class="text-xs font-bold text-orange-600 hover:text-orange-700">Select all {{ number_format($matchingCount) }}</button>
                @endif
            </div>

            <div class="divide-y divide-gray-50 dark:divide-zinc-800">
                @forelse($candidates as $row)
                    @php $isSelected = in_array($row['user_id'], $selected, true); @endphp
                    <div wire:key="cand-{{ $row['user_id'] }}" @class(['flex flex-col gap-3 px-5 py-3.5 transition md:flex-row md:items-center', 'bg-orange-50/60 dark:bg-orange-950/10' => $isSelected])>
                        <label class="flex min-w-0 flex-1 cursor-pointer items-center gap-3">
                            <input type="checkbox" class="size-4 shrink-0 rounded accent-orange-500" @checked($isSelected) wire:click="toggle({{ $row['user_id'] }})">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {{ $avatar($row['name']) }}">{{ $initials($row['name']) }}</span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-bold text-gray-900 dark:text-white">{{ $row['name'] }}</span>
                                <span class="block truncate font-mono text-xs text-gray-400">{{ $row['student_id'] }}</span>
                            </span>
                        </label>

                        <div class="flex flex-1 flex-wrap gap-1.5 md:justify-start">
                            @foreach($row['courses'] as $course)
                                <span @class([
                                    'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset',
                                    'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/30 dark:text-emerald-300' => $course['covered'],
                                    'bg-orange-50 text-orange-700 ring-orange-500/30 dark:bg-orange-950/30 dark:text-orange-300' => ! $course['covered'],
                                ]) title="{{ $course['completed'] ? 'Course completed' : $course['progress'].'% progress' }}">
                                    <flux:icon :name="$course['covered'] ? 'check' : 'plus'" variant="micro" class="size-3" />
                                    {{ $course['title'] }}
                                    @unless($course['completed'])<span class="opacity-60">· {{ $course['progress'] }}%</span>@endunless
                                </span>
                            @endforeach
                        </div>

                        <div class="flex shrink-0 items-center gap-2 md:w-72 md:justify-end">
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 ring-inset {{ $statusMeta[$row['status']]['class'] }}">{{ $statusMeta[$row['status']]['label'] }}</span>
                            @foreach($row['certificates'] as $cert)
                                <a href="{{ route('certificates.view', $cert['id']) }}" target="_blank" title="{{ $cert['number'] }} · {{ $cert['modules'] }} modules"
                                   class="flex size-8 items-center justify-center rounded-lg text-gray-400 ring-1 ring-gray-200 transition hover:text-orange-600 hover:ring-orange-300 dark:ring-zinc-700">
                                    <flux:icon name="document-text" variant="micro" class="size-4" />
                                </a>
                            @endforeach
                            <button type="button" wire:click="issueOne({{ $row['user_id'] }})" wire:loading.attr="disabled"
                                    class="rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-orange-600 dark:bg-white dark:text-gray-900">
                                {{ $row['status'] === 'new' ? 'Issue' : ($row['status'] === 'update' ? 'Add '.$row['pending'] : 'Re-issue') }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-500 dark:bg-orange-950/30"><flux:icon name="check-badge" class="size-7" /></span>
                        <p class="mt-3 text-sm font-bold text-gray-800 dark:text-white">{{ $statusFilter === 'pending' ? 'Everyone ready has an up-to-date certificate' : 'No students match' }}</p>
                        <p class="mt-1 text-xs text-gray-400">A student is ready once they finish a course or reach the minimum progress set in Settings.</p>
                    </div>
                @endforelse
            </div>

            @if($candidates->hasPages())
                <div class="border-t border-gray-100 px-5 py-3 dark:border-zinc-800">{{ $candidates->links() }}</div>
            @endif
        </section>

        {{-- Floating action bar --}}
        @if(count($selected) > 0)
            <div class="sticky bottom-4 z-20 flex flex-col gap-3 rounded-2xl bg-cau-deep p-3 pl-5 text-white shadow-2xl ring-1 ring-white/10 lg:flex-row lg:items-center">
                <p class="text-sm font-bold">{{ count($selected) }} selected</p>
                <div class="flex flex-1 flex-wrap items-center gap-2">
                    <div class="flex rounded-xl bg-white/10 p-1 text-xs font-bold">
                        <button type="button" wire:click="$set('issueMode', 'merge')" class="rounded-lg px-3 py-1.5 transition {{ $issueMode === 'merge' ? 'bg-white text-gray-900' : 'text-white/70 hover:text-white' }}" title="New courses are added as modules on the certificate the student already has">Add to existing certificate</button>
                        <button type="button" wire:click="$set('issueMode', 'separate')" class="rounded-lg px-3 py-1.5 transition {{ $issueMode === 'separate' ? 'bg-white text-gray-900' : 'text-white/70 hover:text-white' }}" title="Each course gets its own certificate">Separate per course</button>
                    </div>
                    <select wire:model="signatoryProfile" class="rounded-xl border-0 bg-white/10 py-2 pl-3 pr-8 text-xs font-bold text-white ring-1 ring-white/10 focus:ring-orange-400">
                        <option value="auto" class="text-gray-900">Signatory: automatic</option>
                        @foreach($signatoryProfiles as $key => $profile)
                            <option value="{{ $key }}" class="text-gray-900">Signatory: {{ $profile['label'] }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-2 text-xs font-semibold text-white/80">
                        <input type="checkbox" wire:model="downloadZip" class="size-4 rounded accent-orange-500"> Download PDFs
                    </label>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="clearSelected" class="rounded-xl px-3 py-2 text-xs font-bold text-white/70 hover:text-white">Clear</button>
                    <button type="button" wire:click="issueSelected" wire:loading.attr="disabled" wire:target="issueSelected"
                            class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-bold text-white shadow transition hover:bg-orange-400 disabled:opacity-60">
                        <flux:icon name="check-badge" variant="micro" class="size-4" wire:loading.remove wire:target="issueSelected" />
                        <flux:icon name="arrow-path" variant="micro" class="size-4 animate-spin" wire:loading wire:target="issueSelected" />
                        <span wire:loading.remove wire:target="issueSelected">Issue {{ count($selected) }} {{ Str::plural('certificate', count($selected)) }}</span>
                        <span wire:loading wire:target="issueSelected">Generating…</span>
                    </button>
                </div>
            </div>
        @endif
    @endif

    {{-- ─────────────── Issued ─────────────── --}}
    @if($tab === 'issued')
        <section class="{{ $card }} overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-4 dark:border-zinc-800">
                <div class="relative w-full max-w-md">
                    <flux:icon name="magnifying-glass" variant="micro" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                    <input type="search" wire:model.live.debounce.300ms="issuedSearch" placeholder="Search name or certificate number" class="{{ $input }} pl-10">
                </div>
                @if($issued->count() > 1)
                    <button type="button" wire:click="downloadSelectedIssued('{{ $issued->pluck('id')->implode(',') }}')" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gray-900 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-orange-600 dark:bg-white dark:text-gray-900">
                        <flux:icon name="archive-box-arrow-down" variant="micro" class="size-4" /> Download this page (ZIP)
                    </button>
                @endif
            </div>
            <div class="divide-y divide-gray-50 dark:divide-zinc-800">
                @forelse($issued as $certificate)
                    @php
                        $name = $certificate->user?->studentProfile?->full_name ?? $certificate->user?->name ?? 'Unknown';
                        $courseIds = data_get($certificate->completion_data, 'course_ids') ?: [$certificate->course_id];
                        $moduleCount = count(data_get($certificate->completion_data, 'modules', []));
                    @endphp
                    <div wire:key="issued-{{ $certificate->id }}" class="flex flex-col gap-3 px-5 py-3.5 md:flex-row md:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {{ $avatar($name) }}">{{ $initials($name) }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $name }}</p>
                                <p class="truncate font-mono text-xs text-gray-400">{{ $certificate->certificate_number }}</p>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-wrap gap-1.5">
                            @foreach($courseIds as $courseId)
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $courseTitles[$courseId] ?? 'Course #'.$courseId }}</span>
                            @endforeach
                            @if(data_get($certificate->completion_data, 'separate'))
                                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-semibold text-violet-700 dark:bg-violet-950/30 dark:text-violet-300">Separate</span>
                            @endif
                        </div>
                        <div class="flex shrink-0 items-center gap-4 md:w-80 md:justify-end">
                            <span class="text-xs text-gray-500"><b class="text-gray-900 dark:text-white">{{ $moduleCount }}</b> {{ Str::plural('module', $moduleCount) }}</span>
                            <span class="text-xs text-gray-400">{{ $certificate->issued_at?->format('j M Y') }}</span>
                            <div class="flex gap-1.5">
                                <a href="{{ route('certificates.view', $certificate) }}" target="_blank" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-gray-600 ring-1 ring-gray-200 transition hover:text-orange-600 hover:ring-orange-300 dark:text-zinc-300 dark:ring-zinc-700">View</a>
                                <a href="{{ route('certificates.download', $certificate) }}" class="rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs font-bold text-white transition hover:bg-orange-600 dark:bg-white dark:text-gray-900">PDF</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <flux:icon name="document-check" class="mx-auto size-10 text-gray-300" />
                        <p class="mt-3 text-sm font-bold text-gray-700 dark:text-white">No certificates issued yet</p>
                        <p class="mt-1 text-xs text-gray-400">Go to “Ready to issue” to create them in bulk.</p>
                    </div>
                @endforelse
            </div>
            @if($issued->hasPages())
                <div class="border-t border-gray-100 px-5 py-3 dark:border-zinc-800">{{ $issued->links() }}</div>
            @endif
        </section>
    @endif

    {{-- ─────────────── One student ─────────────── --}}
    @if($tab === 'single')
        @php $hasSelection = $selectedUserId && $candidateName; @endphp
        <div class="grid gap-5 xl:grid-cols-12">
            <div class="space-y-5 xl:col-span-5">
                <section class="{{ $card }} space-y-4 p-5">
                    <div>
                        <label class="{{ $label }}">Course</label>
                        <select wire:model.live="selectedCourseId" class="{{ $input }}">
                            <option value="">Choose a course…</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedCourseId)
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <flux:icon name="magnifying-glass" variant="micro" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                                <input type="search" wire:model.live.debounce.300ms="studentSearch" placeholder="Search student" class="{{ $input }} pl-10">
                            </div>
                            <select wire:model.live="eligibilityFilter" class="{{ $input }} !w-36">
                                <option value="ready">Ready</option>
                                <option value="not_issued">Not issued</option>
                                <option value="all">All enrolled</option>
                            </select>
                        </div>
                        <div class="max-h-72 space-y-1.5 overflow-y-auto pr-1">
                            @forelse($studentResults as $student)
                                @php $active = (int) $selectedUserId === (int) $student['user_id']; @endphp
                                <button type="button" wire:click="selectStudent({{ $student['user_id'] }})" wire:key="single-{{ $student['user_id'] }}"
                                        @class(['flex w-full items-center gap-3 rounded-xl p-2.5 text-left ring-1 transition', 'bg-orange-50 ring-2 ring-orange-500 dark:bg-orange-950/20' => $active, 'ring-gray-100 hover:ring-orange-200 dark:ring-zinc-800' => ! $active])>
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-[11px] font-bold text-white {{ $avatar($student['name']) }}">{{ $initials($student['name']) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-bold text-gray-900 dark:text-white">{{ $student['name'] }}</span>
                                        <span class="block truncate text-xs text-gray-400">{{ $student['student_id'] }} · {{ $student['progress'] }}% · {{ $student['completed_modules'] }}/{{ max($student['total_modules'], 1) }} modules</span>
                                    </span>
                                    @if($student['has_certificate'])
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">On certificate</span>
                                    @elseif($student['is_ready'])
                                        <span class="rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-bold text-sky-700">Ready</span>
                                    @endif
                                </button>
                            @empty
                                <p class="rounded-xl bg-gray-50 px-4 py-6 text-center text-sm text-gray-500 dark:bg-zinc-800">No students match.</p>
                            @endforelse
                        </div>
                    @endif
                </section>

                @if($hasSelection)
                    <section class="{{ $card }} space-y-4 p-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-[15px] font-extrabold text-gray-900 dark:text-white">Certificate details</h2>
                            <button type="button" wire:click="clearSelection" class="text-xs font-bold text-gray-400 hover:text-gray-700">Clear</button>
                        </div>

                        <div class="flex rounded-xl bg-gray-100 p-1 text-xs font-bold dark:bg-zinc-800">
                            <button type="button" wire:click="$set('issueMode', 'merge')" class="flex-1 rounded-lg px-3 py-1.5 transition {{ $issueMode === 'merge' ? 'bg-white text-gray-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-gray-500' }}">Add to existing certificate</button>
                            <button type="button" wire:click="$set('issueMode', 'separate')" class="flex-1 rounded-lg px-3 py-1.5 transition {{ $issueMode === 'separate' ? 'bg-white text-gray-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-gray-500' }}">Separate certificate</button>
                        </div>

                        @if($single['target'])
                            <div class="flex gap-3 rounded-xl bg-amber-50 p-3 text-xs text-amber-900 ring-1 ring-amber-200 dark:bg-amber-950/20 dark:text-amber-200 dark:ring-amber-900/50">
                                <flux:icon name="information-circle" class="size-5 shrink-0" />
                                <p>
                                    Updates certificate <b class="font-mono">{{ $single['target']->certificate_number }}</b>.
                                    @if($single['otherCourses'])
                                        It keeps the modules from <b>{{ implode(', ', $single['otherCourses']) }}</b> and adds this course — {{ count($single['modules']) }} modules in total.
                                    @else
                                        This course's modules will be replaced with the list below.
                                    @endif
                                </p>
                            </div>
                        @else
                            <p class="rounded-xl bg-sky-50 p-3 text-xs text-sky-900 ring-1 ring-sky-200 dark:bg-sky-950/20 dark:text-sky-200 dark:ring-sky-900/50">A new certificate will be created.</p>
                        @endif

                        @if($selectedStudent)
                            <div class="grid grid-cols-3 gap-2">
                                @foreach(['Progress' => $selectedStudent['progress'].'%', 'Modules' => $selectedStudent['completed_modules'].'/'.max($selectedStudent['total_modules'], 1), 'Completed' => $selectedStudent['completed_at'] ?? '—'] as $k => $v)
                                    <div class="rounded-xl bg-gray-50 px-3 py-2 dark:bg-zinc-800">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">{{ $k }}</p>
                                        <p class="text-sm font-extrabold text-gray-900 dark:text-white">{{ $v }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}">Candidate name</label>
                                <input type="text" wire:model.live.debounce.400ms="candidateName" class="{{ $input }}" @readonly(! $manualEdit)>
                            </div>
                            <div>
                                <label class="{{ $label }}">Student ID</label>
                                <input type="text" wire:model.live.debounce.400ms="candidateNo" class="{{ $input }} font-mono" @readonly(! $manualEdit)>
                            </div>
                            <div>
                                <label class="{{ $label }}">Issue date</label>
                                <input type="date" wire:model.live="signatureDate" class="{{ $input }}">
                            </div>
                        </div>
                        @error('candidateName') <p class="text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label class="{{ $label }} !mb-0">Modules from this course</label>
                                @if($manualEdit)
                                    <button type="button" wire:click="addModule" class="text-xs font-bold text-orange-600">+ Add module</button>
                                @else
                                    <button type="button" wire:click="$set('manualEdit', true)" class="text-xs font-bold text-orange-600">Edit</button>
                                @endif
                            </div>
                            <div class="space-y-1.5">
                                @foreach($modules as $index => $module)
                                    @if($manualEdit)
                                        <div class="grid grid-cols-[minmax(0,1fr)_4.5rem_8.5rem_auto] items-center gap-1.5" wire:key="mod-{{ $index }}">
                                            <input type="text" wire:model.live.debounce.400ms="modules.{{ $index }}.name" placeholder="Module name" class="{{ $input }} !py-2">
                                            <input type="text" wire:model.live.debounce.400ms="modules.{{ $index }}.version" placeholder="v" class="{{ $input }} !py-2">
                                            <input type="date" wire:model.live="modules.{{ $index }}.date" class="{{ $input }} !py-2">
                                            <button type="button" wire:click="removeModule({{ $index }})" class="p-1 text-gray-300 hover:text-rose-500" @disabled(count($modules) < 2)><flux:icon name="x-mark" variant="micro" class="size-4" /></button>
                                        </div>
                                    @elseif($module['name'])
                                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-3 py-2 text-sm dark:bg-zinc-800">
                                            <span class="font-semibold text-gray-800 dark:text-zinc-200">{{ $module['name'] }}</span>
                                            <span class="text-xs text-gray-400">v{{ $module['version'] }} · {{ \Carbon\Carbon::parse($module['date'])->format('j M Y') }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            @error('modules') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <details class="rounded-xl bg-gray-50 p-3 dark:bg-zinc-800/60">
                            <summary class="cursor-pointer text-xs font-bold text-gray-600 dark:text-zinc-300">Signatory</summary>
                            <div class="mt-3 space-y-2">
                                <select wire:model.live="signatoryProfile" class="{{ $input }}">
                                    <option value="auto">Automatic ({{ $signatoryProfiles[$resolvedSignatoryKey]['label'] ?? $resolvedSignatoryKey }})</option>
                                    @foreach($signatoryProfiles as $key => $profile)
                                        <option value="{{ $key }}">{{ $profile['label'] }}{{ $profile['has_signature'] ? '' : ' (no signature uploaded)' }}</option>
                                    @endforeach
                                </select>
                                <input type="text" wire:model.live.debounce.400ms="customSignatoryOverride" class="{{ $input }}" placeholder="{{ $signatoryProfiles[$resolvedSignatoryKey]['signatory_line'] ?? 'Override signatory line' }}">
                            </div>
                        </details>

                        <div class="flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-zinc-800">
                            <button type="button" wire:click="issueAndDownload" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-orange-600 disabled:opacity-60">
                                <flux:icon name="check-badge" variant="micro" class="size-4" />
                                <span wire:loading.remove wire:target="issueAndDownload">Issue &amp; download</span>
                                <span wire:loading wire:target="issueAndDownload">Generating…</span>
                            </button>
                            <button type="button" wire:click="generate" wire:loading.attr="disabled" class="rounded-xl px-4 py-2.5 text-sm font-bold text-gray-600 ring-1 ring-gray-200 transition hover:bg-gray-50 dark:text-zinc-300 dark:ring-zinc-700">
                                Download draft only
                            </button>
                        </div>
                    </section>
                @endif
            </div>

            <div class="xl:col-span-7">
                <section class="{{ $card }} sticky top-4 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-zinc-800">
                        <h2 class="text-[15px] font-extrabold text-gray-900 dark:text-white">Preview</h2>
                        @if($previewUrl)
                            <a href="{{ $previewUrl }}" target="_blank" class="text-xs font-bold text-orange-600">Open full size →</a>
                        @endif
                    </div>
                    @if($previewUrl)
                        <div class="overflow-auto bg-gray-100 p-3 dark:bg-zinc-950" style="max-height: calc(100vh - 8rem);">
                            <iframe src="{{ $previewUrl }}" wire:key="preview-{{ md5($previewUrl) }}" class="mx-auto w-full border-0 bg-white shadow-lg" style="height: 1100px; min-width: 820px; max-width: 820px;" title="Certificate preview"></iframe>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center px-6 py-24 text-center">
                            <span class="flex size-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-500 dark:bg-orange-950/30"><flux:icon name="document-text" class="size-7" /></span>
                            <p class="mt-3 text-sm font-semibold text-gray-600 dark:text-zinc-300">Pick a course and a student to preview their certificate.</p>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    @endif

    {{-- ─────────────── CSV import ─────────────── --}}
    @if($tab === 'csv')
        <section class="{{ $card }} mx-auto max-w-2xl p-6">
            <h2 class="text-[15px] font-extrabold text-gray-900 dark:text-white">Import from CSV</h2>
            <p class="mt-1 text-sm text-gray-500">For students outside the system or one-off batches. Rows with the same candidate number become one certificate with several modules. These PDFs are not saved to student profiles.</p>

            <label class="mt-5 flex cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl border-2 border-dashed border-gray-200 px-6 py-10 text-center transition hover:border-orange-300 hover:bg-orange-50/40 dark:border-zinc-700 dark:hover:bg-orange-950/10">
                <flux:icon name="cloud-arrow-up" class="size-9 text-orange-400" />
                <span class="text-sm font-bold text-gray-700 dark:text-zinc-200">{{ $csvFile ? $csvFile->getClientOriginalName() : 'Choose a CSV file' }}</span>
                <span class="text-xs text-gray-400">candidate_name, candidate_no, module_name, module_version, module_date, signature_date</span>
                <input type="file" wire:model="csvFile" accept=".csv" class="hidden">
            </label>
            <div wire:loading wire:target="csvFile" class="mt-2 text-xs font-semibold text-orange-600">Reading file…</div>
            @if($bulkError)<p class="mt-3 text-sm font-semibold text-rose-600">{{ $bulkError }}</p>@endif

            @if(count($bulkCandidates) > 0)
                <div class="mt-5 rounded-xl bg-gray-50 p-4 dark:bg-zinc-800/60">
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ count($bulkCandidates) }} {{ Str::plural('candidate', count($bulkCandidates)) }} loaded</p>
                    <ul class="mt-2 max-h-48 space-y-1 overflow-y-auto text-xs text-gray-600 dark:text-zinc-300">
                        @foreach($bulkCandidates as $c)
                            <li class="flex justify-between"><span>{{ $c['candidateName'] }}</span><span class="text-gray-400">{{ count($c['modules']) }} modules</span></li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" wire:click="generateBulk" wire:loading.attr="disabled" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-orange-600">
                    <flux:icon name="archive-box-arrow-down" variant="micro" class="size-4" /> Download ZIP
                </button>
            @endif

            <a href="{{ route('certificates.sample-csv') }}" class="mt-4 inline-block text-xs font-bold text-orange-600 hover:underline" download>Download sample CSV</a>
        </section>
    @endif
</div>
