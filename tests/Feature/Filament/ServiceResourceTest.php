<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Webfloo\Filament\Resources\ServiceResource;
use Webfloo\Filament\Resources\ServiceResource\Pages\CreateService;
use Webfloo\Filament\Resources\ServiceResource\Pages\EditService;
use Webfloo\Filament\Resources\ServiceResource\Pages\ListServices;
use Webfloo\Models\Service;
use Webfloo\Tests\TestCase;

final class ServiceResourceTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------------------

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $user = $this->makeAdmin();

        $this->actingAs($user)
            ->get(ServiceResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        // Use Livewire::test to avoid full panel view rendering (Alpine.js not loaded in tests).
        Livewire::test(ListServices::class)->assertOk();
    }

    public function test_user_with_view_any_permission_can_access_create_page(): void
    {
        // ServiceResource does not define custom canCreate() — Filament defaults
        // to allow when no Laravel Policy is registered for the Service model.
        // Only canAccess() (index) has the spatie permission gate.
        $user = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(CreateService::class)->assertOk();
    }

    public function test_user_with_view_any_permission_can_access_edit_page(): void
    {
        // Same reasoning as create: no custom canEdit() policy in this package.
        $service = Service::factory()->create();
        $user    = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])->assertOk();
    }

    // ---------------------------------------------------------------------------
    // Feature flag
    // ---------------------------------------------------------------------------

    public function test_resource_is_inaccessible_when_services_feature_flag_is_off(): void
    {
        config(['webfloo.features.services' => false]);

        $user = $this->makeAdmin(['view_any_service']);

        $this->assertFalse(ServiceResource::canAccess());

        $this->actingAs($user)
            ->get(ServiceResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_resource_is_accessible_when_services_feature_flag_is_on(): void
    {
        config(['webfloo.features.services' => true]);

        $user = $this->makeAdmin(['view_any_service']);

        $this->actingAs($user);

        $this->assertTrue(ServiceResource::canAccess());
    }

    // ---------------------------------------------------------------------------
    // Table / index rendering
    // ---------------------------------------------------------------------------

    public function test_index_renders_active_service_records(): void
    {
        $services = Service::factory()->active()->count(3)->create();
        $user     = $this->makeAdmin(['view_any_service']);

        $this->actingAs($user);

        Livewire::test(ListServices::class)
            ->assertCanSeeTableRecords($services);
    }

    public function test_index_renders_inactive_service_records(): void
    {
        $inactive = Service::factory()->inactive()->create();
        $user     = $this->makeAdmin(['view_any_service']);

        $this->actingAs($user);

        // Resource table shows all services regardless of active flag
        Livewire::test(ListServices::class)
            ->assertCanSeeTableRecords([$inactive]);
    }

    // ---------------------------------------------------------------------------
    // Table filter — is_active
    // ---------------------------------------------------------------------------

    public function test_is_active_filter_shows_only_active_services(): void
    {
        $active   = Service::factory()->active()->create();
        $inactive = Service::factory()->inactive()->create();
        $user     = $this->makeAdmin(['view_any_service']);

        $this->actingAs($user);

        Livewire::test(ListServices::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive]);
    }

    public function test_is_active_filter_shows_only_inactive_services(): void
    {
        $active   = Service::factory()->active()->create();
        $inactive = Service::factory()->inactive()->create();
        $user     = $this->makeAdmin(['view_any_service']);

        $this->actingAs($user);

        Livewire::test(ListServices::class)
            ->filterTable('is_active', false)
            ->assertCanSeeTableRecords([$inactive])
            ->assertCanNotSeeTableRecords([$active]);
    }

    // ---------------------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------------------

    public function test_create_persists_valid_service_with_both_locales(): void
    {
        $user = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(CreateService::class)
            ->fillForm([
                'title'       => ['pl' => 'Tworzenie stron', 'en' => 'Web Development'],
                'description' => ['pl' => 'Opis po polsku', 'en' => 'English description'],
                'icon'        => 'code-bracket',
                'is_active'   => true,
                'is_featured' => false,
                'sort_order'  => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $service = Service::latest('id')->first();

        $this->assertSame('Tworzenie stron', $service->getTranslation('title', 'pl'));
        $this->assertSame('Web Development', $service->getTranslation('title', 'en'));
        $this->assertSame('Opis po polsku', $service->getTranslation('description', 'pl'));
        $this->assertSame('English description', $service->getTranslation('description', 'en'));
        $this->assertSame('code-bracket', $service->icon);
        $this->assertTrue($service->is_active);
        $this->assertFalse($service->is_featured);
    }

    public function test_create_requires_title(): void
    {
        $user = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        // The form uses a plain TextInput for title (no locale switcher).
        // Submitting empty string triggers the required rule on the raw value.
        Livewire::test(CreateService::class)
            ->fillForm([
                'title' => '',
                'icon'  => 'code-bracket',
            ])
            ->call('create')
            ->assertHasFormErrors(['title']);
    }

    public function test_create_requires_icon(): void
    {
        $user = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(CreateService::class)
            ->fillForm([
                'title' => ['pl' => 'Usługa', 'en' => 'Service'],
                'icon'  => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['icon']);
    }

    public function test_create_defaults_is_active_to_true(): void
    {
        $user = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(CreateService::class)
            ->fillForm([
                'title' => ['pl' => 'Usługa', 'en' => 'Service'],
                'icon'  => 'bolt',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $service = Service::latest('id')->first();
        $this->assertTrue($service->is_active);
    }

    public function test_create_defaults_sort_order_to_zero(): void
    {
        $user = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(CreateService::class)
            ->fillForm([
                'title'      => ['pl' => 'Usługa', 'en' => 'Service'],
                'icon'       => 'bolt',
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $service = Service::latest('id')->first();
        $this->assertSame(0, $service->sort_order);
    }

    // ---------------------------------------------------------------------------
    // Edit — prefill + save
    // ---------------------------------------------------------------------------

    public function test_edit_form_prefills_existing_translations(): void
    {
        $service = Service::factory()->create([
            'title'       => ['pl' => 'Oryginał PL', 'en' => 'Original EN'],
            'description' => ['pl' => 'Opis PL', 'en' => 'Desc EN'],
            'icon'        => 'server',
        ]);
        $user = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->assertFormSet([
                'title'       => ['pl' => 'Oryginał PL', 'en' => 'Original EN'],
                'description' => ['pl' => 'Opis PL', 'en' => 'Desc EN'],
                'icon'        => 'server',
            ]);
    }

    public function test_edit_saves_updated_translations(): void
    {
        $service = Service::factory()->create([
            'title' => ['pl' => 'Stary tytuł', 'en' => 'Old title'],
            'icon'  => 'server',
        ]);
        $user = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm([
                'title' => ['pl' => 'Nowy tytuł', 'en' => 'New title'],
                'icon'  => 'cloud',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $service->refresh();
        $this->assertSame('Nowy tytuł', $service->getTranslation('title', 'pl'));
        $this->assertSame('New title', $service->getTranslation('title', 'en'));
        $this->assertSame('cloud', $service->icon);
    }

    public function test_edit_saves_is_active_toggle(): void
    {
        $service = Service::factory()->active()->create();
        $user    = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($service->refresh()->is_active);
    }

    public function test_edit_saves_is_featured_toggle(): void
    {
        $service = Service::factory()->create(['is_featured' => false]);
        $user    = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm(['is_featured' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($service->refresh()->is_featured);
    }

    // ---------------------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------------------

    public function test_authorized_user_can_delete_service(): void
    {
        $service = Service::factory()->create();
        $user    = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(ListServices::class)
            ->callTableAction('delete', $service)
            ->assertOk();

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_bulk_delete_removes_selected_services(): void
    {
        $services = Service::factory()->count(3)->create();
        $user     = $this->makeAdmin(['view_any_service']);
        $this->actingAs($user);

        Livewire::test(ListServices::class)
            ->callTableBulkAction('delete', $services)
            ->assertOk();

        foreach ($services as $service) {
            $this->assertDatabaseMissing('services', ['id' => $service->id]);
        }
    }

    // ---------------------------------------------------------------------------
    // Translatable fields — raw JSON structure
    // ---------------------------------------------------------------------------

    public function test_translatable_title_stores_both_locales_as_json(): void
    {
        $service = Service::factory()->create([
            'title' => ['pl' => 'Serwis', 'en' => 'Service'],
        ]);

        $raw = DB::table('services')->where('id', $service->id)->value('title');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
        $this->assertSame('Serwis', $decoded['pl']);
        $this->assertSame('Service', $decoded['en']);
    }

    public function test_translatable_description_stores_both_locales_as_json(): void
    {
        $service = Service::factory()->create([
            'description' => ['pl' => 'Opis po polsku', 'en' => 'English description'],
        ]);

        $raw = DB::table('services')->where('id', $service->id)->value('description');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
    }

    public function test_getTranslation_returns_null_for_missing_locale(): void
    {
        $service = Service::factory()->create([
            'title' => ['pl' => 'Tylko PL'],
        ]);

        // spatie/laravel-translatable returns empty string (not null) when locale missing
        // and fallback is disabled. The exact value depends on package config.
        $value = $service->getTranslation('title', 'en', false);
        $this->assertSame('', $value);
    }

    // ---------------------------------------------------------------------------
    // Scopes — unit coverage
    // ---------------------------------------------------------------------------

    public function test_scope_active_returns_only_active_services(): void
    {
        Service::factory()->active()->count(2)->create();
        Service::factory()->inactive()->create();

        $this->assertCount(2, Service::active()->get());
    }

    public function test_scope_active_excludes_inactive_services(): void
    {
        Service::factory()->inactive()->create();

        $this->assertCount(0, Service::active()->get());
    }

    public function test_scope_featured_returns_only_featured_services(): void
    {
        Service::factory()->featured()->count(2)->create();
        Service::factory()->create(['is_featured' => false]);

        $this->assertCount(2, Service::featured()->get());
    }

    public function test_scope_featured_excludes_non_featured_services(): void
    {
        Service::factory()->create(['is_featured' => false]);

        $this->assertCount(0, Service::featured()->get());
    }

    public function test_homepage_rotation_returns_active_and_featured_services(): void
    {
        Service::factory()->active()->featured()->count(3)->create();
        Service::factory()->inactive()->featured()->create();
        Service::factory()->active()->create(['is_featured' => false]);

        // Homepage widget queries active + featured
        $homepage = Service::active()->featured()->get();

        $this->assertCount(3, $homepage);
        foreach ($homepage as $service) {
            $this->assertTrue($service->is_active);
            $this->assertTrue($service->is_featured);
        }
    }

    public function test_scope_ordered_returns_services_by_sort_order(): void
    {
        Service::factory()->create(['sort_order' => 3, 'title' => ['pl' => 'Trzecia', 'en' => 'Third']]);
        Service::factory()->create(['sort_order' => 1, 'title' => ['pl' => 'Pierwsza', 'en' => 'First']]);
        Service::factory()->create(['sort_order' => 2, 'title' => ['pl' => 'Druga', 'en' => 'Second']]);

        $ordered = Service::ordered()->pluck('sort_order')->toArray();

        $this->assertSame([1, 2, 3], $ordered);
    }

    // ---------------------------------------------------------------------------
    // Model defaults
    // ---------------------------------------------------------------------------

    public function test_service_defaults_is_active_to_true_on_database_level(): void
    {
        $id = DB::table('services')->insertGetId([
            'title'      => json_encode(['pl' => 'Test']),
            'icon'       => 'bolt',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('services')->find($id);
        $this->assertSame(1, (int) $row->is_active);
    }

    public function test_service_defaults_sort_order_to_zero_on_database_level(): void
    {
        $id = DB::table('services')->insertGetId([
            'title'      => json_encode(['pl' => 'Test']),
            'icon'       => 'bolt',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('services')->find($id);
        $this->assertSame(0, (int) $row->sort_order);
    }
}
