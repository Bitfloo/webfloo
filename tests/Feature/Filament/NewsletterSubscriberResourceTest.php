<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\NewsletterSubscriberResource;
use Webfloo\Filament\Resources\NewsletterSubscriberResource\Pages\CreateNewsletterSubscriber;
use Webfloo\Filament\Resources\NewsletterSubscriberResource\Pages\EditNewsletterSubscriber;
use Webfloo\Filament\Resources\NewsletterSubscriberResource\Pages\ListNewsletterSubscribers;
use Webfloo\Models\NewsletterSubscriber;
use Webfloo\Tests\TestCase;

final class NewsletterSubscriberResourceTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------------------

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

    public function test_authorized_user_can_access_create_page(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(CreateNewsletterSubscriber::class)->assertOk();
    }

    public function test_authorized_user_can_access_edit_page(): void
    {
        $subscriber = NewsletterSubscriber::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(EditNewsletterSubscriber::class, ['record' => $subscriber->getRouteKey()])->assertOk();
    }

    // ---------------------------------------------------------------------------
    // Feature flag
    // ---------------------------------------------------------------------------

    public function test_resource_is_inaccessible_when_newsletter_feature_flag_is_off(): void
    {
        config(['webfloo.features.newsletter' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]);

        $this->assertFalse(NewsletterSubscriberResource::canAccess());

        $this->actingAs($user)
            ->get(NewsletterSubscriberResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_resource_is_accessible_when_newsletter_feature_flag_is_on(): void
    {
        config(['webfloo.features.newsletter' => true]);

        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        $this->assertTrue(NewsletterSubscriberResource::canAccess());
    }

    // ---------------------------------------------------------------------------
    // Table / index rendering
    // ---------------------------------------------------------------------------

    public function test_index_renders_subscriber_records(): void
    {
        $subscribers = NewsletterSubscriber::factory()->count(3)->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(ListNewsletterSubscribers::class)
            ->assertCanSeeTableRecords($subscribers);
    }

    // ---------------------------------------------------------------------------
    // Table filters
    // ---------------------------------------------------------------------------

    public function test_is_active_filter_shows_only_active_subscribers(): void
    {
        $active = NewsletterSubscriber::factory()->active()->create();
        $unsubscribed = NewsletterSubscriber::factory()->unsubscribed()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(ListNewsletterSubscribers::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$unsubscribed]);
    }

    public function test_is_active_filter_shows_only_inactive_subscribers(): void
    {
        $active = NewsletterSubscriber::factory()->active()->create();
        $unsubscribed = NewsletterSubscriber::factory()->unsubscribed()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(ListNewsletterSubscribers::class)
            ->filterTable('is_active', false)
            ->assertCanSeeTableRecords([$unsubscribed])
            ->assertCanNotSeeTableRecords([$active]);
    }

    public function test_source_filter_narrows_to_selected_source(): void
    {
        $footer = NewsletterSubscriber::factory()->create(['source' => 'footer']);
        $blog = NewsletterSubscriber::factory()->create(['source' => 'blog']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(ListNewsletterSubscribers::class)
            ->filterTable('source', 'footer')
            ->assertCanSeeTableRecords([$footer])
            ->assertCanNotSeeTableRecords([$blog]);
    }

    // ---------------------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------------------

    public function test_create_persists_valid_subscriber(): void
    {
        Carbon::setTestNow('2026-06-12 10:00:00');
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(CreateNewsletterSubscriber::class)
            ->fillForm([
                'email' => 'new@example.com',
                'name' => 'Jan Kowalski',
                'source' => 'footer',
                'is_active' => true,
                'subscribed_at' => '2026-06-12 10:00:00',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $sub = NewsletterSubscriber::latest('id')->first();
        $this->assertSame('new@example.com', $sub->email);
        $this->assertSame('Jan Kowalski', $sub->name);
        $this->assertSame('footer', $sub->source);
        $this->assertTrue($sub->is_active);

        Carbon::setTestNow();
    }

    public function test_create_requires_email(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(CreateNewsletterSubscriber::class)
            ->fillForm(['email' => ''])
            ->call('create')
            ->assertHasFormErrors(['email' => 'required']);
    }

    public function test_create_rejects_invalid_email_format(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(CreateNewsletterSubscriber::class)
            ->fillForm(['email' => 'not-an-email'])
            ->call('create')
            ->assertHasFormErrors(['email' => 'email']);
    }

    public function test_create_rejects_duplicate_email(): void
    {
        NewsletterSubscriber::factory()->create(['email' => 'taken@example.com']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(CreateNewsletterSubscriber::class)
            ->fillForm(['email' => 'taken@example.com'])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    }

    public function test_create_defaults_source_to_footer(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(CreateNewsletterSubscriber::class)
            ->fillForm(['email' => 'check@example.com'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('footer', NewsletterSubscriber::latest('id')->first()->source);
    }

    // ---------------------------------------------------------------------------
    // Edit — prefill + save
    // ---------------------------------------------------------------------------

    public function test_edit_form_prefills_existing_subscriber_data(): void
    {
        $sub = NewsletterSubscriber::factory()->create([
            'email' => 'edit@example.com',
            'name' => 'Anna',
            'source' => 'blog',
        ]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(EditNewsletterSubscriber::class, ['record' => $sub->getRouteKey()])
            ->assertFormSet([
                'email' => 'edit@example.com',
                'name' => 'Anna',
                'source' => 'blog',
            ]);
    }

    public function test_edit_saves_updated_name_and_source(): void
    {
        $sub = NewsletterSubscriber::factory()->create(['name' => 'Old', 'source' => 'footer']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(EditNewsletterSubscriber::class, ['record' => $sub->getRouteKey()])
            ->fillForm(['name' => 'New', 'source' => 'popup'])
            ->call('save')
            ->assertHasNoFormErrors();

        $sub->refresh();
        $this->assertSame('New', $sub->name);
        $this->assertSame('popup', $sub->source);
    }

    public function test_edit_saves_is_active_toggle(): void
    {
        $sub = NewsletterSubscriber::factory()->active()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(EditNewsletterSubscriber::class, ['record' => $sub->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($sub->refresh()->is_active);
    }

    public function test_edit_allows_same_email_on_own_record(): void
    {
        // unique(ignoreRecord: true) must not fire when email is unchanged
        $sub = NewsletterSubscriber::factory()->create(['email' => 'same@example.com']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(EditNewsletterSubscriber::class, ['record' => $sub->getRouteKey()])
            ->fillForm(['email' => 'same@example.com', 'name' => 'Changed'])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    // ---------------------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------------------

    public function test_authorized_user_can_delete_subscriber(): void
    {
        $sub = NewsletterSubscriber::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(ListNewsletterSubscribers::class)
            ->callTableAction('delete', $sub)
            ->assertOk();

        $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $sub->id]);
    }

    public function test_bulk_delete_removes_selected_subscribers(): void
    {
        $subs = NewsletterSubscriber::factory()->count(3)->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'newsletter_subscriber')]));

        Livewire::test(ListNewsletterSubscribers::class)
            ->callTableBulkAction('delete', $subs)
            ->assertOk();

        foreach ($subs as $sub) {
            $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $sub->id]);
        }
    }

    // ---------------------------------------------------------------------------
    // Scopes (HasActive trait)
    // ---------------------------------------------------------------------------

    public function test_scope_active_returns_only_active_subscribers(): void
    {
        NewsletterSubscriber::factory()->active()->count(2)->create();
        NewsletterSubscriber::factory()->unsubscribed()->create();

        $this->assertCount(2, NewsletterSubscriber::active()->get());
    }

    public function test_scope_active_excludes_unsubscribed_subscribers(): void
    {
        NewsletterSubscriber::factory()->unsubscribed()->create();

        $this->assertCount(0, NewsletterSubscriber::active()->get());
    }
}
