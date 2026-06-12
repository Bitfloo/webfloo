<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Livewire;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Webfloo\Events\LeadCreated;
use Webfloo\Livewire\ContactForm;
use Webfloo\Models\Lead;
use Webfloo\Tests\TestCase;

class ContactFormTest extends TestCase
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

        RateLimiter::clear('webfloo-contact:127.0.0.1');
    }

    public function test_valid_submission_creates_lead_with_consent(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Jan Kowalski')
            ->set('email', 'jan@example.com')
            ->set('message', 'Potrzebuje wyceny strony.')
            ->set('consent', true)
            ->call('submit')
            ->assertSet('submitted', true);

        $lead = Lead::firstOrFail();
        $this->assertSame('jan@example.com', $lead->email);
        $this->assertSame(Lead::SOURCE_CONTACT_FORM, $lead->source);
        $this->assertSame(Lead::STATUS_NEW, $lead->status);
        $this->assertNotNull($lead->consent_at);
    }

    public function test_submission_dispatches_lead_created_event(): void
    {
        Event::fake([LeadCreated::class]);

        Livewire::test(ContactForm::class)
            ->set('name', 'Jan')
            ->set('email', 'jan@example.com')
            ->set('message', 'Hej')
            ->set('consent', true)
            ->call('submit');

        Event::assertDispatched(LeadCreated::class);
    }

    public function test_filled_honeypot_silently_discards_submission(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Bot')
            ->set('email', 'bot@example.com')
            ->set('message', 'spam')
            ->set('consent', true)
            ->set('website', 'http://spam.example')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertSame(0, Lead::count());
    }

    public function test_invalid_email_fails_validation(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Jan')
            ->set('email', 'not-an-email')
            ->set('message', 'Hej')
            ->set('consent', true)
            ->call('submit')
            ->assertHasErrors(['email']);

        $this->assertSame(0, Lead::count());
    }

    public function test_consent_is_required(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Jan')
            ->set('email', 'jan@example.com')
            ->set('message', 'Hej')
            ->set('consent', false)
            ->call('submit')
            ->assertHasErrors(['consent']);
    }

    public function test_rate_limit_blocks_fourth_attempt(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            Livewire::test(ContactForm::class)
                ->set('name', 'Jan')
                ->set('email', "jan{$i}@example.com")
                ->set('message', 'Hej')
                ->set('consent', true)
                ->call('submit')
                ->assertSet('submitted', true);
        }

        Livewire::test(ContactForm::class)
            ->set('name', 'Jan')
            ->set('email', 'jan4@example.com')
            ->set('message', 'Hej')
            ->set('consent', true)
            ->call('submit')
            ->assertHasErrors(['form']);

        $this->assertSame(3, Lead::count());
    }

    public function test_contact_section_embeds_form_when_frontend_enabled(): void
    {
        $html = $this->blade('<x-webfloo-contact />')->__toString();

        $this->assertStringContainsString('wire:submit', $html);
    }
}
