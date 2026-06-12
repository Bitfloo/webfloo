<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Webfloo\Tests\TestCase;

class LeadWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('webfloo.webhook_secret', 'test-secret');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function postWebhook(array $payload, string $secret = 'test-secret'): TestResponse
    {
        return $this->postJson('/api/leads/webhook', $payload, ['X-Webhook-Secret' => $secret]);
    }

    public function test_store_defaults_currency_from_config(): void
    {
        config()->set('webfloo.crm.currency', 'EUR');

        $this->postWebhook([
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
        ])->assertCreated();

        $this->assertDatabaseHas('leads', [
            'email' => 'jan@example.com',
            'currency' => 'EUR',
        ]);
    }
}
