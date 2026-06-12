<?php

declare(strict_types=1);

namespace Webfloo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates super_admin, editor and viewer roles with appropriate permissions.
 *
 * Prerequisites:
 *   php artisan shield:generate --all --panel=admin
 *
 * Usage:
 *   php artisan db:seed --class="Webfloo\Database\Seeders\ShieldRolesSeeder"
 *
 * Idempotent -- safe to run multiple times.
 *
 * Role hierarchy (read/write capability ↑ = more power):
 * - viewer      — read-only dla content surfaces (nie widzi PII, nie edytuje)
 * - editor      — CRUD content, nie widzi newsletter PII (GDPR admin-only)
 * - super_admin — all permissions (reserved dla ops / compliance)
 */
class ShieldRolesSeeder extends Seeder
{
    /*
     * Newsletter subscribers contain PII (email, name, IP) in GDPR scope.
     * Only super_admin may access — do NOT add `newsletter_subscriber` here
     * without legal/privacy review. A dedicated role (e.g. `compliance_officer`)
     * with narrow permissions is the right escalation path.
     */
    private const EDITOR_RESOURCES = [
        'post',
        'post_category',
        'page',
        'project',
        'service',
        'testimonial',
        'faq',
        'menu_item',
        // Content-adjacent ops tool (no PII): editors rename slugs, so they
        // manage the redirects those renames create. Permissions exist only
        // on hosts with the redirects module enabled (shield:generate).
        'redirect',
    ];

    /**
     * Viewer role widzi te same surface'y co editor, ale read-only.
     * Pomijamy newsletter (PII) — viewer też nie ma dostępu.
     *
     * @var list<string>
     */
    private const VIEWER_RESOURCES = self::EDITOR_RESOURCES;

    /**
     * Actions granted to editors. Editors soft-delete and restore content;
     * permanent destruction (force_delete*) is deliberately super_admin-only.
     *
     * @var list<string>
     */
    private const EDITOR_ACTIONS = [
        'view_any',
        'view',
        'create',
        'update',
        'delete',
        'restore',
        'restore_any',
        'replicate',
        'reorder',
    ];

    /**
     * Read-only actions dla viewer roli.
     *
     * @var list<string>
     */
    private const VIEWER_ACTIONS = [
        'view_any',
        'view',
    ];

    /**
     * Page identifiers the editor role can view. Built as `View:{StudlyName}`
     * to match Shield v4 permission identifiers (e.g. View:HomePageSettings).
     *
     * @var list<string>
     */
    private const EDITOR_PAGE_PERMISSIONS = [
        'home_page_settings',
        'contact_page_settings',
        'site_settings',
        // theme_settings deliberately absent: ThemeSettings gates on
        // hasRole('super_admin'), so a permission grant would be dead weight.
    ];

    /**
     * Viewer dostaje te same page permissions co editor — widzi settings,
     * ale nie zapisze (Filament Page view-only gdy brak update permission).
     *
     * @var list<string>
     */
    private const VIEWER_PAGE_PERMISSIONS = self::EDITOR_PAGE_PERMISSIONS;

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        // 1. Create roles (idempotent)
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => $guard]
        );

        $editor = Role::firstOrCreate(
            ['name' => 'editor', 'guard_name' => $guard]
        );

        $viewer = Role::firstOrCreate(
            ['name' => 'viewer', 'guard_name' => $guard]
        );

        // 2. Custom action permissions shield:generate cannot produce —
        //    PII export gates (LeadResource, CrmDashboard, newsletter list).
        //    Created before the super_admin sync so it picks them up;
        //    deliberately NOT granted to editor/viewer.
        Permission::findOrCreate(webfloo_permission('export', 'lead'), $guard);
        Permission::findOrCreate(webfloo_permission('export', 'newsletter_subscriber'), $guard);

        // 3. super_admin gets ALL permissions
        $allPermissions = Permission::where('guard_name', $guard)->pluck('name');
        $superAdmin->syncPermissions($allPermissions);

        // 4. Build editor + viewer permission sets
        $editorPermissions = $this->buildPermissions(
            $guard,
            self::EDITOR_RESOURCES,
            self::EDITOR_ACTIONS,
            self::EDITOR_PAGE_PERMISSIONS,
        );
        $editor->syncPermissions($editorPermissions);

        $viewerPermissions = $this->buildPermissions(
            $guard,
            self::VIEWER_RESOURCES,
            self::VIEWER_ACTIONS,
            self::VIEWER_PAGE_PERMISSIONS,
        );
        $viewer->syncPermissions($viewerPermissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info(sprintf(
            'Roles seeded: super_admin (%d), editor (%d), viewer (%d)',
            $allPermissions->count(),
            $editorPermissions->count(),
            $viewerPermissions->count(),
        ));
    }

    /**
     * @param  list<string>  $resources
     * @param  list<string>  $actions
     * @param  list<string>  $pagePermissions
     * @return Collection<int, string>
     */
    private function buildPermissions(
        string $guard,
        array $resources,
        array $actions,
        array $pagePermissions,
    ): Collection {
        $permissions = collect();

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permissions->push(webfloo_permission($action, $resource));
            }
        }

        foreach ($pagePermissions as $page) {
            $permissions->push(webfloo_permission('view', $page));
        }

        $existing = Permission::where('guard_name', $guard)
            ->whereIn('name', $permissions->all())
            ->pluck('name');

        if ($existing->count() < $permissions->count()) {
            $missing = $permissions->diff($existing);
            $this->command->warn("Missing permissions (run shield:generate first): {$missing->implode(', ')}");
        }

        return $existing;
    }
}
