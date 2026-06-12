<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Components;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Webfloo\Models\Setting;
use Webfloo\Tests\TestCase;

class SeoComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_title_with_site_name_suffix(): void
    {
        config()->set('app.name', 'Acme');

        $view = $this->blade(
            '<x-webfloo-seo :data="$data" />',
            ['data' => ['title' => 'About us', 'description' => null, 'image' => null, 'no_index' => false]]
        );

        $view->assertSee('<title>About us | Acme</title>', false);
        $view->assertSee('og:title', false);
    }

    public function test_renders_bare_site_name_when_no_data(): void
    {
        config()->set('app.name', 'Acme');

        $view = $this->blade('<x-webfloo-seo />');

        $view->assertSee('<title>Acme</title>', false);
    }

    public function test_renders_noindex_robots_when_flag_set(): void
    {
        $view = $this->blade(
            '<x-webfloo-seo :data="$data" />',
            ['data' => ['title' => 'Draft', 'description' => null, 'image' => null, 'no_index' => true]]
        );

        $view->assertSee('noindex,nofollow', false);
    }

    public function test_renders_index_robots_by_default(): void
    {
        $view = $this->blade('<x-webfloo-seo />');

        $view->assertSee('index,follow', false);
    }

    public function test_renders_description_and_og_description(): void
    {
        $view = $this->blade(
            '<x-webfloo-seo :data="$data" />',
            ['data' => ['title' => 'T', 'description' => 'A page about things.', 'image' => null, 'no_index' => false]]
        );

        $view->assertSee('name="description" content="A page about things."', false);
        $view->assertSee('property="og:description"', false);
    }

    public function test_omits_description_meta_when_null(): void
    {
        $view = $this->blade(
            '<x-webfloo-seo :data="$data" />',
            ['data' => ['title' => 'T', 'description' => null, 'image' => null, 'no_index' => false]]
        );

        $view->assertDontSee('name="description"', false);
    }

    public function test_renders_absolute_og_image_from_relative_path(): void
    {
        URL::forceRootUrl('https://example.com');

        $view = $this->blade(
            '<x-webfloo-seo :data="$data" />',
            ['data' => ['title' => 'T', 'description' => null, 'image' => 'posts/cover.jpg', 'no_index' => false]]
        );

        $view->assertSee('property="og:image" content="http://example.com/storage/posts/cover.jpg"', false);
    }

    public function test_keeps_absolute_og_image_url_untouched(): void
    {
        $view = $this->blade(
            '<x-webfloo-seo :data="$data" />',
            ['data' => ['title' => 'T', 'description' => null, 'image' => 'https://cdn.example.com/img.png', 'no_index' => false]]
        );

        $view->assertSee('property="og:image" content="https://cdn.example.com/img.png"', false);
    }

    public function test_renders_canonical_when_provided(): void
    {
        $view = $this->blade('<x-webfloo-seo canonical="https://example.com/about" />');

        $view->assertSee('<link rel="canonical" href="https://example.com/about">', false);
    }

    public function test_site_name_setting_overrides_app_name(): void
    {
        config()->set('app.name', 'Acme');
        Setting::set('site_name', 'Custom Site');

        $view = $this->blade('<x-webfloo-seo />');

        $view->assertSee('<title>Custom Site</title>', false);
    }
}
