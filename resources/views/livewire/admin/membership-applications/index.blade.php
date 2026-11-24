<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-7xl space-y-6 p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Membership Applications</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">Full Code Camp membership submissions — open any record for complete details.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.registration-requests') }}" wire:navigate class="text-xs font-semibold text-slate-500 hover:text-orange-600">All registration requests</a>
                <a href="{{ route('registration.membership') }}" target="_blank" class="text-xs font-semibold text-orange-600 hover:underline">Open public form ↗</a>
            </div>
        </div>

        @if(session()->has('message'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-200">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs uppercase tracking-wide text-slate-500">New</p>
                <p class="text-2xl font-semibold text-orange-600">{{ number_format($stats['new']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs uppercase tracking-wide text-slate-500">Unpaid</p>
                <p class="text-2xl font-semibold text-amber-600">{{ number_format($stats['unpaid']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs uppercase tracking-wide text-slate-500">Closed</p>
                <p class="text-2xl font-semibold text-emerald-600">{{ number_format($stats['closed']) }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 md:flex-row md:items-end">
            <div class="flex-1">
                <label class="text-xs uppercase tracking-wide text-slate-500">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name, email, phone, school…" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
            </div>
            <div class="min-w-[160px]">
                <label class="text-xs uppercase tracking-wide text-slate-500">Status</label>
                <select wire:model.live="filterStatus" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="all">All</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="text-xs uppercase tracking-wide text-slate-500">Payment</label>
                <select wire:model.live="filterPayment" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="all">All</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3 text-left">Child</th>
                            <th class="px-4 py-3 text-left">School / Class</th>
                            <th class="px-4 py-3 text-left">Parent contact</th>
                            <th class="px-4 py-3 text-left">Payment</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Submitted</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-zinc-800">
                        @forelse($applications as $application)
                            @php $meta = $application->meta ?? []; @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-zinc-800/40">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $application->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $application->gender ?? '—' }} · {{ $application->date_of_birth?->format('j M Y') ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <p>{{ $application->organization_name ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $application->school_level ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <p>{{ $meta['parent_name'] ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $application->phone }} · {{ $application->email }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="{{ $application->isPaid() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }} rounded-full px-2 py-0.5 text-xs font-semibold">
                                        {{ $application->isPaid() ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <select wire:change="updateStatus({{ $application->id }}, $event.target.value)" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 dark:border-zinc-700 dark:bg-zinc-950 dark:text-slate-200">
                                        <option value="new" @selected($application->status === 'new')>New</option>
                                        <option value="contacted" @selected($application->status === 'contacted')>Contacted</option>
                                        <option value="scheduled" @selected($application->status === 'scheduled')>Scheduled</option>
                                        <option value="closed" @selected($application->status === 'closed')>Closed</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $application->created_at->format('j M Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.membership-applications.show', $application) }}" wire:navigate class="text-xs font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400">View full</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-500">No membership applications found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 p-4 dark:border-zinc-800">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</div>
