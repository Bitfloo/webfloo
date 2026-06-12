<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Api;

use Illuminate\Foundation\Application;
use Webfloo\Tests\TestCase;

class LeadWebhookCrmDisabledTest extends TestCase
{
    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('webfloo.features.crm', false);
        $app['config']->set('webfloo.webhook_secret', 'test-secret');
    }

    public function test_webhook_routes_not_registered_when_crm_disabled(): void
    {
        $payload = ['name' => 'Jan', 'email' => 'jan@example.com'];
        $headers = ['X-Webhook-Secret' => 'test-secret'];

        $this->postJson('/api/leads/webhook', $payload, $headers)->assertNotFound();
        $this->patchJson('/api/leads/webhook/ext-1', ['status' => 'contacted'], $headers)->assertNotFound();
    }
}
