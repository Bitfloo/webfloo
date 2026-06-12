<?php

declare(strict_types=1);

namespace Webfloo\Listeners;

use Illuminate\Support\Facades\Mail;
use Throwable;
use Webfloo\Events\LeadCreated;
use Webfloo\Mail\NewLeadNotification;

/**
 * Emails the site admin about a new inbound lead.
 *
 * Synchronous on purpose: fresh Laravel apps default to the `database`
 * queue with no worker running, so a queued listener would silently
 * never deliver on turnkey client installs.
 */
class SendNewLeadNotification
{
    public function handle(LeadCreated $event): void
    {
        $recipient = setting('contact_email') ?: config('mail.from.address');

        if (! is_string($recipient) || $recipient === '') {
            return;
        }

        try {
            Mail::to($recipient)->send(new NewLeadNotification($event->lead));
        } catch (Throwable $e) {
            // Notification failure must never break lead creation.
            report($e);
        }
    }
}
