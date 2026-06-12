<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Components;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Page;
use Webfloo\Models\Setting;
use Webfloo\Tests\TestCase;

class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('webfloo.features.frontend', true);
    }

    public function test_component_renders_message_buttons_and_storage_key(): void
    {
        $html = $this->blade('<x-webfloo-cookie-consent />')->__toString();

        $this->assertStringContainsString('webfloo-cookie-consent', $html);
        $this->assertStringContainsString('localStorage', $html);
        $this->assertStringContainsString('Akceptuj', $html);
        $this->assertStringContainsString('Odrzuć', $html);
    }

    public function test_component_uses_settings_for_message_and_privacy_url(): void
    {
        Setting::set('cookie_consent.message', 'Nasza wlasna informacja o cookies');
        Setting::set('cookie_consent.privacy_url', '/polityka-prywatnosci');

        $html = $this->blade('<x-webfloo-cookie-consent />')->__toString();

        $this->assertStringContainsString('Nasza wlasna informacja o cookies', $html);
        $this->assertStringContainsString('/polityka-prywatnosci', $html);
    }

    public function test_component_hides_privacy_link_without_setting(): void
    {
        $html = $this->blade('<x-webfloo-cookie-consent />')->__toString();

        $this->assertStringNotContainsString('<a href=""', $html);
    }

    public function test_layout_includes_banner_when_flag_enabled(): void
    {
        config(['webfloo.features.cookie_consent' => true]);
        Page::factory()->published()->create(['slug' => 'about']);

        $this->get('/about')
            ->assertOk()
            ->assertSee('webfloo-cookie-consent');
    }

    public function test_layout_omits_banner_by_default(): void
    {
        $this->assertFalse(config('webfloo.features.cookie_consent'));

        Page::factory()->published()->create(['slug' => 'about']);

        $this->get('/about')
            ->assertOk()
            ->assertDontSee('webfloo-cookie-consent');
    }
}
