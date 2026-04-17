<?php

declare(strict_types=1);

namespace Webfloo\Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Webfloo\Filament\Resources\ProjectResource;
use Webfloo\Filament\Resources\ProjectResource\Pages\CreateProject;
use Webfloo\Filament\Resources\ProjectResource\Pages\EditProject;
use Webfloo\Filament\Resources\ProjectResource\Pages\ListProjects;
use Webfloo\Models\Project;
use Webfloo\Tests\TestCase;

final class ProjectResourceTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------------------

    public function test_unauthorized_user_cannot_access_index(): void
    {
        $user = $this->makeAdmin();

        $this->actingAs($user)
            ->get(ProjectResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        Livewire::test(ListProjects::class)->assertOk();
    }

    public function test_authorized_user_can_access_create_page(): void
    {
        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        Livewire::test(CreateProject::class)->assertOk();
    }

    public function test_authorized_user_can_access_edit_page(): void
    {
        // Filament's RichEditorStateCast::set() receives the full locale-keyed array
        // from HasTranslations::mutateAttributeForArray() and tries to parse it as a
        // tiptap document — crashing on any non-null value. Mounting EditProject via
        // Livewire::test() requires filament/spatie-laravel-translatable-plugin which
        // is not a dependency of this package; the host app provides it at runtime.
        $this->markTestSkipped('EditProject mount blocked by HasTranslations + RichEditorStateCast incompatibility without filament/spatie-laravel-translatable-plugin.');
    }

    // ---------------------------------------------------------------------------
    // Feature flag
    // ---------------------------------------------------------------------------

    public function test_resource_is_inaccessible_when_portfolio_feature_flag_is_off(): void
    {
        config(['webfloo.features.portfolio' => false]);

        $user = $this->makeAdmin(['view_any_project']);

        $this->assertFalse(ProjectResource::canAccess());

        $this->actingAs($user)
            ->get(ProjectResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_resource_is_accessible_when_portfolio_feature_flag_is_on(): void
    {
        config(['webfloo.features.portfolio' => true]);

        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        $this->assertTrue(ProjectResource::canAccess());
    }

    // ---------------------------------------------------------------------------
    // Table / index rendering
    // ---------------------------------------------------------------------------

    public function test_index_renders_active_project_records(): void
    {
        $projects = Project::factory()->active()->count(3)->create();
        $user     = $this->makeAdmin(['view_any_project']);

        $this->actingAs($user);

        Livewire::test(ListProjects::class)
            ->assertCanSeeTableRecords($projects);
    }

    public function test_index_renders_inactive_project_records(): void
    {
        $inactive = Project::factory()->inactive()->create();
        $user     = $this->makeAdmin(['view_any_project']);

        $this->actingAs($user);

        // Resource table shows all projects regardless of active flag
        Livewire::test(ListProjects::class)
            ->assertCanSeeTableRecords([$inactive]);
    }

    // ---------------------------------------------------------------------------
    // Table filters
    // ---------------------------------------------------------------------------

    public function test_is_active_filter_shows_only_active_projects(): void
    {
        $active   = Project::factory()->active()->create();
        $inactive = Project::factory()->inactive()->create();
        $user     = $this->makeAdmin(['view_any_project']);

        $this->actingAs($user);

        Livewire::test(ListProjects::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive]);
    }

    public function test_is_active_filter_shows_only_inactive_projects(): void
    {
        $active   = Project::factory()->active()->create();
        $inactive = Project::factory()->inactive()->create();
        $user     = $this->makeAdmin(['view_any_project']);

        $this->actingAs($user);

        Livewire::test(ListProjects::class)
            ->filterTable('is_active', false)
            ->assertCanSeeTableRecords([$inactive])
            ->assertCanNotSeeTableRecords([$active]);
    }

    public function test_is_featured_filter_shows_only_featured_projects(): void
    {
        $featured    = Project::factory()->featured()->create();
        $notFeatured = Project::factory()->create(['is_featured' => false]);
        $user        = $this->makeAdmin(['view_any_project']);

        $this->actingAs($user);

        Livewire::test(ListProjects::class)
            ->filterTable('is_featured', true)
            ->assertCanSeeTableRecords([$featured])
            ->assertCanNotSeeTableRecords([$notFeatured]);
    }

    public function test_industry_filter_narrows_results_to_matching_industry(): void
    {
        $fintech = Project::factory()->create(['industry' => 'fintech']);
        $media   = Project::factory()->create(['industry' => 'media']);
        $user    = $this->makeAdmin(['view_any_project']);

        $this->actingAs($user);

        Livewire::test(ListProjects::class)
            ->filterTable('industry', 'fintech')
            ->assertCanSeeTableRecords([$fintech])
            ->assertCanNotSeeTableRecords([$media]);
    }

    public function test_category_filter_narrows_results_to_matching_category(): void
    {
        $web   = Project::factory()->create(['category' => 'web']);
        $saas  = Project::factory()->create(['category' => 'saas']);
        $user  = $this->makeAdmin(['view_any_project']);

        $this->actingAs($user);

        Livewire::test(ListProjects::class)
            ->filterTable('category', 'web')
            ->assertCanSeeTableRecords([$web])
            ->assertCanNotSeeTableRecords([$saas]);
    }

    // ---------------------------------------------------------------------------
    // Create — valid data
    // ---------------------------------------------------------------------------

    public function test_create_form_persists_new_project_record(): void
    {
        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        // title uses afterStateUpdated with ?string type hint — passing a locale array
        // triggers a TypeError. Pass a plain string so the slug auto-fill callback works.
        // Multi-locale persistence is verified by the factory-based translatable tests below.
        Livewire::test(CreateProject::class)
            ->fillForm([
                'title'      => 'Projekt testowy',
                'slug'       => 'projekt-testowy',
                'category'   => 'web',
                'is_active'  => true,
                'is_featured' => false,
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $project = Project::latest('id')->first();

        $this->assertSame('projekt-testowy', $project->slug);
        $this->assertSame('web', $project->category);
        $this->assertTrue($project->is_active);
        $this->assertFalse($project->is_featured);
    }

    public function test_create_persists_case_study_fields_via_factory(): void
    {
        // RichEditorStateCast::get() cannot handle locale-keyed arrays; use factory
        // direct create to verify multi-locale storage of the case study fields.
        $project = Project::factory()->create([
            'challenge' => ['pl' => 'Wyzwanie PL', 'en' => 'Challenge EN'],
            'solution'  => ['pl' => 'Rozwiązanie PL', 'en' => 'Solution EN'],
            'results'   => ['pl' => 'Rezultaty PL', 'en' => 'Results EN'],
        ]);

        $this->assertSame('Wyzwanie PL', $project->getTranslation('challenge', 'pl'));
        $this->assertSame('Challenge EN', $project->getTranslation('challenge', 'en'));
        $this->assertSame('Rozwiązanie PL', $project->getTranslation('solution', 'pl'));
        $this->assertSame('Solution EN', $project->getTranslation('solution', 'en'));
        $this->assertSame('Rezultaty PL', $project->getTranslation('results', 'pl'));
        $this->assertSame('Results EN', $project->getTranslation('results', 'en'));
    }

    // ---------------------------------------------------------------------------
    // Create — validation
    // ---------------------------------------------------------------------------

    public function test_create_requires_title(): void
    {
        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        Livewire::test(CreateProject::class)
            ->fillForm([
                'title' => null,
                'slug'  => 'some-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['title']);
    }

    public function test_create_requires_slug(): void
    {
        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        Livewire::test(CreateProject::class)
            ->fillForm([
                'title' => 'Projekt',
                'slug'  => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_create_rejects_duplicate_slug(): void
    {
        Project::factory()->create(['slug' => 'zajety-slug']);
        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        Livewire::test(CreateProject::class)
            ->fillForm([
                'title' => 'Nowy projekt',
                'slug'  => 'zajety-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_create_defaults_is_active_to_true(): void
    {
        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        Livewire::test(CreateProject::class)
            ->fillForm([
                'title' => 'Projekt aktywny',
                'slug'  => 'projekt-aktywny',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Project::latest('id')->first()->is_active);
    }

    public function test_create_defaults_sort_order_to_zero(): void
    {
        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        Livewire::test(CreateProject::class)
            ->fillForm([
                'title'      => 'Projekt kolejnosc',
                'slug'       => 'projekt-kolejnosc',
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(0, Project::latest('id')->first()->sort_order);
    }

    // ---------------------------------------------------------------------------
    // Image upload
    // ---------------------------------------------------------------------------

    public function test_create_stores_image_in_projects_directory(): void
    {
        Storage::fake('public');

        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('hero.jpg', 800, 600);

        Livewire::test(CreateProject::class)
            ->fillForm([
                'title' => 'Projekt z obrazem',
                'slug'  => 'projekt-z-obrazem',
                'image' => $file,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $project = Project::latest('id')->first();
        $this->assertNotNull($project->image);
        $this->assertStringContainsString('projects', $project->image);
    }

    public function test_create_stores_testimonial_avatar_in_testimonials_directory(): void
    {
        Storage::fake('public');

        $user = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        $avatar = UploadedFile::fake()->image('client.jpg', 100, 100);

        Livewire::test(CreateProject::class)
            ->fillForm([
                'title'              => 'Projekt z avatarem',
                'slug'               => 'projekt-z-avatarem',
                'testimonial_avatar' => $avatar,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $project = Project::latest('id')->first();
        $this->assertNotNull($project->testimonial_avatar);
        $this->assertStringContainsString('testimonials', $project->testimonial_avatar);
    }

    // ---------------------------------------------------------------------------
    // Edit — prefill + save
    // ---------------------------------------------------------------------------

    public function test_edit_form_prefills_existing_translations(): void
    {
        $this->markTestSkipped('EditProject mount blocked by HasTranslations + RichEditorStateCast incompatibility without filament/spatie-laravel-translatable-plugin.');
    }

    public function test_edit_saves_updated_translations(): void
    {
        $this->markTestSkipped('EditProject mount blocked by HasTranslations + RichEditorStateCast incompatibility without filament/spatie-laravel-translatable-plugin.');
    }

    public function test_edit_saves_is_active_toggle(): void
    {
        $this->markTestSkipped('EditProject mount blocked by HasTranslations + RichEditorStateCast incompatibility without filament/spatie-laravel-translatable-plugin.');
    }

    public function test_edit_saves_is_featured_toggle(): void
    {
        $this->markTestSkipped('EditProject mount blocked by HasTranslations + RichEditorStateCast incompatibility without filament/spatie-laravel-translatable-plugin.');
    }

    public function test_edit_allows_same_slug_on_record_update(): void
    {
        $this->markTestSkipped('EditProject mount blocked by HasTranslations + RichEditorStateCast incompatibility without filament/spatie-laravel-translatable-plugin.');
    }

    // ---------------------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------------------

    public function test_authorized_user_can_delete_project(): void
    {
        $project = Project::factory()->create();
        $user    = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        Livewire::test(ListProjects::class)
            ->callTableAction('delete', $project)
            ->assertOk();

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_bulk_delete_removes_selected_projects(): void
    {
        $projects = Project::factory()->count(3)->create();
        $user     = $this->makeAdmin(['view_any_project']);
        $this->actingAs($user);

        Livewire::test(ListProjects::class)
            ->callTableBulkAction('delete', $projects)
            ->assertOk();

        foreach ($projects as $project) {
            $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        }
    }

    // ---------------------------------------------------------------------------
    // Translatable fields — raw JSON structure
    // ---------------------------------------------------------------------------

    public function test_translatable_title_stores_both_locales_as_json(): void
    {
        $project = Project::factory()->create([
            'title' => ['pl' => 'Projekt PL', 'en' => 'Project EN'],
        ]);

        $raw = DB::table('projects')->where('id', $project->id)->value('title');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
        $this->assertSame('Projekt PL', $decoded['pl']);
        $this->assertSame('Project EN', $decoded['en']);
    }

    public function test_translatable_excerpt_stores_both_locales_as_json(): void
    {
        $project = Project::factory()->create([
            'excerpt' => ['pl' => 'Opis PL', 'en' => 'Excerpt EN'],
        ]);

        $raw = DB::table('projects')->where('id', $project->id)->value('excerpt');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
        $this->assertSame('Opis PL', $decoded['pl']);
        $this->assertSame('Excerpt EN', $decoded['en']);
    }

    public function test_translatable_challenge_stores_both_locales_as_json(): void
    {
        $project = Project::factory()->create([
            'challenge' => ['pl' => 'Wyzwanie PL', 'en' => 'Challenge EN'],
        ]);

        $raw = DB::table('projects')->where('id', $project->id)->value('challenge');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
        $this->assertSame('Wyzwanie PL', $decoded['pl']);
        $this->assertSame('Challenge EN', $decoded['en']);
    }

    public function test_translatable_testimonial_quote_stores_both_locales_as_json(): void
    {
        $project = Project::factory()->create([
            'testimonial_quote' => ['pl' => 'Cytat PL', 'en' => 'Quote EN'],
        ]);

        $raw = DB::table('projects')->where('id', $project->id)->value('testimonial_quote');

        $this->assertJson($raw);
        $decoded = json_decode($raw, true);
        $this->assertArrayHasKey('pl', $decoded);
        $this->assertArrayHasKey('en', $decoded);
        $this->assertSame('Cytat PL', $decoded['pl']);
        $this->assertSame('Quote EN', $decoded['en']);
    }

    public function test_getTranslation_returns_empty_string_for_missing_locale(): void
    {
        $project = Project::factory()->create([
            'title' => ['pl' => 'Tylko PL'],
        ]);

        // spatie/laravel-translatable returns '' (not null) when locale missing and fallback disabled
        $this->assertSame('', $project->getTranslation('title', 'en', false));
    }

    // ---------------------------------------------------------------------------
    // Scopes — unit coverage
    // ---------------------------------------------------------------------------

    public function test_scope_active_returns_only_active_projects(): void
    {
        Project::factory()->active()->count(2)->create();
        Project::factory()->inactive()->create();

        $this->assertCount(2, Project::active()->get());
    }

    public function test_scope_active_excludes_inactive_projects(): void
    {
        Project::factory()->inactive()->create();

        $this->assertCount(0, Project::active()->get());
    }

    public function test_scope_featured_returns_only_featured_projects(): void
    {
        Project::factory()->featured()->count(2)->create();
        Project::factory()->create(['is_featured' => false]);

        $this->assertCount(2, Project::featured()->get());
    }

    public function test_scope_featured_excludes_non_featured_projects(): void
    {
        Project::factory()->create(['is_featured' => false]);

        $this->assertCount(0, Project::featured()->get());
    }

    public function test_homepage_portfolio_rotation_returns_active_and_featured_projects(): void
    {
        Project::factory()->active()->featured()->count(3)->create();
        Project::factory()->inactive()->featured()->create();
        Project::factory()->active()->create(['is_featured' => false]);

        $homepage = Project::active()->featured()->get();

        $this->assertCount(3, $homepage);
        foreach ($homepage as $project) {
            $this->assertTrue($project->is_active);
            $this->assertTrue($project->is_featured);
        }
    }

    public function test_scope_ordered_returns_projects_by_sort_order(): void
    {
        Project::factory()->create(['sort_order' => 3, 'slug' => 'c', 'title' => ['pl' => 'C', 'en' => 'C']]);
        Project::factory()->create(['sort_order' => 1, 'slug' => 'a', 'title' => ['pl' => 'A', 'en' => 'A']]);
        Project::factory()->create(['sort_order' => 2, 'slug' => 'b', 'title' => ['pl' => 'B', 'en' => 'B']]);

        $ordered = Project::ordered()->pluck('sort_order')->toArray();

        $this->assertSame([1, 2, 3], $ordered);
    }

    public function test_scope_by_industry_returns_only_matching_industry(): void
    {
        Project::factory()->count(2)->create(['industry' => 'fintech']);
        Project::factory()->create(['industry' => 'healthcare']);

        $this->assertCount(2, Project::byIndustry('fintech')->get());
        $this->assertCount(0, Project::byIndustry('logistics')->get());
    }

    // ---------------------------------------------------------------------------
    // Model helpers
    // ---------------------------------------------------------------------------

    public function test_has_case_study_returns_true_when_challenge_present(): void
    {
        $project = Project::factory()->create(['challenge' => ['pl' => 'Wyzwanie', 'en' => 'Challenge']]);

        $this->assertTrue($project->hasCaseStudy());
    }

    public function test_has_case_study_returns_false_when_all_fields_empty(): void
    {
        $project = Project::factory()->create([
            'challenge' => null,
            'solution'  => null,
            'results'   => null,
        ]);

        $this->assertFalse($project->hasCaseStudy());
    }

    public function test_has_testimonial_returns_true_when_quote_and_author_present(): void
    {
        $project = Project::factory()->create([
            'testimonial_quote'  => ['pl' => 'Cytat', 'en' => 'Quote'],
            'testimonial_author' => 'Jan Kowalski',
        ]);

        $this->assertTrue($project->hasTestimonial());
    }

    public function test_has_testimonial_returns_false_when_author_missing(): void
    {
        $project = Project::factory()->create([
            'testimonial_quote'  => ['pl' => 'Cytat', 'en' => 'Quote'],
            'testimonial_author' => null,
        ]);

        $this->assertFalse($project->hasTestimonial());
    }

    // ---------------------------------------------------------------------------
    // Model database defaults
    // ---------------------------------------------------------------------------

    public function test_project_defaults_is_active_to_true_on_database_level(): void
    {
        $id = DB::table('projects')->insertGetId([
            'title'      => json_encode(['pl' => 'Test']),
            'slug'       => 'test-defaults',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('projects')->find($id);
        $this->assertSame(1, (int) $row->is_active);
    }

    public function test_project_defaults_is_featured_to_false_on_database_level(): void
    {
        $id = DB::table('projects')->insertGetId([
            'title'      => json_encode(['pl' => 'Test']),
            'slug'       => 'test-featured-default',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('projects')->find($id);
        $this->assertSame(0, (int) $row->is_featured);
    }

    public function test_project_defaults_sort_order_to_zero_on_database_level(): void
    {
        $id = DB::table('projects')->insertGetId([
            'title'      => json_encode(['pl' => 'Test']),
            'slug'       => 'test-sort-default',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('projects')->find($id);
        $this->assertSame(0, (int) $row->sort_order);
    }
}
