<?php

declare(strict_types=1);

namespace Webfloo\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Webfloo\Models\Lead;

class NewLeadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lead $lead,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nowy lead: '.$this->lead->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'webfloo::mail.new-lead-notification',
            with: [
                'lead' => $this->lead,
                'crmUrl' => url('/admin/crm'),
            ],
        );
    }
}
