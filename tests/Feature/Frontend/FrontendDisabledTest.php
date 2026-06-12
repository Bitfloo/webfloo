<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Frontend;

use Illuminate\Support\Facades\Route;
use Webfloo\Models\Post;
use Webfloo\Tests\TestCase;

class FrontendDisabledTest extends TestCase
{
    public function test_frontend_routes_not_registered_by_default(): void
    {
        $this->assertFalse(Route::has('webfloo.home'));
        $this->assertFalse(Route::has('webfloo.blog.index'));
        $this->assertFalse(Route::has('webfloo.portfolio.index'));
        $this->assertFalse(Route::has('webfloo.page.show'));
        $this->assertFalse(Route::has('webfloo.preview.page'));
        $this->assertFalse(Route::has('webfloo.preview.post'));
    }

    public function test_post_url_falls_back_to_literal_path_without_routes(): void
    {
        $post = new Post(['slug' => 'my-post']);

        $this->assertSame('/blog/my-post', $post->url);
    }
}
