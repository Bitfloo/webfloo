<?php

declare(strict_types=1);

namespace Webfloo\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\PermissionServiceProvider;
use Webfloo\Tests\Models\User;
use Webfloo\Tests\Providers\TestPanelProvider;
use Webfloo\WebflooServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    // Enable Composer package discovery so all filament/* sub-package providers
    // (e.g. SupportServiceProvider which registers Blade macros like ->grid())
    // are auto-loaded without having to enumerate them manually.
    protected $enablesPackageDiscoveries = true;

    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            PermissionServiceProvider::class,
            // TestPanelProvider registers the Filament panel via PanelProvider::register()
            // which hooks into Filament::registerPanel() before routes/web.php loads.
            TestPanelProvider::class,
            WebflooServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Point auth to the test User model (has HasRoles from spatie).
        $app['config']->set('auth.providers.users.model', User::class);

        // spatie/laravel-permission required config keys.
        $app['config']->set('permission.models.permission', Permission::class);
        $app['config']->set('permission.models.role', \Spatie\Permission\Models\Role::class);
        $app['config']->set('permission.table_names.roles', 'roles');
        $app['config']->set('permission.table_names.permissions', 'permissions');
        $app['config']->set('permission.table_names.model_has_permissions', 'model_has_permissions');
        $app['config']->set('permission.table_names.model_has_roles', 'model_has_roles');
        $app['config']->set('permission.table_names.role_has_permissions', 'role_has_permissions');
        $app['config']->set('permission.column_names.role_pivot_key', 'role_id');
        $app['config']->set('permission.column_names.permission_pivot_key', 'permission_id');
        $app['config']->set('permission.column_names.model_morph_key', 'model_id');
        $app['config']->set('permission.column_names.team_foreign_key', 'team_id');
        $app['config']->set('permission.register_permission_check_method', true);
        $app['config']->set('permission.teams', false);
        $app['config']->set('permission.cache.expiration_time', \DateInterval::createFromDateString('24 hours'));
        $app['config']->set('permission.cache.key', 'spatie.permission.cache');
        $app['config']->set('permission.cache.store', 'default');
    }

    protected function defineDatabaseMigrations(): void
    {
        // Testbench's bundled users/sessions migrations must run first so that
        // the package's own ALTER TABLE migrations (e.g. add job_title to users)
        // find the table already in place.
        $this->loadMigrationsFrom(
            __DIR__ . '/../vendor/orchestra/testbench-core/laravel/migrations'
        );
        // spatie/laravel-permission ships only a .stub file; the wrapper .php
        // in tests/database/migrations makes the migrator pick it up.
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function makeAdmin(array $permissions = []): User
    {
        /** @var User $user */
        $user = User::factory()->create();

        foreach ($permissions as $permission) {
            // spatie v7: the Permission record must exist in DB before it can be granted.
            Permission::findOrCreate($permission, 'web');
        }

        // Flush the spatie permission cache so the gate picks up the new permissions.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($permissions !== []) {
            $user->givePermissionTo($permissions);
        }

        return $user;
    }
}
