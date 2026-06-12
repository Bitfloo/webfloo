<?php

declare(strict_types=1);

namespace Webfloo\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Webfloo\Models\Lead;

/**
 * Fired when a new inbound Lead is created from a public source
 * (webhook, contact form). Deliberately NOT fired for leads entered
 * manually in the admin panel — those are editorial actions, not
 * new inquiries.
 */
class LeadCreated
{
    use Dispatchable;

    public function __construct(
        public Lead $lead,
    ) {}
}
