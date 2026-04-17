<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature;

use Webfloo\Services\PluginTranslationRegistry;
use Webfloo\Services\ThemeService;
use Webfloo\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_theme_service_is_registered_as_singleton(): void
    {
        $first = app(ThemeService::class);
        $second = app(ThemeService::class);

        $this->assertSame($first, $second);
    }

    public function test_plugin_translation_registry_is_registered_as_singleton(): void
    {
        $first = app(PluginTranslationRegistry::class);
        $second = app(PluginTranslationRegistry::class);

        $this->assertSame($first, $second);
    }

    public function test_webfloo_config_is_loaded(): void
    {
        $this->assertNotNull(config('webfloo'));
        $this->assertNotNull(config('webfloo-modules'));
    }
}
