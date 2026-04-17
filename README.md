# Webfloo

**Reusable Laravel + Filament CMS foundation by [Bitfloo](https://bitfloo.com).**

Laravel 12 + Filament v5 package dostarczający gotowy backend (14 modeli, 11 Filament Resources, 4 PageSettings, CRM lead pipeline, i18n infra, Shield RBAC). Skin layer (theme, frontend) — host responsibility.

> Produkt używany w produkcji przez `bitfloo-web` (strona firmowa Bitfloo) od 2026-04. Target consumers: agency MVPs, corporate sites, SaaS CMS foundations.

---

## Requirements

| Component | Version | Note |
|---|---|---|
| PHP | `^8.2` | Readonly props, enums, first-class callable |
| Laravel | `^11.0 \|\| ^12.0` | Tested on L12 |
| Filament | `^5.0` | Schema API, Heroicon enum |
| MySQL | `8.0+` | JSON columns, `SHOW INDEX` guards w 4 migracjach |
| `bezhansalleh/filament-shield` | `^4.0` | Host installs separately — patrz Installation |

Full host contract: [ADR 005](docs/decisions/005-webfloo-host-contract.md).

---

## Installation

### 1. Composer require

```bash
composer require bitfloo/webfloo
```

### 2. Publish config

```bash
php artisan vendor:publish --tag=webfloo-config
```

Edytuj `config/webfloo.php`:

```php
return [
    'user_model' => \App\Models\User::class,   // MANDATORY — helper throws jeśli null

    'features' => [
        'blog' => true,
        'portfolio' => true,
        'services' => true,
        'testimonials' => true,
        'faq' => true,
        'newsletter' => true,
        'crm' => true,
        'custom_js' => false,  // stored-XSS surface, opt-in only
    ],

    // ... (patrz published config)
];
```

### 3. Shield authorization (required)

```bash
composer require bezhansalleh/filament-shield
php artisan shield:install admin
```

### 4. Register WebflooPanel plugin w host PanelProvider

```php
// app/Providers/Filament/AdminPanelProvider.php
use Webfloo\Filament\WebflooPanel;

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        // ... host-specific config (auth, colors, brand) ...
        ->plugins([
            WebflooPanel::make(),
        ]);
}
```

WebflooPanel auto-discovers wszystkie 11 Resources (`Post`, `Page`, `Lead`, itd.), 5 Pages (`SiteSettings`, `ThemeSettings`, `CrmDashboard`, `HomePageSettings`, `ContactPageSettings`) oraz 5 Widgets. Każdy surface ma własne `canAccess()` gate oparte na Shield permissions + `webfloo.features.*` flag.

### 5. Migrate + seed

```bash
php artisan migrate
php artisan shield:generate --all --panel=admin
php artisan db:seed --class=Webfloo\\Database\\Seeders\\ShieldRolesSeeder
```

Seeder tworzy 3 role:
- **super_admin** — wszystkie permissions (ops / compliance)
- **editor** — CRUD content (Post, Page, Project, itd.), **nie widzi** newsletter PII
- **viewer** — read-only dla content surfaces, bez PII

Editor + viewer traktują Newsletter Subscribers jako admin-only (GDPR).

### 6. Optional publish

```bash
php artisan vendor:publish --tag=webfloo-views   # custom Blade overrides
php artisan vendor:publish --tag=webfloo-lang    # translation overrides
```

### 7. Storage link

```bash
php artisan storage:link
```

Pakiet używa dysku `public` (Post images, Page meta images, Project/Testimonial assets).

---

## Features

### Content CRUD
11 Filament Resources (wszystkie z Shield canAccess + feature flag gate):

- Post + PostCategory (blog)
- Page (podstrony CMS, hierarchia parent/child, SEO tab)
- Project (portfolio + case studies)
- Service (oferta usługowa)
- Testimonial (opinie z rating + avatar)
- Faq (FAQ z kategoriami)
- MenuItem (nawigacja lokowana per placement)
- NewsletterSubscriber (**admin-only PII**, GDPR scope)
- Lead + LeadTag (CRM)

### CRM (gated `webfloo.features.crm`)
- Lead pipeline: new → contacted → qualified → converted / lost
- LeadActivity (timeline), LeadReminder (tasks), LeadTag (kategoryzacja)
- CrmDashboard kanban view (5 widgets — stats + 3 charts + reminders)
- LeadExporter CSV
- Public API webhook: `POST /api/leads/webhook` (hash_equals auth + throttle)

### Theme customization
- `ThemeService`: HEX→OKLCH converter, CSS variables, WCAG AA contrast
- 8 base themes + roundness + density settings
- Optional inline `custom_js` (default DISABLED — XSS surface)

### Page Settings
- `SiteSettings` (global config: logo, favicon, contact data)
- `ThemeSettings` (kolory, style, custom CSS/JS gate)
- `HomePageSettings` / `ContactPageSettings` (BITFLOO-specific — abstrakcja + concrete klasa)
- Locale-aware save/load przez `AbstractPageSettings`

### i18n
- PL source locale (klucze = polskie stringi)
- JSON translations — `lang/{pl,en}.json`
- Publish tag `webfloo-lang` dla host override per-key
- `PluginTranslationRegistry` dla Inertia/Vue side

Patrz [ADR 006](https://github.com/Bitfloo/bitfloo-web/blob/main/docs/decisions/006-webfloo-translation-strategy.md) dla strategy details.

### Other
- SEO: `HasSeo` trait + sitemap generator (PL/EN hreflang via `GenerateSitemap` command)
- Blade atomic components pod `webfloo::` namespace (`<x-webfloo-button>`, `<x-webfloo-hero>`, itd.)
- Scope traits: `HasActive`, `HasFeatured`, `HasSlug`, `HasSeo`, `Publishable`, `Sortable`
- Lead notification mail (`webfloo::mail.lead-email`)

---

## What Webfloo does NOT provide

- Public frontend (Vue/Inertia landing, blog show page) — host dostarcza.
- `resources/js/` — theme-specific, host owns.
- Test scaffold — host runs tests against webfloo models/resources.
- Layout templates — host own.
- User model — host's `App\Models\User` wire'owany przez `config/webfloo.php` `user_model`.
- CSP middleware — szczególnie ważne jeśli włączasz `custom_js` feature flag.

---

## Feature flag matrix

| Flag | Default | Scope | Security |
|---|---|---|---|
| `blog` | `true` | PostResource + PostCategoryResource | LOW |
| `portfolio` | `true` | ProjectResource | LOW |
| `services` | `true` | ServiceResource | LOW |
| `testimonials` | `true` | TestimonialResource | LOW |
| `faq` | `true` | FaqResource | LOW |
| `newsletter` | `true` | NewsletterSubscriberResource (admin-only) | MEDIUM (PII) |
| `crm` | `true` | Lead pipeline + API webhook + widgets | MEDIUM (PII) |
| `custom_js` | `false` | ThemeSettings inline JS | **HIGH (XSS)** |

Flag sprawdzany PRZED permission check:

```php
canAccess() = config('webfloo.features.<flag>') && user()->can('view_any_<slug>')
```

Patrz [ADR 007](https://github.com/Bitfloo/bitfloo-web/blob/main/docs/decisions/007-webfloo-feature-flag-matrix.md) dla scope + security per flag.

---

## Extracting to a new host

Typowy workflow:

1. `composer require bitfloo/webfloo`
2. Publish config → wire `user_model` (krytyczne)
3. Shield sequence (install → migrate → shield:generate → seed roles)
4. Zaimplementuj własny frontend (Vue/Inertia/Blade — wybór hosta)
5. Filament admin panel ma wszystkie Resources gotowe pod `/admin`

Landing + blog frontend = host's code. Core dostarcza **data layer + admin UI + API webhooks**.

---

## Development

```bash
make install     # composer install
make check       # pint + phpstan + phpunit
make test        # tylko phpunit
make stan        # tylko phpstan
make pint        # tylko pint
```

### Stack

- PHPStan level 10 (zero baseline)
- Laravel Pint (PSR-12 + Laravel convention)
- PHPUnit (backend testing — models, traits, Filament resources)

---

## Consumed by

| Project | Role |
|---|---|
| [`Bitfloo/bitfloocom-web`](https://github.com/Bitfloo/bitfloocom-web) | Strona firmowa bitfloo.com — production consumer od 2026-04 |

## Development workflow (dla konsumentów)

```bash
# 1. Zmiana w core
cd ~/DEV/webfloo
# edit → make check → commit
git tag v1.x.y && git push origin main --tags

# 2. Update w konsumencie (np. bitfloo-web)
cd ~/DEV/bitfloo-web
composer update bitfloo/webfloo
make check
git commit -m "chore(deps): bump webfloo to v1.x.y"
```

Lokalny dev: konsument może używać `composer.json` z `"type": "path"` na `../webfloo` (symlink) — zmiany w webfloo od razu widoczne bez `composer update`. Do CI/prod należy przełączyć na versioned dep.

## License

Proprietary (all rights reserved by Bitfloo). Contact `hello@bitfloo.com` for licensing inquiries.

## Authors

Produkt: [Bitfloo](https://bitfloo.com) — Polska / EU / USA software house.

## Changelog

Patrz `CHANGELOG.md`.
