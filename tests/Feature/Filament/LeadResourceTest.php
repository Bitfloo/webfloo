<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\LeadResource;
use Webfloo\Filament\Resources\LeadResource\Pages\ListLeads;
use Webfloo\Models\Lead;
use Webfloo\Tests\TestCase;

final class LeadResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(LeadResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)->assertOk();
    }

    public function test_unauthorized_user_cannot_access_view_page(): void
    {
        $lead = Lead::factory()->create();

        $this->actingAs($this->makeAdmin())
            ->get(LeadResource::getUrl('view', ['record' => $lead]))
            ->assertForbidden();
    }

    public function test_resource_is_inaccessible_when_crm_feature_flag_is_off(): void
    {
        config(['webfloo.features.crm' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'lead')]);

        $this->assertFalse(LeadResource::canAccess());

        $this->actingAs($user)
            ->get(LeadResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_resource_hidden_from_navigation_by_design(): void
    {
        // Leads are reached through the CRM dashboard, not the sidebar.
        $this->assertFalse(LeadResource::shouldRegisterNavigation());
    }
}
