<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="px-6 py-6 space-y-6">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm px-6 py-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">ICDL Workflow</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Corporate overview of payments, test reviews, and exam approvals</p>
                </div>
                <div class="w-full md:w-80">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, ID, ICDL..." class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Payments Pending</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-2">{{ $paymentPending->total() }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">ICDL Tests Pending</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-2">{{ $icdlPending->total() }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Exam Requests</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-2">{{ $examRequests->total() }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Exam Payments</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-2">{{ $examPayments->total() }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Payments Pending Verification</h2>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">{{ $paymentPending->total() }} items</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Submitted</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($paymentPending as $student)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        <div class="font-medium">
                                            <a href="{{ route('students.show', $student->id) }}" wire:navigate class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                                {{ $student->full_name }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $student->payment_amount ? number_format($student->payment_amount, 2) : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $student->payment_submitted_at?->format('M j, Y H:i') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <button wire:click="verifyPayment({{ $student->id }})" class="px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Verify</button>
                                        <button wire:click="rejectPayment({{ $student->id }})" class="ml-2 px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700">Reject</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No pending payments.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $paymentPending->links() }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">ICDL Test Marks Pending Review</h2>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">{{ $icdlPending->total() }} items</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Score</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($icdlPending as $student)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        <div class="font-medium">
                                            <a href="{{ route('students.show', $student->id) }}" wire:navigate class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                                {{ $student->full_name }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $student->icdl_test_score !== null ? number_format($student->icdl_test_score, 1) : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <button wire:click="reviewIcdlTest({{ $student->id }}, 'approved')" class="px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Approve</button>
                                        <button wire:click="reviewIcdlTest({{ $student->id }}, 'rejected')" class="ml-2 px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700">Reject</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No pending ICDL tests.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $icdlPending->links() }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Exam Requests</h2>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300">{{ $examRequests->total() }} items</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Requested</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($examRequests as $student)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        <div class="font-medium">
                                            <a href="{{ route('students.show', $student->id) }}" wire:navigate class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                                {{ $student->full_name }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $student->exam_requested_at?->format('M j, Y H:i') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <button wire:click="approveExamRequest({{ $student->id }})" class="px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Approve</button>
                                        <button wire:click="declineExamRequest({{ $student->id }})" class="ml-2 px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700">Decline</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No exam requests.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $examRequests->links() }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Exam Payments & Scheduling</h2>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">{{ $examPayments->total() }} items</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Schedule</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($examPayments as $student)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        <div class="font-medium">
                                            <a href="{{ route('students.show', $student->id) }}" wire:navigate class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                                {{ $student->full_name }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ str_replace('_', ' ', $student->exam_payment_status ?? 'not_submitted') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        <input type="datetime-local" wire:model.defer="examDates.{{ $student->id }}" class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs" />
                                        @if($student->exam_scheduled_for)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Scheduled: {{ $student->exam_scheduled_for->format('M j, Y H:i') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <button wire:click="verifyExamPayment({{ $student->id }})" class="px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Verify</button>
                                        <button wire:click="saveExamDate({{ $student->id }})" class="ml-2 px-3 py-1.5 rounded-md bg-teal-600 text-white hover:bg-teal-700">Save Date</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No exam payments submitted.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $examPayments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
