<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Events;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Webfloo\Events\LeadCreated;
use Webfloo\Mail\NewLeadNotification;
use Webfloo\Models\Lead;
use Webfloo\Models\Setting;
use Webfloo\Tests\TestCase;

class LeadCreatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_store_dispatches_lead_created_event(): void
    {
        Event::fake([LeadCreated::class]);
        config()->set('webfloo.webhook_secret', 'test-secret');

        $response = $this->postJson('/api/leads/webhook', [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
        ], ['X-Webhook-Secret' => 'test-secret']);

        $response->assertCreated();

        $lead = Lead::firstOrFail();
        Event::assertDispatched(
            LeadCreated::class,
            fn (LeadCreated $event): bool => $event->lead->is($lead)
        );
    }

    public function test_listener_sends_notification_to_contact_email_setting(): void
    {
        Mail::fake();
        Setting::set('contact_email', 'admin@example.com');

        $lead = Lead::factory()->create();
        event(new LeadCreated($lead));

        Mail::assertSent(
            NewLeadNotification::class,
            fn (NewLeadNotification $mail): bool => $mail->hasTo('admin@example.com')
                && $mail->lead->is($lead)
        );
    }

    public function test_listener_falls_back_to_mail_from_address_when_setting_missing(): void
    {
        Mail::fake();
        config()->set('mail.from.address', 'fallback@example.com');

        $lead = Lead::factory()->create();
        event(new LeadCreated($lead));

        Mail::assertSent(
            NewLeadNotification::class,
            fn (NewLeadNotification $mail): bool => $mail->hasTo('fallback@example.com')
        );
    }

    public function test_listener_sends_nothing_when_no_recipient_available(): void
    {
        Mail::fake();
        config()->set('mail.from.address', null);

        $lead = Lead::factory()->create();
        event(new LeadCreated($lead));

        Mail::assertNothingSent();
    }
}
