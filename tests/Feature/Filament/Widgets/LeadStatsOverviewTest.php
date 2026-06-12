<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament\Widgets;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Widgets\LeadStatsOverview;
use Webfloo\Models\Lead;
use Webfloo\Tests\TestCase;

class LeadStatsOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_stat_uses_configured_currency(): void
    {
        config()->set('webfloo.crm.currency', 'EUR');

        Lead::factory()->create([
            'status' => Lead::STATUS_NEW,
            'estimated_value' => 1000,
        ]);

        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'crm_dashboard')]));

        Livewire::test(LeadStatsOverview::class)->assertSee('EUR');
    }
}
