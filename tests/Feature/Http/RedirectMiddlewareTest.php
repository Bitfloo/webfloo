<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Http;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Page;
use Webfloo\Models\Redirect;
use Webfloo\Tests\TestCase;

class RedirectMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('webfloo.features.redirects', true);
        $app['config']->set('webfloo.features.frontend', true);
    }

    public function test_active_redirect_fires_on_404(): void
    {
        Redirect::create(['from_path' => '/old-page', 'to_path' => '/new-page']);

        $this->get('/old-page')
            ->assertStatus(301)
            ->assertRedirect('/new-page');
    }

    public function test_redirect_increments_hits_count(): void
    {
        $redirect = Redirect::create(['from_path' => '/old', 'to_path' => '/new']);

        $this->get('/old');
        $this->get('/old');

        $this->assertSame(2, $redirect->fresh()?->hits_count);
    }

    public function test_redirect_respects_custom_status_code(): void
    {
        Redirect::create(['from_path' => '/moved', 'to_path' => '/target', 'status_code' => 302]);

        $this->get('/moved')
            ->assertStatus(302)
            ->assertRedirect('/target');
    }

    public function test_live_page_wins_over_stale_redirect(): void
    {
        Page::factory()->published()->create([
            'slug' => 'taken',
            'title' => ['pl' => 'Strona', 'en' => 'Live page'],
        ]);
        Redirect::create(['from_path' => '/taken', 'to_path' => '/elsewhere']);

        $this->get('/taken')
            ->assertOk()
            ->assertSee('Live page');
    }

    public function test_inactive_redirect_results_in_404(): void
    {
        Redirect::create(['from_path' => '/off', 'to_path' => '/x', 'is_active' => false]);

        $this->get('/off')->assertNotFound();
    }

    public function test_non_get_request_is_never_redirected(): void
    {
        Redirect::create(['from_path' => '/old-page', 'to_path' => '/new-page']);

        // GET-only fallback route answers 405 for POST; the point pinned
        // here is that the middleware does not turn it into a redirect.
        $this->post('/old-page')->assertStatus(405);
    }

    public function test_redirect_preserves_query_string(): void
    {
        Redirect::create(['from_path' => '/old-page', 'to_path' => '/new-page']);

        // Symfony normalizes query parameter order alphabetically.
        $this->get('/old-page?utm_source=newsletter&page=2')
            ->assertRedirect('/new-page?page=2&utm_source=newsletter');
    }
}
