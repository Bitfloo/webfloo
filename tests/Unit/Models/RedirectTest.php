<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Models;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Redirect;
use Webfloo\Tests\TestCase;

final class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_persists_with_defaults(): void
    {
        $redirect = Redirect::create([
            'from_path' => '/old-page',
            'to_path' => '/new-page',
        ]);

        $redirect->refresh();
        $this->assertSame(301, $redirect->status_code);
        $this->assertTrue($redirect->is_active);
    }

    public function test_from_path_is_unique_on_database_level(): void
    {
        Redirect::create(['from_path' => '/taken', 'to_path' => '/a']);

        $this->expectException(QueryException::class);

        Redirect::create(['from_path' => '/taken', 'to_path' => '/b']);
    }

    public function test_for_path_returns_active_redirect(): void
    {
        $redirect = Redirect::create(['from_path' => '/old', 'to_path' => '/new']);
        Redirect::create(['from_path' => '/off', 'to_path' => '/x', 'is_active' => false]);

        $this->assertTrue($redirect->is(Redirect::forPath('/old')));
        $this->assertNull(Redirect::forPath('/off'));
        $this->assertNull(Redirect::forPath('/missing'));
    }

    public function test_normalize_path_adds_leading_and_strips_trailing_slash(): void
    {
        $this->assertSame('/old-page', Redirect::normalizePath('old-page/'));
        $this->assertSame('/old-page', Redirect::normalizePath('/old-page'));
        $this->assertSame('/', Redirect::normalizePath('/'));
        $this->assertSame('/', Redirect::normalizePath(''));
        $this->assertSame('/a/b', Redirect::normalizePath('a/b'));
    }
}
