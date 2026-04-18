# bitfloo/webfloo

Reusable Composer package. Every client project installs this via Composer. Everything here must work for any project, not just bitfloo.com.

Specific to bitfloo.com -> `app/`. Reusable -> here.

## 🧭 Gdzie dodawać nowe feature (decision tree)

**KONIECZNIE PRZECZYTAJ przed dodaniem nowej funkcjonalności:** [`docs/ARCHITECTURE.md`](./docs/ARCHITECTURE.md)

Krótka wersja:
- Logika / Model / Filament / Blade admin → **tu (`webfloo`)**
- Reusable PHP Services (`src/Services/`) → **tu (`webfloo`)**
- Vue Atom / shadcn-vue primitive → **`~/DEV/thezero/packages/core/`** (`@bitfloo/thezero-core` npm)
- Vue Molecule / Organism / Section / Page / Layout → **`~/DEV/thezero/packages/template/`** (`@bitfloo/thezero-template`, NIE publikowany)
- bitfloo.com-specific (content, routes, środowisko) → **`~/DEV/bitfloo-web/`**

Pełna matrix (content types, widgets, services, controllery, trait'y, flow end-to-end) — w `docs/ARCHITECTURE.md`.

## Release workflow (Conventional Commits + release-please)

**WYMAGANE** od 2026-04-17 (ADR-011). Każdy commit na main MUSI mieć prefix:

| Prefix | Bump w 0.0.x | Produkcja 1.x+ |
|--------|-----------|----------------|
| `feat:` | patch (0.0.25 → 0.0.26) | minor |
| `fix:` | patch (0.0.25 → 0.0.26) | patch |
| `feat!:` / `BREAKING CHANGE:` | minor (0.0 → 0.1) | **major** |
| `docs:`, `chore:`, `refactor:`, `test:`, `ci:`, `style:` | żaden | żaden |

W `0.0.x` wszystko idzie jako patch (shipowanie szybkie, stabilizacja API). Minor bump (`0.0 → 0.1`) rezerwowany dla jawnych breaking changes. Od `1.0` normalny semver.

**Flow**:
1. Dev pushes commit na main z właściwym prefixem
2. `.github/workflows/release.yml` uruchamia `googleapis/release-please-action@v4`
3. Release-please otwiera / aktualizuje Release PR (z bump version + CHANGELOG)
4. Jakiś dev mergeuje Release PR
5. Auto: tag `v0.x.y` + GitHub Release
6. Konsumenci (bitfloo-web, przyszli klienci) dostają dostęp przez `composer update`

**NIE tagujemy ręcznie**. NIE edytujemy CHANGELOG ręcznie (nadpisze release-please).

## Distribution (type: vcs)

Konsument dostaje webfloo przez `type: vcs` w swoim composer.json (ADR-011). Wymaga PAT z `repo` scope w `auth.json`. Szczegóły:

```json
// konsument composer.json
{
  "repositories": [{
    "type": "vcs",
    "url": "https://github.com/Bitfloo/webfloo.git"
  }],
  "require": {
    "bitfloo/webfloo": "0.0.*"
  }
}
```

Dev webfloo który chce testować zmiany lokalnie w bitfloo-web (bez push):

```bash
cd ~/DEV/bitfloo-web
composer config repositories.webfloo path ../webfloo  # override
composer update bitfloo/webfloo
# ...iterate...
composer config --unset repositories.webfloo         # restore vcs
composer update bitfloo/webfloo
```

**Nie commituj** tego override'u.

## Structure

```
src/
  Components/     Blade (Atomic Design: Atoms, Molecules, Organisms, Sections)
  Filament/       Resources, Pages (SiteSettings, ThemeSettings, CrmDashboard, PageSettings)
  Models/         Setting, Lead, LeadActivity, LeadReminder, LeadTag, Page, Service, Project, Testimonial, Faq, Post, PostCategory, MenuItem, NewsletterSubscriber
  Traits/         HasActive, HasFeatured, HasSlug, HasSeo, Publishable, Sortable
  Services/       ThemeService (HEX to OKLCH, CSS variables)
  Http/           LeadWebhookController (API only — public-facing blog frontend is app-level)
  Support/        helpers.php (setting() function)
```

## Adding a Model

1. PHP class in `src/Models/` with `@property` PHPDoc, `$fillable`, `$casts`
2. Scopes: `scopeActive()`, `scopeOrdered()`, optionally `scopeFeatured()`
3. Migration in `database/migrations/` with `down()` method
4. Standard columns: `is_active` default true, `sort_order` default 0

## Adding a Blade Component

1. Determine level: Atom / Molecule / Organism / Section
2. PHP class in `src/Components/{Level}/` extending `Illuminate\View\Component`
3. Blade view in `resources/views/components/{level}/`
4. Auto-registers as `<x-webfloo-name />`
5. Props: camelCase in PHP, kebab-case in Blade

## Adding a Filament Resource

1. Class in `src/Filament/Resources/`
2. Follow Filament v5 imports (see root CLAUDE.md)
3. Policy in `app/Policies/` (project-level)
4. Run `make artisan cmd="shield:generate --all --panel=admin"`

## Adding a PageSettings Page

Extend `AbstractPageSettings` (handles boilerplate, locale-aware save/load):

1. Create class in `src/Filament/Pages/PageSettings/` extending `AbstractPageSettings`
2. Define: `settingsPrefix()`, `notificationBody()`, `nonTranslatableKeys()`
3. Use `$this->getSetting('prefix.key')` in `mount()` -- locale-aware automatically
4. Non-translatable keys (files, booleans, numbers) go in `nonTranslatableKeys()`
5. Add flag in `config/bitfloo.php` under `pages`
6. Reference: `HomePageSettings.php`, `ContactPageSettings.php`

## Component Usage

```blade
{{-- Atoms --}}
<x-webfloo-button variant="primary" size="lg">Click me</x-webfloo-button>
<x-webfloo-badge color="success">New</x-webfloo-badge>
<x-webfloo-heading :level="2">Title</x-webfloo-heading>
<x-webfloo-icon name="check" size="md" />
<x-webfloo-text size="lg" color="muted">Description</x-webfloo-text>

{{-- Molecules --}}
<x-webfloo-card title="Title" subtitle="Subtitle">Content</x-webfloo-card>
<x-webfloo-service-card icon="code-bracket" title="Web Dev" description="..." />
<x-webfloo-project-card title="Project" slug="project-1" category="Web" />

{{-- Organisms --}}
<x-webfloo-header :navigation="[['label' => 'Home', 'href' => '/']]" cta-text="Contact" cta-href="#contact" />
<x-webfloo-footer />

{{-- Sections --}}
<x-webfloo-hero title="Welcome" cta-text="Start" cta-href="/" />
<x-webfloo-services :services="$services" :columns="3" description="Our services" />
<x-webfloo-about :features="[...]" :stats="[...]" />
<x-webfloo-portfolio :projects="$projects" :show-filters="false" />
<x-webfloo-testimonials :testimonials="[...]" />
<x-webfloo-faq :items="[['question' => '...', 'answer' => '...']]" />
<x-webfloo-cta title="Ready?" :primary-cta="['text' => 'Contact', 'href' => '#']" variant="gradient" />
<x-webfloo-contact id="contact" />

```

## Component Props - Critical Rules

| Component | Prop Format | Example |
|-----------|-------------|---------|
| **Header** | `cta-text`, `cta-href` (strings) | `cta-text="Contact"` |
| **Hero** | `cta-text`, `cta-href` (strings) | `cta-text="Start"` |
| **CTA** | `:primary-cta` (array) | `:primary-cta="['text' => '...', 'href' => '...']"` |
| **FAQ** | `:items` (NOT `:faqs`) | `:items="[...]"` |
| **Services** | has `description` prop | `description="..."` |
| **Portfolio** | `:show-filters` (kebab-case) | `:show-filters="false"` |

**Why this matters:** Passing wrong prop format causes `trim(): array given` errors because undefined props go to `$attributes` bag.

## Blade Pitfalls

| Error | Cause | Fix |
|-------|-------|-----|
| `trim(): array given` | Passing array to string prop | Check PHP class for exact prop names/types |
| `Unable to locate component` | Wrong path or missing registration | Use `x-webfloo-name` format |
| Component not found | `x-webfloo::path.name` syntax | Use `x-webfloo-name` (flat, no colons/dots) |
| Layout not found | Layout in `views/layouts/` | Move to `views/components/layouts/` |
| `isNotEmpty() on array` | `$var->isNotEmpty()` on array | Use `!empty($var)` |

## Props Naming Convention

```php
// PHP Component (camelCase)
public ?string $ctaText = null;

// Blade Usage (kebab-case)
<x-component cta-text="..." />
```

Before using any component: read PHP class for prop names/types, match format (strings vs arrays), use kebab-case.

## Rules

- No emoji
- No hardcoded bitfloo.com content -- use `setting()` with generic defaults
- Use `webfloo::` view prefix
- Always include `down()` in migrations
- Follow Filament v5 API (see root CLAUDE.md for exact imports)

## Agenci spoza zakresu

Nie używaj `cbc:*` ani `plugin-dev:*` w tym repo — webfloo to Laravel Composer
package, nie plugin Claude Code ani źródło pluginu CBC.

- Dla pracy nad CBC: `~/DEV/cbc/`
- Dla pracy nad pluginami Claude Code: użyj `plugin-dev:*` w odpowiednim repo pluginu
- Dla webfloo: używaj agentów ogólnych (`feature-dev:code-reviewer`,
  `feature-dev:code-architect`, `cbc:code-guardian`) oraz webfloo-specyficznych:
  - `webfloo-laravel-auditor` — invarianty Laravel/Model/Migration/Filament
  - `blade-atomic-validator` — Atomic Design + naming
  - `filament-resource-tester` — testy PHPUnit dla Filament Resources
  - `livewire-interaction-tester` — testy interakcji Livewire/Filament
  - `policy-coverage-auditor` — audyt pokrycia uprawnień (spatie/permission)
  - `translatable-field-guardian` — testy spatie/laravel-translatable
