# Module: Portfolio

**Feature flag:** `webfloo.features.portfolio` (default `true`)
**Scope:** projekty / case studies z technology stack, galerią, SEO metadata.

## Public API

### Resources
- `Webfloo\Filament\Resources\ProjectResource`

### Models
- `Webfloo\Models\Project` — project entry (title, slug, description, category, technologies, gallery, case_study fields, is_active, is_featured, sort_order)

### Traits applied
- `HasSlug`, `HasSeo`, `HasActive`, `HasFeatured`, `Sortable`, `HasTranslations`

### Relationships
- `Project::belongsToMany(Post, 'post_project')` — related blog posts

## Migrations

- `*_create_projects_table`
- `*_add_case_study_fields_to_projects_table` — challenge, solution, results fields
- `*_add_is_featured_to_services_and_testimonials` (cross-module — też dotyka services/testimonials)

## Shield permissions

```
view_any_project   view_project   create_project   update_project   delete_project
```

## Host integration

### Frontend
Pakiet nie dostarcza frontend:
- Route: `GET /portfolio` → listing
- Route: `GET /portfolio/{project:slug}` → detail / case study

`Webfloo\Components\Sections\Portfolio` Blade section renderuje grid z filtrami — host wire'uje route per card.

### Case studies
Pola `challenge`, `solution`, `results` są opcjonalne — Project bez tych pól = regular portfolio item; z nimi = full case study.

### Feature flag scenarios
- `features.portfolio = false`:
  - ProjectResource niewidoczny.
  - `Webfloo\Components\Sections\Portfolio` nadal renderuje (empty state jeśli brak projektów).
  - Post's `relatedProjects()` relation zwraca pusty collection.

## Testing

1. Navigate `/admin/projects`.
2. Create project z case_study fields — sprawdź że zapisują się poprawnie.
3. Toggle `features.portfolio = false` + cache clear — navigation item znika.

## Limitations / known gaps

- Gallery sorting manual przez drag-drop w Filament.
- Technology tags hardcoded enum — host override przez `config/webfloo.php` (future follow-up: configurable).
