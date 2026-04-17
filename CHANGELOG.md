# Changelog

Wszystkie istotne zmiany dokumentowane w tym pliku.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Wersjonowanie: [Semantic Versioning](https://semver.org/).

Od v0.1.0 ten plik jest **automatycznie generowany** przez [release-please](https://github.com/googleapis/release-please)
na podstawie [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/). Manualne zmiany powyżej sekcji
"Pre-release history" będą nadpisywane.

---

<!-- release-please inserts new entries here -->

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
