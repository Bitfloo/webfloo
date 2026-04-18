# Changelog

Wszystkie istotne zmiany dokumentowane w tym pliku.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Wersjonowanie: [Semantic Versioning](https://semver.org/).

Od v0.1.0 ten plik jest **automatycznie generowany** przez [release-please](https://github.com/googleapis/release-please)
na podstawie [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/). Manualne zmiany powyżej sekcji
"Pre-release history" będą nadpisywane.

---

<!-- release-please inserts new entries here -->

## 0.0.1 (2026-04-18)


### Features

* **crm:** kanban column pagination — cap 25 leadów per status + "Pokaż więcej" ([c866939](https://github.com/Bitfloo/webfloo/commit/c866939de971cdffef54a5c60c1eed2dcea9772f))
* **dist:** release-please + vcs distribution + ADR-011 + ADR-012 ([592384e](https://github.com/Bitfloo/webfloo/commit/592384e46035e385a593c2f8c056ee6bef34996e))
* **factories:** add inactive() and withRating() states to TestimonialFactory ([eb283b9](https://github.com/Bitfloo/webfloo/commit/eb283b9e031cbcdf15a140811589571cb06a2d66))
* **factories:** add model factories for 10 Models + HasFactory trait ([9494719](https://github.com/Bitfloo/webfloo/commit/9494719d9f936558684d8a6c73188a3c94e07eaa))
* **factories:** add ServiceFactory and wire HasFactory on Service ([f7072ae](https://github.com/Bitfloo/webfloo/commit/f7072ae3376e11f8daaa18de92225c5efa7457db))
* **i18n:** D2 wave 2 — wrap admin UI labels w __() na 11 Resources ([4a5ef6d](https://github.com/Bitfloo/webfloo/commit/4a5ef6d18b1168abc220ef952ab1cb15f8785f34))
* **modules:** logical module registry + ModuleRegistry helper + docs ([5da3f4a](https://github.com/Bitfloo/webfloo/commit/5da3f4a705b318d4e6cc8094ecb51c9b185706a8))
* **panel:** WebflooPanel Filament plugin + viewer role + dev harness ([677703f](https://github.com/Bitfloo/webfloo/commit/677703f7a68f41ab4fc93a72ccae3a115a982b80))
* **perf:** D5 GenerateSitemap cursor + D4 FULLTEXT index (partial) na posts ([e20764b](https://github.com/Bitfloo/webfloo/commit/e20764b96960a303d7271ea0ee2fcd579bdf7363))


### Bug Fixes

* **factory:** remove redundant new() state on LeadFactory ([e0c2aa0](https://github.com/Bitfloo/webfloo/commit/e0c2aa0ed1430a9bc168a6ca7755eac4e194743c))
* **hardening:** M2 — logo FileUpload explicit MIME whitelist ([cb5dae4](https://github.com/Bitfloo/webfloo/commit/cb5dae4cfd88c57e750c80cb907142cd9b74c86e))
* **migrations:** add SQLite driver fallbacks for MySQL-only schema queries ([0c6c4a4](https://github.com/Bitfloo/webfloo/commit/0c6c4a47341758e9952660713c385f60cae4b9ce))
* **stan:** resolve 8 phpstan L10 errors — CrmDashboard + GenerateSitemap ([fa8e5d5](https://github.com/Bitfloo/webfloo/commit/fa8e5d55ca633b62ef1e870e7417fff68aa55bf5))


### Miscellaneous Chores

* **agents:** audit testerskich agentów — REMOVE livewire-interaction-tester, IMPROVE 3 pozostałe ([c63ac29](https://github.com/Bitfloo/webfloo/commit/c63ac299facd1d8c863362bd9386a5e908323686))
* **autoload:** register Webfloo\\Database\\Factories PSR-4 namespace ([f1d540b](https://github.com/Bitfloo/webfloo/commit/f1d540b875a1d730c13f62aac5f326fe22f5f640))
* **claude:** clarify agents addition in 7a37490 ([baaa87d](https://github.com/Bitfloo/webfloo/commit/baaa87d34b5afba3d18d0f9dab8a28eaa982f4c1))
* **cleanup:** A — 1 item — remove Filament scaffold // placeholder in PageResource.getRelations ([9ea9e1f](https://github.com/Bitfloo/webfloo/commit/9ea9e1fb90a010085cbbb4bc9dbddb4d4c5bc38b))
* **cleanup:** KISS/SSOT/slop sweep on recent changes ([3d18307](https://github.com/Bitfloo/webfloo/commit/3d18307404b4b9a4eab18a39e408b7e3d9d83a13))
* **cleanup:** summary — Batch E DRAFT + final report ([0952500](https://github.com/Bitfloo/webfloo/commit/0952500211a8876587ae41799278b769046a8d56))
* final polish — CODEOWNERS + PR template + SECURITY + commit template ([0861fcd](https://github.com/Bitfloo/webfloo/commit/0861fcd0a5b23fc08be314623584b931d547fafa))
* **gitignore:** exclude local audit/state markdown scratchpads ([c14d556](https://github.com/Bitfloo/webfloo/commit/c14d55615ca6a9044e2051149b37abccecd879fa))
* **gitignore:** ignore .cbc-bridge.md + .cbc-push-gate/ ([4164af0](https://github.com/Bitfloo/webfloo/commit/4164af0e34d6a9f89c1924b045e8e98a19727a65))
* **hooks:** commit-msg Conventional Commits validator + install script ([7a37490](https://github.com/Bitfloo/webfloo/commit/7a374908163c4985a30280c04fa3a84beef84cc3))
* **release:** patch-only bumps in 0.x ([467c34d](https://github.com/Bitfloo/webfloo/commit/467c34d675ae05325f7e86364f87a734bf63e78e))
* **release:** restart numbering from 0.0.x baseline ([874da20](https://github.com/Bitfloo/webfloo/commit/874da203bbc8a777f29a150474296fba24d08cbb))
* **scripts:** finalize-ecosystem.sh — one-shot Phase 1 completion ([27ea614](https://github.com/Bitfloo/webfloo/commit/27ea6149b43e44f09345a4a748bce63debf7464a))
* **seed:** extract webfloo from bitfloocom-web packages/webfloo ([f05056a](https://github.com/Bitfloo/webfloo/commit/f05056afac74675dbc68d73f56a801f5c0a2241c))
* trigger initial release 0.0.1 ([460ed26](https://github.com/Bitfloo/webfloo/commit/460ed2690157748ea522c86959d3c4725f355026))

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
