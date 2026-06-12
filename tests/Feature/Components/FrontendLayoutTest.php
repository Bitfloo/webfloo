<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Components;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\MenuItem;
use Webfloo\Models\Setting;
use Webfloo\Services\ThemeService;
use Webfloo\Tests\TestCase;

class FrontendLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_renders_skeleton_with_seo_and_slot(): void
    {
        config()->set('app.name', 'Acme');

        $view = $this->blade(
            '<x-webfloo-layout :seo="$seo">Hello content</x-webfloo-layout>',
            ['seo' => ['title' => 'About', 'description' => null, 'image' => null, 'no_index' => false]]
        );

        $view->assertSee('<title>About | Acme</title>', false);
        $view->assertSee('Hello content');
        $view->assertSee('<!DOCTYPE html>', false);
    }

    public function test_layout_injects_theme_css_variables(): void
    {
        $view = $this->blade('<x-webfloo-layout>x</x-webfloo-layout>');

        $view->assertSee('<style>', false);
        $view->assertSee('--', false);
    }

    public function test_layout_renders_header_navigation_from_menu(): void
    {
        MenuItem::factory()->create([
            'label' => ['pl' => 'Cennik', 'en' => 'Pricing'],
            'href' => '/cennik',
            'location' => MenuItem::LOCATION_HEADER,
            'is_active' => true,
        ]);

        $view = $this->blade('<x-webfloo-layout>x</x-webfloo-layout>');

        $view->assertSee('Pricing');
        $view->assertSee('/cennik');
    }

    public function test_layout_omits_custom_js_when_flag_disabled(): void
    {
        app(ThemeService::class)->saveConfig(['custom' => ['js' => 'console.log("marker-xyz")']]);

        $view = $this->blade('<x-webfloo-layout>x</x-webfloo-layout>');

        $view->assertDontSee('marker-xyz');
    }

    public function test_layout_renders_custom_js_when_flag_enabled(): void
    {
        config()->set('webfloo.features.custom_js', true);
        app(ThemeService::class)->saveConfig(['custom' => ['js' => 'console.log("marker-xyz")']]);

        $view = $this->blade('<x-webfloo-layout>x</x-webfloo-layout>');

        $view->assertSee('marker-xyz');
    }

    public function test_layout_renders_favicon_link_when_setting_present(): void
    {
        Setting::set('favicon', 'site/favicon.png');

        $view = $this->blade('<x-webfloo-layout>x</x-webfloo-layout>');

        $view->assertSee('rel="icon"', false);
        $view->assertSee('/storage/site/favicon.png', false);
    }
}
