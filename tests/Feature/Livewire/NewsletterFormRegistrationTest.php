<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Livewire;

use Illuminate\Foundation\Application;
use Livewire\Finder\Finder;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Webfloo\Livewire\NewsletterForm;
use Webfloo\Tests\TestCase;

/**
 * The registration conditional is the exposure boundary for a public,
 * unauthenticated PII-collecting endpoint — pin it so it cannot regress
 * to always-register. Flags must be set pre-boot (provider-time).
 */
class NewsletterFormRegistrationTest extends TestCase
{
    /**
     * @param  Application  $app
     */
    protected function enableFrontendOnly($app): void
    {
        $app['config']->set('webfloo.features.frontend', true);
        $app['config']->set('webfloo.features.newsletter', false);
    }

    /**
     * @param  Application  $app
     */
    protected function enableFrontendAndNewsletter($app): void
    {
        $app['config']->set('webfloo.features.frontend', true);
    }

    protected function resolveComponentClass(): ?string
    {
        // Livewire binds its Finder as the 'livewire.finder' singleton —
        // resolving the class directly would query a fresh, empty instance.
        /** @var Finder $finder */
        $finder = app('livewire.finder');

        return $finder->resolveClassComponentClassName('webfloo-newsletter-form');
    }

    public function test_component_not_registered_when_frontend_disabled(): void
    {
        $this->assertNull($this->resolveComponentClass());
    }

    #[DefineEnvironment('enableFrontendOnly')]
    public function test_component_not_registered_when_newsletter_module_disabled(): void
    {
        $this->assertNull($this->resolveComponentClass());
    }

    #[DefineEnvironment('enableFrontendAndNewsletter')]
    public function test_component_registered_when_frontend_and_newsletter_enabled(): void
    {
        $this->assertSame(NewsletterForm::class, $this->resolveComponentClass());
    }
}
