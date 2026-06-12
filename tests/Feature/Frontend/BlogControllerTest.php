<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Frontend;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Post;
use Webfloo\Tests\TestCase;

class BlogControllerTest extends TestCase
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

    public function test_blog_index_lists_published_posts(): void
    {
        Post::factory()->published()->create([
            'title' => ['pl' => 'Pierwszy wpis', 'en' => 'First post'],
        ]);
        Post::factory()->draft()->create([
            'title' => ['pl' => 'Szkic', 'en' => 'Hidden draft post'],
        ]);

        $this->get('/blog')
            ->assertOk()
            ->assertSee('First post')
            ->assertDontSee('Hidden draft post');
    }

    public function test_blog_show_renders_post_and_increments_views(): void
    {
        $post = Post::factory()->published()->create([
            'title' => ['pl' => 'Wpis', 'en' => 'A great article'],
            'views_count' => 5,
        ]);

        $this->get($post->url)
            ->assertOk()
            ->assertSee('A great article');

        $this->assertSame(6, $post->fresh()?->views_count);
    }

    public function test_blog_show_returns_branded_404_for_draft(): void
    {
        $post = Post::factory()->draft()->create();

        $this->get('/blog/'.$post->slug)
            ->assertNotFound()
            ->assertSee('Strona nie znaleziona');
    }

    public function test_soft_deleted_post_returns_404(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'trashed-post']);
        $post->delete();

        $this->get('/blog/trashed-post')->assertNotFound();
    }

    public function test_blog_show_preserves_rich_text_markup(): void
    {
        $post = Post::factory()->published()->create([
            'content' => [
                'pl' => '<h2>Naglowek</h2>',
                'en' => '<h2>Section heading</h2><blockquote><p>Quoted wisdom</p></blockquote><pre><code>echo 1;</code></pre>',
            ],
        ]);

        $this->get($post->url)
            ->assertOk()
            ->assertSee('<h2>Section heading</h2>', false)
            ->assertSee('<blockquote>', false)
            ->assertSee('<code>', false);
    }

    public function test_blog_show_strips_script_tags(): void
    {
        $post = Post::factory()->published()->create([
            'content' => [
                'pl' => 'tresc',
                'en' => '<p>Safe paragraph</p><script>alert(1)</script>',
            ],
        ]);

        $this->get($post->url)
            ->assertOk()
            ->assertSee('Safe paragraph')
            ->assertDontSee('alert(1)', false);
    }

    public function test_blog_index_search_filters_by_title(): void
    {
        Post::factory()->published()->create([
            'title' => ['pl' => 'Laravel w praktyce', 'en' => 'Laravel in practice'],
        ]);
        Post::factory()->published()->create([
            'title' => ['pl' => 'Kuchnia wloska', 'en' => 'Italian cooking'],
        ]);

        $this->get('/blog?q=Laravel')
            ->assertOk()
            ->assertSee('Laravel in practice')
            ->assertDontSee('Italian cooking');
    }

    public function test_scheduled_post_returns_404_before_publish_date(): void
    {
        Post::factory()->create([
            'slug' => 'future-post',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->get('/blog/future-post')->assertNotFound();
    }

    public function test_blog_index_excludes_scheduled_posts(): void
    {
        Post::factory()->published()->create([
            'title' => ['pl' => 'Na zywo', 'en' => 'Live post'],
        ]);
        Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
            'title' => ['pl' => 'Przyszly', 'en' => 'Scheduled future post'],
        ]);

        $this->get('/blog')
            ->assertOk()
            ->assertSee('Live post')
            ->assertDontSee('Scheduled future post');
    }

    public function test_post_url_uses_named_route(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'my-post']);

        $this->assertSame('/blog/my-post', $post->url);
    }
}
