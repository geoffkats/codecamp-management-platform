@php
    $app = $application;
    $meta = $app->meta ?? [];
    $val = fn (?string $key, $fallback = '—') => filled($meta[$key] ?? null) ? $meta[$key] : $fallback;
    $reports = [
        ['key' => 'term_report_1', 'label' => 'Most recent term'],
        ['key' => 'term_report_2', 'label' => 'Previous term'],
        ['key' => 'term_report_3', 'label' => 'Term before that'],
    ];
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('admin.membership-applications.index') }}" wire:navigate class="text-xs font-semibold text-orange-600 hover:underline">← Membership applications</a>
                <h1 class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $app->full_name }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Submitted {{ $app->created_at->format('j F Y · g:i A') }}
                    · ID #{{ $app->id }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="{{ $app->isPaid() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }} rounded-full px-3 py-1 text-xs font-semibold">
                    {{ $app->isPaid() ? 'Fee paid' : 'Fee unpaid' }}
                </span>
                <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold capitalize text-slate-800 dark:bg-zinc-700 dark:text-zinc-100">{{ $app->status }}</span>
            </div>
        </div>

        @if(session()->has('message'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-200">
                {{ session('message') }}
            </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select wire:change="updateStatus($event.target.value)" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="new" @selected($app->status === 'new')>New</option>
                    <option value="contacted" @selected($app->status === 'contacted')>Contacted</option>
                    <option value="scheduled" @selected($app->status === 'scheduled')>Scheduled</option>
                    <option value="closed" @selected($app->status === 'closed')>Closed</option>
                </select>
                @unless($app->isPaid())
                    <button type="button" wire:click="markPaidOffline" wire:confirm="Mark this application fee as paid offline?" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                        Mark fee paid
                    </button>
                @endunless
                @if($app->paymentUrl())
                    <a href="{{ $app->paymentUrl() }}" target="_blank" class="text-xs font-semibold text-orange-600 hover:underline">Payment link ↗</a>
                @endif
            </div>
            <button
                type="button"
                wire:click="deleteApplication"
                wire:confirm="Delete membership application for {{ $app->full_name }}? Term report photos and payment records will be removed. This cannot be undone."
                class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
            >
                Delete application
            </button>
        </div>

        {{-- Child --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <header class="border-b border-slate-100 bg-slate-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Child details</h2>
            </header>
            <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                <x-admin.field label="First name" :value="$val('first_name')" />
                <x-admin.field label="Middle name" :value="$val('middle_name')" />
                <x-admin.field label="Last name" :value="$val('last_name')" />
                <x-admin.field label="Full name" :value="$app->full_name" />
                <x-admin.field label="Date of birth" :value="$app->date_of_birth?->format('j F Y') ?? '—'" />
                <x-admin.field label="Gender" :value="$app->gender ?? '—'" />
                <x-admin.field label="Nationality" :value="$val('nationality')" />
                <x-admin.field label="School" :value="$app->organization_name ?? '—'" />
                <x-admin.field label="Class / grade" :value="$app->school_level ?? '—'" />
                <x-admin.field label="T-shirt size" :value="$val('tshirt_size')" />
                <x-admin.field label="Owns a laptop" :value="$val('laptop')" />
                <x-admin.field label="Coding experience" :value="$val('experience')" />
            </div>
        </section>

        {{-- Health & care --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <header class="border-b border-slate-100 bg-slate-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Health & care</h2>
            </header>
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <x-admin.field label="Medical conditions" :value="$val('medical_notes')" class="sm:col-span-2" />
                <x-admin.field label="History of mental illness" :value="$val('mental_illness')" />
                <x-admin.field label="Special care or attention" :value="$val('special_care')" />
                <x-admin.field label="Passions" :value="$val('passions')" />
                <x-admin.field label="Known weaknesses" :value="$val('weaknesses')" />
            </div>
        </section>

        {{-- Term reports --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <header class="border-b border-slate-100 bg-slate-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">School term reports</h2>
            </header>
            <div class="grid gap-4 p-5 sm:grid-cols-3">
                @foreach($reports as $report)
                    @php $path = $meta[$report['key']] ?? null; @endphp
                    <div class="rounded-lg border border-slate-200 p-3 dark:border-zinc-700">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $report['label'] }}</p>
                        @if(filled($path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($path))
                            @php $url = \Illuminate\Support\Facades\Storage::disk('public')->url($path); @endphp
                            <a href="{{ $url }}" target="_blank" class="block overflow-hidden rounded-md ring-1 ring-slate-200 dark:ring-zinc-700">
                                <img src="{{ $url }}" alt="{{ $report['label'] }}" class="aspect-[3/4] w-full object-cover object-top">
                            </a>
                            <a href="{{ $url }}" target="_blank" class="mt-2 inline-block text-xs font-semibold text-orange-600 hover:underline">Open full size ↗</a>
                        @else
                            <p class="py-8 text-center text-sm text-slate-400">No photo uploaded</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Teachers --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <header class="border-b border-slate-100 bg-slate-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Teacher references</h2>
            </header>
            <div class="grid gap-6 p-5 md:grid-cols-2">
                <div class="space-y-3 rounded-lg bg-slate-50 p-4 dark:bg-zinc-800/40">
                    <p class="text-xs font-bold uppercase tracking-wide text-orange-600">Teacher 1</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-admin.field label="Name" :value="$val('teacher1_name')" />
                        <x-admin.field label="Phone" :value="$val('teacher1_phone')" />
                        <x-admin.field label="School" :value="$val('teacher1_school')" />
                        <x-admin.field label="Subject" :value="$val('teacher1_subject')" />
                    </div>
                </div>
                <div class="space-y-3 rounded-lg bg-slate-50 p-4 dark:bg-zinc-800/40">
                    <p class="text-xs font-bold uppercase tracking-wide text-orange-600">Teacher 2</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-admin.field label="Name" :value="$val('teacher2_name')" />
                        <x-admin.field label="Phone" :value="$val('teacher2_phone')" />
                        <x-admin.field label="School" :value="$val('teacher2_school')" />
                        <x-admin.field label="Subject" :value="$val('teacher2_subject')" />
                    </div>
                </div>
            </div>
        </section>

        {{-- Parents --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <header class="border-b border-slate-100 bg-slate-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Parent / guardian</h2>
            </header>
            <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                <x-admin.field label="Primary parent" :value="$val('parent_name')" />
                <x-admin.field label="Relationship" :value="$val('parent_relationship')" />
                <x-admin.field label="Occupation" :value="$val('parent_occupation')" />
                <x-admin.field label="Email" :value="$app->email ?? '—'" />
                <x-admin.field label="Phone" :value="$app->phone ?? '—'" />
                <x-admin.field label="How they heard about us" :value="$val('how_heard')" />
                <x-admin.field label="Second parent" :value="$val('parent2_name')" />
                <x-admin.field label="Second relationship" :value="$val('parent2_relationship')" />
                <x-admin.field label="Second phone" :value="$val('parent2_phone')" />
                <x-admin.field label="Address" :value="$val('address')" class="sm:col-span-2" />
                <x-admin.field label="Area / neighbourhood" :value="$val('area')" />
            </div>
        </section>

        {{-- Safety --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <header class="border-b border-slate-100 bg-slate-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Safety & consent</h2>
            </header>
            <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                <x-admin.field label="Emergency contact" :value="$val('emergency_name')" />
                <x-admin.field label="Emergency phone" :value="$val('emergency_phone')" />
                <x-admin.field label="Pickup person" :value="$val('pickup_person')" />
                <x-admin.field label="Photo consent" :value="$val('photo_consent')" />
            </div>
        </section>

        {{-- Payment history --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <header class="border-b border-slate-100 bg-slate-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">Payment</h2>
            </header>
            <div class="space-y-4 p-5">
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-admin.field label="Payment status" :value="ucfirst($app->payment_status ?? 'unpaid')" />
                    <x-admin.field label="Marked paid by" :value="$val('paid_offline_by')" />
                    <x-admin.field label="Marked paid at" :value="$val('paid_offline_at')" />
                </div>
                @if($app->payments->isNotEmpty())
                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-zinc-700">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-3 py-2 text-left">ID</th>
                                    <th class="px-3 py-2 text-left">Amount</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2 text-left">Tracking</th>
                                    <th class="px-3 py-2 text-left">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                @foreach($app->payments as $payment)
                                    <tr>
                                        <td class="px-3 py-2">#{{ $payment->id }}</td>
                                        <td class="px-3 py-2">{{ number_format((float) $payment->amount, 0) }} {{ $payment->currency ?? 'UGX' }}</td>
                                        <td class="px-3 py-2 capitalize">{{ $payment->status }}</td>
                                        <td class="px-3 py-2 font-mono text-xs">{{ $payment->order_tracking_id ?? '—' }}</td>
                                        <td class="px-3 py-2">{{ $payment->created_at?->format('j M Y g:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-slate-500">No online payment attempts recorded.</p>
                @endif
            </div>
        </section>
    </div>
</div>
