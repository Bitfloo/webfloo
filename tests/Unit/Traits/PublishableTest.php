<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Webfloo\Models\Post;
use Webfloo\Tests\TestCase;

/**
 * Exercised through Post (real model + DB) — the trait merges fillable
 * and casts via initializePublishable(), so a dummy class would not pin
 * the integration that matters.
 */
final class PublishableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-12 12:00:00');
    }

    public function test_scope_published_includes_past_and_null_published_at(): void
    {
        Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);
        Post::factory()->create(['status' => 'published', 'published_at' => null]);

        $this->assertSame(2, Post::published()->count());
    }

    public function test_scope_published_excludes_drafts_and_future_dates(): void
    {
        Post::factory()->create(['status' => 'draft', 'published_at' => now()->subDay()]);
        Post::factory()->create(['status' => 'published', 'published_at' => now()->addDay()]);

        $this->assertSame(0, Post::published()->count());
    }

    public function test_scope_draft_returns_only_drafts(): void
    {
        Post::factory()->create(['status' => 'draft']);
        Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);

        $this->assertSame(1, Post::draft()->count());
    }

    public function test_scope_scheduled_returns_only_future_published(): void
    {
        $scheduled = Post::factory()->create(['status' => 'published', 'published_at' => now()->addDay()]);
        Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);
        Post::factory()->create(['status' => 'draft', 'published_at' => now()->addDay()]);

        $this->assertSame([$scheduled->id], Post::scheduled()->pluck('id')->all());
    }

    public function test_is_published_false_for_future_published_at(): void
    {
        $post = Post::factory()->create(['status' => 'published', 'published_at' => now()->addDay()]);

        $this->assertFalse($post->isPublished());
    }

    public function test_is_published_true_for_null_published_at(): void
    {
        $post = Post::factory()->create(['status' => 'published', 'published_at' => null]);

        $this->assertTrue($post->isPublished());
    }

    public function test_publish_sets_published_at_when_null_and_preserves_existing(): void
    {
        $fresh = Post::factory()->create(['status' => 'draft', 'published_at' => null]);
        $fresh->publish();
        $this->assertSame('published', $fresh->refresh()->status);
        $this->assertNotNull($fresh->published_at);

        $original = now()->subWeek();
        $dated = Post::factory()->create(['status' => 'draft', 'published_at' => $original]);
        $dated->publish();
        $this->assertTrue($original->equalTo($dated->refresh()->published_at));
    }

    public function test_unpublish_sets_draft_status(): void
    {
        $post = Post::factory()->published()->create();

        $post->unpublish();

        $this->assertSame('draft', $post->refresh()->status);
        $this->assertTrue($post->isDraft());
    }
}
