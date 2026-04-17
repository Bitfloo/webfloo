<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Webfloo\Filament\Resources\TestimonialResource;
use Webfloo\Filament\Resources\TestimonialResource\Pages\CreateTestimonial;
use Webfloo\Filament\Resources\TestimonialResource\Pages\EditTestimonial;
use Webfloo\Filament\Resources\TestimonialResource\Pages\ListTestimonials;
use Webfloo\Models\Testimonial;
use Webfloo\Tests\TestCase;

final class TestimonialResourceTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------------------

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $user = $this->makeAdmin();

        $this->actingAs($user)
            ->get(TestimonialResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(ListTestimonials::class)->assertOk();
    }

    public function test_authorized_user_can_access_create_page(): void
    {
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(CreateTestimonial::class)->assertOk();
    }

    public function test_authorized_user_can_access_edit_page(): void
    {
        $testimonial = Testimonial::factory()->create();
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])->assertOk();
    }

    // ---------------------------------------------------------------------------
    // Feature flag
    // ---------------------------------------------------------------------------

    public function test_resource_is_inaccessible_when_testimonials_feature_flag_is_off(): void
    {
        config(['webfloo.features.testimonials' => false]);

        $user = $this->makeAdmin(['view_any_testimonial']);

        $this->assertFalse(TestimonialResource::canAccess());

        $this->actingAs($user)
            ->get(TestimonialResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_resource_is_accessible_when_testimonials_feature_flag_is_on(): void
    {
        config(['webfloo.features.testimonials' => true]);

        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        $this->assertTrue(TestimonialResource::canAccess());
    }

    // ---------------------------------------------------------------------------
    // Table / index rendering
    // ---------------------------------------------------------------------------

    public function test_index_renders_active_testimonial_records(): void
    {
        $testimonials = Testimonial::factory()->active()->count(3)->create();
        $user = $this->makeAdmin(['view_any_testimonial']);

        $this->actingAs($user);

        Livewire::test(ListTestimonials::class)
            ->assertCanSeeTableRecords($testimonials);
    }

    public function test_index_renders_inactive_testimonial_records(): void
    {
        $inactive = Testimonial::factory()->inactive()->create();
        $user = $this->makeAdmin(['view_any_testimonial']);

        $this->actingAs($user);

        Livewire::test(ListTestimonials::class)
            ->assertCanSeeTableRecords([$inactive]);
    }

    // ---------------------------------------------------------------------------
    // Table filter — is_active
    // ---------------------------------------------------------------------------

    public function test_is_active_filter_shows_only_active_testimonials(): void
    {
        $active = Testimonial::factory()->active()->create();
        $inactive = Testimonial::factory()->inactive()->create();
        $user = $this->makeAdmin(['view_any_testimonial']);

        $this->actingAs($user);

        Livewire::test(ListTestimonials::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive]);
    }

    public function test_is_active_filter_shows_only_inactive_testimonials(): void
    {
        $active = Testimonial::factory()->active()->create();
        $inactive = Testimonial::factory()->inactive()->create();
        $user = $this->makeAdmin(['view_any_testimonial']);

        $this->actingAs($user);

        Livewire::test(ListTestimonials::class)
            ->filterTable('is_active', false)
            ->assertCanSeeTableRecords([$inactive])
            ->assertCanNotSeeTableRecords([$active]);
    }

    // ---------------------------------------------------------------------------
    // Create — valid data
    // ---------------------------------------------------------------------------

    public function test_create_persists_valid_testimonial_with_both_locales(): void
    {
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(CreateTestimonial::class)
            ->fillForm([
                'content' => ['pl' => 'Świetna firma!', 'en' => 'Great company!'],
                'author' => 'Jan Kowalski',
                'role' => ['pl' => 'Dyrektor', 'en' => 'Director'],
                'company' => ['pl' => 'Firma Sp. z o.o.', 'en' => 'Company Ltd.'],
                'rating' => 5,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $testimonial = Testimonial::latest('id')->first();

        $this->assertSame('Świetna firma!', $testimonial->getTranslation('content', 'pl'));
        $this->assertSame('Great company!', $testimonial->getTranslation('content', 'en'));
        $this->assertSame('Dyrektor', $testimonial->getTranslation('role', 'pl'));
        $this->assertSame('Director', $testimonial->getTranslation('role', 'en'));
        $this->assertSame('Firma Sp. z o.o.', $testimonial->getTranslation('company', 'pl'));
        $this->assertSame('Company Ltd.', $testimonial->getTranslation('company', 'en'));
        $this->assertSame('Jan Kowalski', $testimonial->author);
        $this->assertSame(5, $testimonial->rating);
        $this->assertTrue($testimonial->is_active);
        $this->assertFalse($testimonial->is_featured);
    }

    // ---------------------------------------------------------------------------
    // Create — validation
    // ---------------------------------------------------------------------------

    public function test_create_requires_content_not_null(): void
    {
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        // The Textarea->required() rule evaluates the raw field value.
        // For a translatable field backed by spatie, an array with empty strings
        // is truthy (passes required). Passing null forces the required failure.
        Livewire::test(CreateTestimonial::class)
            ->fillForm([
                'content' => null,
                'author' => 'Jan Kowalski',
            ])
            ->call('create')
            ->assertHasFormErrors(['content']);
    }

    public function test_create_requires_author(): void
    {
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(CreateTestimonial::class)
            ->fillForm([
                'content' => ['pl' => 'Opinia', 'en' => 'Review'],
                'author' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['author']);
    }

    public function test_create_stores_rating_from_select(): void
    {
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(CreateTestimonial::class)
            ->fillForm([
                'content' => ['pl' => 'Opinia', 'en' => 'Review'],
                'author' => 'Anna Nowak',
                'rating' => 3,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(3, Testimonial::latest('id')->first()->rating);
    }

    public function test_create_defaults_is_active_to_true(): void
    {
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(CreateTestimonial::class)
            ->fillForm([
                'content' => ['pl' => 'Opinia', 'en' => 'Review'],
                'author' => 'Piotr Wiśniewski',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Testimonial::latest('id')->first()->is_active);
    }

    public function test_create_defaults_rating_to_five(): void
    {
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(CreateTestimonial::class)
            ->fillForm([
                'content' => ['pl' => 'Opinia', 'en' => 'Review'],
                'author' => 'Maria Wiśniewska',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(5, Testimonial::latest('id')->first()->rating);
    }

    // ---------------------------------------------------------------------------
    // Avatar upload
    // ---------------------------------------------------------------------------

    public function test_create_stores_avatar_in_testimonials_directory(): void
    {
        Storage::fake('public');

        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        Livewire::test(CreateTestimonial::class)
            ->fillForm([
                'content' => ['pl' => 'Opinia', 'en' => 'Review'],
                'author' => 'Tomasz Nowak',
                'avatar' => $file,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $testimonial = Testimonial::latest('id')->first();
        $this->assertNotNull($testimonial->avatar);
        // Filament stores the path relative to the disk root; directory prefix must be present.
        $this->assertStringContainsString('testimonials', $testimonial->avatar);
    }

    // ---------------------------------------------------------------------------
    // Edit — prefill + save
    // ---------------------------------------------------------------------------

    public function test_edit_form_prefills_existing_translations(): void
    {
        $testimonial = Testimonial::factory()->create([
            'content' => ['pl' => 'Oryginalna opinia', 'en' => 'Original review'],
            'author' => 'Zbigniew Testowy',
            'role' => ['pl' => 'Menedżer', 'en' => 'Manager'],
            'rating' => 4,
        ]);
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
            ->assertFormSet([
                'content' => ['pl' => 'Oryginalna opinia', 'en' => 'Original review'],
                'author' => 'Zbigniew Testowy',
                'role' => ['pl' => 'Menedżer', 'en' => 'Manager'],
                'rating' => 4,
            ]);
    }

    public function test_edit_saves_updated_translations(): void
    {
        $testimonial = Testimonial::factory()->create([
            'content' => ['pl' => 'Stara opinia', 'en' => 'Old review'],
            'author' => 'Stary Autor',
        ]);
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
            ->fillForm([
                'content' => ['pl' => 'Nowa opinia', 'en' => 'New review'],
                'author' => 'Nowy Autor',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $testimonial->refresh();
        $this->assertSame('Nowa opinia', $testimonial->getTranslation('content', 'pl'));
        $this->assertSame('New review', $testimonial->getTranslation('content', 'en'));
        $this->assertSame('Nowy Autor', $testimonial->author);
    }

    public function test_edit_saves_is_active_toggle(): void
    {
        $testimonial = Testimonial::factory()->active()->create();
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($testimonial->refresh()->is_active);
    }

    public function test_edit_saves_is_featured_toggle(): void
    {
        $testimonial = Testimonial::factory()->create(['is_featured' => false]);
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
            ->fillForm(['is_featured' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($testimonial->refresh()->is_featured);
    }

    public function test_edit_saves_updated_rating(): void
    {
        $testimonial = Testimonial::factory()->withRating(5)->create();
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
            ->fillForm(['rating' => 2])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(2, $testimonial->refresh()->rating);
    }

    // ---------------------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------------------

    public function test_authorized_user_can_delete_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create();
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(ListTestimonials::class)
            ->callTableAction('delete', $testimonial)
            ->assertOk();

        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }

    public function test_bulk_delete_removes_selected_testimonials(): void
    {
        $testimonials = Testimonial::factory()->count(3)->create();
        $user = $this->makeAdmin(['view_any_testimonial']);
        $this->actingAs($user);

        Livewire::test(ListTestimonials::class)
            ->callTableBulkAction('delete', $testimonials)
            ->assertOk();

        foreach ($testimonials as $testimonial) {
            $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
        }
    }

    // ---------------------------------------------------------------------------
    // Translatable fields — raw JSON structure
    // ---------------------------------------------------------------------------

    public function test_translatable_content_stores_both_locales_as_json(): void
    {
        $testimonial = Testimonial::factory()->create([
            'content' => ['pl' => 'Opinia po polsku', 'en' => 'English review'],
        ]);

        $raw = DB::table('testimonials')->where('id', $testimonial->id)->value('content');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
        $this->assertSame('Opinia po polsku', $decoded['pl']);
        $this->assertSame('English review', $decoded['en']);
    }

    public function test_translatable_role_stores_both_locales_as_json(): void
    {
        $testimonial = Testimonial::factory()->create([
            'role' => ['pl' => 'Prezes', 'en' => 'President'],
        ]);

        $raw = DB::table('testimonials')->where('id', $testimonial->id)->value('role');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
        $this->assertSame('Prezes', $decoded['pl']);
        $this->assertSame('President', $decoded['en']);
    }

    public function test_translatable_company_stores_both_locales_as_json(): void
    {
        $testimonial = Testimonial::factory()->create([
            'company' => ['pl' => 'Firma Polska', 'en' => 'Polish Company'],
        ]);

        $raw = DB::table('testimonials')->where('id', $testimonial->id)->value('company');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
    }

    public function test_get_translation_returns_empty_string_for_missing_locale(): void
    {
        $testimonial = Testimonial::factory()->create([
            'content' => ['pl' => 'Tylko po polsku'],
        ]);

        // spatie/laravel-translatable returns '' (not null) when locale missing and fallback disabled
        $this->assertSame('', $testimonial->getTranslation('content', 'en', false));
    }

    // ---------------------------------------------------------------------------
    // Scopes — unit coverage
    // ---------------------------------------------------------------------------

    public function test_scope_active_returns_only_active_testimonials(): void
    {
        Testimonial::factory()->active()->count(2)->create();
        Testimonial::factory()->inactive()->create();

        $this->assertCount(2, Testimonial::active()->get());
    }

    public function test_scope_active_excludes_inactive_testimonials(): void
    {
        Testimonial::factory()->inactive()->create();

        $this->assertCount(0, Testimonial::active()->get());
    }

    public function test_scope_featured_returns_only_featured_testimonials(): void
    {
        Testimonial::factory()->featured()->count(2)->create();
        Testimonial::factory()->create(['is_featured' => false]);

        $this->assertCount(2, Testimonial::featured()->get());
    }

    public function test_scope_featured_excludes_non_featured_testimonials(): void
    {
        Testimonial::factory()->create(['is_featured' => false]);

        $this->assertCount(0, Testimonial::featured()->get());
    }

    public function test_homepage_rotation_returns_active_and_featured_testimonials(): void
    {
        Testimonial::factory()->active()->featured()->count(3)->create();
        Testimonial::factory()->inactive()->featured()->create();
        Testimonial::factory()->active()->create(['is_featured' => false]);

        $homepage = Testimonial::active()->featured()->get();

        $this->assertCount(3, $homepage);
        foreach ($homepage as $testimonial) {
            $this->assertTrue($testimonial->is_active);
            $this->assertTrue($testimonial->is_featured);
        }
    }

    public function test_scope_ordered_returns_testimonials_by_sort_order(): void
    {
        Testimonial::factory()->create(['sort_order' => 3, 'author' => 'C']);
        Testimonial::factory()->create(['sort_order' => 1, 'author' => 'A']);
        Testimonial::factory()->create(['sort_order' => 2, 'author' => 'B']);

        $ordered = Testimonial::ordered()->pluck('sort_order')->toArray();

        $this->assertSame([1, 2, 3], $ordered);
    }

    // ---------------------------------------------------------------------------
    // Model database defaults
    // ---------------------------------------------------------------------------

    public function test_testimonial_defaults_is_active_to_true_on_database_level(): void
    {
        $id = DB::table('testimonials')->insertGetId([
            'content' => json_encode(['pl' => 'Test']),
            'author' => 'Test Author',
            'rating' => 5,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('testimonials')->find($id);
        $this->assertSame(1, (int) $row->is_active);
    }

    public function test_testimonial_defaults_sort_order_to_zero_on_database_level(): void
    {
        $id = DB::table('testimonials')->insertGetId([
            'content' => json_encode(['pl' => 'Test']),
            'author' => 'Test Author',
            'rating' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('testimonials')->find($id);
        $this->assertSame(0, (int) $row->sort_order);
    }

    public function test_testimonial_defaults_rating_to_five_on_database_level(): void
    {
        $id = DB::table('testimonials')->insertGetId([
            'content' => json_encode(['pl' => 'Test']),
            'author' => 'Test Author',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('testimonials')->find($id);
        $this->assertSame(5, (int) $row->rating);
    }

    // ---------------------------------------------------------------------------
    // Rating — factory state coverage
    // ---------------------------------------------------------------------------

    public function test_factory_with_rating_state_stores_correct_value(): void
    {
        $testimonial = Testimonial::factory()->withRating(3)->create();

        $this->assertSame(3, $testimonial->rating);
    }
}
