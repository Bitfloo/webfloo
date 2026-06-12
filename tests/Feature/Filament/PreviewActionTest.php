<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Webfloo\Filament\Resources\PageResource\Pages\EditPage;
use Webfloo\Models\Page;
use Webfloo\Tests\TestCase;

class PreviewActionTest extends TestCase
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

    /**
     * RichEditor's TipTap cast rejects the factory's empty-array content
     * on edit-page mount, so preview-action tests use a minimal valid doc.
     */
    private function makeEditablePage(): Page
    {
        return Page::factory()->draft()->create([
            'content' => [
                'type' => 'doc',
                'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Body']]],
                ],
            ],
        ]);
    }

    public function test_edit_page_shows_preview_action_with_signed_url(): void
    {
        Carbon::setTestNow('2026-06-12 12:00:00');

        $page = $this->makeEditablePage();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'page')]));

        $expected = URL::temporarySignedRoute('webfloo.preview.page', now()->addHour(), ['page' => $page->id]);

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertActionVisible('preview')
            ->assertActionHasUrl('preview', $expected);
    }

    public function test_edit_post_shows_preview_action(): void
    {
        // EditPost cannot be mounted in package tests: Post::content is
        // translatable and RichEditor's TipTap cast crashes on the
        // locale-keyed array without the host-level filament spatie
        // translatable plugin (same documented blocker as
        // ProjectResourceTest / FaqResourceTest edit-mount skips).
        // The shared PreviewAction mechanism is pinned via EditPage above.
        $this->markTestSkipped(
            'EditPost mount blocked by RichEditor translatable state cast; '
            .'PreviewAction wiring identical to EditPage (PreviewAction::make).'
        );
    }

    public function test_preview_action_hidden_when_frontend_disabled(): void
    {
        $page = $this->makeEditablePage();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'page')]));

        config(['webfloo.features.frontend' => false]);

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertActionHidden('preview');
    }
}
