# Module: Blog

**Feature flag:** `webfloo.features.blog` (default `true`)
**Scope:** publikacja artykułów z kategoriami, SEO metadata, related posts + related projects (cross-module do Portfolio).

## Public API

### Resources
- `Webfloo\Filament\Resources\PostResource`
- `Webfloo\Filament\Resources\PostCategoryResource`

### Models
- `Webfloo\Models\Post` — blog post (title, slug, excerpt, content, featured_image, author, reading_time, status, published_at, is_featured, views_count)
- `Webfloo\Models\PostCategory` — kategoria z color, icon, sort_order

### Traits applied
- `HasSlug` — auto-slug z title
- `HasSeo` — meta_title, meta_description, meta_image, no_index
- `Publishable` — status draft/published, published_at
- `HasFeatured` — is_featured flag dla frontend highlighting
- `Sortable` — sort_order column
- `HasTranslations` (spatie) — title, excerpt, content, meta_title, meta_description

### Relationships
- `Post::belongsTo(PostCategory)` — category
- `Post::belongsTo(bitfloo_user_model())` — author (przez config/webfloo.php user_model)
- `Post::belongsToMany(Post, 'post_related')` — related posts (curated)
- `Post::belongsToMany(Project, 'post_project')` — related portfolio projects
- `PostCategory::hasMany(Post)` — posts w kategorii

## Migrations

- `*_create_post_categories_table` — kategorie
- `*_create_posts_table` — główna tabela
- `*_create_post_project_table` — pivot dla related projects
- `*_create_post_related_table` — pivot dla related posts
- `*_make_post_categories_translatable` — migration JSON kolumny

## Shield permissions

```
view_any_post   view_post   create_post   update_post   delete_post
view_any_post_category   view_post_category   create_post_category   update_post_category   delete_post_category
```

Role assignments (ShieldRolesSeeder):
- `super_admin` — all permissions
- `editor` — all (CRUD content)
- `viewer` — `view_any_*` + `view_*` only

## Host integration

### Frontend
Pakiet **nie dostarcza** public blog frontend. Host wire'uje własny:
- Route: `GET /blog` → controller / Inertia page
- Route: `GET /blog/{post:slug}` → post detail
- Route: `GET /blog/category/{category:slug}` (opcjonalnie) → category filter

`Post` model ma `getUrlAttribute()` zwracający `"/blog/{slug}"` — ale jeśli host używa innego prefixu, powinien nadpisać accessor.

`Webfloo\Components\Sections\Blog` Blade component renderuje listę Post'ów z `Route::has('blog.show')` guard — bezpieczny fallback gdy route nie zarejestrowany.

### Rendering HTML content
`Post::toDetailArray()` zwraca content przepuszczony przez `mews/purifier`'s `clean()` helper — bezpieczny dla `{!! !!}` output w Blade.

### Feature flag scenarios
- `features.blog = false`:
  - PostResource + PostCategoryResource niewidoczne w panelu.
  - Blog section component (`<x-webfloo-blog>`) nadal działa (renderuje cached content lub empty state).
  - Migracje nie dotknięte — host może później włączyć feature bez re-migration.

## Testing

Manualny smoke test (host app):
1. `php artisan db:seed --class=Webfloo\\Database\\Seeders\\BlogSeeder` (tworzy 3 kategorie + 3 posts).
2. Navigate `/admin/posts` w Filament — panel loads bez błędów.
3. Navigate `/admin/post-categories` — listing + create/edit works.
4. Set `features.blog = false` w `config/webfloo.php` — navigation items znikają po cache clear.

## Limitations / known gaps

- **Blog frontend layer = host responsibility.** Pakiet nie zawiera Vue/Inertia landing templates.
- **FULLTEXT index na `posts.content`** = D4 deferred (post-alpha). Global search może być slow na dużych blog'ach.
- **i18n form labels w Wave 2 = deferred.** Obecnie PL hardcoded w form field labels.
