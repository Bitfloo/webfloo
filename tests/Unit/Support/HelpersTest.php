<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Support;

use Webfloo\Support\ModuleRegistry;
use Webfloo\Tests\Models\User;
use Webfloo\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_webfloo_fallback_locale_returns_app_fallback_locale(): void
    {
        config()->set('app.fallback_locale', 'en');

        $this->assertSame('en', webfloo_fallback_locale());
    }

    public function test_webfloo_fallback_locale_defaults_to_pl_when_config_not_string(): void
    {
        config()->set('app.fallback_locale', null);

        $this->assertSame('pl', webfloo_fallback_locale());
    }

    public function test_frontend_feature_flag_defaults_to_disabled(): void
    {
        $this->assertFalse(config('webfloo.features.frontend'));
        $this->assertFalse(ModuleRegistry::isEnabled('frontend'));
    }

    public function test_frontend_module_is_registered_and_enableable(): void
    {
        $this->assertArrayHasKey('frontend', ModuleRegistry::all());

        config()->set('webfloo.features.frontend', true);

        $this->assertTrue(ModuleRegistry::isEnabled('frontend'));
    }

    public function test_webfloo_user_model_falls_back_to_auth_provider_model(): void
    {
        config()->set('webfloo.user_model', null);

        $this->assertSame(User::class, webfloo_user_model());
    }
}
