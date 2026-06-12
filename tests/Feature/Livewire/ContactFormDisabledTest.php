<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Livewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Webfloo\Tests\TestCase;

class ContactFormDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_section_renders_without_form_when_frontend_disabled(): void
    {
        $html = $this->blade('<x-webfloo-contact />')->__toString();

        $this->assertStringNotContainsString('wire:submit', $html);
    }
}
