<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Webfloo\Models\Post;
use Webfloo\Models\PostCategory;
use Webfloo\Tests\Models\User;
use Webfloo\Tests\TestCase;

final class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_and_author_relationships_resolve(): void
    {
        $category = PostCategory::factory()->create();
        $author = User::factory()->create();

        $post = Post::factory()->create([
            'post_category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $this->assertTrue($post->category->is($category));
        $this->assertTrue($post->author->is($author));
    }

    public function test_increment_views_persists(): void
    {
        $post = Post::factory()->create(['views_count' => 7]);

        $post->incrementViews();

        $this->assertSame(8, $post->fresh()->views_count);
    }

    public function test_url_accessor_falls_back_to_path_without_named_route(): void
    {
        // Frontend module is OFF in the default test environment, so the
        // named route does not exist and the accessor uses the literal path.
        $post = Post::factory()->create(['slug' => 'hello']);

        $this->assertSame('/blog/hello', $post->url);
    }

    public function test_translatable_content_stores_both_locales_as_json(): void
    {
        $post = Post::factory()->create([
            'content' => ['pl' => '<p>Tresc</p>', 'en' => '<p>Body</p>'],
        ]);

        $raw = DB::table('posts')->where('id', $post->id)->value('content');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertSame('<p>Tresc</p>', $decoded['pl']);
        $this->assertSame('<p>Body</p>', $decoded['en']);
    }
}
