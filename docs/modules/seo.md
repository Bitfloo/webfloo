# Module: SEO

**Feature flag:** — (always-on, cross-cutting concern)
**Scope:** HasSeo trait dla dowolnego Model + sitemap generator command. **Nie ma dedicated Resources** — SEO fields żyją w Post/Project/Page forms (SEO tab).

## Public API

### Traits
- `Webfloo\Traits\HasSeo` — aplikuje meta_title, meta_description, meta_image, no_index columns na model. Używane przez Post, Project, Page.

### Commands
- `Webfloo\Console\Commands\GenerateSitemap` — `php artisan webfloo:generate-sitemap`

## Migrations

SEO kolumny są integralną częścią migrations `create_posts`, `create_projects`, `create_pages` (nie ma osobnej migracji `add_seo_fields`).

Każda tabela z HasSeo trait ma:
- `meta_title VARCHAR(70) NULL`
- `meta_description VARCHAR(160) NULL`
- `meta_image VARCHAR(255) NULL` (path w storage/public/)
- `no_index BOOLEAN DEFAULT false`

## Shield permissions

Moduł nie definiuje dedicated permissions — SEO fields są zarządzane przez permissions modułu hosta (`update_post` implicitly grant access do SEO tab w PostResource).

## Host integration

### Using HasSeo na własnym modelu

```php
use Webfloo\Traits\HasSeo;

class MyCustomContent extends Model
{
    use HasSeo;
    // ...
}
```

Migration:
```php
Schema::table('my_custom_contents', function (Blueprint $table) {
    $table->string('meta_title', 70)->nullable();
    $table->string('meta_description', 160)->nullable();
    $table->string('meta_image')->nullable();
    $table->boolean('no_index')->default(false);
});
```

### Sitemap generation

```bash
# Manual
php artisan webfloo:generate-sitemap

# Cron (recommended)
0 3 * * * cd /var/www && php artisan webfloo:generate-sitemap
```

Generator output: `public/sitemap.xml` z published Posts + active Projects + Pages. Hreflang support dla PL + EN locales.

Custom models można dodać do sitemap — follow-up (obecnie hardcoded lista w GenerateSitemap::handle()).

### Head metadata w frontend

`$post->getSeoTitle()`, `$post->getSeoDescription()`, `$post->getSeoImage()` zwracają fallback values jeśli meta fields pusty:
- title → `$model->title` albo `config('app.name')`
- description → `$model->excerpt` albo empty
- image → `meta_image` albo `featured_image`

Host wstrzykuje w `<head>` via Blade layout albo Inertia `<Head>` component.

### robots.txt

Pakiet nie dostarcza dynamic robots.txt. Host wire'uje statyczny `public/robots.txt` albo dynamic route. `no_index` flag per model jest renderowany jako `<meta name="robots" content="noindex">` w `<head>`.

## Feature flag scenarios

- **Always-on** — moduł nie ma feature flag. Wyłączenie wymaga manualnego unregister HasSeo trait na modelach + usunięcie SEO tab z form schemas.

## Testing

1. Create Post z meta_title = "Custom title" → frontend head renders custom, nie `$post->title`.
2. Set no_index = true → head renders `<meta name="robots" content="noindex">`.
3. `php artisan webfloo:generate-sitemap` → `public/sitemap.xml` zawiera wszystkie published Posts + Pages + active Projects.

## Limitations / known gaps

- **Zero dedicated SeoSettings PageSettings** (Phase 3 step 17 deferred). Global SEO defaults (robots.txt rules, sitemap update frequency, default og:image fallback) wymagają host config edit — nie admin UI.
- **GenerateSitemap bez chunking** (D5 deferred). Może być slow na tabelach >10k rekordów.
- **Brak Open Graph twitter tags jako dedykowane pola** — fallback na meta_title/description/image. Follow-up: per-model `og_title`, `twitter_card` fields jeśli potrzeba.
- **Hreflang hardcoded do PL + EN** — multi-locale hosty wymagają modyfikacji GenerateSitemap.
