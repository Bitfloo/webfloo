<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Http;

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
