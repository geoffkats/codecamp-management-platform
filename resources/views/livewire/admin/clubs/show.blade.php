<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 space-y-6">
    @if(session('message'))
        <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-sm font-semibold text-green-800 dark:text-green-200">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm font-semibold text-red-800 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    @if(session('student_credentials'))
        @php $creds = session('student_credentials'); @endphp
        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Login credentials for {{ $creds['full_name'] }}</p>
            <p class="text-sm text-amber-800 dark:text-amber-200 mt-2">
                <span class="font-medium">Student ID:</span> <code class="font-mono">{{ $creds['student_id'] }}</code>
                &nbsp;·&nbsp;
                <span class="font-medium">Password:</span> <code class="font-mono">{{ $creds['password'] }}</code>
            </p>
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-2">Share these with the student. They sign in using Student ID + password (no email needed).</p>
            @if(!empty($creds['print_url']))
                <a href="{{ $creds['print_url'] }}" target="_blank" class="inline-block mt-3 text-sm font-semibold text-amber-900 dark:text-amber-100 underline">Print credentials card →</a>
            @endif
        </div>
    @endif

    <a href="{{ route('admin.code-clubs.index') }}" wire:navigate class="text-sm text-blue-600 hover:underline">← Back to Code Clubs</a>

    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $club->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $club->school?->name }} · {{ $club->schedule_label }}</p>
        </div>
        @if($canManageSettings)
            <button wire:click="toggleEdit" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-sm font-semibold">
                {{ $isEditing ? 'Cancel' : 'Edit Club' }}
            </button>
        @endif
    </div>

    <div class="grid grid-cols-3 gap-4">
        @foreach([
            ['label' => 'Active Members', 'value' => $stats['active']],
            ['label' => 'Dropped', 'value' => $stats['dropped']],
            ['label' => 'Facilitators', 'value' => $stats['facilitators']],
        ] as $stat)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 uppercase">{{ $stat['label'] }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    @if($isEditing)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="text-xs font-semibold text-gray-500">Name</label>
                <input type="text" wire:model="editName" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Day</label>
                <input type="text" wire:model="editDayOfWeek" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Status</label>
                <select wire:model="editStatus" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Start</label>
                <input type="time" wire:model="editSessionStart" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">End</label>
                <input type="time" wire:model="editSessionEnd" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
            </div>
            <div class="sm:col-span-2">
                <label class="text-xs font-semibold text-gray-500">Description</label>
                <textarea wire:model="editDescription" rows="2" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
            </div>
            <div>
                <button wire:click="saveClub" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">Save Changes</button>
            </div>
        </div>
    @endif

    <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
        @foreach(['students' => 'Students', 'facilitators' => 'Facilitators', 'schedule' => 'Schedule', 'reports' => 'Term Reports'] as $key => $label)
            @if($key !== 'reports' || $canGenerateReports)
                <button wire:click="setTab('{{ $key }}')" class="px-4 py-2 text-sm font-semibold border-b-2 {{ $activeTab === $key ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500' }}">
                    {{ $label }}
                </button>
            @endif
        @endforeach
    </div>

    @if($activeTab === 'students')
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Assign course to members</p>
                <p class="text-xs text-gray-500 mb-3">Select a course, then enroll all members or assign individually from the roster.</p>
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <select wire:model="bulkCourseId" class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                            <option value="">Select a course...</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button wire:click="enrollMembersInCourse" wire:confirm="Enroll all active club members in this course?" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-bold">
                        Enroll All
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs font-semibold text-gray-500">Search members</label>
                    <input type="text" wire:model.live.debounce.300ms="searchStudents" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                </div>
                <div class="min-w-[140px]">
                    <label class="text-xs font-semibold text-gray-500">Class / grade</label>
                    <select wire:model.live="filterClass" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                        <option value="">All classes</option>
                        @if(($unassignedCount ?? 0) > 0)
                            <option value="{{ \App\Livewire\Admin\Clubs\Show::CLASS_FILTER_UNASSIGNED }}">Unassigned</option>
                        @endif
                        @foreach($classOptions ?? [] as $classOption)
                            <option value="{{ $classOption }}">{{ $classOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Show</label>
                    <select wire:model.live="memberFilter" class="mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                        <option value="active">Active members</option>
                        <option value="dropped">Removed / transferred</option>
                        <option value="all">All records</option>
                    </select>
                </div>
                <a href="{{ route('students.create-codeclub', ['club_id' => $club->id, 'school_id' => $club->school_id]) }}" wire:navigate class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold">Register Student</a>
                <a href="{{ route('admin.code-clubs.reports.bulk-download', array_merge(['club' => $club], $reportQuery ?? [])) }}"
                   class="px-4 py-2 rounded-xl bg-orange-600 text-white text-sm font-bold hover:bg-orange-700"
                   title="Download end-of-term progress report PDFs for all active members">
                    Download All Term Reports
                </a>
                @php $sampleMember = $memberships->first(); @endphp
                @if($sampleMember)
                    <a href="{{ route('admin.code-clubs.reports.preview', array_merge(['club' => $club, 'student' => $sampleMember->student_id], $reportQuery ?? [])) }}"
                       target="_blank"
                       class="px-4 py-2 rounded-xl border border-orange-300 dark:border-orange-700 text-orange-700 dark:text-orange-300 text-sm font-bold hover:bg-orange-50 dark:hover:bg-orange-900/20"
                       title="Preview a sample term report PDF in your browser">
                        Preview Sample
                    </a>
                @endif
                <button type="button" wire:click="setTab('reports')" class="px-4 py-2 rounded-xl border border-indigo-300 text-indigo-700 dark:text-indigo-300 text-sm font-bold hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                    Prep &amp; Fill Reports
                </button>
                <button wire:click="exportRosterCsv" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-sm font-bold">Export Roster CSV</button>
                <button wire:click="toggleImportPanel" class="px-4 py-2 rounded-xl bg-amber-600 text-white text-sm font-bold hover:bg-amber-700 shadow-sm">↑ Bulk Import</button>
                <button wire:click="$set('showAddStudentModal', true)" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">Add Existing</button>
            </div>

            @if(($classOptions ?? collect())->isNotEmpty() || ($unassignedCount ?? 0) > 0)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Class:</span>
                    <button type="button" wire:click="selectClass('')"
                        class="px-2.5 py-1 rounded-lg border text-xs font-semibold transition {{ $filterClass === '' ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300' }}">
                        All
                    </button>
                    @if(($unassignedCount ?? 0) > 0)
                        <button type="button" wire:click="selectClass('{{ \App\Livewire\Admin\Clubs\Show::CLASS_FILTER_UNASSIGNED }}')"
                            class="px-2.5 py-1 rounded-lg border text-xs font-semibold transition {{ $filterClass === \App\Livewire\Admin\Clubs\Show::CLASS_FILTER_UNASSIGNED ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300' }}">
                            Unassigned
                        </button>
                    @endif
                    @foreach($classOptions ?? [] as $classOption)
                        <button type="button" wire:click="selectClass('{{ $classOption }}')"
                            class="px-2.5 py-1 rounded-lg border text-xs font-semibold transition {{ $filterClass === $classOption ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300' }}">
                            {{ $classOption }}
                        </button>
                    @endforeach
                </div>
            @endif

            @if($showImportPanel)
                <div class="rounded-2xl border-2 border-amber-300 dark:border-amber-800 bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/30 dark:to-gray-900 p-5 space-y-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-600 text-white text-sm">↑</span>
                                Bulk student import
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1.5 max-w-xl">
                                Upload an Excel or CSV file with columns like
                                <span class="font-mono text-amber-800 dark:text-amber-300">full_name, class_grade, scratch_account, scratch_password</span>
                            </p>
                        </div>
                        <button type="button" wire:click="downloadImportTemplate" class="inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300">
                            ↓ Download template
                        </button>
                    </div>

                    <x-codeclub.bulk-import-uploader wire:model="importCsv" :file="$importCsv" />

                    <x-codeclub.import-default-class />

                    @error('importCsv')
                        <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="flex flex-wrap gap-2">
                        <button wire:click="importStudents" wire:loading.attr="disabled" wire:target="importCsv,importStudents"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold shadow-sm hover:bg-emerald-700 disabled:opacity-50">
                            <span wire:loading wire:target="importStudents">Importing…</span>
                            <span wire:loading.remove wire:target="importStudents">Run Import</span>
                        </button>
                        <button wire:click="toggleImportPanel" class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800">Cancel</button>
                    </div>

                    @if($importReport)
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/80 p-3 space-y-1">
                            <p class="font-semibold text-emerald-700 dark:text-emerald-300">{{ $importReport['imported'] }} imported · {{ $importReport['skipped'] }} skipped</p>
                            @if(!empty($importReport['errors']))
                                <ul class="text-xs text-red-700 dark:text-red-300 list-disc pl-5 max-h-40 overflow-y-auto">
                                    @foreach($importReport['errors'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            @if($showAddStudentModal)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 space-y-3">
                    <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Search Code Club students..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" />
                    @foreach($studentResults as $profile)
                        <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg px-3 py-2">
                            <span class="text-sm">{{ $profile->full_name }} ({{ $profile->student_id }})</span>
                            <button wire:click="enrollStudent({{ $profile->user_id }})" class="text-xs font-bold text-blue-600">Enroll</button>
                        </div>
                    @endforeach
                    <button wire:click="$set('showAddStudentModal', false)" class="text-xs text-gray-500">Close</button>
                </div>
            @endif

            @if(count($selectedMembers) > 0)
                <div class="rounded-xl border border-blue-200 bg-blue-50 dark:border-blue-900/50 dark:bg-blue-950/30 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">{{ count($selectedMembers) }} selected</p>
                        <div class="flex flex-1 flex-wrap gap-2">
                            <button wire:click="bulkEnrollSelectedInCourse" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-bold">Enroll in Course</button>
                            <button wire:click="openBulkClassModal" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold">Set Class</button>
                            <button wire:click="exportSelectedRosterCsv" class="px-3 py-1.5 rounded-lg border border-blue-300 bg-white text-xs font-bold text-blue-800">Export CSV</button>
                            <button wire:click="bulkPrintCredentials" class="px-3 py-1.5 rounded-lg border border-blue-300 bg-white text-xs font-bold text-blue-800">Print Credentials</button>
                            <button wire:click="bulkDropSelected" wire:confirm="Remove selected students from this club? They will stay in the system." class="px-3 py-1.5 rounded-lg bg-amber-600 text-white text-xs font-bold">Remove from Club</button>
                            <button wire:click="clearMemberSelection" class="px-3 py-1.5 rounded-lg border border-blue-300 text-xs font-bold text-blue-800">Clear</button>
                        </div>
                    </div>

                    @if($canBulkAdminActions ?? false)
                        <x-students.bulk-advanced-panel
                            :can-deactivate="auth()->user()->isAdmin() || auth()->user()->isSupervisor() || auth()->user()->isOperationsManager()"
                            :can-delete="auth()->user()->isAdmin() || auth()->user()->isSupervisor()"
                            export-action="exportSelectedRosterCsv"
                        />
                    @endif
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" wire:model.live="selectAllMembers" class="rounded border-gray-300 text-blue-600">
                            </th>
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Class</th>
                            <th class="px-4 py-3">Courses</th>
                            <th class="px-4 py-3">Scratch</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Joined</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($memberships as $membership)
                            @php $profile = $membership->student?->studentProfile; @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    @if($membership->student_id)
                                        <input type="checkbox" value="{{ $membership->student_id }}" wire:model.live="selectedMembers" class="rounded border-gray-300 text-blue-600">
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $profile?->full_name ?? $membership->student?->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $profile?->student_id }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $profile?->class_grade ?: '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php $memberCourses = $membership->student?->enrollments?->pluck('course.title')->filter() ?? collect(); @endphp
                                    @if($memberCourses->isNotEmpty())
                                        <p class="text-xs text-gray-700 dark:text-gray-300">{{ $memberCourses->take(2)->join(', ') }}{{ $memberCourses->count() > 2 ? '…' : '' }}</p>
                                    @else
                                        <span class="text-xs text-amber-600 dark:text-amber-400">No courses</span>
                                    @endif
                                    @if($membership->status === 'active')
                                        <button wire:click="enrollMemberInCourse({{ $membership->student_id }})" class="block mt-1 text-xs font-bold text-indigo-600">+ Assign course</button>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($profile?->scratch_account)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300" title="Scratch account on file">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            {{ $profile->scratch_account }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ ucfirst($membership->status) }}</td>
                                <td class="px-4 py-3">{{ $membership->enrolled_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('admin.code-clubs.reports.preview', array_merge(['club' => $club, 'student' => $membership->student_id], $reportQuery ?? [])) }}"
                                       target="_blank"
                                       class="text-xs text-blue-600 font-semibold hover:underline"
                                       title="Preview end-of-term progress report PDF">
                                        Preview
                                    </a>
                                    <a href="{{ route('admin.code-clubs.reports.download', array_merge(['club' => $club, 'student' => $membership->student_id], $reportQuery ?? [])) }}"
                                       target="_blank"
                                       class="text-xs text-orange-600 font-semibold hover:underline"
                                       title="Download end-of-term progress report PDF">
                                        Report PDF
                                    </a>
                                    @if($membership->status === 'active')
                                        <button wire:click="dropStudent({{ $membership->student_id }})" wire:confirm="Remove this student from the club roster?" class="text-xs text-red-600 font-semibold">Remove</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No members yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $memberships->links() }}
        </div>

        @if($showBulkClassModal ?? false)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white dark:bg-gray-800 shadow-xl">
                    <div class="border-b border-gray-200 dark:border-gray-700 px-5 py-4">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Set class for {{ count($selectedMembers) }} student(s)</h2>
                    </div>
                    <div class="p-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Class / Grade</label>
                        <input type="text" wire:model="bulkClassGrade" placeholder="e.g. P.2, P.3" class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                        @error('bulkClassGrade') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 px-5 py-4">
                        <button wire:click="closeBulkClassModal" class="px-4 py-2 rounded-xl border text-sm">Cancel</button>
                        <button wire:click="applyBulkClassGrade" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold">Apply</button>
                    </div>
                </div>
            </div>
        @endif
    @elseif($activeTab === 'schedule')
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Weekly Schedule</p>
                        <p class="text-xs text-gray-500 mt-1">Add one row per meeting day. Leave times blank to use the club defaults above. Assign a facilitator per day or leave open for any club facilitator.</p>
                    </div>
                    @if($canManageSettings)
                        <button wire:click="addScheduleRow" class="px-3 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 text-xs font-bold">+ Add Day</button>
                    @endif
                </div>

                @error('scheduleRows') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                @forelse($scheduleRows as $index => $row)
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end border border-gray-100 dark:border-gray-700 rounded-xl p-3" wire:key="schedule-row-{{ $index }}-{{ $row['id'] ?? 'new' }}">
                        <div>
                            <label class="text-xs font-semibold text-gray-500">Day</label>
                            <select wire:model="scheduleRows.{{ $index }}.day_of_week" @disabled(! $canManageSettings) class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                                <option value="">Select day...</option>
                                @foreach($scheduleDays as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('scheduleRows.'.$index.'.day_of_week') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500">Start</label>
                            <input type="time" wire:model="scheduleRows.{{ $index }}.session_start" @disabled(! $canManageSettings) class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500">End</label>
                            <input type="time" wire:model="scheduleRows.{{ $index }}.session_end" @disabled(! $canManageSettings) class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500">Facilitator</label>
                            <select wire:model="scheduleRows.{{ $index }}.instructor_id" @disabled(! $canManageSettings) class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                                <option value="">Any club facilitator</option>
                                @foreach($facilitatorOptions as $facilitator)
                                    <option value="{{ $facilitator->id }}">{{ $facilitator->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($canManageSettings)
                            <div>
                                <button wire:click="removeScheduleRow({{ $index }})" class="text-xs font-bold text-red-600">Remove</button>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">No schedule days configured. Use Edit Club for a single default day, or add rows here for Mon–Fri sessions.</p>
                @endforelse

                @if($canManageSettings && count($scheduleRows) > 0)
                    <button wire:click="saveSchedules" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">Save Schedule</button>
                @endif
            </div>
        </div>
    @elseif($activeTab === 'reports' && $canGenerateReports)
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Term report settings</p>
                    <p class="text-xs text-gray-500 mt-1">Set the term once for the club. Soft fields (comments, strengths, behavior) are edited per student below. Progress metrics are filled automatically from LMS data.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Term key</label>
                        <input type="text" wire:model.live.debounce.400ms="reportTermKey" placeholder="2025-T2" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                        @error('reportTermKey') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Term label</label>
                        <input type="text" wire:model="reportTermLabel" placeholder="Term 2 2025" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Period start</label>
                        <input type="date" wire:model="reportPeriodStart" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Period end</label>
                        <input type="date" wire:model="reportPeriodEnd" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Default instructor comment (pre-fills empty rows)</label>
                    <textarea wire:model="reportDefaultComment" rows="2" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="saveReportTermSettings" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">Save drafts</button>
                    <button type="button" wire:click="exportReportDraftsCsv" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-sm font-bold">Export drafts CSV</button>
                    <a href="{{ route('admin.code-clubs.reports.bulk-download', array_merge(['club' => $club], $reportQuery ?? [])) }}"
                       class="px-4 py-2 rounded-xl bg-orange-600 text-white text-sm font-bold hover:bg-orange-700">
                        Download all ZIP
                    </a>
                    <a href="{{ route('admin.code-clubs.reports.school-summary', array_merge(['club' => $club], $reportQuery ?? [])) }}"
                       class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700">
                        School summary PDF
                    </a>
                    @php $sampleReportStudent = collect($reportDraftRows)->keys()->first(); @endphp
                    @if($sampleReportStudent)
                        <a href="{{ route('admin.code-clubs.reports.preview', array_merge(['club' => $club, 'student' => $sampleReportStudent], $reportQuery ?? [])) }}"
                           target="_blank"
                           class="px-4 py-2 rounded-xl border border-orange-300 text-orange-700 text-sm font-bold hover:bg-orange-50">
                            Preview sample
                        </a>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Bulk fill performance metrics</p>
                        <p class="text-xs text-gray-500 mt-1">Set scores once and apply to every student on this club roster. Leave a field blank to skip it. Grades (A+, D, …) are calculated automatically on the PDF.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="setBulkPerformanceMetricsTo(100)" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold">Fill all 100</button>
                        <button type="button" wire:click="setBulkPerformanceMetricsTo(0)" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-bold">Clear to 0</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Overall score %</label>
                        <input type="number" min="0" max="100" step="0.1" wire:model="bulkOverallScore" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                    </div>
                    @foreach($bulkPerformanceMetrics as $metricKey => $metric)
                        <div>
                            <label class="text-xs font-semibold text-gray-500">{{ $metric['label'] ?? $metricKey }}</label>
                            <input type="number" min="0" max="100" step="0.1" wire:model="bulkPerformanceMetrics.{{ $metricKey }}.value" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                        </div>
                    @endforeach
                </div>
                <button type="button" wire:click="applyBulkPerformanceMetrics" wire:confirm="Apply these metric scores to all students for this term?" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-bold">
                    Apply to all students
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Roster prep</p>
                    <p class="text-xs text-gray-500">Edit soft fields per student, then download individually or as a ZIP.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Student</th>
                                <th class="px-4 py-3 text-left">Class</th>
                                <th class="px-4 py-3 text-left">Draft</th>
                                <th class="px-4 py-3 text-left">Comment preview</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($reportDraftRows as $studentId => $row)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $row['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $row['student_code'] ?? $studentId }}</p>
                                    </td>
                                    <td class="px-4 py-3">{{ $row['class_grade'] ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($row['has_metric_overrides'] ?? false)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">Overrides</span>
                                        @elseif($row['has_draft'])
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">Saved</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">Empty</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                        {{ \Illuminate\Support\Str::limit($row['instructor_comment'] ?: '—', 80) }}
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                        <button type="button" wire:click="openReportEditor({{ $studentId }})" class="text-xs font-bold text-indigo-600">Edit</button>
                                        <a href="{{ route('admin.code-clubs.reports.preview', array_merge(['club' => $club, 'student' => $studentId], $reportQuery ?? [])) }}" target="_blank" class="text-xs font-bold text-blue-600">Preview</a>
                                        <a href="{{ route('admin.code-clubs.reports.download', array_merge(['club' => $club, 'student' => $studentId], $reportQuery ?? [])) }}" target="_blank" class="text-xs font-bold text-orange-600">PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No active members to prepare reports for.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($editingReportStudentId && !empty($reportEditor))
                @php $editingRow = $reportDraftRows[$editingReportStudentId] ?? null; @endphp
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white dark:bg-gray-800 shadow-xl">
                        <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-5 py-4 flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Edit report · {{ $editingRow['name'] ?? 'Student' }}</h2>
                                <p class="text-xs text-gray-500 mt-1">Edit comments and override progress, attendance, and track completion for this PDF.</p>
                            </div>
                            <button type="button" wire:click="closeReportEditor" class="text-sm text-gray-500">Close</button>
                        </div>
                        <div class="p-5 space-y-5">
                            <div class="rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50/60 dark:bg-indigo-950/20 p-4 space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-bold text-indigo-900 dark:text-indigo-100">Performance metrics (on PDF)</p>
                                        <p class="text-xs text-indigo-700/80 dark:text-indigo-300/80">These scores appear as bars with letter grades. Edit per student here, or use Bulk fill above.</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" wire:click="setEditorPerformanceMetricsTo(100)" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold">Set all 100</button>
                                        <button type="button" wire:click="resetReportMetricsToAuto" wire:confirm="Reset all progress fields to LMS auto values?" class="px-3 py-1.5 rounded-lg border border-indigo-300 text-indigo-800 dark:text-indigo-200 text-xs font-bold">Reset to LMS auto</button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">Overall score %</label>
                                        <input type="number" min="0" max="100" wire:model="reportEditor.overall_score" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                                        <p class="text-[11px] text-gray-500 mt-1">Auto: {{ $reportEditor['auto_overall_score'] ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">Attendance present</label>
                                        <input type="number" min="0" wire:model="reportEditor.attendance_present" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">Attendance total</label>
                                        <input type="number" min="0" wire:model="reportEditor.attendance_total" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-600">Attendance rate %</label>
                                        <input type="number" min="0" max="100" wire:model="reportEditor.attendance_rate" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm" />
                                        <p class="text-[11px] text-gray-500 mt-1">Auto: {{ $reportEditor['auto_attendance']['rate'] ?? '—' }}{{ isset($reportEditor['auto_attendance']['rate']) ? '%' : '' }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($reportEditor['performance_metrics'] ?? [] as $metricKey => $metric)
                                        <div class="flex items-center gap-2 rounded-lg bg-white/70 dark:bg-gray-900/40 border border-indigo-100 dark:border-indigo-900 px-3 py-2">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $metric['label'] ?? $metricKey }}</p>
                                                <p class="text-[10px] text-gray-500">Auto: {{ $metric['auto'] ?? '—' }}</p>
                                            </div>
                                            <input type="number" min="0" max="100" step="0.1" wire:model="reportEditor.performance_metrics.{{ $metricKey }}.value" class="w-20 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm text-right" />
                                        </div>
                                    @endforeach
                                </div>

                                <div>
                                    <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Skills %</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                        @foreach($reportEditor['skills'] ?? [] as $skillKey => $skill)
                                            <div>
                                                <label class="text-[11px] text-gray-500">{{ $skill['label'] ?? $skillKey }}</label>
                                                <input type="number" min="0" max="100" wire:model="reportEditor.skills.{{ $skillKey }}.value" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm" />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 pt-1">
                                    <button type="button" wire:click="markAllTracksComplete" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold">Mark all tracks complete</button>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Tracks (progress + notes)</p>
                                <div class="space-y-3">
                                    @foreach($reportEditor['tracks'] ?? [] as $trackKey => $track)
                                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3 space-y-3" style="border-left-width: 4px; border-left-color: {{ $track['color'] ?? '#64748b' }};">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $track['label'] ?? $trackKey }}</p>
                                                    <p class="text-[11px] text-gray-500">LMS auto: {{ ($track['auto_enrolled'] ?? false) ? (($track['auto_progress'] ?? '—').'% · '.$track['auto_lessons']) : 'Not enrolled' }}</p>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-3">
                                                    <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                        <input type="checkbox" wire:model="reportEditor.tracks.{{ $trackKey }}.force_enrolled" class="rounded border-gray-300" />
                                                        Show as enrolled
                                                    </label>
                                                    <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                                        <input type="checkbox" wire:model="reportEditor.tracks.{{ $trackKey }}.mark_complete" class="rounded border-gray-300" />
                                                        Mark complete
                                                    </label>
                                                    <button type="button" wire:click="markTrackComplete('{{ $trackKey }}')" class="text-xs font-bold text-emerald-600">Apply 100%</button>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                                <div>
                                                    <label class="text-[11px] text-gray-500">Progress %</label>
                                                    <input type="number" min="0" max="100" wire:model="reportEditor.tracks.{{ $trackKey }}.progress_percent" class="w-full mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm" />
                                                </div>
                                                <div>
                                                    <label class="text-[11px] text-gray-500">Lessons done</label>
                                                    <input type="number" min="0" wire:model="reportEditor.tracks.{{ $trackKey }}.lessons_completed" class="w-full mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm" />
                                                </div>
                                                <div>
                                                    <label class="text-[11px] text-gray-500">Lessons total</label>
                                                    <input type="number" min="0" wire:model="reportEditor.tracks.{{ $trackKey }}.lessons_total" class="w-full mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm" />
                                                </div>
                                                <div>
                                                    <label class="text-[11px] text-gray-500">Quiz avg %</label>
                                                    <input type="number" min="0" max="100" wire:model="reportEditor.tracks.{{ $trackKey }}.quiz_average" class="w-full mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm" />
                                                </div>
                                                <div>
                                                    <label class="text-[11px] text-gray-500">Projects count</label>
                                                    <input type="number" min="0" wire:model="reportEditor.tracks.{{ $trackKey }}.projects_count" class="w-full mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm" />
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <div>
                                                    <label class="text-[11px] text-gray-500">Projects (one per line)</label>
                                                    <textarea wire:model="reportEditor.tracks.{{ $trackKey }}.projects_text" rows="2" class="w-full mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm"></textarea>
                                                </div>
                                                <div>
                                                    <label class="text-[11px] text-gray-500">Skills gained (one per line)</label>
                                                    <textarea wire:model="reportEditor.tracks.{{ $trackKey }}.skills_gained_text" rows="2" class="w-full mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm"></textarea>
                                                </div>
                                                <div>
                                                    <label class="text-[11px] text-gray-500">Strengths</label>
                                                    <input type="text" wire:model="reportEditor.tracks.{{ $trackKey }}.strengths" class="w-full mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm" />
                                                </div>
                                                <div>
                                                    <label class="text-[11px] text-gray-500">Next focus</label>
                                                    <input type="text" wire:model="reportEditor.tracks.{{ $trackKey }}.next_focus" class="w-full mt-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-2 py-1.5 text-sm" />
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-gray-500">Summary</label>
                                <textarea wire:model="reportEditor.summary" rows="3" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500">Overall label override</label>
                                    <select wire:model="reportEditor.overall_label" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                                        <option value="">Auto from score</option>
                                        @foreach($overallLabelOptions as $label)
                                            <option value="{{ $label }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500">Instructor comment</label>
                                    <textarea wire:model="reportEditor.instructor_comment" rows="3" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Behavior (1–5)</p>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach($behaviorKeys as $key => $label)
                                        <div>
                                            <label class="text-xs text-gray-500">{{ $label }}</label>
                                            <select wire:model="reportEditor.behavior.{{ $key }}" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500">Achievements (one per line)</label>
                                    <textarea wire:model="reportEditor.achievements_text" rows="4" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500">Improvements (one per line)</label>
                                    <textarea wire:model="reportEditor.improvements_text" rows="4" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500">Next-term goals (one per line)</label>
                                    <textarea wire:model="reportEditor.goals_text" rows="4" class="w-full mt-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-5 py-4 flex justify-end gap-3">
                            <button type="button" wire:click="closeReportEditor" class="px-4 py-2 rounded-xl border text-sm">Cancel</button>
                            <button type="button" wire:click="saveReportDraft" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">Save draft</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @elseif($activeTab === 'facilitators')
        <div class="space-y-4">
            @if($canManageSettings)
                <button wire:click="$set('showAddInstructorModal', true)" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold">Assign Facilitator</button>
            @endif

            @if($showAddInstructorModal)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 rounded-xl p-4 space-y-3">
                    <input type="text" wire:model.live.debounce.300ms="instructorSearch" placeholder="Search trainers..." class="w-full rounded-xl border px-3 py-2 text-sm" />
                    @foreach($instructorResults as $instructor)
                        <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg px-3 py-2">
                            <span class="text-sm">{{ $instructor->name }}</span>
                            <button wire:click="assignInstructor({{ $instructor->id }})" class="text-xs font-bold text-blue-600">Assign</button>
                        </div>
                    @endforeach
                    <button wire:click="$set('showAddInstructorModal', false)" class="text-xs text-gray-500">Close</button>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($club->activeInstructors as $assignment)
                    <div class="flex items-center justify-between px-4 py-3">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $assignment->instructor?->name }}</p>
                            <p class="text-xs text-gray-500">{{ $assignment->instructor?->email }}</p>
                        </div>
                        @if($canManageSettings)
                            <button wire:click="removeInstructor({{ $assignment->instructor_id }})" class="text-xs text-red-600 font-semibold">Remove</button>
                        @endif
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-gray-500 text-sm">No facilitators assigned.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
