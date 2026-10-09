<?php

namespace App\Services\Payments;

use App\Mail\MembershipPaymentReceived;
use App\Models\RegistrationPayment;
use App\Models\RegistrationRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MembershipPaymentService
{
    public function __construct(private PesapalClient $pesapal) {}

    /**
     * Create a payment attempt and return the Pesapal page the parent should be sent to.
     */
    public function start(RegistrationRequest $application): string
    {
        $amount = RegistrationRequest::membershipFee();
        $meta = $application->meta ?? [];
        [$firstName, $lastName] = array_pad(explode(' ', trim((string) ($meta['parent_name'] ?? $application->full_name)), 2), 2, '');

        $payment = $application->payments()->create([
            'gateway' => 'pesapal',
            'merchant_reference' => 'MEM-'.$application->id.'-'.Str::upper(Str::random(8)),
            'amount' => $amount,
            'currency' => config('membership.currency', 'UGX'),
            'status' => 'pending',
        ]);

        try {
            $order = $this->submitOrder($application, $payment, $firstName, $lastName);
        } catch (\Throwable $e) {
            $payment->delete();

            throw $e;
        }

        $payment->update([
            'order_tracking_id' => $order['order_tracking_id'],
            'gateway_response' => $order,
        ]);

        return $order['redirect_url'];
    }

    /**
     * @return array{order_tracking_id: string, merchant_reference: string, redirect_url: string}
     */
    private function submitOrder(RegistrationRequest $application, RegistrationPayment $payment, string $firstName, string $lastName): array
    {
        return $this->pesapal->submitOrder([
            'id' => $payment->merchant_reference,
            'currency' => $payment->currency,
            'amount' => $payment->amount,
            'description' => Str::limit('Code Camp membership application - '.$application->full_name, 100, ''),
            'callback_url' => route('registration.payments.pesapal.callback'),
            'billing_address' => [
                'email_address' => $application->email,
                'phone_number' => $application->phone,
                'country_code' => 'UG',
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
        ]);
    }

    /**
     * Ask Pesapal for the latest status and record it. Safe to call repeatedly
     * (callback, IPN and page refreshes can all arrive for the same payment).
     */
    public function refresh(RegistrationPayment $payment): RegistrationPayment
    {
        if (! $payment->order_tracking_id || $payment->isCompleted()) {
            return $payment;
        }

        $status = $this->pesapal->transactionStatus($payment->order_tracking_id);
        $newStatus = match (strtolower((string) ($status['payment_status_description'] ?? ''))) {
            'completed' => 'completed',
            'failed' => 'failed',
            'reversed' => 'reversed',
            default => 'pending',
        };

        $justPaid = DB::transaction(function () use ($payment, $status, $newStatus) {
            $locked = RegistrationPayment::whereKey($payment->id)->lockForUpdate()->first();
            $wasCompleted = $locked->isCompleted();

            $locked->update([
                'status' => $wasCompleted ? 'completed' : $newStatus,
                'payment_method' => $status['payment_method'] ?? $locked->payment_method,
                'confirmation_code' => $status['confirmation_code'] ?? $locked->confirmation_code,
                'paid_at' => ! $wasCompleted && $newStatus === 'completed' ? now() : $locked->paid_at,
                'gateway_response' => $status,
            ]);

            if (! $wasCompleted && $newStatus === 'completed') {
                $locked->registration->update(['payment_status' => 'paid']);

                return true;
            }

            return false;
        });

        $payment->refresh();

        if ($justPaid) {
            $this->announcePaid($payment);
        }

        return $payment;
    }

    private function announcePaid(RegistrationPayment $payment): void
    {
        $application = $payment->registration;

        try {
            Mail::to($application->email)->send(new MembershipPaymentReceived($application, $payment));
        } catch (\Throwable $e) {
            Log::error('Failed to send membership payment receipt', ['registration_id' => $application->id, 'error' => $e->getMessage()]);
        }

        $notifications = app(NotificationService::class);
        User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'operations_manager']))
            ->get()
            ->each(fn (User $user) => $notifications->notify(
                $user,
                'Membership fee paid',
                $application->full_name.'\'s Code Camp membership fee (UGX '.number_format($payment->amount).') was paid.',
                'info',
                ['action_url' => route('admin.registration-requests')]
            ));
    }
}
