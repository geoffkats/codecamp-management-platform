<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="px-6 py-6 space-y-6">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm px-6 py-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $school->name }} — ICT Students</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">School intake, payments, test review, and exam workflow</p>
                </div>
                <div class="flex flex-wrap gap-3 items-center">
                    <a href="{{ route('admin.schools.show', $school->id) }}" wire:navigate class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg">Back</a>
                    <a href="{{ route('students.create-ict', ['school_id' => $school->id]) }}" wire:navigate class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Add Student</a>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm px-6 py-4">
            <div class="flex flex-col md:flex-row gap-4 md:items-center md:justify-between">
                <div class="flex flex-1 gap-3 items-center">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, ID, ICDL..." class="w-full md:w-96 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="showInactive" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        Show removed
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Readiness</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ICDL Test</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Exam Request</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Exam Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Exam Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Enrolled By</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($students as $student)
                        @php
                            $passedModules = (int) ($passedModulesByStudent[$student->id] ?? 0);
                            $totalModules = (int) ($totalModulesByUser[$student->user_id] ?? 0);
                            $progress = $totalModules > 0 ? round(($passedModules / $totalModules) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                <div class="font-medium">
                                    <a href="{{ route('students.show', $student->id) }}" wire:navigate class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $student->full_name }}
                                    </a>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }} @if($student->icdl_number) · ICDL {{ $student->icdl_number }} @endif</div>
                                @if(!$student->is_active)
                                    <span class="inline-flex mt-1 px-2 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">Removed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 text-xs rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">ICT / ICDL</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="text-xs mt-1">{{ $progress }}%</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="inline-flex px-2.5 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                    {{ str_replace('_', ' ', $student->exam_readiness_status ?? 'not_ready') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="inline-flex px-2.5 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                    {{ str_replace('_', ' ', $student->payment_status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="inline-flex px-2.5 py-1 text-xs rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                    {{ str_replace('_', ' ', $student->icdl_test_status ?? 'not_submitted') }}
                                </span>
                                @if($student->icdl_test_score !== null)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Score: {{ number_format($student->icdl_test_score, 1) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="inline-flex px-2.5 py-1 text-xs rounded-full bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300">
                                    {{ str_replace('_', ' ', $student->exam_request_status ?? 'not_requested') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span class="inline-flex px-2.5 py-1 text-xs rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                    {{ str_replace('_', ' ', $student->exam_payment_status ?? 'not_submitted') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <input type="datetime-local" wire:model.defer="examDates.{{ $student->id }}" class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs" />
                                    <button type="button" wire:click="saveExamDate({{ $student->id }})" class="text-indigo-600 hover:text-indigo-700 text-xs">Save</button>
                                </div>
                                @if($student->exam_scheduled_for)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Scheduled: {{ $student->exam_scheduled_for->format('M j, Y H:i') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                —
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <div x-data="{ open: false }" class="inline-block">
                                    <button type="button" @click="open = true" class="inline-flex items-center gap-2 px-2.5 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs">
                                        Actions
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="open" x-cloak class="fixed inset-0 z-50">
                                        <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
                                        <div class="absolute inset-0 flex items-center justify-center p-4">
                                            <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700">
                                                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Actions — {{ $student->full_name }}</h3>
                                                    <button type="button" @click="open = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">✕</button>
                                                </div>
                                                <div class="p-4 space-y-3">
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                        <a href="{{ route('students.show', $student->id) }}" wire:navigate class="px-3 py-2 text-sm rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">View Profile</a>
                                                        <a href="{{ route('students.edit', $student->id) }}" wire:navigate class="px-3 py-2 text-sm rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Edit Student</a>
                                                    </div>

                                                    @if(($student->payment_status ?? 'pending') === 'pending')
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                            <button wire:click="verifyPayment({{ $student->id }})" @click="open = false" class="px-3 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Verify Payment</button>
                                                            <button wire:click="rejectPayment({{ $student->id }})" @click="open = false" class="px-3 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700">Reject Payment</button>
                                                        </div>
                                                    @endif

                                                    @if(($student->exam_request_status ?? 'not_requested') === 'requested')
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                            <button wire:click="approveExamRequest({{ $student->id }})" @click="open = false" class="px-3 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Approve Exam</button>
                                                            <button wire:click="declineExamRequest({{ $student->id }})" @click="open = false" class="px-3 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700">Decline Exam</button>
                                                        </div>
                                                    @endif

                                                    @if(($student->icdl_test_status ?? 'not_submitted') === 'pending_review')
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                            <button wire:click="reviewIcdlTest({{ $student->id }}, 'approved')" @click="open = false" class="px-3 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Approve Marks</button>
                                                            <button wire:click="reviewIcdlTest({{ $student->id }}, 'rejected')" @click="open = false" class="px-3 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700">Reject Marks</button>
                                                        </div>
                                                    @endif

                                                    @if(($student->exam_payment_status ?? 'not_submitted') === 'submitted')
                                                        <button wire:click="verifyExamPayment({{ $student->id }})" @click="open = false" class="w-full px-3 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Verify Exam Payment</button>
                                                    @endif

                                                    <button wire:click="toggleActive({{ $student->id }})" @click="open = false" class="w-full px-3 py-2 text-sm rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-200 hover:bg-red-100 dark:hover:bg-red-900/50">
                                                        {{ $student->is_active ? 'Remove Student' : 'Restore Student' }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">No ICT students found for this school.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{ $students->links() }}
    </div>
</div>
