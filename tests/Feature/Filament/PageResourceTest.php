<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\PageResource;
use Webfloo\Filament\Resources\PageResource\Pages\ListPages;
use Webfloo\Tests\TestCase;

final class PageResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(PageResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_access_create_page(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(PageResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'page')]));

        Livewire::test(ListPages::class)->assertOk();
    }
}
