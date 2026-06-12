<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Webfloo\Filament\Pages\ThemeSettings;
use Webfloo\Tests\TestCase;

/**
 * ThemeSettings is deliberately ROLE-gated (hasRole super_admin), not
 * permission-gated — see the ShieldRolesSeeder comment explaining why a
 * View:ThemeSettings permission grant would be dead weight. These tests
 * pin that asymmetry so a future "alignment" does not happen by accident.
 */
final class ThemeSettingsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_super_admin_role_cannot_access(): void
    {
        // Even an explicit View:ThemeSettings permission must NOT open the
        // page — the gate is the role, not the permission.
        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'theme_settings')]))
            ->get(ThemeSettings::getUrl())
            ->assertForbidden();
    }

    public function test_super_admin_role_can_access(): void
    {
        Role::findOrCreate('super_admin', 'web');

        $user = $this->makeAdmin();
        $user->assignRole('super_admin');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($user);

        $this->assertTrue(ThemeSettings::canAccess());

        Livewire::test(ThemeSettings::class)->assertOk();
    }
}
