<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Frontend;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Page;
use Webfloo\Tests\TestCase;

class PageControllerTest extends TestCase
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

    public function test_published_page_returns_200_with_title(): void
    {
        Page::factory()->published()->create([
            'title' => ['pl' => 'O firmie', 'en' => 'About us'],
            'slug' => 'about-us',
        ]);

        $this->get('/about-us')
            ->assertOk()
            ->assertSee('About us');
    }

    public function test_draft_page_returns_404(): void
    {
        Page::factory()->draft()->create(['slug' => 'secret-draft']);

        $this->get('/secret-draft')->assertNotFound();
    }

    public function test_scheduled_page_returns_404_before_publish_date(): void
    {
        Page::factory()->create([
            'slug' => 'future-page',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->get('/future-page')->assertNotFound();
    }

    public function test_nested_page_resolves_by_full_slug_chain(): void
    {
        $parent = Page::factory()->published()->create(['slug' => 'services']);
        Page::factory()->published()->create([
            'slug' => 'web-development',
            'parent_id' => $parent->id,
            'title' => ['pl' => 'Strony WWW', 'en' => 'Web development'],
        ]);

        $this->get('/services/web-development')
            ->assertOk()
            ->assertSee('Web development');
    }

    public function test_nested_page_is_not_served_at_bare_slug(): void
    {
        $parent = Page::factory()->published()->create(['slug' => 'services']);
        Page::factory()->published()->create([
            'slug' => 'web-development',
            'parent_id' => $parent->id,
        ]);

        $this->get('/web-development')->assertNotFound();
    }

    public function test_unknown_path_returns_branded_404(): void
    {
        $this->get('/does-not-exist')
            ->assertNotFound()
            ->assertSee('Strona nie znaleziona');
    }

    public function test_home_route_renders_home_template_page(): void
    {
        Page::factory()->published()->create([
            'slug' => 'home',
            'template' => 'home',
            'title' => ['pl' => 'Start', 'en' => 'Welcome home'],
        ]);

        $this->get('/')->assertOk();
    }

    public function test_home_route_returns_404_without_home_page(): void
    {
        $this->get('/')->assertNotFound();
    }

    public function test_page_renders_rich_editor_content(): void
    {
        Page::factory()->published()->create([
            'slug' => 'rich-page',
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'Unique paragraph marker']],
                    ],
                ],
            ],
        ]);

        $this->get('/rich-page')
            ->assertOk()
            ->assertSee('Unique paragraph marker');
    }

    public function test_unknown_template_falls_back_to_default_view(): void
    {
        Page::factory()->published()->create([
            'slug' => 'odd-template',
            'template' => 'nonexistent-template',
        ]);

        $this->get('/odd-template')->assertOk();
    }

    public function test_robots_txt_served_as_plain_text(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *');
    }
}
