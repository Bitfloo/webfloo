<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Post;
use Webfloo\Models\Setting;
use Webfloo\Tests\TestCase;

/**
 * Exercised through Post (meta_* fields are translatable there, so the
 * trait runs against real spatie/laravel-translatable accessors).
 */
final class HasSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_title_prefers_meta_title_over_title(): void
    {
        $post = Post::factory()->create([
            'title' => ['pl' => 'Tytul', 'en' => 'Title'],
            'meta_title' => ['pl' => 'Meta PL', 'en' => 'Meta EN'],
        ]);

        $this->assertSame('Meta EN', $post->getSeoTitle());
    }

    public function test_seo_title_falls_back_to_title_then_site_name_setting(): void
    {
        $post = Post::factory()->create([
            'title' => ['pl' => 'Tytul', 'en' => 'Title'],
            'meta_title' => null,
        ]);
        $this->assertSame('Title', $post->getSeoTitle());

        Setting::set('site_name', 'Acme Site');
        $untitled = Post::factory()->create([
            'title' => ['pl' => '', 'en' => ''],
            'meta_title' => null,
        ]);
        $this->assertSame('Acme Site', $untitled->getSeoTitle());
    }

    public function test_seo_description_prefers_meta_description(): void
    {
        $post = Post::factory()->create([
            'excerpt' => ['pl' => 'Zajawka', 'en' => 'Excerpt'],
            'meta_description' => ['pl' => 'Meta opis', 'en' => 'Meta description'],
        ]);

        $this->assertSame('Meta description', $post->getSeoDescription());
    }

    public function test_seo_description_strips_tags_and_limits_to_160_chars(): void
    {
        $longExcerpt = '<b>'.str_repeat('word ', 50).'</b>';

        $post = Post::factory()->create([
            'excerpt' => ['pl' => $longExcerpt, 'en' => $longExcerpt],
            'meta_description' => null,
        ]);

        $description = $post->getSeoDescription();

        $this->assertNotNull($description);
        $this->assertStringNotContainsString('<b>', $description);
        $this->assertLessThanOrEqual(163, mb_strlen($description)); // 160 + "..."
        $this->assertStringEndsWith('...', $description);
    }

    public function test_seo_description_falls_back_to_site_description_setting(): void
    {
        Setting::set('site_description', 'Default description');

        $post = Post::factory()->create([
            'excerpt' => ['pl' => '', 'en' => ''],
            'content' => ['pl' => '', 'en' => ''],
            'meta_description' => null,
        ]);

        $this->assertSame('Default description', $post->getSeoDescription());
    }

    public function test_seo_image_precedence_meta_then_featured_then_setting(): void
    {
        $post = Post::factory()->create([
            'meta_image' => 'meta.jpg',
            'featured_image' => 'featured.jpg',
        ]);
        $this->assertSame('meta.jpg', $post->getSeoImage());

        $post->meta_image = null;
        $this->assertSame('featured.jpg', $post->getSeoImage());

        Setting::set('default_og_image', 'og-default.jpg');
        $post->featured_image = null;
        $this->assertSame('og-default.jpg', $post->getSeoImage());
    }

    public function test_get_seo_data_shape_and_no_index_defaults_to_false(): void
    {
        $post = Post::factory()->create([
            'title' => ['pl' => 'Tytul', 'en' => 'Title'],
            'meta_title' => null,
        ]);

        $data = $post->getSeoData();

        $this->assertSame(['title', 'description', 'image', 'no_index'], array_keys($data));
        $this->assertSame('Title', $data['title']);
        $this->assertFalse($data['no_index']);
    }
}
