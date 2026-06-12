<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Setting;
use Webfloo\Tests\TestCase;

final class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_returns_default_for_missing_key(): void
    {
        $this->assertSame('fallback', Setting::get('nope', 'fallback'));
        $this->assertNull(Setting::get('nope'));
    }

    public function test_set_then_get_roundtrips_value(): void
    {
        Setting::set('site_name', 'Acme');

        $this->assertSame('Acme', Setting::get('site_name'));
    }

    public function test_set_updates_existing_key_and_group(): void
    {
        Setting::set('site_name', 'Old', 'general');
        Setting::set('site_name', 'New', 'branding');

        $this->assertSame('New', Setting::get('site_name'));
        $this->assertSame(1, Setting::where('key', 'site_name')->count());
        $this->assertSame('branding', Setting::where('key', 'site_name')->value('group'));
    }

    public function test_get_returns_default_for_stored_null_value(): void
    {
        // Deliberate semantics of the `?? $default` read: a key explicitly
        // stored as null behaves like an absent key. Change Setting::get()
        // if "stored null wins over default" is ever wanted.
        Setting::set('maybe', null);

        $this->assertSame('fallback', Setting::get('maybe', 'fallback'));
    }

    public function test_get_group_returns_only_matching_group_keys(): void
    {
        Setting::set('a', '1', 'home');
        Setting::set('b', '2', 'home');
        Setting::set('c', '3', 'contact');

        $group = Setting::getGroup('home');

        $this->assertSame(['a' => '1', 'b' => '2'], $group);
    }

    public function test_set_invalidates_request_and_persistent_cache(): void
    {
        Setting::set('color', 'red');
        $this->assertSame('red', Setting::get('color'));

        Setting::set('color', 'blue');

        $this->assertSame('blue', Setting::get('color'));
    }

    public function test_json_value_cast_roundtrips_array(): void
    {
        Setting::set('nav', ['a' => 1, 'b' => [2, 3]]);

        $this->assertSame(['a' => 1, 'b' => [2, 3]], Setting::get('nav'));
    }
}
