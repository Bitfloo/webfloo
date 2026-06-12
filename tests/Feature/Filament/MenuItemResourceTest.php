<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\MenuItemResource;
use Webfloo\Filament\Resources\MenuItemResource\Pages\ListMenuItems;
use Webfloo\Support\ModuleRegistry;
use Webfloo\Tests\TestCase;

final class MenuItemResourceTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_resource_is_inaccessible_when_menu_feature_flag_is_off(): void
    {
        // MenuItemResource is the one resource gating through ModuleRegistry
        // instead of a raw config read — pin both layers.
        config(['webfloo.features.menu' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'menu_item')]);

        $this->assertFalse(ModuleRegistry::isEnabled('menu'));
        $this->assertFalse(MenuItemResource::canAccess());

        $this->actingAs($user)
            ->get(MenuItemResource::getUrl('index'))
            ->assertForbidden();
    }
}
