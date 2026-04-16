# Module: Testimonials

**Feature flag:** `webfloo.features.testimonials` (default `true`)
**Scope:** opinie klientów z rating 1-5, autorem (imię, stanowisko, firma), avatar, featured flag.

## Public API

### Resources
- `Webfloo\Filament\Resources\TestimonialResource`

### Models
- `Webfloo\Models\Testimonial` — content, rating, author, role, company, avatar, is_active, is_featured, sort_order

### Traits applied
- `HasActive`, `HasFeatured`, `Sortable`

## Migrations

- `*_create_testimonials_table`
- `*_add_is_featured_to_services_and_testimonials`

## Shield permissions

```
view_any_testimonial   view_testimonial   create_testimonial   update_testimonial   delete_testimonial
```

## Host integration

`Webfloo\Components\Sections\Testimonials` Blade section renderuje karuzelę / grid. Host dostarcza Vue komponent jeśli używa Inertia.

## Feature flag scenarios

- `features.testimonials = false` — Resource niewidoczny, section component renders empty state.

## Limitations

- Avatar upload hardcoded disk `public` — host override przez Filament FileUpload config.
- Rating stars hardcoded (5 stars).
