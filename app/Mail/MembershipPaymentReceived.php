<?php

namespace App\Mail;

use App\Models\RegistrationPayment;
use App\Models\RegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipPaymentReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RegistrationRequest $application, public RegistrationPayment $payment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment received: Code Camp membership for '.$this->application->full_name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.membership-payment-received');
    }
}
