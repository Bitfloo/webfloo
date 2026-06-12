<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Frontend;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Post;
use Webfloo\Models\Setting;
use Webfloo\Tests\TestCase;

class BlogFeedTest extends TestCase
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

    public function test_feed_lists_published_posts_as_rss(): void
    {
        Setting::set('site_name', 'Acme Blog');

        Post::factory()->published()->create([
            'title' => ['pl' => 'Wpis', 'en' => 'Fresh article'],
            'slug' => 'fresh-article',
            'excerpt' => ['pl' => 'Zajawka', 'en' => 'Short summary'],
        ]);

        $response = $this->get('/blog/feed');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->assertSee('<rss version="2.0"', false)
            ->assertSee('Acme Blog', false)
            ->assertSee('Fresh article', false)
            ->assertSee('http://localhost/blog/fresh-article', false)
            ->assertSee('Short summary', false);
    }

    public function test_feed_excludes_drafts_and_scheduled_posts(): void
    {
        Post::factory()->draft()->create([
            'title' => ['pl' => 'Szkic', 'en' => 'Hidden draft entry'],
        ]);
        Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
            'title' => ['pl' => 'Przyszly', 'en' => 'Scheduled future entry'],
        ]);

        $this->get('/blog/feed')
            ->assertOk()
            ->assertDontSee('Hidden draft entry')
            ->assertDontSee('Scheduled future entry');
    }

    public function test_feed_escapes_xml_special_characters_in_titles(): void
    {
        Post::factory()->published()->create([
            'title' => ['pl' => 'A & B', 'en' => 'Ampersand & <Brackets>'],
        ]);

        $response = $this->get('/blog/feed');

        $response->assertOk();
        $xml = $response->getContent();
        $this->assertIsString($xml);
        $this->assertStringNotContainsString('Ampersand & <Brackets>', $xml);
        $this->assertStringContainsString('Ampersand &amp; &lt;Brackets&gt;', $xml);
    }

    public function test_feed_is_not_swallowed_by_post_slug_route(): void
    {
        // /blog/feed must be registered before /blog/{slug}.
        $this->get('/blog/feed')->assertOk();
    }
}
