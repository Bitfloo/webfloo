# Changelog

Wszystkie istotne zmiany dokumentowane w tym pliku.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Wersjonowanie: [Semantic Versioning](https://semver.org/).

Od v0.1.0 ten plik jest **automatycznie generowany** przez [release-please](https://github.com/googleapis/release-please)
na podstawie [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/). Manualne zmiany powyżej sekcji
"Pre-release history" będą nadpisywane.

---

<!-- release-please inserts new entries here -->

## [0.2.1](https://github.com/Bitfloo/webfloo/compare/v0.2.0...v0.2.1) (2026-06-12)


### Features

* add branded 404/500 error pages for the frontend module ([91ca9aa](https://github.com/Bitfloo/webfloo/commit/91ca9aa72aba4c9ff262baafeff1603c4e7c6848))
* add draft preview via temporary signed URLs (PreviewAction) ([6ebf405](https://github.com/Bitfloo/webfloo/commit/6ebf4054e22366c76e7015f92b70a0875620c802))
* add frontend module flag and consolidate fallback locale into webfloo_fallback_locale() ([e7cfc4d](https://github.com/Bitfloo/webfloo/commit/e7cfc4d2b98148b8b690847b45097950645d0fed))
* add MediaService with GD-based WebP variants (stage 1) ([6234c3b](https://github.com/Bitfloo/webfloo/commit/6234c3b1807d4bcee3e67cac427b21b658e32cee))
* add opt-in GDPR cookie consent banner ([985f034](https://github.com/Bitfloo/webfloo/commit/985f034f3d76a380181703443ec7e07c7b4249a0))
* add public Blade frontend module (routes, controllers, templates) ([9ece1c1](https://github.com/Bitfloo/webfloo/commit/9ece1c1dff278503251364630dda582e0b15e073))
* add public newsletter signup Livewire component ([b7afb7b](https://github.com/Bitfloo/webfloo/commit/b7afb7b98590b4dfbbe61984d77c025c753c7db3))
* add redirects module (404 middleware, slug-change auto-301, RedirectResource) ([a728271](https://github.com/Bitfloo/webfloo/commit/a728271cc30d9f157e939861bd8279bd609d92dd))
* add RSS feed for the blog module ([3113e4c](https://github.com/Bitfloo/webfloo/commit/3113e4c73e5fc37be411697dbaa886a798999035))
* add trash filter with restore and force-delete to Page and Post resources ([5b6af49](https://github.com/Bitfloo/webfloo/commit/5b6af49ceef59eb5e574a5b69f99698946e58864))
* add webfloo-contact-form Livewire component with honeypot and rate limit ([ce821dc](https://github.com/Bitfloo/webfloo/commit/ce821dc9ddf8427f90449f361569dc69cce7cb95))
* add webfloo:install command with demo seeder ([5c69af6](https://github.com/Bitfloo/webfloo/commit/5c69af6825eb5bf2e724e94be2332045c076b08d))
* add x-webfloo-layout frontend base layout component ([6774c75](https://github.com/Bitfloo/webfloo/commit/6774c75c590e8b47f1de253818edfb33fbe3f5ba))
* add x-webfloo-seo component rendering meta/OG/canonical head block ([df5c72b](https://github.com/Bitfloo/webfloo/commit/df5c72bf8320de9f0747f16f3eb177a5ecdb845a))
* auto-register package commands on the scheduler ([b79e5d5](https://github.com/Bitfloo/webfloo/commit/b79e5d51da7dd63e8b768703fc7c4c549de4b8ef))
* dispatch LeadCreated event and email admin via SendNewLeadNotification ([1f6cf83](https://github.com/Bitfloo/webfloo/commit/1f6cf83bf51ed3bd33236202e665f99972eaa1a8))
* make sitemap generation source-driven and host-extensible ([aa512cb](https://github.com/Bitfloo/webfloo/commit/aa512cb992c0e42be83f672b477e74462de1ec57))
* ship precompiled frontend assets (webfloo.css + bundled Alpine) ([bb232ff](https://github.com/Bitfloo/webfloo/commit/bb232ff1300c50969b0f26def804918eba23ecaf))


### Bug Fixes

* align permission gates with Shield v4 identifiers seeded by ShieldRolesSeeder ([2e67d92](https://github.com/Bitfloo/webfloo/commit/2e67d924bb952a2bf5e6a8fbeeca2b9049e305ee))
* exclude no_index posts from the RSS feed ([4f642df](https://github.com/Bitfloo/webfloo/commit/4f642df9312e6cc6b25cf75a545119d45fda9450))
* fold webfloo.pages.* flags into settings page access ([add8f2d](https://github.com/Bitfloo/webfloo/commit/add8f2df191e8eca1a7acaf5d53a57d24384c3f6))
* gate lead and newsletter PII exports behind Export permissions ([541136e](https://github.com/Bitfloo/webfloo/commit/541136e730fd588ac0538064b87f2024ac62d8d2))
* harden frontend output and install prompts (phase gate findings) ([2b153e1](https://github.com/Bitfloo/webfloo/commit/2b153e13e60a164fe474f09c1a2ce452888e05bb))
* harden redirects module per phase-gate review ([ac04bfa](https://github.com/Bitfloo/webfloo/commit/ac04bfa06ab2b7b37b23249433eec6096a6c6c29))
* parse DateTimePicker string before Lead::scheduleReminder ([9c69d4e](https://github.com/Bitfloo/webfloo/commit/9c69d4e552fde2efbda21b7b3428a0b392588b40))
* skip first-admin prompts when install runs non-interactively ([bb567aa](https://github.com/Bitfloo/webfloo/commit/bb567aaf1e8f415ccf65715717c0057e4a50b682))
* source CRM currency from config instead of hardcoded PLN ([f695d98](https://github.com/Bitfloo/webfloo/commit/f695d98c0b0a756770230870820e11a33b92bbac))
* stop sanitization from stripping rich-text markup (webfloo purifier profile) ([873173d](https://github.com/Bitfloo/webfloo/commit/873173db1f0004605f9509df5f1e9dac6ac04eab))
* translate frontend-facing strings and pin newsletter registration gating ([3d45030](https://github.com/Bitfloo/webfloo/commit/3d4503029a67c4ba308535f25737c69336595e3f))

## [0.2.0](https://github.com/Bitfloo/webfloo/compare/v0.0.1...v0.2.0) (2026-06-12)


### ⚠ BREAKING CHANGES

* minimum Laravel is now 13 (was 11/12) and minimum PHP is now 8.3 (was 8.2). Consumers on Laravel 11/12 or PHP 8.2 must upgrade.


### Features

* **deps:** upgrade to Laravel 13 — drop Laravel 11/12, raise PHP floor to 8.3, testbench ^11, phpunit ^12 ([#3](https://github.com/Bitfloo/webfloo/pull/3)) ([74799be](https://github.com/Bitfloo/webfloo/commit/74799be830c849d07b483141e38b61ea030cf1f2))

## 0.0.1 (2026-06-10)


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
* **seeder:** use Shield v4 permission identifiers in ShieldRolesSeeder ([0478c69](https://github.com/Bitfloo/webfloo/commit/0478c69790b7fe9e1333b45ec79ed6cadfefb709))
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
