<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Registration Requests</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">Review program sign-ups and manage follow-ups.</p>
            </div>
            <div class="flex items-center gap-3">
                @if($stats['awaiting_payment'] > 0)
                    <button type="button" wire:click="$set('filterType', 'membership')" class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 hover:bg-amber-200">
                        {{ $stats['awaiting_payment'] }} membership {{ $stats['awaiting_payment'] === 1 ? 'fee' : 'fees' }} unpaid
                    </button>
                @endif
                <a href="{{ route('registration.membership') }}" target="_blank" class="text-xs font-semibold text-orange-600 hover:underline">Open membership form ↗</a>
            </div>
        </div>

        @if(session()->has('message'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">New</p>
                <p class="text-2xl font-semibold text-orange-600">{{ number_format($stats['new']) }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Contacted</p>
                <p class="text-2xl font-semibold text-blue-600">{{ number_format($stats['contacted']) }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Scheduled</p>
                <p class="text-2xl font-semibold text-purple-600">{{ number_format($stats['scheduled']) }}</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Closed</p>
                <p class="text-2xl font-semibold text-emerald-600">{{ number_format($stats['closed']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl p-4 flex flex-col md:flex-row gap-4 md:items-center">
            <div class="flex-1">
                <label class="text-xs uppercase tracking-wide text-slate-500">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name, email, organization..." class="mt-2 w-full rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-slate-900 dark:text-white">
            </div>
            <div class="min-w-[180px]">
                <label class="text-xs uppercase tracking-wide text-slate-500">Program</label>
                <select wire:model.live="filterType" class="mt-2 w-full rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-slate-900 dark:text-white">
                    <option value="all">All Programs</option>
                    <option value="membership">CodeCamp Membership</option>
                    <option value="codecamp">CodeCamp</option>
                    <option value="school">ICT Schools Program</option>
                    <option value="icdl">ICDL Exam Center</option>
                    <option value="codeclub">CodeClub</option>
                </select>
            </div>
            <div class="min-w-[180px]">
                <label class="text-xs uppercase tracking-wide text-slate-500">Status</label>
                <select wire:model.live="filterStatus" class="mt-2 w-full rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-slate-900 dark:text-white">
                    <option value="all">All Status</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300">
                        <tr>
                            <th class="text-left px-4 py-3">Type</th>
                            <th class="text-left px-4 py-3">Name</th>
                            <th class="text-left px-4 py-3">Email</th>
                            <th class="text-left px-4 py-3">Organization</th>
                            <th class="text-left px-4 py-3">Payment</th>
                            <th class="text-left px-4 py-3">Status</th>
                            <th class="text-right px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                        @forelse($requests as $request)
                            <tr class="hover:bg-slate-50 dark:hover:bg-zinc-800/40">
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ strtoupper($request->type) }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $request->full_name }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $request->email }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $request->organization_name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($request->isMembership())
                                        <span class="{{ $request->isPaid() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }} rounded-full px-2 py-0.5 text-xs font-semibold">
                                            {{ $request->isPaid() ? 'Paid' : 'Unpaid' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <select wire:change="updateStatus({{ $request->id }}, $event.target.value)" class="rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-2 py-1 text-xs text-slate-700 dark:text-slate-200">
                                        <option value="new" @selected($request->status === 'new')>New</option>
                                        <option value="contacted" @selected($request->status === 'contacted')>Contacted</option>
                                        <option value="scheduled" @selected($request->status === 'scheduled')>Scheduled</option>
                                        <option value="closed" @selected($request->status === 'closed')>Closed</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" wire:click="viewRequest({{ $request->id }})" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-semibold text-xs">View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-slate-500">No registration requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200 dark:border-zinc-800">
                {{ $requests->links() }}
            </div>
        </div>
    </div>

    @if($showDetailsModal && $this->selectedRequest)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-slate-900/60" wire:click="closeDetails"></div>
            <div class="relative bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 w-full max-w-2xl mx-4">
                <div class="flex items-center justify-between p-5 border-b border-slate-200 dark:border-zinc-800">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500">{{ strtoupper($this->selectedRequest->type) }}</p>
                        <h3 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $this->selectedRequest->full_name }}</h3>
                    </div>
                    <button type="button" wire:click="closeDetails" class="text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">Close</button>
                </div>
                <div class="p-5 space-y-4 text-sm text-slate-700 dark:text-slate-300">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Email</p>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $this->selectedRequest->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Phone</p>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $this->selectedRequest->phone }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Organization</p>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $this->selectedRequest->organization_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Role</p>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $this->selectedRequest->role_title ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Program Interest</p>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $this->selectedRequest->program_interest ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Course Interest</p>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $this->selectedRequest->course_interest ?? '-' }}</p>
                        </div>
                    </div>

                    @if($this->selectedRequest->meta)
                        <div class="bg-slate-50 dark:bg-zinc-800/60 rounded-xl p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Additional Details</p>
                            <div class="grid sm:grid-cols-2 gap-3">
                                @foreach($this->selectedRequest->meta as $key => $value)
                                    @if($value)
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-500">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                                            <p class="font-semibold text-slate-900 dark:text-white">
                                                {{ is_array($value) ? implode(', ', $value) : $value }}
                                            </p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($this->selectedRequest->isMembership())
                        @php $membership = $this->selectedRequest; @endphp
                        <div class="rounded-xl border border-slate-200 dark:border-zinc-700 p-4 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Membership details</p>
                                <span class="{{ $membership->isPaid() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }} rounded-full px-2 py-0.5 text-xs font-semibold">
                                    Application fee {{ $membership->isPaid() ? 'paid' : 'unpaid' }}
                                </span>
                            </div>
                            <div class="grid sm:grid-cols-3 gap-3">
                                <div>
                                    <p class="text-xs text-slate-500">Date of birth</p>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $membership->date_of_birth?->format('d M Y') ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Gender / class</p>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $membership->gender ?? '-' }} · {{ $membership->school_level ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Camp</p>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $membership->preferred_schedule ?: 'Next available' }}</p>
                                </div>
                            </div>

                            @if($membership->payments->isNotEmpty())
                                <table class="w-full text-xs">
                                    <thead class="text-slate-500">
                                        <tr>
                                            <th class="text-left py-1">Reference</th>
                                            <th class="text-left py-1">Amount</th>
                                            <th class="text-left py-1">Method</th>
                                            <th class="text-left py-1">Status</th>
                                            <th class="text-left py-1">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                        @foreach($membership->payments->sortByDesc('id') as $payment)
                                            <tr>
                                                <td class="py-1 font-mono">{{ $payment->confirmation_code ?: $payment->merchant_reference }}</td>
                                                <td class="py-1">{{ $payment->currency }} {{ number_format($payment->amount) }}</td>
                                                <td class="py-1">{{ $payment->payment_method ?: '-' }}</td>
                                                <td class="py-1 capitalize">{{ $payment->status }}</td>
                                                <td class="py-1">{{ ($payment->paid_at ?? $payment->created_at)->format('d M Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            <div class="flex flex-wrap gap-2 pt-1">
                                @if(! $membership->isPaid())
                                    <button type="button" wire:click="markPaidOffline({{ $membership->id }})" wire:confirm="Mark the application fee as paid (cash, bank or mobile money received outside Pesapal)?" class="rounded-lg border border-slate-300 dark:border-zinc-600 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-zinc-800">
                                        Mark paid (offline)
                                    </button>
                                    <a href="{{ $membership->paymentUrl() }}" target="_blank" class="rounded-lg border border-slate-300 dark:border-zinc-600 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-zinc-800">
                                        Payment link ↗
                                    </a>
                                @endif
                                @if(! empty($membership->meta['student_profile_id']))
                                    <a href="{{ route('students.show', $membership->meta['student_profile_id']) }}" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">View student</a>
                                @elseif(auth()->user()->can('manage_students'))
                                    <a href="{{ route('students.create-codecamp', ['membership' => $membership->id]) }}" class="rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-700">Create student from this application</a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($this->selectedRequest->message)
                        <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl p-4">
                            <p class="text-xs uppercase tracking-wide text-orange-600 dark:text-orange-300">Message</p>
                            <p class="mt-2 text-slate-800 dark:text-slate-200">{{ $this->selectedRequest->message }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
