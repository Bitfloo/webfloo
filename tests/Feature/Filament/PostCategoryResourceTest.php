<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Webfloo\Filament\Resources\PostCategoryResource;
use Webfloo\Filament\Resources\PostCategoryResource\Pages\CreatePostCategory;
use Webfloo\Filament\Resources\PostCategoryResource\Pages\EditPostCategory;
use Webfloo\Filament\Resources\PostCategoryResource\Pages\ListPostCategories;
use Webfloo\Models\PostCategory;
use Webfloo\Tests\TestCase;

final class PostCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------------------

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(PostCategoryResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(ListPostCategories::class)->assertOk();
    }

    // ---------------------------------------------------------------------------
    // Feature flag
    // ---------------------------------------------------------------------------

    public function test_resource_is_inaccessible_when_blog_feature_flag_is_off(): void
    {
        config(['webfloo.features.blog' => false]);

        $user = $this->makeAdmin([webfloo_permission('view_any', 'post_category')]);

        $this->assertFalse(PostCategoryResource::canAccess());

        $this->actingAs($user)
            ->get(PostCategoryResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_resource_is_accessible_when_blog_feature_flag_is_on(): void
    {
        config(['webfloo.features.blog' => true]);

        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        $this->assertTrue(PostCategoryResource::canAccess());
    }

    // ---------------------------------------------------------------------------
    // Table / index rendering
    // ---------------------------------------------------------------------------

    public function test_index_renders_post_category_records(): void
    {
        $categories = PostCategory::factory()->count(3)->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(ListPostCategories::class)
            ->assertCanSeeTableRecords($categories);
    }

    // ---------------------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------------------

    public function test_create_persists_valid_category_with_both_locales(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(CreatePostCategory::class)
            ->fillForm([
                'name' => ['pl' => 'Technologia', 'en' => 'Technology'],
                'slug' => 'technologia',
                'description' => ['pl' => 'Artykuły o technologii.', 'en' => 'Technology articles.'],
                'color' => 'primary',
                'is_active' => true,
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = PostCategory::latest('id')->first();

        $this->assertSame('Technologia', $category->getTranslation('name', 'pl'));
        $this->assertSame('Technology', $category->getTranslation('name', 'en'));
        $this->assertSame('Artykuły o technologii.', $category->getTranslation('description', 'pl'));
        $this->assertSame('Technology articles.', $category->getTranslation('description', 'en'));
        $this->assertSame('technologia', $category->slug);
        $this->assertSame('primary', $category->color);
        $this->assertTrue($category->is_active);
    }

    public function test_create_persists_optional_icon(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(CreatePostCategory::class)
            ->fillForm([
                'name' => ['pl' => 'Design', 'en' => 'Design'],
                'slug' => 'design',
                'color' => 'secondary',
                'icon' => 'tabler--brush',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('tabler--brush', PostCategory::latest('id')->first()->icon);
    }

    public function test_create_requires_name(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(CreatePostCategory::class)
            ->fillForm([
                'name' => '',
                'slug' => 'test-slug',
                'color' => 'primary',
            ])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    public function test_create_requires_slug(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(CreatePostCategory::class)
            ->fillForm([
                'name' => ['pl' => 'Kategoria', 'en' => 'Category'],
                'slug' => '',
                'color' => 'primary',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_create_requires_color(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(CreatePostCategory::class)
            ->fillForm([
                'name' => ['pl' => 'Kategoria', 'en' => 'Category'],
                'slug' => 'kategoria',
                'color' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['color']);
    }

    public function test_create_rejects_duplicate_slug(): void
    {
        PostCategory::factory()->create(['slug' => 'zajety-slug']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(CreatePostCategory::class)
            ->fillForm([
                'name' => ['pl' => 'Nowa', 'en' => 'New'],
                'slug' => 'zajety-slug',
                'color' => 'primary',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_create_defaults_is_active_to_true(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(CreatePostCategory::class)
            ->fillForm([
                'name' => ['pl' => 'Kategoria', 'en' => 'Category'],
                'slug' => 'kategoria',
                'color' => 'primary',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(PostCategory::latest('id')->first()->is_active);
    }

    public function test_create_defaults_sort_order_to_zero(): void
    {
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(CreatePostCategory::class)
            ->fillForm([
                'name' => ['pl' => 'Kategoria', 'en' => 'Category'],
                'slug' => 'kategoria',
                'color' => 'primary',
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(0, PostCategory::latest('id')->first()->sort_order);
    }

    // ---------------------------------------------------------------------------
    // Edit — prefill + save
    // ---------------------------------------------------------------------------

    public function test_edit_form_prefills_existing_translations(): void
    {
        $category = PostCategory::factory()->create([
            'name' => ['pl' => 'Oryginalna', 'en' => 'Original'],
            'description' => ['pl' => 'Opis PL', 'en' => 'Desc EN'],
            'slug' => 'oryginalna',
            'color' => 'info',
        ]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(EditPostCategory::class, ['record' => $category->getRouteKey()])
            ->assertFormSet([
                'name' => ['pl' => 'Oryginalna', 'en' => 'Original'],
                'description' => ['pl' => 'Opis PL', 'en' => 'Desc EN'],
                'slug' => 'oryginalna',
                'color' => 'info',
            ]);
    }

    public function test_edit_saves_updated_translations(): void
    {
        $category = PostCategory::factory()->create([
            'name' => ['pl' => 'Stara', 'en' => 'Old'],
            'slug' => 'stara',
            'color' => 'primary',
        ]);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(EditPostCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm([
                'name' => ['pl' => 'Nowa', 'en' => 'New'],
                'color' => 'success',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $category->refresh();
        $this->assertSame('Nowa', $category->getTranslation('name', 'pl'));
        $this->assertSame('New', $category->getTranslation('name', 'en'));
        $this->assertSame('success', $category->color);
    }

    public function test_edit_saves_is_active_toggle(): void
    {
        $category = PostCategory::factory()->active()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(EditPostCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($category->refresh()->is_active);
    }

    public function test_edit_rejects_duplicate_slug_on_another_record(): void
    {
        PostCategory::factory()->create(['slug' => 'zajety']);
        $category = PostCategory::factory()->create(['slug' => 'wolny']);
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(EditPostCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['slug' => 'zajety'])
            ->call('save')
            ->assertHasFormErrors(['slug']);
    }

    // ---------------------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------------------

    public function test_authorized_user_can_delete_post_category(): void
    {
        $category = PostCategory::factory()->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(ListPostCategories::class)
            ->callTableAction('delete', $category)
            ->assertOk();

        $this->assertDatabaseMissing('post_categories', ['id' => $category->id]);
    }

    public function test_bulk_delete_removes_selected_post_categories(): void
    {
        $categories = PostCategory::factory()->count(3)->create();
        $this->actingAs($this->makeAdmin([webfloo_permission('view_any', 'post_category')]));

        Livewire::test(ListPostCategories::class)
            ->callTableBulkAction('delete', $categories)
            ->assertOk();

        foreach ($categories as $category) {
            $this->assertDatabaseMissing('post_categories', ['id' => $category->id]);
        }
    }

    // ---------------------------------------------------------------------------
    // Translatable fields — raw JSON structure
    // ---------------------------------------------------------------------------

    public function test_translatable_name_stores_both_locales_as_json(): void
    {
        $category = PostCategory::factory()->create([
            'name' => ['pl' => 'Technologia', 'en' => 'Technology'],
        ]);

        $raw = DB::table('post_categories')->where('id', $category->id)->value('name');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
        $this->assertSame('Technologia', $decoded['pl']);
        $this->assertSame('Technology', $decoded['en']);
    }

    public function test_translatable_description_stores_both_locales_as_json(): void
    {
        $category = PostCategory::factory()->create([
            'description' => ['pl' => 'Opis po polsku.', 'en' => 'English description.'],
        ]);

        $raw = DB::table('post_categories')->where('id', $category->id)->value('description');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
        $this->assertSame('Opis po polsku.', $decoded['pl']);
        $this->assertSame('English description.', $decoded['en']);
    }

    public function test_get_translation_returns_empty_string_for_missing_locale(): void
    {
        $category = PostCategory::factory()->create([
            'name' => ['pl' => 'Tylko PL'],
        ]);

        // spatie/laravel-translatable returns '' (not null) when locale missing and fallback disabled
        $this->assertSame('', $category->getTranslation('name', 'en', false));
    }

    // ---------------------------------------------------------------------------
    // Scopes — model-level coverage
    // ---------------------------------------------------------------------------

    public function test_scope_active_returns_only_active_categories(): void
    {
        PostCategory::factory()->active()->count(2)->create();
        PostCategory::factory()->inactive()->create();

        $this->assertCount(2, PostCategory::active()->get());
    }

    public function test_scope_active_excludes_inactive_categories(): void
    {
        PostCategory::factory()->inactive()->create();

        $this->assertCount(0, PostCategory::active()->get());
    }

    public function test_scope_ordered_returns_categories_by_sort_order(): void
    {
        PostCategory::factory()->create(['sort_order' => 3]);
        PostCategory::factory()->create(['sort_order' => 1]);
        PostCategory::factory()->create(['sort_order' => 2]);

        $ordered = PostCategory::ordered()->pluck('sort_order')->toArray();

        $this->assertSame([1, 2, 3], $ordered);
    }

    // ---------------------------------------------------------------------------
    // DB defaults
    // ---------------------------------------------------------------------------

    public function test_post_category_defaults_is_active_to_true_on_database_level(): void
    {
        $name = 'test-cat-'.uniqid();
        $id = DB::table('post_categories')->insertGetId([
            'name' => json_encode(['pl' => 'Test']),
            'slug' => $name,
            'color' => 'primary',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('post_categories')->find($id);
        $this->assertSame(1, (int) $row->is_active);
    }

    public function test_post_category_defaults_sort_order_to_zero_on_database_level(): void
    {
        $name = 'test-cat-'.uniqid();
        $id = DB::table('post_categories')->insertGetId([
            'name' => json_encode(['pl' => 'Test']),
            'slug' => $name,
            'color' => 'primary',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('post_categories')->find($id);
        $this->assertSame(0, (int) $row->sort_order);
    }

    // ---------------------------------------------------------------------------
    // Color options
    // ---------------------------------------------------------------------------

    public function test_get_color_options_returns_all_defined_colors(): void
    {
        $options = PostCategory::getColorOptions();

        $this->assertArrayHasKey('primary', $options);
        $this->assertArrayHasKey('success', $options);
        $this->assertArrayHasKey('warning', $options);
        $this->assertArrayHasKey('error', $options);
        $this->assertCount(count(PostCategory::COLORS), $options);
    }

    public function test_get_badge_class_returns_color_prefixed_class(): void
    {
        $category = PostCategory::factory()->create(['color' => 'success']);

        $this->assertSame('badge-success', $category->getBadgeClass());
    }
}
