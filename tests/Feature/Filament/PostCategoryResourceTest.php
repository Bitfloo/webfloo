<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\PostCategoryResource;
use Webfloo\Filament\Resources\PostCategoryResource\Pages\ListPostCategories;
use Webfloo\Tests\TestCase;

final class PostCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(PostCategoryResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(ListPostCategories::class)->assertOk();
    }

    public function test_resource_is_inaccessible_when_blog_feature_flag_is_off(): void
    {
        config(['webfloo.features.blog' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'post_category')]);

        $this->assertFalse(PostCategoryResource::canAccess());

        $this->actingAs($user)
            ->get(PostCategoryResource::getUrl('index'))
            ->assertForbidden();
    }
}
