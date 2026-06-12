<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\PostResource\Pages\ListPosts;
use Webfloo\Models\Post;
use Webfloo\Tests\TestCase;

final class PostResourceTrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_trashed_post_visible_under_trashed_filter_and_restorable(): void
    {
        $this->actingAs($this->makeAdmin(['ViewAny:Post']));

        $post = Post::factory()->published()->create();
        $post->delete();

        Livewire::test(ListPosts::class)
            ->assertCanNotSeeTableRecords([$post])
            ->filterTable('trashed', false)
            ->assertCanSeeTableRecords([$post])
            ->callTableAction('restore', $post);

        $this->assertFalse($post->fresh()?->trashed());
    }
}
