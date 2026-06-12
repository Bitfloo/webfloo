<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Events;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Webfloo\Events\LeadCreated;
use Webfloo\Tests\TestCase;

class LeadCreatedCrmDisabledTest extends TestCase
{
    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('webfloo.features.crm', false);
    }

    public function test_listener_not_registered_when_crm_disabled(): void
    {
        $this->assertFalse(Event::hasListeners(LeadCreated::class));
    }
}
