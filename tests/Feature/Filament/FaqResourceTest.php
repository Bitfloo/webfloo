<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\FaqResource;
use Webfloo\Filament\Resources\FaqResource\Pages\ListFaqs;
use Webfloo\Tests\TestCase;

final class FaqResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(FaqResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'faq')]));

        Livewire::test(ListFaqs::class)->assertOk();
    }

    public function test_resource_is_inaccessible_when_faq_feature_flag_is_off(): void
    {
        config(['webfloo.features.faq' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'faq')]);

        $this->assertFalse(FaqResource::canAccess());

        $this->actingAs($user)
            ->get(FaqResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_resource_is_accessible_when_faq_feature_flag_is_on(): void
    {
        config(['webfloo.features.faq' => true]);

        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'faq')]));

        $this->assertTrue(FaqResource::canAccess());
    }
}
