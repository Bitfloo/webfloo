<?php

declare(strict_types=1);

namespace Webfloo\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Orchestra\Testbench\Factories\UserFactory;
use Spatie\Permission\Traits\HasRoles;

/**
 * Minimal User model for package tests only.
 * Host app provides its own User model — this one exists solely so
 * Filament + spatie/laravel-permission work inside Orchestra Testbench
 * without a real application context.
 */
final class User extends Authenticatable
{
    use HasFactory;
    use HasRoles;

    protected $table = 'users';

    protected $guarded = [];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
