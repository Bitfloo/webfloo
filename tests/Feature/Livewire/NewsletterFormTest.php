<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Livewire;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Webfloo\Livewire\NewsletterForm;
use Webfloo\Models\NewsletterSubscriber;
use Webfloo\Tests\TestCase;

class NewsletterFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('webfloo.features.frontend', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear(NewsletterForm::RATE_LIMITER_PREFIX.'127.0.0.1');
    }

    public function test_submit_creates_active_subscriber(): void
    {
        Livewire::test(NewsletterForm::class)
            ->set('email', 'jan@example.com')
            ->call('subscribe')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'jan@example.com',
            'is_active' => true,
            'source' => 'footer',
        ]);

        $subscriber = NewsletterSubscriber::firstOrFail();
        $this->assertNotNull($subscriber->subscribed_at);
    }

    public function test_email_is_required_and_must_be_valid(): void
    {
        Livewire::test(NewsletterForm::class)
            ->set('email', '')
            ->call('subscribe')
            ->assertHasErrors(['email' => 'required']);

        Livewire::test(NewsletterForm::class)
            ->set('email', 'not-an-email')
            ->call('subscribe')
            ->assertHasErrors(['email' => 'email']);

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_honeypot_fakes_success_without_storing(): void
    {
        Livewire::test(NewsletterForm::class)
            ->set('email', 'bot@example.com')
            ->set('website', 'http://spam.example')
            ->call('subscribe')
            ->assertSet('submitted', true);

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_duplicate_email_shows_success_without_second_row(): void
    {
        NewsletterSubscriber::factory()->create(['email' => 'jan@example.com']);

        // Anti-enumeration: an existing address must be indistinguishable
        // from a fresh signup for the visitor.
        Livewire::test(NewsletterForm::class)
            ->set('email', 'jan@example.com')
            ->call('subscribe')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_rate_limit_blocks_fourth_attempt(): void
    {
        foreach ([1, 2, 3] as $i) {
            Livewire::test(NewsletterForm::class)
                ->set('email', "user{$i}@example.com")
                ->call('subscribe')
                ->assertHasNoErrors();
        }

        Livewire::test(NewsletterForm::class)
            ->set('email', 'user4@example.com')
            ->call('subscribe')
            ->assertHasErrors(['form']);

        $this->assertDatabaseCount('newsletter_subscribers', 3);
    }

    public function test_source_prop_is_persisted(): void
    {
        Livewire::test(NewsletterForm::class, ['source' => 'blog'])
            ->set('email', 'jan@example.com')
            ->call('subscribe')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'jan@example.com',
            'source' => 'blog',
        ]);
    }
}
