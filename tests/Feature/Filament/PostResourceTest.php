<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\PostResource;
use Webfloo\Filament\Resources\PostResource\Pages\ListPosts;
use Webfloo\Tests\TestCase;

final class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(PostResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_access_create_page(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(PostResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post')]));

        Livewire::test(ListPosts::class)->assertOk();
    }

    public function test_resource_is_inaccessible_when_blog_feature_flag_is_off(): void
    {
        config(['webfloo.features.blog' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'post')]);

        $this->assertFalse(PostResource::canAccess());

        $this->actingAs($user)
            ->get(PostResource::getUrl('index'))
            ->assertForbidden();
    }
}
