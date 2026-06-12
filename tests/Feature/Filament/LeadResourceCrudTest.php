<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\LeadResource\Pages\CreateLead;
use Webfloo\Filament\Resources\LeadResource\Pages\EditLead;
use Webfloo\Filament\Resources\LeadResource\Pages\ListLeads;
use Webfloo\Models\Lead;
use Webfloo\Models\LeadTag;
use Webfloo\Tests\TestCase;

final class LeadResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Table / index
    // ---------------------------------------------------------------------------

    public function test_index_renders_lead_records_for_authorized_user(): void
    {
        $leads = Lead::factory()->count(3)->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->assertCanSeeTableRecords($leads);
    }

    // ---------------------------------------------------------------------------
    // Create — valid data
    // ---------------------------------------------------------------------------

    public function test_create_persists_lead_with_required_fields(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(CreateLead::class)
            ->fillForm([
                'name' => 'Jan Kowalski',
                'email' => 'jan@example.com',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('leads', [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
        ]);
    }

    public function test_create_defaults_status_to_new(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(CreateLead::class)
            ->fillForm([
                'name' => 'Anna Nowak',
                'email' => 'anna@example.com',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $lead = Lead::latest('id')->first();
        $this->assertSame(Lead::STATUS_NEW, $lead->status);
    }

    public function test_create_defaults_source_to_contact_form(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(CreateLead::class)
            ->fillForm([
                'name' => 'Piotr Wiśniewski',
                'email' => 'piotr@example.com',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $lead = Lead::latest('id')->first();
        $this->assertSame(Lead::SOURCE_CONTACT_FORM, $lead->source);
    }

    public function test_create_defaults_currency_to_webfloo_currency(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(CreateLead::class)
            ->fillForm([
                'name' => 'Maria Zielinska',
                'email' => 'maria@example.com',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $lead = Lead::latest('id')->first();
        $this->assertSame(webfloo_currency(), $lead->currency);
    }

    // ---------------------------------------------------------------------------
    // Create — validation
    // ---------------------------------------------------------------------------

    public function test_create_rejects_missing_name(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(CreateLead::class)
            ->fillForm([
                'name' => '',
                'email' => 'valid@example.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_create_rejects_missing_email(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(CreateLead::class)
            ->fillForm([
                'name' => 'Tomek',
                'email' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'required']);
    }

    public function test_create_rejects_invalid_email_format(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(CreateLead::class)
            ->fillForm([
                'name' => 'Tomek',
                'email' => 'not-an-email',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'email']);
    }

    // ---------------------------------------------------------------------------
    // Edit — prefill
    // ---------------------------------------------------------------------------

    public function test_edit_form_prefills_existing_lead_fields(): void
    {
        $lead = Lead::factory()->create([
            'name' => 'Zofia Kaminska',
            'email' => 'zofia@example.com',
            'status' => Lead::STATUS_CONTACTED,
            'estimated_value' => '2500.00',
        ]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
            ->assertFormSet([
                'name' => 'Zofia Kaminska',
                'email' => 'zofia@example.com',
                'status' => Lead::STATUS_CONTACTED,
                'estimated_value' => '2500.00',
            ]);
    }

    // ---------------------------------------------------------------------------
    // Edit — save
    // ---------------------------------------------------------------------------

    public function test_edit_saves_status_change(): void
    {
        // Deterministic phone — faker numbers can fail the tel() field regex on save.
        $lead = Lead::factory()->create(['status' => Lead::STATUS_NEW, 'phone' => '123456789']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
            ->fillForm(['status' => Lead::STATUS_QUALIFIED])
            ->call('save')
            ->assertHasNoFormErrors();

        // Direct DB update via the form bypasses transitionTo(); refresh and check.
        $this->assertSame(Lead::STATUS_QUALIFIED, $lead->refresh()->status);
    }

    public function test_edit_saves_estimated_value(): void
    {
        $lead = Lead::factory()->create(['estimated_value' => null, 'phone' => '123456789']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
            ->fillForm(['estimated_value' => 9999.99])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('9999.99', $lead->refresh()->estimated_value);
    }

    public function test_edit_saves_assigned_to_user(): void
    {
        // Use a clean phone to avoid tel() validation rejecting faker's formatted numbers.
        $lead = Lead::factory()->create(['assigned_to' => null, 'phone' => '123456789']);
        $assignee = $this->makeAdmin([webfloo_permission('view_any', 'lead')]);
        $this->actingAs($assignee);

        Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
            ->fillForm(['assigned_to' => $assignee->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($assignee->id, $lead->refresh()->assigned_to);
    }

    // ---------------------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------------------

    public function test_authorized_user_can_delete_lead(): void
    {
        $lead = Lead::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableAction('delete', $lead)
            ->assertOk();

        // Lead model has no SoftDeletes — hard delete expected.
        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    public function test_bulk_delete_removes_selected_leads(): void
    {
        $leads = Lead::factory()->count(3)->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->callTableBulkAction('delete', $leads)
            ->assertOk();

        foreach ($leads as $lead) {
            $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
        }
    }

    // ---------------------------------------------------------------------------
    // Table filters
    // ---------------------------------------------------------------------------

    public function test_status_filter_shows_only_leads_with_matching_status(): void
    {
        $newLead = Lead::factory()->create(['status' => Lead::STATUS_NEW]);
        $lostLead = Lead::factory()->lost()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->filterTable('status', [Lead::STATUS_NEW])
            ->assertCanSeeTableRecords([$newLead])
            ->assertCanNotSeeTableRecords([$lostLead]);
    }

    public function test_source_filter_shows_only_leads_from_matching_source(): void
    {
        $formLead = Lead::factory()->create(['source' => Lead::SOURCE_CONTACT_FORM]);
        $manualLead = Lead::factory()->create(['source' => Lead::SOURCE_MANUAL]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->filterTable('source', [Lead::SOURCE_MANUAL])
            ->assertCanSeeTableRecords([$manualLead])
            ->assertCanNotSeeTableRecords([$formLead]);
    }

    public function test_unassigned_ternary_filter_shows_only_unassigned_leads(): void
    {
        $unassigned = Lead::factory()->create(['assigned_to' => null]);
        $assigned = Lead::factory()->create(['assigned_to' => $this->makeAdmin()->id]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->filterTable('unassigned', true)
            ->assertCanSeeTableRecords([$unassigned])
            ->assertCanNotSeeTableRecords([$assigned]);
    }

    // ---------------------------------------------------------------------------
    // Tags relationship — create / edit syncs pivot
    // ---------------------------------------------------------------------------

    public function test_create_with_tags_syncs_pivot(): void
    {
        $tag = LeadTag::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(CreateLead::class)
            ->fillForm([
                'name' => 'Lead z tagiem',
                'email' => 'tagged@example.com',
                'tags' => [$tag->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $lead = Lead::latest('id')->first();
        $this->assertTrue($lead->tags->contains($tag));
    }

    public function test_edit_with_tags_syncs_pivot(): void
    {
        $lead = Lead::factory()->create(['phone' => '123456789']);
        $tag = LeadTag::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
            ->fillForm(['tags' => [$tag->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($lead->refresh()->tags->contains($tag));
    }

    // ---------------------------------------------------------------------------
    // Scopes — unit coverage
    // ---------------------------------------------------------------------------

    public function test_scope_new_returns_only_new_status_leads(): void
    {
        Lead::factory()->create(['status' => Lead::STATUS_NEW]);
        Lead::factory()->contacted()->create();
        Lead::factory()->lost()->create();

        $this->assertCount(1, Lead::new()->get());
        $this->assertSame(Lead::STATUS_NEW, Lead::new()->first()->status);
    }

    public function test_scope_converted_returns_only_converted_leads(): void
    {
        Lead::factory()->converted()->count(2)->create();
        Lead::factory()->create(['status' => Lead::STATUS_NEW]);

        $this->assertCount(2, Lead::converted()->get());
    }

    public function test_scope_in_pipeline_excludes_terminal_statuses(): void
    {
        Lead::factory()->create(['status' => Lead::STATUS_NEW]);
        Lead::factory()->contacted()->create();
        Lead::factory()->qualified()->create();
        Lead::factory()->converted()->create();
        Lead::factory()->lost()->create();

        $pipeline = Lead::inPipeline()->get();

        $this->assertCount(3, $pipeline);
        foreach ($pipeline as $lead) {
            $this->assertContains($lead->status, Lead::PIPELINE_STATUSES);
        }
    }

    public function test_scope_unassigned_excludes_assigned_leads(): void
    {
        $owner = $this->makeAdmin();
        Lead::factory()->create(['assigned_to' => $owner->id]);
        Lead::factory()->create(['assigned_to' => null]);

        $this->assertCount(1, Lead::unassigned()->get());
        $this->assertNull(Lead::unassigned()->first()->assigned_to);
    }
}
