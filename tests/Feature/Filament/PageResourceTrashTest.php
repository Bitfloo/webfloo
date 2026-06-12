<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Webfloo\Filament\Resources\PageResource\Pages\ListPages;
use Webfloo\Models\Page;
use Webfloo\Tests\TestCase;

final class PageResourceTrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_trashed_page_hidden_by_default_and_visible_under_trashed_filter(): void
    {
        $this->actingAs($this->makeAdmin(['view_any_page']));

        $active = Page::factory()->published()->create();
        $trashed = Page::factory()->published()->create();
        $trashed->delete();

        Livewire::test(ListPages::class)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$trashed])
            ->filterTable('trashed', false)
            ->assertCanSeeTableRecords([$trashed])
            ->assertCanNotSeeTableRecords([$active]);
    }

    public function test_restore_action_restores_trashed_page(): void
    {
        $this->actingAs($this->makeAdmin(['view_any_page']));

        $page = Page::factory()->published()->create();
        $page->delete();

        Livewire::test(ListPages::class)
            ->filterTable('trashed', false)
            ->callTableAction('restore', $page);

        $this->assertFalse($page->fresh()?->trashed());
    }

    public function test_force_delete_permanently_removes_page(): void
    {
        $this->actingAs($this->makeAdmin(['view_any_page']));

        $page = Page::factory()->published()->create();
        $page->delete();

        Livewire::test(ListPages::class)
            ->filterTable('trashed', false)
            ->callTableAction('forceDelete', $page);

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }
}
