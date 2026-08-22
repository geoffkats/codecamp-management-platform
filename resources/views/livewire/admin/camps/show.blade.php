<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 space-y-6">

    {{-- Flash --}}
    @if(session('message'))
        <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-semibold text-green-800 dark:text-green-200">{{ session('message') }}</p>
        </div>
    @endif

    {{-- Back --}}
    <a href="{{ route('admin.camps.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        All Camps
    </a>

    {{-- Camp header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="h-1.5 bg-orange-500"></div>
        <div class="p-6">
            @if(!$isEditing)
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Code Camp</p>
                        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $camp->name }}</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $camp->date_range }}</p>
                        @if($camp->description)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $camp->description }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        @php
                            $statusColors = [
                                'upcoming'  => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
                                'active'    => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
                                'completed' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                                'archived'  => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                            ];
                            $nextStatus = ['upcoming' => 'Activate', 'active' => 'Complete', 'completed' => 'Archive'];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-sm font-bold {{ $statusColors[$camp->status] ?? '' }}">
                            {{ ucfirst($camp->status) }}
                        </span>
                        <div class="flex gap-2">
                            @if($canManageCampSettings && isset($nextStatus[$camp->status]))
                                <button wire:click="advanceStatus"
                                        class="px-3 py-1.5 text-xs font-bold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    {{ $nextStatus[$camp->status] }}
                                </button>
                            @endif
                            @if($canManageCampSettings)
                            <button wire:click="toggleEdit"
                                    class="px-3 py-1.5 text-xs font-bold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Edit
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                @if($canManageCampSettings)
                {{-- Inline edit form --}}
                <div class="space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Edit Camp</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Name</label>
                            <input type="text" wire:model="editName"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" />
                            @error('editName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Start Date</label>
                            <input type="date" wire:model="editStartDate"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">End Date</label>
                            <input type="date" wire:model="editEndDate"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Max Capacity</label>
                            <input type="number" wire:model="editMaxCapacity" min="1"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Description</label>
                            <textarea wire:model="editDescription" rows="2"
                                      class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 transition resize-none"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button wire:click="toggleEdit"
                                class="px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="saveCamp"
                                class="px-5 py-2 text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition-colors">
                            Save Changes
                        </button>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Active Students',  'value' => $stats['active'],      'color' => 'text-green-600'],
            ['label' => 'Transferred Out',  'value' => $stats['transferred'], 'color' => 'text-blue-600'],
            ['label' => 'Completed',        'value' => $stats['completed'],   'color' => 'text-gray-600'],
            ['label' => 'Dropped',          'value' => $stats['dropped'],     'color' => 'text-red-600'],
        ] as $stat)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
                <p class="text-2xl font-extrabold {{ $stat['color'] }}">{{ $stat['value'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold mt-0.5">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-gray-100 dark:bg-gray-700/50 rounded-xl p-1 w-fit">
        @foreach(['students' => 'Students', 'history' => 'Transfer History'] as $tab => $label)
            <button wire:click="setTab('{{ $tab }}')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors
                        {{ $activeTab === $tab
                            ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Students tab --}}
    @if($activeTab === 'students')
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="searchStudents" placeholder="Search students…"
                       class="flex-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" />
                <button wire:click="openTransferModal"
                        class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 text-sm font-bold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Transfer
                </button>
                <button wire:click="openAddModal"
                        class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Student
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                @forelse($activeStudents as $enrollment)
                    <div class="flex items-center gap-4 px-5 py-3.5 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                        <div class="w-9 h-9 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-bold text-orange-600 dark:text-orange-400">
                                {{ strtoupper(substr($enrollment->student->name ?? '?', 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('students.show', $enrollment->student_id) }}"
                               class="text-sm font-bold text-gray-900 dark:text-white hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                                {{ $enrollment->student->name }}
                            </a>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                @if($enrollment->student->studentProfile?->student_id)
                                    {{ $enrollment->student->studentProfile->student_id }} &middot;
                                @endif
                                Enrolled {{ $enrollment->enrolled_at->format('d M Y') }}
                                @if($enrollment->enrolledBy)
                                    by {{ $enrollment->enrolledBy->name }}
                                @endif
                            </p>
                        </div>
                        @if($enrollment->previousCamp)
                            <span class="hidden sm:inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 font-semibold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                                from {{ $enrollment->previousCamp->name }}
                            </span>
                        @endif
                        <span class="flex-shrink-0 w-2 h-2 rounded-full bg-green-500"></span>
                        <button wire:click="confirmRemove({{ $enrollment->student_id }})"
                                class="flex-shrink-0 text-xs text-red-500 hover:text-red-700 font-semibold transition-colors">
                            Remove
                        </button>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No students enrolled yet.</p>
                        <button wire:click="openAddModal" class="mt-2 text-sm font-semibold text-orange-600 dark:text-orange-400 hover:underline">Add the first student</button>
                    </div>
                @endforelse
            </div>

            @if($activeStudents->hasPages())
                <div>{{ $activeStudents->links() }}</div>
            @endif
        </div>
    @endif

    {{-- History tab --}}
    @if($activeTab === 'history')
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            @forelse($historyEnrollments as $enrollment)
                @php
                    $statusColors = [
                        'transferred' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
                        'completed'   => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
                        'dropped'     => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                    ];
                @endphp
                <div class="flex items-center gap-4 px-5 py-3.5 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                    <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold text-gray-500 dark:text-gray-400">
                            {{ strtoupper(substr($enrollment->student->name ?? '?', 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $enrollment->student->name }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                            Enrolled {{ $enrollment->enrolled_at->format('d M Y') }}
                            @if($enrollment->completed_at)
                                → Left {{ $enrollment->completed_at->format('d M Y') }}
                            @endif
                        </p>
                        @if($enrollment->status === 'transferred')
                            @php $dest = $transferDestinations[$enrollment->student_id . ':' . $enrollment->camp_id] ?? null; @endphp
                            @if($dest?->camp)
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5 font-semibold">
                                    → transferred to {{ $dest->camp->name }}
                                </p>
                            @endif
                        @endif
                        @if($enrollment->notes)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 italic">{{ $enrollment->notes }}</p>
                        @endif
                    </div>
                    <span class="flex-shrink-0 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusColors[$enrollment->status] ?? '' }}">
                        {{ ucfirst($enrollment->status) }}
                    </span>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">No transfer or completion history yet.</div>
            @endforelse
        </div>
    @endif

    {{-- ── Add Student Modal ── --}}
    @if($showAddModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="closeAddModal"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
                <div class="h-1 bg-orange-500"></div>
                <div class="p-6">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Add Student to Camp</h2>
                    <input type="text" wire:model.live.debounce.300ms="studentSearch"
                           placeholder="Search by student name…"
                           class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" />

                    <div class="mt-3 space-y-2 max-h-72 overflow-y-auto">
                        @if(strlen($studentSearch) >= 2 && empty($studentResults))
                            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">No CodeCamp students found.</p>
                        @endif
                        @foreach($studentResults as $student)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50">
                                <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-orange-600 dark:text-orange-400">{{ strtoupper(substr($student['name'], 0, 1)) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $student['name'] }}</p>
                                    @if(!empty($student['current_camp_enrollment']['camp']['name']))
                                        <p class="text-xs text-amber-600 dark:text-amber-400">Currently in: {{ $student['current_camp_enrollment']['camp']['name'] }}</p>
                                    @else
                                        <p class="text-xs text-gray-400 dark:text-gray-500">No current camp</p>
                                    @endif
                                </div>
                                <button wire:click="enrollStudent({{ $student['id'] }})"
                                        class="flex-shrink-0 px-3 py-1.5 text-xs font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors">
                                    Enroll
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button wire:click="closeAddModal"
                                class="px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Transfer Modal ── --}}
    @if($showTransferModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="closeTransferModal"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
                <div class="h-1 bg-blue-500"></div>
                <div class="p-6">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white mb-1">Transfer Students</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Move selected students to another camp. Their history in this camp is preserved.</p>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Destination Camp <span class="text-red-500">*</span></label>
                        <select wire:model="transferTargetCampId"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">Select destination camp…</option>
                            @foreach($otherCamps as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('transferTargetCampId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Select Students to Transfer <span class="text-red-500">*</span></label>
                        @error('selectedStudentIds') <p class="text-xs text-red-500 mb-2">{{ $message }}</p> @enderror
                        <div class="max-h-48 overflow-y-auto space-y-1 border border-gray-200 dark:border-gray-600 rounded-xl p-2">
                            @foreach($activeStudents as $enrollment)
                                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" wire:model="selectedStudentIds" value="{{ $enrollment->student_id }}"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $enrollment->student->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if(count($selectedStudentIds) > 0)
                            <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold mt-1">{{ count($selectedStudentIds) }} selected</p>
                        @endif
                    </div>

                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Note (optional)</label>
                        <input type="text" wire:model="transferNote" placeholder="e.g. Promoted to next session"
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <button wire:click="closeTransferModal"
                                class="px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="transferStudents"
                                class="px-5 py-2 text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">
                            Transfer Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Remove Confirm Modal ── --}}
    @if($showRemoveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="closeRemoveModal"></div>
            <div class="relative w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2">Remove Student</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">This will mark the student as dropped from this camp. History is preserved.</p>
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Reason (optional)</label>
                    <input type="text" wire:model="removeNote" placeholder="e.g. Left the program"
                           class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 transition" />
                </div>
                <div class="flex justify-end gap-3">
                    <button wire:click="closeRemoveModal"
                            class="px-4 py-2 text-sm font-semibold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="removeStudent"
                            class="px-5 py-2 text-sm font-bold bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
