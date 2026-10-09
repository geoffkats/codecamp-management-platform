@php
    $meta = $application->meta ?? [];
    $paid = $application->isPaid();
    $failed = ! $paid && $payment && in_array($payment->status, ['failed', 'reversed'], true);
    $awaiting = ! $paid && $payment && $payment->status === 'pending' && $payment->order_tracking_id;
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $paid ? 'Payment received' : 'Pay application fee' }} - Code Academy Uganda</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @include('partials.analytics.head')
    </head>
    <body class="bg-orange-50 dark:bg-blue-950">
        @include('partials.analytics.body')
        @include('partials.analytics.conversion')
        <div class="min-h-screen px-4 py-12 sm:px-6 lg:px-8">
            <div class="max-w-xl mx-auto space-y-6">
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Code Camp membership</p>
                    <h1 class="text-3xl md:text-4xl font-bold text-blue-900 dark:text-white mt-3">
                        {{ $paid ? 'Payment received' : 'Pay the application fee' }}
                    </h1>
                </div>

                @if(session('message'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('message') }}</div>
                @endif
                @if(session('error'))
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
                @endif

                <div class="bg-white dark:bg-blue-900 rounded-2xl shadow-xl border border-orange-200 dark:border-blue-700 overflow-hidden">
                    <div class="p-6 sm:p-8 text-center border-b border-orange-100 dark:border-blue-800">
                        @if($paid)
                            <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-100 text-3xl text-emerald-600">✓</div>
                            <p class="mt-4 text-gray-700 dark:text-gray-200">
                                Thank you! We received <strong>UGX {{ number_format($payment?->amount ?? $fee) }}</strong> for <strong>{{ $application->full_name }}</strong>.
                                A receipt was sent to {{ $application->email }}. Our team will contact you with camp details.
                            </p>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">Application fee for {{ $application->full_name }}</p>
                            <p class="mt-1 text-4xl font-extrabold text-blue-900 dark:text-white">UGX {{ number_format($fee) }}</p>

                            @if($awaiting)
                                <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                    We haven't received confirmation of your last payment yet. If you already approved it on your phone, refresh this page in a minute.
                                </p>
                            @elseif($failed)
                                <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">Your last payment did not go through. You can try again below.</p>
                            @endif

                            @if($canPayOnline)
                                <form method="POST" action="{{ route('registration.membership.checkout', $application->public_token) }}" class="mt-6">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3.5 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold text-lg transition-all">
                                        {{ $payment ? 'Try payment again' : 'Pay now' }}
                                    </button>
                                </form>
                                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">You'll be taken to Pesapal to pay with MTN or Airtel Mobile Money, or a Visa/Mastercard card.</p>
                                @if($awaiting)
                                    <a href="{{ request()->url() }}" class="mt-3 inline-block text-sm font-semibold text-orange-600 hover:underline">Refresh payment status</a>
                                @endif
                            @else
                                <p class="mt-6 rounded-lg bg-gray-50 px-3 py-3 text-sm text-gray-700 dark:bg-blue-950 dark:text-gray-200">
                                    Online payment isn't available right now. Our team will contact you on {{ $application->phone }} with payment details.
                                </p>
                            @endif
                        @endif
                    </div>

                    <dl class="divide-y divide-orange-50 dark:divide-blue-800 text-sm">
                        @foreach([
                            'Child' => $application->full_name,
                            'School & class' => trim($application->organization_name.' · '.$application->school_level, ' ·'),
                            'Camp' => $application->preferred_schedule ?: 'Next available camp',
                            'Parent / guardian' => $meta['parent_name'] ?? null,
                            'Contact' => $application->phone.' · '.$application->email,
                            'Reference' => '#'.$application->id,
                        ] as $label => $value)
                            @if(filled($value))
                                <div class="flex justify-between gap-4 px-6 py-3">
                                    <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                                    <dd class="text-right font-medium text-gray-900 dark:text-white">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>

                <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                    Keep this page's link (also in your email) to come back and pay later.
                </p>
            </div>
        </div>
    </body>
</html>
