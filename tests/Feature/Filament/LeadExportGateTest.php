<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Pages\CrmDashboard;
use Webfloo\Filament\Resources\LeadResource\Pages\ListLeads;
use Webfloo\Tests\TestCase;

/**
 * Lead exports expose PII (names, e-mails, phones) — the export surfaces
 * must stay hidden from users without the dedicated Export:Lead permission.
 */
class LeadExportGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_action_hidden_without_export_lead_permission(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead')]));

        Livewire::test(ListLeads::class)
            ->assertActionHidden(TestAction::make('export')->table());
    }

    public function test_export_action_visible_with_export_lead_permission(): void
    {
        $this->actingAs($this->makeAdmin([
            webfloo_permission('view_any', 'lead'),
            webfloo_permission('export', 'lead'),
        ]));

        Livewire::test(ListLeads::class)
            ->assertActionVisible(TestAction::make('export')->table());
    }

    public function test_dashboard_export_button_hidden_without_export_lead_permission(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'crm_dashboard')]));

        Livewire::test(CrmDashboard::class)
            ->assertActionHidden('exportLeads');
    }

    public function test_dashboard_export_button_visible_with_export_lead_permission(): void
    {
        $this->actingAs($this->makeAdmin([
            webfloo_permission('view', 'crm_dashboard'),
            webfloo_permission('export', 'lead'),
        ]));

        Livewire::test(CrmDashboard::class)
            ->assertActionVisible('exportLeads');
    }
}
