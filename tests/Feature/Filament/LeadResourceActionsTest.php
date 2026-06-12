<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Webfloo\Filament\Resources\LeadResource\Pages\ListLeads;
use Webfloo\Mail\LeadEmail;
use Webfloo\Models\Lead;
use Webfloo\Models\LeadActivity;
use Webfloo\Tests\TestCase;

final class LeadResourceActionsTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // addNote action
    // ---------------------------------------------------------------------------

    public function test_add_note_action_creates_note_activity(): void
    {
        $lead = Lead::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('addNote', $lead, data: ['note' => 'Ważna uwaga'])
            ->assertOk();

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => LeadActivity::TYPE_NOTE,
            'description' => 'Ważna uwaga',
        ]);
    }

    public function test_add_note_action_requires_note_text(): void
    {
        $lead = Lead::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('addNote', $lead, data: ['note' => ''])
            ->assertHasTableActionErrors(['note' => 'required']);
    }

    // ---------------------------------------------------------------------------
    // logCall action
    // ---------------------------------------------------------------------------

    public function test_log_call_action_creates_call_activity(): void
    {
        Carbon::setTestNow('2026-06-12 10:00:00');

        $lead = Lead::factory()->create(['last_contacted_at' => null]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('logCall', $lead, data: ['notes' => 'Omówiono warunki'])
            ->assertOk();

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => LeadActivity::TYPE_CALL,
        ]);

        // logCall updates last_contacted_at on the lead
        $this->assertNotNull($lead->refresh()->last_contacted_at);

        Carbon::setTestNow();
    }

    // ---------------------------------------------------------------------------
    // sendEmail action
    // ---------------------------------------------------------------------------

    public function test_send_email_action_dispatches_lead_email_mailable(): void
    {
        Mail::fake();

        $lead = Lead::factory()->create(['email' => 'klient@example.com']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('sendEmail', $lead, data: [
                'subject' => 'Oferta specjalna',
                'body' => 'Przesyłam ofertę.',
            ])
            ->assertOk();

        Mail::assertSent(LeadEmail::class, function (LeadEmail $mail) use ($lead): bool {
            return $mail->lead->is($lead)
                && $mail->emailSubject === 'Oferta specjalna'
                && $mail->emailBody === 'Przesyłam ofertę.';
        });
    }

    public function test_send_email_action_logs_email_activity(): void
    {
        Mail::fake();

        $lead = Lead::factory()->create(['email' => 'klient@example.com']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('sendEmail', $lead, data: [
                'subject' => 'Temat wiadomości',
                'body' => 'Treść.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => LeadActivity::TYPE_EMAIL_SENT,
            'description' => 'Temat wiadomości',
        ]);
    }

    public function test_send_email_action_requires_subject_and_body(): void
    {
        Mail::fake();

        $lead = Lead::factory()->create(['email' => 'klient@example.com']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('sendEmail', $lead, data: [
                'subject' => '',
                'body' => '',
            ])
            ->assertHasTableActionErrors(['subject' => 'required', 'body' => 'required']);
    }

    // ---------------------------------------------------------------------------
    // markContacted action (status transition)
    // ---------------------------------------------------------------------------

    public function test_mark_contacted_action_transitions_new_lead_to_contacted(): void
    {
        Carbon::setTestNow('2026-06-12 12:00:00');

        $lead = Lead::factory()->create(['status' => Lead::STATUS_NEW]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('markContacted', $lead)
            ->assertOk();

        $this->assertSame(Lead::STATUS_CONTACTED, $lead->refresh()->status);
        $this->assertNotNull($lead->last_contacted_at);

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => LeadActivity::TYPE_STATUS_CHANGE,
        ]);

        Carbon::setTestNow();
    }

    // ---------------------------------------------------------------------------
    // markLost action (status transition)
    // ---------------------------------------------------------------------------

    public function test_mark_lost_action_transitions_pipeline_lead_to_lost(): void
    {
        $lead = Lead::factory()->qualified()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('markLost', $lead)
            ->assertOk();

        $this->assertSame(Lead::STATUS_LOST, $lead->refresh()->status);
        $this->assertTrue($lead->isTerminal());

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => LeadActivity::TYPE_STATUS_CHANGE,
        ]);
    }

    // ---------------------------------------------------------------------------
    // convert action (status transition)
    // ---------------------------------------------------------------------------

    public function test_convert_action_transitions_pipeline_lead_to_converted(): void
    {
        Carbon::setTestNow('2026-06-12 14:00:00');

        $lead = Lead::factory()->qualified()->create(['converted_at' => null]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('convert', $lead)
            ->assertOk();

        $lead->refresh();
        $this->assertSame(Lead::STATUS_CONVERTED, $lead->status);
        $this->assertNotNull($lead->converted_at);

        Carbon::setTestNow();
    }

    // ---------------------------------------------------------------------------
    // scheduleReminder action
    // ---------------------------------------------------------------------------

    public function test_schedule_reminder_action_requires_title_and_due_at(): void
    {
        Carbon::setTestNow('2026-06-12 09:00:00');

        $lead = Lead::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('scheduleReminder', $lead, data: [
                'title' => '',
                'due_at' => null,
                'priority' => 'normal',
            ])
            ->assertHasTableActionErrors(['title' => 'required', 'due_at' => 'required']);

        Carbon::setTestNow();
    }

    // ---------------------------------------------------------------------------
    // Model method coverage — transitionTo side effects
    // ---------------------------------------------------------------------------

    public function test_transition_to_converted_sets_converted_at_timestamp(): void
    {
        Carbon::setTestNow('2026-06-12 15:00:00');

        $lead = Lead::factory()->qualified()->create(['converted_at' => null]);
        $user = $this->makeAdmin();
        $this->actingAs($user);

        $lead->transitionTo(Lead::STATUS_CONVERTED);

        $lead->refresh();
        $this->assertSame(Lead::STATUS_CONVERTED, $lead->status);
        $this->assertSame('2026-06-12 15:00:00', $lead->converted_at->toDateTimeString());

        Carbon::setTestNow();
    }

    public function test_transition_to_same_status_is_a_no_op(): void
    {
        $lead = Lead::factory()->create(['status' => Lead::STATUS_NEW]);
        $this->actingAs($this->makeAdmin());

        $lead->transitionTo(Lead::STATUS_NEW);

        $this->assertCount(0, $lead->activities);
    }

    public function test_is_in_pipeline_returns_false_for_terminal_statuses(): void
    {
        $converted = Lead::factory()->converted()->make();
        $lost = Lead::factory()->lost()->make();

        $this->assertFalse($converted->isInPipeline());
        $this->assertFalse($lost->isInPipeline());
    }

    public function test_is_in_pipeline_returns_true_for_pipeline_statuses(): void
    {
        foreach (Lead::PIPELINE_STATUSES as $status) {
            $lead = Lead::factory()->make(['status' => $status]);
            $this->assertTrue($lead->isInPipeline(), "Expected {$status} to be in pipeline");
        }
    }
}
