<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Webfloo\Models\MenuItem;
use Webfloo\Models\Page;
use Webfloo\Tests\Models\User;
use Webfloo\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // The install command publishes config into the Testbench skeleton;
        // remove it so runs do not pollute vendor/.
        @unlink(config_path('webfloo.php'));
        @unlink(config_path('webfloo-modules.php'));

        parent::tearDown();
    }

    public function test_install_creates_roles_and_skips_admin_when_users_exist(): void
    {
        User::factory()->create();

        $this->artisan('webfloo:install')
            ->expectsOutputToContain('webfloo installed')
            ->assertSuccessful();

        $this->assertTrue(Role::where('name', 'super_admin')->exists());
        $this->assertTrue(Role::where('name', 'editor')->exists());
        $this->assertTrue(Role::where('name', 'viewer')->exists());
    }

    public function test_install_prompts_for_first_admin_when_no_users(): void
    {
        $this->artisan('webfloo:install')
            ->expectsQuestion('Admin name', 'Admin')
            ->expectsQuestion('Admin e-mail', 'admin@example.com')
            ->expectsQuestion('Admin password', 'secret-password')
            ->assertSuccessful();

        /** @var User $user */
        $user = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('super_admin'));
    }

    public function test_install_skips_admin_creation_when_not_interactive(): void
    {
        $this->artisan('webfloo:install', ['--no-interaction' => true])
            ->expectsOutputToContain('Non-interactive mode')
            ->assertSuccessful();

        $this->assertSame(0, User::count());
    }

    public function test_install_with_demo_seeds_content(): void
    {
        User::factory()->create();

        $this->artisan('webfloo:install', ['--demo' => true])->assertSuccessful();

        $this->assertTrue(Page::where('slug', 'home')->where('template', 'home')->exists());
        $this->assertTrue(MenuItem::where('location', MenuItem::LOCATION_HEADER)->exists());
    }

    public function test_install_is_idempotent(): void
    {
        User::factory()->create();

        $this->artisan('webfloo:install', ['--demo' => true])->assertSuccessful();
        $this->artisan('webfloo:install', ['--demo' => true])->assertSuccessful();

        $this->assertSame(1, Page::where('slug', 'home')->count());
        $this->assertSame(1, Role::where('name', 'super_admin')->count());
    }
}
