# Module: Services

**Feature flag:** `webfloo.features.services` (default `true`)
**Scope:** oferta usługowa — lista services z ikoną, opisem, CTA link.

## Public API

### Resources
- `Webfloo\Filament\Resources\ServiceResource`

### Models
- `Webfloo\Models\Service` — title, description, icon, href, is_active, is_featured, sort_order

### Traits applied
- `HasActive`, `HasFeatured`, `Sortable`, `HasTranslations`

## Migrations

- `*_create_services_table`
- `*_add_is_featured_to_services_and_testimonials` (cross-module)

## Shield permissions

```
view_any_service   view_service   create_service   update_service   delete_service
```

## Host integration

`Webfloo\Components\Sections\Services` Blade section renderuje grid z ikonami + CTA. Host może użyć w Blade layout albo wypełnić Inertia props z `Service::active()->ordered()->get()`.

## Feature flag scenarios

- `features.services = false` — ServiceResource niewidoczny; `<x-webfloo-services>` renderuje empty state.

## Limitations

- Icon library hardcoded do Tabler icons (Iconify). Follow-up: configurable icon set.
