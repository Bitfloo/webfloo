<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Webfloo\Models\Lead;
use Webfloo\Tests\TestCase;

class LeadWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-secret';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config()->set('webfloo.webhook_secret', self::SECRET);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function postWebhook(array $payload, string $secret = self::SECRET): TestResponse
    {
        return $this->postJson('/api/leads/webhook', $payload, ['X-Webhook-Secret' => $secret]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function patchWebhook(string $externalId, array $payload, string $secret = self::SECRET): TestResponse
    {
        return $this->patchJson("/api/leads/webhook/{$externalId}", $payload, ['X-Webhook-Secret' => $secret]);
    }

    /**
     * @return array<string, string>
     */
    protected function validPayload(): array
    {
        return ['name' => 'Jan Kowalski', 'email' => 'jan@example.com'];
    }

    public function test_store_returns_401_with_wrong_secret(): void
    {
        $this->postWebhook($this->validPayload(), 'wrong-secret')->assertUnauthorized();

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_store_returns_401_with_missing_secret_header(): void
    {
        $this->postJson('/api/leads/webhook', $this->validPayload())->assertUnauthorized();

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_store_rejects_all_requests_when_secret_config_is_null(): void
    {
        config()->set('webfloo.webhook_secret', null);

        // Fail-closed: even a matching empty header must never authenticate.
        $this->postJson('/api/leads/webhook', $this->validPayload())->assertUnauthorized();
        $this->postWebhook($this->validPayload(), '')->assertUnauthorized();

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_store_rejects_all_requests_when_secret_config_is_empty_string(): void
    {
        config()->set('webfloo.webhook_secret', '');

        $this->postWebhook($this->validPayload(), '')->assertUnauthorized();

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_store_returns_422_when_email_missing(): void
    {
        $this->postWebhook(['name' => 'Jan Kowalski'])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Validation failed')
            ->assertJsonStructure(['details' => ['email']]);
    }

    public function test_store_creates_lead_with_webhook_source_and_logs_activity(): void
    {
        $response = $this->postWebhook([
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'source_name' => 'landing-x',
            'metadata' => ['campaign' => 'spring'],
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        $this->assertDatabaseHas('leads', [
            'email' => 'jan@example.com',
            'source' => Lead::SOURCE_WEBHOOK,
            'status' => Lead::STATUS_NEW,
        ]);

        $lead = Lead::firstOrFail();
        $activity = $lead->activities()->where('type', 'webhook')->firstOrFail();
        $this->assertSame('landing-x', $activity->metadata['source_name'] ?? null);
        $this->assertSame('spring', $activity->metadata['campaign'] ?? null);
    }

    public function test_store_defaults_currency_from_config(): void
    {
        config()->set('webfloo.crm.currency', 'EUR');

        $this->postWebhook($this->validPayload())->assertCreated();

        $this->assertDatabaseHas('leads', [
            'email' => 'jan@example.com',
            'currency' => 'EUR',
        ]);
    }

    public function test_store_returns_409_for_duplicate_external_id(): void
    {
        $existing = Lead::factory()->create(['external_id' => 'ext-123']);

        $this->postWebhook([
            'name' => 'Jan Kowalski',
            'email' => 'other@example.com',
            'external_id' => 'ext-123',
        ])
            ->assertStatus(409)
            ->assertJsonPath('lead_id', $existing->id);

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_store_returns_409_for_same_email_within_24_hours(): void
    {
        Carbon::setTestNow('2026-06-12 12:00:00');

        $existing = Lead::factory()->create([
            'email' => 'jan@example.com',
            'source' => Lead::SOURCE_WEBHOOK,
            'created_at' => '2026-06-12 10:00:00',
        ]);

        $this->postWebhook($this->validPayload())
            ->assertStatus(409)
            ->assertJsonPath('lead_id', $existing->id);

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_store_accepts_same_email_after_24_hours(): void
    {
        Carbon::setTestNow('2026-06-12 12:00:00');

        Lead::factory()->create([
            'email' => 'jan@example.com',
            'source' => Lead::SOURCE_WEBHOOK,
            'created_at' => '2026-06-11 11:00:00',
        ]);

        $this->postWebhook($this->validPayload())->assertCreated();

        $this->assertDatabaseCount('leads', 2);
    }

    public function test_update_returns_401_with_wrong_secret(): void
    {
        Lead::factory()->create(['external_id' => 'ext-123', 'status' => Lead::STATUS_NEW]);

        $this->patchWebhook('ext-123', ['status' => 'contacted'], 'wrong-secret')
            ->assertUnauthorized();

        $this->assertDatabaseHas('leads', ['external_id' => 'ext-123', 'status' => Lead::STATUS_NEW]);
    }

    public function test_update_returns_404_for_unknown_external_id(): void
    {
        $this->patchWebhook('nope', ['status' => 'contacted'])->assertNotFound();
    }

    public function test_update_returns_422_for_invalid_status(): void
    {
        Lead::factory()->create(['external_id' => 'ext-123']);

        $this->patchWebhook('ext-123', ['status' => 'exploded'])
            ->assertStatus(422)
            ->assertJsonStructure(['details' => ['status']]);
    }

    public function test_update_transitions_status_adds_note_and_updates_estimated_value(): void
    {
        $lead = Lead::factory()->create([
            'external_id' => 'ext-123',
            'status' => Lead::STATUS_NEW,
        ]);

        $this->patchWebhook('ext-123', [
            'status' => 'contacted',
            'note' => 'Called back via integration',
            'estimated_value' => 2500,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $lead->refresh();
        $this->assertSame(Lead::STATUS_CONTACTED, $lead->status);
        $this->assertNotNull($lead->last_contacted_at);
        $this->assertSame(2500.0, (float) $lead->estimated_value);
        $this->assertTrue(
            $lead->activities()->where('description', 'Called back via integration')->exists()
        );
    }
}
