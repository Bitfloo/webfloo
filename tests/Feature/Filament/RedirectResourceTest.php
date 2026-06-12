<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\RedirectResource;
use Webfloo\Filament\Resources\RedirectResource\Pages\CreateRedirect;
use Webfloo\Filament\Resources\RedirectResource\Pages\EditRedirect;
use Webfloo\Filament\Resources\RedirectResource\Pages\ListRedirects;
use Webfloo\Models\Redirect;
use Webfloo\Tests\TestCase;

final class RedirectResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('webfloo.features.redirects', true);
    }

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(RedirectResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(ListRedirects::class)->assertOk();
    }

    public function test_resource_is_inaccessible_when_redirects_feature_flag_is_off(): void
    {
        config(['webfloo.features.redirects' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'redirect')]);

        $this->assertFalse(RedirectResource::canAccess());

        $this->actingAs($user)
            ->get(RedirectResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_index_renders_redirect_records(): void
    {
        $redirects = collect([
            Redirect::create(['from_path' => '/a', 'to_path' => '/b']),
            Redirect::create(['from_path' => '/c', 'to_path' => '/d']),
        ]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(ListRedirects::class)
            ->assertCanSeeTableRecords($redirects);
    }

    public function test_is_active_filter_shows_only_active_redirects(): void
    {
        $active = Redirect::create(['from_path' => '/on', 'to_path' => '/x']);
        $inactive = Redirect::create(['from_path' => '/off', 'to_path' => '/y', 'is_active' => false]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(ListRedirects::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive]);
    }

    public function test_create_persists_redirect_with_normalized_from_path(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(CreateRedirect::class)
            ->fillForm([
                'from_path' => 'old-url/',
                'to_path' => '/new-url',
                'status_code' => 302,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('redirects', [
            'from_path' => '/old-url',
            'to_path' => '/new-url',
            'status_code' => 302,
        ]);
    }

    public function test_create_requires_from_and_to_path(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(CreateRedirect::class)
            ->fillForm(['from_path' => '', 'to_path' => ''])
            ->call('create')
            ->assertHasFormErrors(['from_path' => 'required', 'to_path' => 'required']);
    }

    public function test_create_rejects_external_url_target(): void
    {
        // Open-redirect guard: targets must be site-relative paths.
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(CreateRedirect::class)
            ->fillForm(['from_path' => '/old', 'to_path' => 'https://evil.example'])
            ->call('create')
            ->assertHasFormErrors(['to_path']);
    }

    public function test_create_rejects_duplicate_from_path(): void
    {
        Redirect::create(['from_path' => '/taken', 'to_path' => '/x']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(CreateRedirect::class)
            ->fillForm(['from_path' => '/taken', 'to_path' => '/y'])
            ->call('create')
            ->assertHasFormErrors(['from_path' => 'unique']);
    }

    public function test_edit_form_prefills_existing_redirect(): void
    {
        $redirect = Redirect::create(['from_path' => '/from', 'to_path' => '/to', 'status_code' => 302]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(EditRedirect::class, ['record' => $redirect->getRouteKey()])
            ->assertFormSet([
                'from_path' => '/from',
                'to_path' => '/to',
                'status_code' => 302,
            ]);
    }

    public function test_edit_saves_target_and_active_toggle(): void
    {
        $redirect = Redirect::create(['from_path' => '/from', 'to_path' => '/to']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(EditRedirect::class, ['record' => $redirect->getRouteKey()])
            ->fillForm(['to_path' => '/elsewhere', 'is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $redirect->refresh();
        $this->assertSame('/elsewhere', $redirect->to_path);
        $this->assertFalse($redirect->is_active);
    }

    public function test_authorized_user_can_delete_redirect(): void
    {
        $redirect = Redirect::create(['from_path' => '/gone', 'to_path' => '/x']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(ListRedirects::class)
            ->callTableAction('delete', $redirect)
            ->assertOk();

        $this->assertDatabaseMissing('redirects', ['id' => $redirect->id]);
    }

    public function test_bulk_delete_removes_selected_redirects(): void
    {
        $redirects = collect([
            Redirect::create(['from_path' => '/a', 'to_path' => '/b']),
            Redirect::create(['from_path' => '/c', 'to_path' => '/d']),
        ]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'redirect')]));

        Livewire::test(ListRedirects::class)
            ->callTableBulkAction('delete', $redirects)
            ->assertOk();

        $this->assertDatabaseCount('redirects', 0);
    }
}
