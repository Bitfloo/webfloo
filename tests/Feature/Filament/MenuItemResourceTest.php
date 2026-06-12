<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Webfloo\Filament\Resources\MenuItemResource;
use Webfloo\Filament\Resources\MenuItemResource\Pages\CreateMenuItem;
use Webfloo\Filament\Resources\MenuItemResource\Pages\EditMenuItem;
use Webfloo\Filament\Resources\MenuItemResource\Pages\ListMenuItems;
use Webfloo\Models\MenuItem;
use Webfloo\Support\ModuleRegistry;
use Webfloo\Tests\TestCase;

final class MenuItemResourceTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------------------

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(MenuItemResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(ListMenuItems::class)->assertOk();
    }

    public function test_authorized_user_can_access_create_page(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(CreateMenuItem::class)->assertOk();
    }

    public function test_authorized_user_can_access_edit_page(): void
    {
        $item = MenuItem::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(EditMenuItem::class, ['record' => $item->getRouteKey()])->assertOk();
    }

    // ---------------------------------------------------------------------------
    // Feature flag
    // ---------------------------------------------------------------------------

    public function test_resource_is_inaccessible_when_menu_feature_flag_is_off(): void
    {
        // MenuItemResource gates through ModuleRegistry instead of a raw config read — pin both layers.
        config(['webfloo.features.menu' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'menu_item')]);

        $this->assertFalse(ModuleRegistry::isEnabled('menu'));
        $this->assertFalse(MenuItemResource::canAccess());

        $this->actingAs($user)
            ->get(MenuItemResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_resource_is_accessible_when_menu_feature_flag_is_on(): void
    {
        config(['webfloo.features.menu' => true]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'menu_item')]);
        $this->actingAs($user);

        $this->assertTrue(ModuleRegistry::isEnabled('menu'));
        $this->assertTrue(MenuItemResource::canAccess());
    }

    // ---------------------------------------------------------------------------
    // Table / index rendering
    // ---------------------------------------------------------------------------

    public function test_index_renders_menu_item_records(): void
    {
        $items = MenuItem::factory()->count(3)->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(ListMenuItems::class)
            ->assertCanSeeTableRecords($items);
    }

    // ---------------------------------------------------------------------------
    // Table filters
    // ---------------------------------------------------------------------------

    public function test_location_filter_narrows_to_selected_location(): void
    {
        $header = MenuItem::factory()->create(['location' => MenuItem::LOCATION_HEADER]);
        $footer = MenuItem::factory()->create(['location' => 'footer_company']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(ListMenuItems::class)
            ->filterTable('location', MenuItem::LOCATION_HEADER)
            ->assertCanSeeTableRecords([$header])
            ->assertCanNotSeeTableRecords([$footer]);
    }

    public function test_is_active_ternary_filter_shows_only_active_items(): void
    {
        $active = MenuItem::factory()->active()->create();
        $inactive = MenuItem::factory()->inactive()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(ListMenuItems::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive]);
    }

    public function test_is_active_ternary_filter_shows_only_inactive_items(): void
    {
        $active = MenuItem::factory()->active()->create();
        $inactive = MenuItem::factory()->inactive()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(ListMenuItems::class)
            ->filterTable('is_active', false)
            ->assertCanSeeTableRecords([$inactive])
            ->assertCanNotSeeTableRecords([$active]);
    }

    // ---------------------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------------------

    public function test_create_persists_valid_menu_item_with_both_locales(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(CreateMenuItem::class)
            ->fillForm([
                'label' => ['pl' => 'Strona główna', 'en' => 'Home'],
                'href' => '/home',
                'target' => '_self',
                'location' => MenuItem::LOCATION_HEADER,
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = MenuItem::latest('id')->first();

        $this->assertSame('Strona główna', $item->getTranslation('label', 'pl'));
        $this->assertSame('Home', $item->getTranslation('label', 'en'));
        $this->assertSame('/home', $item->href);
        $this->assertSame(MenuItem::LOCATION_HEADER, $item->location);
        $this->assertTrue($item->is_active);
    }

    public function test_create_requires_location(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(CreateMenuItem::class)
            ->fillForm([
                'label' => ['pl' => 'Item', 'en' => 'Item'],
                'location' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['location' => 'required']);
    }

    public function test_create_requires_label(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(CreateMenuItem::class)
            ->fillForm([
                'label' => '',
                'location' => MenuItem::LOCATION_HEADER,
            ])
            ->call('create')
            ->assertHasFormErrors(['label']);
    }

    public function test_create_defaults_is_active_to_true(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(CreateMenuItem::class)
            ->fillForm([
                'label' => ['pl' => 'Link', 'en' => 'Link'],
                'location' => MenuItem::LOCATION_HEADER,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(MenuItem::latest('id')->first()->is_active);
    }

    // ---------------------------------------------------------------------------
    // Edit — prefill + save
    // ---------------------------------------------------------------------------

    public function test_edit_form_prefills_existing_translations(): void
    {
        $item = MenuItem::factory()->create([
            'label' => ['pl' => 'O nas', 'en' => 'About us'],
            'href' => '/about',
            'location' => MenuItem::LOCATION_HEADER,
        ]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(EditMenuItem::class, ['record' => $item->getRouteKey()])
            ->assertFormSet([
                'label' => ['pl' => 'O nas', 'en' => 'About us'],
                'href' => '/about',
                'location' => MenuItem::LOCATION_HEADER,
            ]);
    }

    public function test_edit_saves_updated_translations(): void
    {
        $item = MenuItem::factory()->create([
            'label' => ['pl' => 'Stary', 'en' => 'Old'],
            'location' => MenuItem::LOCATION_HEADER,
        ]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(EditMenuItem::class, ['record' => $item->getRouteKey()])
            ->fillForm([
                'label' => ['pl' => 'Nowy', 'en' => 'New'],
                'href' => '/new',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $item->refresh();
        $this->assertSame('Nowy', $item->getTranslation('label', 'pl'));
        $this->assertSame('New', $item->getTranslation('label', 'en'));
        $this->assertSame('/new', $item->href);
    }

    public function test_edit_saves_is_active_toggle(): void
    {
        $item = MenuItem::factory()->active()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(EditMenuItem::class, ['record' => $item->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($item->refresh()->is_active);
    }

    // ---------------------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------------------

    public function test_authorized_user_can_delete_menu_item(): void
    {
        $item = MenuItem::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(ListMenuItems::class)
            ->callTableAction('delete', $item)
            ->assertOk();

        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }

    public function test_bulk_delete_removes_selected_menu_items(): void
    {
        $items = MenuItem::factory()->count(3)->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'menu_item')]));

        Livewire::test(ListMenuItems::class)
            ->callTableBulkAction('delete', $items)
            ->assertOk();

        foreach ($items as $item) {
            $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
        }
    }

    // ---------------------------------------------------------------------------
    // Translatable fields — raw JSON structure
    // ---------------------------------------------------------------------------

    public function test_translatable_label_stores_both_locales_as_json(): void
    {
        $item = MenuItem::factory()->create([
            'label' => ['pl' => 'Kontakt', 'en' => 'Contact'],
        ]);

        $raw = DB::table('menu_items')->where('id', $item->id)->value('label');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
        $this->assertSame('Kontakt', $decoded['pl']);
        $this->assertSame('Contact', $decoded['en']);
    }

    public function test_get_translation_returns_empty_string_for_missing_locale(): void
    {
        $item = MenuItem::factory()->create([
            'label' => ['pl' => 'Tylko PL'],
        ]);

        // spatie/laravel-translatable returns '' when locale missing and fallback disabled
        $this->assertSame('', $item->getTranslation('label', 'en', false));
    }

    // ---------------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------------

    public function test_scope_active_returns_only_active_menu_items(): void
    {
        MenuItem::factory()->active()->count(2)->create();
        MenuItem::factory()->inactive()->create();

        $this->assertCount(2, MenuItem::active()->get());
    }

    public function test_scope_active_excludes_inactive_menu_items(): void
    {
        MenuItem::factory()->inactive()->create();

        $this->assertCount(0, MenuItem::active()->get());
    }

    public function test_scope_in_location_filters_by_location(): void
    {
        MenuItem::factory()->count(2)->create(['location' => MenuItem::LOCATION_HEADER]);
        MenuItem::factory()->create(['location' => 'footer_company']);

        $this->assertCount(2, MenuItem::inLocation(MenuItem::LOCATION_HEADER)->get());
    }

    public function test_scope_top_level_excludes_child_items(): void
    {
        $parent = MenuItem::factory()->create(['parent_id' => null]);
        MenuItem::factory()->create(['parent_id' => $parent->id]);

        $this->assertCount(1, MenuItem::topLevel()->get());
    }

    public function test_scope_ordered_returns_items_by_sort_order(): void
    {
        MenuItem::factory()->create(['sort_order' => 3]);
        MenuItem::factory()->create(['sort_order' => 1]);
        MenuItem::factory()->create(['sort_order' => 2]);

        $this->assertSame([1, 2, 3], MenuItem::ordered()->pluck('sort_order')->toArray());
    }

    // ---------------------------------------------------------------------------
    // DB defaults
    // ---------------------------------------------------------------------------

    public function test_menu_item_defaults_is_active_to_true_on_database_level(): void
    {
        $id = DB::table('menu_items')->insertGetId([
            'label' => json_encode(['pl' => 'Test']),
            'location' => MenuItem::LOCATION_HEADER,
            'target' => '_self',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(1, (int) DB::table('menu_items')->find($id)->is_active);
    }
}
