# Webfloo

**Reusable Laravel + Filament CMS foundation by [Bitfloo](https://bitfloo.com).**

Laravel 13 + Filament v5 package dostarczający gotowy backend: **14 modeli, 11 Filament Resources, 5 Pages** (SiteSettings, ThemeSettings, CrmDashboard, HomePageSettings, ContactPageSettings), **5 Widgets**, CRM lead pipeline, i18n infra, Shield RBAC. Skin layer (theme, public frontend) — host responsibility.

> Produkt używany w produkcji przez `bitfloo-web` (strona firmowa Bitfloo) od 2026-04. Target consumers: agency MVPs, corporate sites, SaaS CMS foundations.

---

> **Status: Pre-1.0 (`0.x`).** API niestabilne — release'y mogą wprowadzać breaking changes (sygnalizowane bumpem minor). Pinuj przez `0.2.*` lub exact version i czytaj CHANGELOG przy każdym `composer update`. Stabilizacja do `1.0` po zakończeniu roadmapy ekosystemu (`docs/plans/2026-04-17-ecosystem-phase-1.md`).

---

## Spis treści

- [Position w ekosystemie Bitfloo](#position-w-ekosystemie-bitfloo)
- [Requirements](#requirements)
- [Installation](#installation)
  - [Consumer setup (`type: vcs`)](#consumer-setup-type-vcs)
  - [Package bootstrap](#package-bootstrap)
- [Features](#features)
- [What Webfloo does NOT provide](#what-webfloo-does-not-provide)
- [Feature flag matrix](#feature-flag-matrix)
- [Extracting to a new host](#extracting-to-a-new-host)
- [Versioning & updates](#versioning--updates)
- [Contributing (Conventional Commits)](#contributing-conventional-commits)
- [Development](#development)
- [Consumed by](#consumed-by)
- [License](#license)

---

## Position w ekosystemie Bitfloo

Webfloo to **backend layer** 3-warstwowego ekosystemu:

| Warstwa | Repo | Rola | Dystrybucja |
|---|---|---|---|
| **Backend (tu)** | `Bitfloo/webfloo` | Models, Filament admin, API, CMS logic | Composer `type: vcs` (ADR-011) |
| **Frontend primitives** | `Bitfloo/thezero` → `@bitfloo/thezero-core` | Vue Atoms, shadcn-vue, composables | npm GitHub Packages |
| **Frontend template** | `Bitfloo/thezero` → `@bitfloo/thezero-template` | Molecules, Organisms, Sections, Pages | GitHub Template repo (scaffold-once) |
| **Konsument (klient)** | `bitfloo-web`, `acme-web`, … | Content, routing, brand customization | Laravel app — instaluje powyższe |

**Decision tree — gdzie dodać feature:** [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) (source of truth).

Krótka reguła: logika PHP / Model / Filament / Blade admin → tu. Vue UI → thezero. Content bitfloo.com-specific → bitfloo-web.

---

## Requirements

| Component | Version | Note |
|---|---|---|
| PHP | `^8.3` | Readonly props, enums, first-class callable |
| Laravel | `^13.0` | Tested on L13 |
| Filament | `^5.0` | Schema API, Heroicon enum |
| MySQL | `8.0+` | JSON columns, `SHOW INDEX` guards w 4 migracjach |
| `bezhansalleh/filament-shield` | `^4.0` | Host installs separately — patrz Installation |

Full host contract: [ADR 005](docs/decisions/005-webfloo-host-contract.md).

---

## Installation

### Consumer setup (`type: vcs`)

Webfloo to prywatne repo — Composer wymaga `type: vcs` + PAT auth (ADR-011).

**1.** W `composer.json` konsumenta:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/Bitfloo/webfloo.git"
    }
  ],
  "require": {
    "bitfloo/webfloo": "0.2.*"
  }
}
```

**2.** Auth lokalnie — `~/.composer/auth.json` (NIGDY w repo, NIGDY w Dockerfile):

```json
{
  "github-oauth": {
    "github.com": "<github-pat-with-repo-scope>"
  }
}
```

Token: `github.com/settings/tokens` → Classic PAT → scope `repo` → save.

**3.** Auth w CI konsumenta (GitHub Actions):

```yaml
env:
  COMPOSER_AUTH: |
    {
      "github-oauth": {
        "github.com": "${{ secrets.GH_PACKAGES_TOKEN }}"
      }
    }
```

Lokalny dev webfloo (testowanie zmian bez push) — patrz [Versioning & updates](#versioning--updates) niżej.

### Package bootstrap

Po skonfigurowanym `type: vcs` + auth:

#### 1. Composer require

```bash
composer require bitfloo/webfloo:0.2.*
composer require bezhansalleh/filament-shield
php artisan shield:install admin
```

#### 2. Register WebflooPanel plugin w host PanelProvider

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

WebflooPanel auto-discovers wszystkie Resources (`Post`, `Page`, `Lead`, itd.), Pages (`SiteSettings`, `ThemeSettings`, `CrmDashboard`, `HomePageSettings`, `ContactPageSettings`) oraz Widgets. Każdy surface ma własne `canAccess()` gate oparte na Shield permissions + `webfloo.features.*` flag.

#### 3. Install

```bash
php artisan webfloo:install --demo
php artisan storage:link
```

`webfloo:install` w jednej komendzie: publikuje config, migruje, odpala `shield:generate`, seeduje role (**super_admin** — wszystko / **editor** — CRUD content bez newsletter PII / **viewer** — read-only) i interaktywnie tworzy pierwszego admina, gdy baza nie ma userów. `--demo` dosiewa generyczne strony, menu i przykładowe rekordy. Komenda jest idempotentna.

`user_model` w `config/webfloo.php` domyślnie spada na model z `auth.providers.users.model` — nadpisz tylko, gdy Twój User żyje gdzie indziej. Pakiet używa dysku `public` (Post images, Page meta images, Project/Testimonial assets) — stąd `storage:link`.

#### 4. Optional publish

```bash
php artisan vendor:publish --tag=webfloo-views        # custom Blade overrides
php artisan vendor:publish --tag=webfloo-lang         # translation overrides
php artisan vendor:publish --tag=webfloo-error-pages  # host-level errors/404+500
```

#### 5. Public frontend (optional, turnkey sites)

Pakiet zawiera opcjonalny publiczny frontend Blade — strona klienta działa bez pisania kontrolerów:

```php
// config/webfloo.php
'features' => ['frontend' => true],
```

```bash
php artisan vendor:publish --tag=webfloo-assets   # dist/webfloo.css + Alpine
```

Po włączeniu flagi pakiet rejestruje routy: `/` (strona z template `home`), `/blog` + `/blog/{slug}`, `/portfolio` + `/portfolio/{slug}`, `/robots.txt` oraz zagnieżdżone strony CMS przez `Route::fallback` (routy hosta zawsze wygrywają). Layout `<x-webfloo-layout>` sam wstrzykuje SEO (`<x-webfloo-seo>`), zmienne theme z ThemeService, favicon i nawigację z `MenuItem`. Sekcja kontaktu osadza formularz Livewire (honeypot + rate limit + zgoda RODO → Lead + mail do admina). Konsumenci z własnym frontendem (Inertia/Vue) zostawiają flagę OFF — zero routów, zero zmian.

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

Patrz [ADR 006](docs/decisions/006-webfloo-translation-strategy.md) dla strategy details.

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

Patrz [ADR 007](docs/decisions/007-webfloo-feature-flag-matrix.md) dla scope + security per flag.

---

## Extracting to a new host

Najpierw skonfiguruj `type: vcs` + `auth.json` — patrz [Installation → Consumer setup](#consumer-setup-type-vcs).

Typowy workflow:

1. `composer require bitfloo/webfloo:0.2.*` + filament-shield
2. Zarejestruj `WebflooPanel` w PanelProviderze
3. `php artisan webfloo:install --demo` (config + migrate + shield + role + pierwszy admin)
4. Frontend: włącz `webfloo.features.frontend` (turnkey Blade) ALBO zaimplementuj własny (Vue/Inertia)
5. Filament admin panel ma wszystkie Resources gotowe pod `/admin`

Landing + blog frontend = host's code. Core dostarcza **data layer + admin UI + API webhooks**.

---

## Versioning & updates

Webfloo używa **release-please** (automatyczny semver + CHANGELOG + tag) — ADR-011.

### Jak propagować zmiany do konsumentów

1. **Dev commits na `main`** z Conventional Commit prefixem (`feat:`, `fix:`, itp. — patrz [Contributing](#contributing-conventional-commits)).
2. `.github/workflows/release.yml` uruchamia `googleapis/release-please-action@v4`.
3. Release-please otwiera / aktualizuje Release PR (version bump + CHANGELOG diff).
4. Maintainer mergeuje Release PR → auto tag `v0.x.y` + GitHub Release.
5. Konsumenci robią `composer update bitfloo/webfloo` → dostają nową wersję.

**Nie taguj ręcznie. Nie edytuj CHANGELOG ręcznie.** Release-please nadpisze oba.

### Lokalny dev (override `type: vcs` na `path`)

Testowanie zmian webfloo w konsumencie bez push:

```bash
cd ~/PROJEKTY/bitfloo-web
composer config repositories.webfloo path ../webfloo
composer update bitfloo/webfloo
# ...iterate — edytuj w ~/PROJEKTY/webfloo, konsument widzi od razu (symlink)...

# Restore do produkcyjnego vcs
composer config --unset repositories.webfloo
composer update bitfloo/webfloo
```

**NIE commituj** tego override'u w `composer.json` konsumenta.

### Pre-1.0 caveats

W `0.x` patch = bezpieczny, minor = możliwy breaking change (pre-stable API). Pinuj `0.2.*` albo exact version, czytaj CHANGELOG przed każdym `composer update`. Od `1.0` pełny semver.

---

## Contributing (Conventional Commits)

**WYMAGANE** od 2026-04-17 (ADR-011). Każdy commit na `main` MUSI mieć prefix:

| Prefix | Bump w 0.0.x | Produkcja 1.x+ |
|--------|-----------|----------------|
| `feat:` | patch (0.0.25 → 0.0.26) | minor |
| `fix:` | patch (0.0.25 → 0.0.26) | patch |
| `feat!:` / `BREAKING CHANGE:` | minor (0.0 → 0.1) | **major** |
| `docs:`, `chore:`, `refactor:`, `test:`, `ci:`, `style:` | żaden | żaden |

W `0.0.x` wszystko idzie jako patch (pre-stable API). Minor bump (`0.0 → 0.1`) rezerwowany dla jawnych breaking changes. Od `1.0` normalny semver.

Commit bez prefixu psuje release-please auto-bump. Lokalny `commit-msg` hook waliduje format — instalacja przez `./scripts/install-hooks.sh`.

Pełne reguły: [ADR-011](docs/decisions/011-distribution-strategy.md), ekosystem context: [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

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
| [`Bitfloo/bitfloo-web`](https://github.com/Bitfloo/bitfloo-web) | Strona firmowa bitfloo.com — production consumer od 2026-04 |

---

## License

Proprietary (all rights reserved by Bitfloo). Contact `hello@bitfloo.com` for licensing inquiries.

## Authors

Produkt: [Bitfloo](https://bitfloo.com) — Polska / EU / USA software house.

## Changelog

Patrz `CHANGELOG.md`.
