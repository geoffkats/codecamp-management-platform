<?php

namespace App\Mail;

use App\Models\RegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationRequestSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RegistrationRequest $registration)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Registration Request - ' . ucfirst($this->registration->type),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-request-submitted',
        );
    }
}
