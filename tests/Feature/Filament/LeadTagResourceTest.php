<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\LeadTagResource;
use Webfloo\Filament\Resources\LeadTagResource\Pages\CreateLeadTag;
use Webfloo\Filament\Resources\LeadTagResource\Pages\EditLeadTag;
use Webfloo\Filament\Resources\LeadTagResource\Pages\ListLeadTags;
use Webfloo\Models\LeadTag;
use Webfloo\Tests\TestCase;

final class LeadTagResourceTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------------------

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

    public function test_authorized_user_can_access_create_page(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(CreateLeadTag::class)->assertOk();
    }

    public function test_authorized_user_can_access_edit_page(): void
    {
        $tag = LeadTag::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(EditLeadTag::class, ['record' => $tag->getRouteKey()])->assertOk();
    }

    // ---------------------------------------------------------------------------
    // Feature flag
    // ---------------------------------------------------------------------------

    public function test_resource_is_inaccessible_when_crm_feature_flag_is_off(): void
    {
        config(['webfloo.features.crm' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]);

        $this->assertFalse(LeadTagResource::canAccess());

        $this->actingAs($user)
            ->get(LeadTagResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_resource_is_accessible_when_crm_feature_flag_is_on(): void
    {
        config(['webfloo.features.crm' => true]);

        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        $this->assertTrue(LeadTagResource::canAccess());
    }

    // ---------------------------------------------------------------------------
    // Table / index rendering
    // ---------------------------------------------------------------------------

    public function test_index_renders_lead_tag_records(): void
    {
        $tags = LeadTag::factory()->count(3)->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(ListLeadTags::class)
            ->assertCanSeeTableRecords($tags);
    }

    // ---------------------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------------------

    public function test_create_persists_valid_lead_tag(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(CreateLeadTag::class)
            ->fillForm([
                'name' => 'VIP',
                'color' => 'success',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $tag = LeadTag::latest('id')->first();
        $this->assertSame('VIP', $tag->name);
        $this->assertSame('success', $tag->color);
    }

    public function test_create_requires_name(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(CreateLeadTag::class)
            ->fillForm(['name' => '', 'color' => 'gray'])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_create_requires_color(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(CreateLeadTag::class)
            ->fillForm(['name' => 'Tag', 'color' => null])
            ->call('create')
            ->assertHasFormErrors(['color' => 'required']);
    }

    public function test_create_rejects_duplicate_name(): void
    {
        LeadTag::factory()->create(['name' => 'VIP']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(CreateLeadTag::class)
            ->fillForm(['name' => 'VIP', 'color' => 'gray'])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_create_defaults_color_to_gray(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(CreateLeadTag::class)
            ->fillForm(['name' => 'NewTag'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('gray', LeadTag::latest('id')->first()->color);
    }

    // ---------------------------------------------------------------------------
    // Edit — prefill + save
    // ---------------------------------------------------------------------------

    public function test_edit_form_prefills_existing_tag_data(): void
    {
        $tag = LeadTag::factory()->create(['name' => 'Prospect', 'color' => 'primary']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(EditLeadTag::class, ['record' => $tag->getRouteKey()])
            ->assertFormSet([
                'name' => 'Prospect',
                'color' => 'primary',
            ]);
    }

    public function test_edit_saves_updated_name_and_color(): void
    {
        $tag = LeadTag::factory()->create(['name' => 'Old', 'color' => 'gray']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(EditLeadTag::class, ['record' => $tag->getRouteKey()])
            ->fillForm(['name' => 'New', 'color' => 'danger'])
            ->call('save')
            ->assertHasNoFormErrors();

        $tag->refresh();
        $this->assertSame('New', $tag->name);
        $this->assertSame('danger', $tag->color);
    }

    public function test_edit_allows_same_name_on_own_record(): void
    {
        // unique(ignoreRecord: true) must not fire when name is unchanged
        $tag = LeadTag::factory()->create(['name' => 'MyTag', 'color' => 'gray']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(EditLeadTag::class, ['record' => $tag->getRouteKey()])
            ->fillForm(['name' => 'MyTag', 'color' => 'success'])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    // ---------------------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------------------

    public function test_authorized_user_can_delete_lead_tag(): void
    {
        $tag = LeadTag::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(ListLeadTags::class)
            ->callTableAction('delete', $tag)
            ->assertOk();

        $this->assertDatabaseMissing('lead_tags', ['id' => $tag->id]);
    }

    public function test_bulk_delete_removes_selected_lead_tags(): void
    {
        $tags = LeadTag::factory()->count(3)->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'lead_tag')]));

        Livewire::test(ListLeadTags::class)
            ->callTableBulkAction('delete', $tags)
            ->assertOk();

        foreach ($tags as $tag) {
            $this->assertDatabaseMissing('lead_tags', ['id' => $tag->id]);
        }
    }

    // ---------------------------------------------------------------------------
    // getColorOptions — model static method coverage
    // ---------------------------------------------------------------------------

    public function test_get_color_options_returns_all_expected_keys(): void
    {
        $options = LeadTag::getColorOptions();

        foreach (['gray', 'primary', 'success', 'warning', 'danger', 'info'] as $key) {
            $this->assertArrayHasKey($key, $options);
        }
    }
}
