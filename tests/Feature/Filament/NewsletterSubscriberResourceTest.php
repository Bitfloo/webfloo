<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\NewsletterSubscriberResource;
use Webfloo\Filament\Resources\NewsletterSubscriberResource\Pages\ListNewsletterSubscribers;
use Webfloo\Tests\TestCase;

final class NewsletterSubscriberResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(NewsletterSubscriberResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_user_with_content_permissions_cannot_access_index(): void
    {
        // PII isolation: holding every content permission must not open
        // the subscriber list — only the dedicated permission does.
        $this->actingAs($this->makeAdmin([
            webfloo_permission('view_any', 'page'),
            webfloo_permission('view_any', 'post'),
        ]))
            ->get(NewsletterSubscriberResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(ListNewsletterSubscribers::class)->assertOk();
    }

    public function test_resource_is_inaccessible_when_newsletter_feature_flag_is_off(): void
    {
        config(['webfloo.features.newsletter' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]);

        $this->assertFalse(NewsletterSubscriberResource::canAccess());

        $this->actingAs($user)
            ->get(NewsletterSubscriberResource::getUrl('index'))
            ->assertForbidden();
    }
}
