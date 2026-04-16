<?php

declare(strict_types=1);

namespace Webfloo\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Webfloo\Models\Lead;

class LeadEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lead $lead,
        public string $emailSubject,
        public string $emailBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
            replyTo: [
                new Address(
                    is_string($addr = config('mail.from.address')) ? $addr : 'noreply@bitfloo.com',
                    is_string($name = config('mail.from.name')) ? $name : 'Bitfloo'
                ),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'webfloo::mail.lead-email',
            with: [
                'lead' => $this->lead,
                'body' => $this->emailBody,
            ],
        );
    }
}
