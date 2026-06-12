<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Frontend;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Webfloo\Models\Page;
use Webfloo\Models\Post;
use Webfloo\Tests\TestCase;

class PreviewControllerTest extends TestCase
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

    public function test_signed_url_renders_draft_page(): void
    {
        $page = Page::factory()->draft()->create([
            'title' => ['pl' => 'Szkic', 'en' => 'Unpublished draft page'],
        ]);

        $url = URL::temporarySignedRoute('webfloo.preview.page', now()->addHour(), ['page' => $page->id]);

        $this->get($url)
            ->assertOk()
            ->assertSee('Unpublished draft page');
    }

    public function test_signed_url_renders_published_page_too(): void
    {
        $page = Page::factory()->published()->create([
            'title' => ['pl' => 'Opublikowana', 'en' => 'Published page'],
        ]);

        $url = URL::temporarySignedRoute('webfloo.preview.page', now()->addHour(), ['page' => $page->id]);

        $this->get($url)->assertOk()->assertSee('Published page');
    }

    public function test_unsigned_url_is_forbidden(): void
    {
        $page = Page::factory()->draft()->create();

        $this->get("/preview/page/{$page->id}")->assertForbidden();
    }

    public function test_tampered_signature_is_forbidden(): void
    {
        $page = Page::factory()->draft()->create();

        $url = URL::temporarySignedRoute('webfloo.preview.page', now()->addHour(), ['page' => $page->id]);

        $this->get($url.'tampered')->assertForbidden();
    }

    public function test_expired_signature_is_forbidden(): void
    {
        $page = Page::factory()->draft()->create();

        $url = URL::temporarySignedRoute('webfloo.preview.page', now()->subMinute(), ['page' => $page->id]);

        $this->get($url)->assertForbidden();
    }

    public function test_signed_url_renders_draft_post(): void
    {
        $post = Post::factory()->draft()->create([
            'title' => ['pl' => 'Szkic wpisu', 'en' => 'Unpublished draft post'],
            'content' => ['pl' => 'tresc', 'en' => 'body'],
        ]);

        $url = URL::temporarySignedRoute('webfloo.preview.post', now()->addHour(), ['post' => $post->id]);

        $this->get($url)
            ->assertOk()
            ->assertSee('Unpublished draft post');
    }

    public function test_soft_deleted_page_preview_returns_404(): void
    {
        $page = Page::factory()->draft()->create();
        $url = URL::temporarySignedRoute('webfloo.preview.page', now()->addHour(), ['page' => $page->id]);
        $page->delete();

        $this->get($url)->assertNotFound();
    }
}
