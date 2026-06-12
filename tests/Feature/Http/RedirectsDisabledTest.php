<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Http;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Redirect;
use Webfloo\Tests\TestCase;

class RedirectsDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_flag_defaults_to_disabled(): void
    {
        $this->assertFalse(config('webfloo.features.redirects'));
    }

    public function test_redirect_rows_are_ignored_when_module_disabled(): void
    {
        Redirect::create(['from_path' => '/old-page', 'to_path' => '/new-page']);

        $this->get('/old-page')->assertNotFound();
    }
}

/**
 * A host with a stale published config/webfloo.php (features array from
 * before the redirects module existed) must NOT get the module activated:
 * opt-in modules fail closed on missing flags.
 */
class RedirectsStaleConfigTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $features = $app['config']->get('webfloo.features');
        unset($features['redirects']);
        $app['config']->set('webfloo.features', $features);
    }

    public function test_missing_flag_keeps_redirects_module_off(): void
    {
        Redirect::create(['from_path' => '/old-page', 'to_path' => '/new-page']);

        $this->get('/old-page')->assertNotFound();
    }
}
