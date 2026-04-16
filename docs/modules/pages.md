# Module: Pages (CMS core)

**Feature flag:** — (always-on)
**Scope:** generyczne CMS pages z hierarchią parent/child, SEO tab, templates. Baza dla custom podstron którymi zarządza admin. Plus PageSettings API (SiteSettings, HomePageSettings, ContactPageSettings).

## Public API

### Resources
- `Webfloo\Filament\Resources\PageResource`

### Pages (non-Resource)
- `Webfloo\Filament\Pages\SiteSettings` — global site config (logo, favicon, contact)
- `Webfloo\Filament\Pages\PageSettings\HomePageSettings` — home-specific content slots
- `Webfloo\Filament\Pages\PageSettings\ContactPageSettings` — contact page content
- (abstract) `AbstractPageSettings` — base class dla custom PageSettings (SSOT canAccess pattern)

### Models
- `Webfloo\Models\Page` — title, slug, content (RichEditor), meta fields, parent_id, template, status, published_at

### Traits applied
- `HasSlug`, `HasSeo`, `Publishable`, `HasTranslations`

### Relationships
- `Page::belongsTo(Page, 'parent_id')` — parent
- `Page::hasMany(Page, 'parent_id')` — children

## Migrations

- `*_create_pages_table`
- `*_add_published_at_index_to_pages_table`
- `*_change_pages_status_from_enum_to_string`

## Shield permissions

```
view_any_page   view_page   create_page   update_page   delete_page
view_site_settings
view_home_page_settings
view_contact_page_settings
```

Page Settings permissions sprawdzane przez `AbstractPageSettings::canAccess()` SSOT template-method.

## Host integration

Pakiet nie dostarcza frontend page rendering — host wire'uje `GET /{slug}` catchall albo explicit routes per Page template.

PageSettings są Filament admin pages — host ma je dostępne automatycznie przez WebflooPanel plugin. Ich content wire'owany jest w host layout przez `setting()` helper.

## Limitations

- Custom PageSettings dla host-specific potrzeb tworzone przez extend `AbstractPageSettings` — patrz host's `AboutPageSettings` example.
- SeoSettings (global SEO config) deferred — patrz `seo.md`.
