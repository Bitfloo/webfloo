<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Page;
use Webfloo\Models\Post;
use Webfloo\Models\Project;
use Webfloo\Sitemap\SitemapSource;
use Webfloo\Tests\TestCase;

class GenerateSitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        @unlink(public_path('sitemap.xml'));

        parent::tearDown();
    }

    protected function sitemap(): string
    {
        $this->artisan('sitemap:generate')->assertSuccessful();

        $xml = file_get_contents(public_path('sitemap.xml'));
        $this->assertIsString($xml);

        return $xml;
    }

    public function test_sitemap_contains_published_content_from_all_default_sources(): void
    {
        Page::factory()->published()->create(['slug' => 'about-us']);
        Post::factory()->published()->create(['slug' => 'hello-world', 'no_index' => false]);
        Project::factory()->create(['slug' => 'case-x', 'is_active' => true]);

        $xml = $this->sitemap();

        $this->assertStringContainsString('<loc>http://localhost/about-us</loc>', $xml);
        $this->assertStringContainsString('<loc>http://localhost/blog/hello-world</loc>', $xml);
        $this->assertStringContainsString('<loc>http://localhost/portfolio/case-x</loc>', $xml);
    }

    public function test_sitemap_excludes_drafts_no_index_and_inactive_content(): void
    {
        Page::factory()->draft()->create(['slug' => 'secret-draft']);
        Page::factory()->published()->create(['slug' => 'hidden-page', 'no_index' => true]);
        Post::factory()->published()->create(['slug' => 'hidden-post', 'no_index' => true]);
        Project::factory()->create(['slug' => 'inactive-project', 'is_active' => false]);

        $xml = $this->sitemap();

        $this->assertStringNotContainsString('secret-draft', $xml);
        $this->assertStringNotContainsString('hidden-page', $xml);
        $this->assertStringNotContainsString('hidden-post', $xml);
        $this->assertStringNotContainsString('inactive-project', $xml);
    }

    public function test_sitemap_skips_blog_and_portfolio_when_modules_disabled(): void
    {
        config(['webfloo.features.blog' => false, 'webfloo.features.portfolio' => false]);

        Post::factory()->published()->create(['slug' => 'invisible-post', 'no_index' => false]);
        Project::factory()->create(['slug' => 'invisible-project', 'is_active' => true]);

        $xml = $this->sitemap();

        $this->assertStringNotContainsString('invisible-post', $xml);
        $this->assertStringNotContainsString('invisible-project', $xml);
    }

    public function test_sitemap_emits_configured_static_urls(): void
    {
        config(['webfloo.sitemap.static_urls' => [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => '/cennik', 'priority' => '0.9', 'changefreq' => 'monthly'],
        ]]);

        $xml = $this->sitemap();

        $this->assertStringContainsString('<loc>http://localhost/</loc>', $xml);
        $this->assertStringContainsString('<loc>http://localhost/cennik</loc>', $xml);
        $this->assertStringContainsString('<priority>0.9</priority>', $xml);
    }

    public function test_sitemap_emits_hreflang_alternates_for_default_locales(): void
    {
        Page::factory()->published()->create(['slug' => 'about']);

        $xml = $this->sitemap();

        $this->assertStringContainsString('<loc>http://localhost/en/about</loc>', $xml);
        $this->assertStringContainsString('hreflang="pl" href="http://localhost/about"', $xml);
        $this->assertStringContainsString('hreflang="en" href="http://localhost/en/about"', $xml);
        $this->assertStringContainsString('hreflang="x-default" href="http://localhost/about"', $xml);
    }

    public function test_single_locale_config_produces_plain_urls_without_alternates(): void
    {
        config(['webfloo.sitemap.locales' => ['pl']]);

        Page::factory()->published()->create(['slug' => 'about']);

        $xml = $this->sitemap();

        $this->assertStringContainsString('<loc>http://localhost/about</loc>', $xml);
        $this->assertStringNotContainsString('xhtml:link', $xml);
        $this->assertStringNotContainsString('/en/about', $xml);
    }

    public function test_host_can_register_custom_sitemap_source(): void
    {
        config(['webfloo.sitemap.providers' => [CustomSitemapSource::class]]);

        $xml = $this->sitemap();

        $this->assertStringContainsString('<loc>http://localhost/custom-entry</loc>', $xml);
        $this->assertStringNotContainsString('/blog/', $xml);
    }
}

class CustomSitemapSource implements SitemapSource
{
    public function urls(): iterable
    {
        yield ['loc' => '/custom-entry', 'priority' => '0.4', 'changefreq' => 'yearly', 'lastmod' => null];
    }
}
