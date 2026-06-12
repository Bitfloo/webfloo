<?php

declare(strict_types=1);

namespace Webfloo\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Throwable;
use Webfloo\Database\Seeders\DemoSeeder;
use Webfloo\Database\Seeders\ShieldRolesSeeder;

class Install extends Command
{
    protected $signature = 'webfloo:install {--demo : Seed demo content (pages, menu, sample records)}';

    protected $description = 'Install webfloo: publish config, migrate, generate permissions, seed roles, create first admin';

    public function handle(): int
    {
        $this->info('Installing webfloo...');

        $this->publishConfig();

        // Migrations must fail loudly — a broken schema is not recoverable here.
        $this->call('migrate', ['--force' => true]);

        $this->generateShieldPermissions();

        $this->call('db:seed', ['--class' => ShieldRolesSeeder::class, '--force' => true]);

        $this->createFirstAdmin();

        if ((bool) $this->option('demo')) {
            $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);
        }

        $this->info('webfloo installed.');
        $this->line(
            'Public frontend (optional): set webfloo.features.frontend = true, then run '
            .'php artisan vendor:publish --tag=webfloo-assets --tag=webfloo-error-pages'
        );

        return self::SUCCESS;
    }

    protected function publishConfig(): void
    {
        if (! file_exists(config_path('webfloo.php'))) {
            $this->call('vendor:publish', ['--tag' => 'webfloo-config']);
        }

        if (! file_exists(config_path('webfloo-modules.php'))) {
            $this->call('vendor:publish', ['--tag' => 'webfloo-modules']);
        }
    }

    /**
     * The only soft-failing step: shield:generate requires a configured
     * Filament panel, which a fresh host may not have yet.
     */
    protected function generateShieldPermissions(): void
    {
        try {
            $this->call('shield:generate', ['--all' => true, '--panel' => 'admin']);
        } catch (Throwable) {
            $this->warn(
                'shield:generate failed (no Filament panel named "admin" yet?). '
                .'After configuring your panel run: php artisan shield:generate --all --panel=admin '
                .'and re-run: php artisan db:seed --class="Webfloo\\Database\\Seeders\\ShieldRolesSeeder"'
            );
        }
    }

    protected function createFirstAdmin(): void
    {
        $userModel = webfloo_user_model();

        if ($userModel::query()->count() > 0) {
            $this->line('Users already exist - skipping admin creation.');

            return;
        }

        $name = $this->askString('Admin name');

        $email = $this->askString('Admin e-mail');
        while (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->error('Invalid e-mail address.');
            $email = $this->askString('Admin e-mail');
        }

        $password = $this->secret('Admin password');
        $password = is_string($password) ? $password : '';
        while ($password === '') {
            $this->error('Password cannot be empty.');
            $password = $this->secret('Admin password');
            $password = is_string($password) ? $password : '';
        }

        /** @var User $user */
        $user = $userModel::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('super_admin');
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $this->info("Admin {$email} created with super_admin role.");
    }

    protected function askString(string $question): string
    {
        $answer = $this->ask($question);

        return is_string($answer) ? $answer : '';
    }
}
