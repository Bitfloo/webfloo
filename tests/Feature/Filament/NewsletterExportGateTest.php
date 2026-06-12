<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\NewsletterSubscriberResource\Pages\ListNewsletterSubscribers;
use Webfloo\Tests\TestCase;

/**
 * Newsletter subscriber exports expose PII (e-mails, names, IPs) — the
 * export action must stay hidden without Export:NewsletterSubscriber.
 */
class NewsletterExportGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_action_hidden_without_export_subscriber_permission(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(ListNewsletterSubscribers::class)
            ->assertActionHidden('export');
    }

    public function test_export_action_visible_with_export_subscriber_permission(): void
    {
        $this->actingAs($this->makeAdmin([
            webfloo_permission('view_any', 'newsletter_subscriber'),
            webfloo_permission('export', 'newsletter_subscriber'),
        ]));

        Livewire::test(ListNewsletterSubscribers::class)
            ->assertActionVisible('export');
    }
}
