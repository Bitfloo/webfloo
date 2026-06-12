<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Webfloo\Database\Seeders\ShieldRolesSeeder;
use Webfloo\Filament\Resources\NewsletterSubscriberResource;
use Webfloo\Filament\Resources\PageResource;
use Webfloo\Filament\Widgets\LeadStatsOverview;
use Webfloo\Tests\Models\User;
use Webfloo\Tests\TestCase;

/**
 * Pins the seeder -> role -> canAccess() contract: permissions created in
 * the Shield v4 identifier format must satisfy the package's gates. This
 * is the test that was missing when the seeder migrated to v4 identifiers
 * while every canAccess() still checked snake_case names.
 */
class ShieldRolesContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Simulate shield:generate - create the v4-format permissions the
     * seeder expects to find.
     */
    protected function seedShieldPermissions(): void
    {
        $resources = [
            'post', 'post_category', 'page', 'project',
            'service', 'testimonial', 'faq', 'menu_item', 'newsletter_subscriber', 'redirect',
        ];
        $actions = [
            'view_any', 'view', 'create', 'update', 'delete',
            'restore', 'restore_any', 'force_delete', 'force_delete_any', 'replicate', 'reorder',
        ];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::findOrCreate(webfloo_permission($action, $resource), 'web');
            }
        }

        foreach (['home_page_settings', 'contact_page_settings', 'site_settings', 'theme_settings', 'crm_dashboard'] as $page) {
            Permission::findOrCreate(webfloo_permission('view', $page), 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function seededUserWithRole(string $role): User
    {
        $this->seedShieldPermissions();
        $this->seed(ShieldRolesSeeder::class);

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    public function test_seeded_editor_can_access_page_resource_index(): void
    {
        $this->actingAs($this->seededUserWithRole('editor'))
            ->get(PageResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_seeded_viewer_can_access_page_resource_index(): void
    {
        $this->actingAs($this->seededUserWithRole('viewer'))
            ->get(PageResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_seeded_editor_cannot_access_newsletter_subscribers_pii(): void
    {
        $this->actingAs($this->seededUserWithRole('editor'))
            ->get(NewsletterSubscriberResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_seeded_editor_has_no_force_delete_permission(): void
    {
        $user = $this->seededUserWithRole('editor');

        $this->assertTrue($user->can(webfloo_permission('restore', 'page')));
        $this->assertFalse($user->can(webfloo_permission('force_delete', 'page')));
    }

    public function test_seeded_viewer_cannot_create(): void
    {
        $user = $this->seededUserWithRole('viewer');

        $this->assertTrue($user->can(webfloo_permission('view_any', 'page')));
        $this->assertFalse($user->can(webfloo_permission('create', 'page')));
    }

    public function test_user_without_role_is_forbidden(): void
    {
        $this->seedShieldPermissions();
        $this->seed(ShieldRolesSeeder::class);

        $this->actingAs(User::factory()->create())
            ->get(PageResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_seeded_editor_cannot_export_pii(): void
    {
        $user = $this->seededUserWithRole('editor');

        $this->assertFalse($user->can(webfloo_permission('export', 'lead')));
        $this->assertFalse($user->can(webfloo_permission('export', 'newsletter_subscriber')));
    }

    public function test_seeded_super_admin_holds_export_permissions(): void
    {
        $user = $this->seededUserWithRole('super_admin');

        $this->assertTrue($user->can(webfloo_permission('export', 'lead')));
        $this->assertTrue($user->can(webfloo_permission('export', 'newsletter_subscriber')));
    }

    public function test_crm_widgets_hidden_without_crm_dashboard_permission(): void
    {
        $this->seedShieldPermissions();
        $this->seed(ShieldRolesSeeder::class);

        $this->actingAs(User::factory()->create());
        $this->assertFalse(LeadStatsOverview::canView());

        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'crm_dashboard')]));
        $this->assertTrue(LeadStatsOverview::canView());
    }
}
