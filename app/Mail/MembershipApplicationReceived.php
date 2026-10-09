<?php

namespace App\Mail;

use App\Models\RegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RegistrationRequest $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Code Camp membership application for '.$this->application->full_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.membership-application-received',
            with: [
                'fee' => RegistrationRequest::membershipFee(),
                'payUrl' => $this->application->paymentUrl(),
            ],
        );
    }
}
