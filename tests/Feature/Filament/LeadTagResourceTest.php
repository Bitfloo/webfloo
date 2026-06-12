<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\LeadTagResource;
use Webfloo\Filament\Resources\LeadTagResource\Pages\ListLeadTags;
use Webfloo\Tests\TestCase;

final class LeadTagResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(LeadTagResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(ListLeadTags::class)->assertOk();
    }

    public function test_resource_is_inaccessible_when_crm_feature_flag_is_off(): void
    {
        config(['webfloo.features.crm' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]);

        $this->assertFalse(LeadTagResource::canAccess());

        $this->actingAs($user)
            ->get(LeadTagResource::getUrl('index'))
            ->assertForbidden();
    }
}
