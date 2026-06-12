<?php

declare(strict_types=1);

namespace Webfloo\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Webfloo\Models\Page;
use Webfloo\Tests\TestCase;

final class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_translatable_title_stores_both_locales_as_json(): void
    {
        $page = Page::factory()->create([
            'title' => ['pl' => 'O nas', 'en' => 'About us'],
        ]);

        $raw = DB::table('pages')->where('id', $page->id)->value('title');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertSame('O nas', $decoded['pl']);
        $this->assertSame('About us', $decoded['en']);
    }

    public function test_parent_child_relationship_resolves(): void
    {
        $parent = Page::factory()->create(['slug' => 'services']);
        $child = Page::factory()->create(['slug' => 'web', 'parent_id' => $parent->id]);

        $this->assertTrue($child->parent->is($parent));
        $this->assertTrue($parent->children->first()->is($child));
    }

    public function test_url_builds_nested_path_from_ancestor_chain(): void
    {
        $root = Page::factory()->create(['slug' => 'services']);
        $mid = Page::factory()->create(['slug' => 'web', 'parent_id' => $root->id]);
        $leaf = Page::factory()->create(['slug' => 'laravel', 'parent_id' => $mid->id]);

        $this->assertSame('/services', $root->url);
        $this->assertSame('/services/web', $mid->fresh()->url);
        $this->assertSame('/services/web/laravel', $leaf->fresh()->url);
    }

    public function test_soft_delete_excludes_page_from_published_scope(): void
    {
        $page = Page::factory()->published()->create();

        $page->delete();

        $this->assertSame(0, Page::published()->count());
        $this->assertSame(1, Page::withTrashed()->count());
    }
}
