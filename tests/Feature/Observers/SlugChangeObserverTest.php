<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Observers;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Models\Page;
use Webfloo\Models\Post;
use Webfloo\Models\Project;
use Webfloo\Models\Redirect;
use Webfloo\Tests\TestCase;

class SlugChangeObserverTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('webfloo.features.redirects', true);
    }

    public function test_page_slug_change_creates_301_redirect(): void
    {
        $page = Page::factory()->published()->create(['slug' => 'old-about']);

        $page->update(['slug' => 'new-about']);

        $this->assertDatabaseHas('redirects', [
            'from_path' => '/old-about',
            'to_path' => '/new-about',
            'status_code' => 301,
            'is_active' => true,
        ]);
    }

    public function test_nested_page_redirect_uses_full_path(): void
    {
        $parent = Page::factory()->published()->create(['slug' => 'services']);
        $child = Page::factory()->published()->create(['slug' => 'old-web', 'parent_id' => $parent->id]);

        $child->update(['slug' => 'new-web']);

        $this->assertDatabaseHas('redirects', [
            'from_path' => '/services/old-web',
            'to_path' => '/services/new-web',
        ]);
    }

    public function test_post_slug_change_creates_blog_redirect(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'old-post']);

        $post->update(['slug' => 'new-post']);

        $this->assertDatabaseHas('redirects', [
            'from_path' => '/blog/old-post',
            'to_path' => '/blog/new-post',
        ]);
    }

    public function test_project_slug_change_creates_portfolio_redirect(): void
    {
        $project = Project::factory()->create(['slug' => 'old-project']);

        $project->update(['slug' => 'new-project']);

        $this->assertDatabaseHas('redirects', [
            'from_path' => '/portfolio/old-project',
            'to_path' => '/portfolio/new-project',
        ]);
    }

    public function test_no_redirect_when_slug_unchanged(): void
    {
        $page = Page::factory()->published()->create(['slug' => 'stable']);

        $page->update(['sort_order' => 5]);

        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_title_rename_that_regenerates_slug_creates_redirect(): void
    {
        // HasSlug regenerates the slug when the source title changes,
        // so a plain title rename must leave a redirect behind too.
        $page = Page::factory()->published()->create([
            'title' => ['pl' => 'Stary', 'en' => 'Old name'],
            'slug' => 'old-name',
        ]);

        $page->update(['title' => ['pl' => 'Nowy', 'en' => 'Fresh name']]);

        $this->assertSame('fresh-name', $page->refresh()->slug);
        $this->assertDatabaseHas('redirects', [
            'from_path' => '/old-name',
            'to_path' => '/fresh-name',
        ]);
    }

    public function test_renaming_back_removes_inverse_rule_instead_of_looping(): void
    {
        $page = Page::factory()->published()->create(['slug' => 'alpha']);

        $page->update(['slug' => 'beta']);
        $page->update(['slug' => 'alpha']);

        $this->assertDatabaseHas('redirects', ['from_path' => '/beta', 'to_path' => '/alpha']);
        $this->assertDatabaseMissing('redirects', ['from_path' => '/alpha']);
    }

    public function test_simultaneous_reparent_and_rename_skips_redirect(): void
    {
        // The old path cannot be derived from post-save state when the
        // parent changed in the same update — no redirect beats a wrong one.
        $oldParent = Page::factory()->published()->create(['slug' => 'services']);
        $newParent = Page::factory()->published()->create(['slug' => 'products']);
        $child = Page::factory()->published()->create(['slug' => 'web', 'parent_id' => $oldParent->id]);

        $child->update(['slug' => 'sites', 'parent_id' => $newParent->id]);

        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_repeated_rename_updates_existing_rule(): void
    {
        $page = Page::factory()->published()->create(['slug' => 'first']);

        $page->update(['slug' => 'second']);
        $page->update(['slug' => 'first']);
        $page->update(['slug' => 'third']);

        $this->assertDatabaseHas('redirects', ['from_path' => '/first', 'to_path' => '/third']);
        $this->assertSame(2, Redirect::count());
    }
}
