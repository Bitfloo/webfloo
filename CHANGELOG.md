# Changelog

Wszystkie istotne zmiany dokumentowane w tym pliku.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Wersjonowanie: [Semantic Versioning](https://semver.org/).

Od v0.1.0 ten plik jest **automatycznie generowany** przez [release-please](https://github.com/googleapis/release-please)
na podstawie [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/). Manualne zmiany powyżej sekcji
"Pre-release history" będą nadpisywane.

---

<!-- release-please inserts new entries here -->

## [0.1.1](https://github.com/Bitfloo/webfloo/compare/v0.1.0...v0.1.1) (2026-04-18)


### Features

* **factories:** add inactive() and withRating() states to TestimonialFactory ([eb283b9](https://github.com/Bitfloo/webfloo/commit/eb283b9e031cbcdf15a140811589571cb06a2d66))
* **factories:** add model factories for 10 Models + HasFactory trait ([9494719](https://github.com/Bitfloo/webfloo/commit/9494719d9f936558684d8a6c73188a3c94e07eaa))
* **factories:** add ServiceFactory and wire HasFactory on Service ([f7072ae](https://github.com/Bitfloo/webfloo/commit/f7072ae3376e11f8daaa18de92225c5efa7457db))


### Bug Fixes

* **factory:** remove redundant new() state on LeadFactory ([e0c2aa0](https://github.com/Bitfloo/webfloo/commit/e0c2aa0ed1430a9bc168a6ca7755eac4e194743c))
* **migrations:** add SQLite driver fallbacks for MySQL-only schema queries ([0c6c4a4](https://github.com/Bitfloo/webfloo/commit/0c6c4a47341758e9952660713c385f60cae4b9ce))


### Miscellaneous Chores

* **agents:** audit testerskich agentów — REMOVE livewire-interaction-tester, IMPROVE 3 pozostałe ([c63ac29](https://github.com/Bitfloo/webfloo/commit/c63ac299facd1d8c863362bd9386a5e908323686))
* **autoload:** register Webfloo\\Database\\Factories PSR-4 namespace ([f1d540b](https://github.com/Bitfloo/webfloo/commit/f1d540b875a1d730c13f62aac5f326fe22f5f640))
* **claude:** clarify agents addition in 7a37490 ([baaa87d](https://github.com/Bitfloo/webfloo/commit/baaa87d34b5afba3d18d0f9dab8a28eaa982f4c1))
* **gitignore:** exclude local audit/state markdown scratchpads ([c14d556](https://github.com/Bitfloo/webfloo/commit/c14d55615ca6a9044e2051149b37abccecd879fa))
* **hooks:** commit-msg Conventional Commits validator + install script ([7a37490](https://github.com/Bitfloo/webfloo/commit/7a374908163c4985a30280c04fa3a84beef84cc3))
* **release:** patch-only bumps in 0.x ([467c34d](https://github.com/Bitfloo/webfloo/commit/467c34d675ae05325f7e86364f87a734bf63e78e))

## [Unreleased]

### Infrastructure
- Distribution via Composer `type: vcs` (ADR-011)
- Release automation via release-please (Conventional Commits → auto bump + CHANGELOG)
- CI: `check.yml` (pint + phpstan + phpunit) + `release.yml` (release-please trigger)
- Smoke tests foundation: TestCase + ServiceProvider + HasActive trait

---

## Pre-release history (manual, pre-v0.1.0)

Niżej pre-release historia sprzed wdrożenia release-please. Zachowane dla audit trail.

### [1.0.0] - 2026-04-01 *(SUPERSEDED — tag removed from remote 2026-04-17, pre-release naming)*

**Status:** ten tag został USUNIĘTY z remote 2026-04-17 gdy rozpoznaliśmy że webfloo
jest w fazie pre-stable API (0.x zgodnie z semver). Zero external consumers w tym momencie,
więc destructive delete tagu był bezpieczny. Historia commitów zachowana.

Oryginalny opis (dla odniesienia historycznego):

> Pierwszy stabilny release core package.
>
> **Funkcjonalnosci**
> - 14 modeli Eloquent z reuzywalnymi traitami (HasActive, HasFeatured, HasSlug, HasSeo, Publishable, Sortable)
> - 10+ Filament Resources (Post, Project, Service, Testimonial, Faq, Page, Lead, MenuItem, PostCategory, NewsletterSubscriber)
> - PageSettings system (Home, Contact, Site, Theme) z locale-aware save/load
> - ThemeService: HEX-to-OKLCH, CSS variables, 8 base themes, WCAG AA contrast
> - CRM: Lead pipeline, activities, reminders, tags, export CSV
> - i18n: spatie/laravel-translatable, setting() helper z locale fallback, PluginTranslationRegistry
> - SEO: sitemap generator (PL+EN hreflang), HasSeo trait
> - Blade components (Atomic Design): Atoms, Molecules, Organisms, Sections
> - filament-shield role separation (super_admin + editor)
> - GDPR: consent_at na leads
>
> **Infrastruktura**
> - Filament v5 compatibility
> - Laravel 12 support
> - PHP 8.4 support
> - PHPStan level 10 (0 errors)
> - Production Dockerfile (multi-stage, no Xdebug, opcache tuned)
>
> **Usuniete**
> - Livewire ContactForm i NewsletterForm (zastapione Vue+Inertia)
> - Blade blog views (zastapione Inertia pages)

Funkcjonalności z tego "1.0.0 draft" są obecne w bieżącej bazie kodu
i zostaną zaadresowane przez release-please od v0.1.0 (wszystkie jako
pre-existing, nie re-introduced).
