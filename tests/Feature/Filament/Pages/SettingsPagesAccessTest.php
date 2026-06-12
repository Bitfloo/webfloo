<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Pages\PageSettings\ContactPageSettings;
use Webfloo\Filament\Pages\PageSettings\HomePageSettings;
use Webfloo\Filament\Pages\SiteSettings;
use Webfloo\Tests\TestCase;

/**
 * webfloo.pages.* flags must gate ACCESS, not just navigation — a flag
 * the host turned off means "this page is not part of this site", so a
 * permission holder must not reach it by URL either (consistent with
 * how every Resource folds its feature flag into canAccess()).
 */
class SettingsPagesAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_settings_blocked_when_pages_flag_off(): void
    {
        config()->set('webfloo.pages.home', false);
        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'home_page_settings')]));

        $this->assertFalse(HomePageSettings::canAccess());
        $this->get(HomePageSettings::getUrl())->assertForbidden();
    }

    public function test_home_page_settings_accessible_with_flag_on_and_permission(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'home_page_settings')]));

        $this->assertTrue(HomePageSettings::canAccess());
    }

    public function test_contact_page_settings_blocked_when_pages_flag_off(): void
    {
        config()->set('webfloo.pages.contact', false);
        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'contact_page_settings')]));

        $this->assertFalse(ContactPageSettings::canAccess());
    }

    public function test_site_settings_unaffected_by_pages_flags(): void
    {
        config()->set('webfloo.pages.home', false);
        config()->set('webfloo.pages.contact', false);
        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'site_settings')]));

        $this->assertTrue(SiteSettings::canAccess());
    }

    public function test_user_without_permission_cannot_access_any_settings_page(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->get(SiteSettings::getUrl())->assertForbidden();
        $this->get(HomePageSettings::getUrl())->assertForbidden();
        $this->get(ContactPageSettings::getUrl())->assertForbidden();
    }

    public function test_user_with_view_permission_can_mount_site_settings(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'site_settings')]));

        Livewire::test(SiteSettings::class)->assertOk();
    }

    public function test_user_with_view_permission_can_mount_home_page_settings(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'home_page_settings')]));

        Livewire::test(HomePageSettings::class)->assertOk();
    }

    public function test_user_with_view_permission_can_mount_contact_page_settings(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view', 'contact_page_settings')]));

        Livewire::test(ContactPageSettings::class)->assertOk();
    }
}
