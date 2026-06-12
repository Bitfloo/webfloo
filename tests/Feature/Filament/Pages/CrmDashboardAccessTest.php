<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Pages\CrmDashboard;
use Webfloo\Tests\TestCase;

final class CrmDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_access_dashboard(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(CrmDashboard::getUrl())
            ->assertForbidden();
    }

    public function test_user_with_view_crm_dashboard_permission_can_access(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'crm_dashboard')]));

        $this->assertTrue(CrmDashboard::canAccess());

        Livewire::test(CrmDashboard::class)->assertOk();
    }

    public function test_dashboard_inaccessible_when_crm_feature_flag_is_off(): void
    {
        config(['webfloo.features.crm' => false]);

        $user = $this->makeAdmin([webfloo_permission('view', 'crm_dashboard')]);

        $this->assertFalse(CrmDashboard::canAccess());

        $this->actingAs($user)
            ->get(CrmDashboard::getUrl())
            ->assertForbidden();
    }
}
